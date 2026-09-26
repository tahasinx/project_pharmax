#!/usr/bin/env bash
# Locked helper for Epharma tenant vhosts and certificates.
# sudo -n /usr/local/sbin/epharma-host-provision <add|remove|status|drop-db> [--env=staging|production] <slug>
set -euo pipefail

log() { printf '[%s] %s\n' "$(date -u +%Y-%m-%dT%H:%M:%SZ)" "$*"; }
die() { log "ERROR: $*"; exit 1; }

ENV_NAME="production"
ACTION=""
SLUG=""

while [[ $# -gt 0 ]]; do
  case "$1" in
    add|remove|status|drop-db) ACTION="$1"; shift ;;
    --env=staging|--env=production) ENV_NAME="${1#--env=}"; shift ;;
    --env)
      [[ $# -ge 2 ]] || die "missing --env value"
      ENV_NAME="$2"; shift 2
      [[ "$ENV_NAME" == "staging" || "$ENV_NAME" == "production" ]] || die "env must be staging|production"
      ;;
    *)
      if [[ -z "$SLUG" ]]; then SLUG="$1"; shift; else die "unexpected arg"; fi
      ;;
  esac
done

[[ -n "$ACTION" && -n "$SLUG" ]] || die "usage: epharma-host-provision <add|remove|status|drop-db> [--env=] <slug>"
[[ "$SLUG" =~ ^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?$|^[a-z0-9]$ ]] || die "invalid slug"

BLOCKED=(admin adminx www staging stagging api app mail platform central localhost root mysql nginx certbot)
for b in "${BLOCKED[@]}"; do
  [[ "$SLUG" == "$b" ]] && die "slug is reserved: $SLUG"
done

case "$ENV_NAME" in
  staging)    CONF="/etc/epharma/host-provision.staging.env" ;;
  production) CONF="/etc/epharma/host-provision.production.env" ;;
esac
[[ -f "$CONF" ]] || die "missing config $CONF"
# shellcheck disable=SC1090
source "$CONF"
: "${APP_ROOT:?}"
: "${BASE_DOMAIN:?}"
: "${DB_PREFIX:=epharma_}"
: "${PHP_FPM_SOCK:=/run/php/php8.3-fpm.sock}"
: "${CERTBOT_EMAIL:=platform@epharma.cloud}"

[[ "$APP_ROOT" == /var/www/* ]] || die "APP_ROOT must be under /var/www"
APP_ROOT="$(readlink -f "$APP_ROOT")"
[[ -d "$APP_ROOT/public" ]] || die "public missing"
HOST="${SLUG}.${BASE_DOMAIN}"
SITE_FILE="/etc/nginx/sites-available/${HOST}"
SITE_LINK="/etc/nginx/sites-enabled/${HOST}"
WEBROOT="${APP_ROOT}/public"
ACME_DIR="${WEBROOT}/.well-known/acme-challenge"

nginx_reload() {
  nginx -t || die "nginx -t failed"
  systemctl reload nginx
}

already_served() {
  grep -Rqs --include='*' "server_name .*${HOST}" /etc/nginx/sites-enabled
}

write_ssl() {
  local cert_dir="$1"
  cat >"$SITE_FILE" <<NGX
server {
    listen 127.0.0.1:8443 ssl;
    server_name ${HOST};
    root ${WEBROOT};
    index index.php;
    charset utf-8;
    location / { try_files \$uri \$uri/ /index.php?\$query_string; }
    location ~ \\.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:${PHP_FPM_SOCK};
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }
    location ~ /\\.(?!well-known).* { deny all; }
    ssl_certificate ${cert_dir}/fullchain.pem;
    ssl_certificate_key ${cert_dir}/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;
}
server {
    listen 80;
    listen [::]:80;
    server_name ${HOST};
    location /.well-known/acme-challenge/ { root ${WEBROOT}; allow all; }
    location / { return 301 https://\$host\$request_uri; }
}
NGX
}

cmd_status() {
  local vhost=0 ssl=0
  [[ -f "$SITE_FILE" || -L "$SITE_LINK" ]] && vhost=1
  already_served && vhost=1
  [[ -f "/etc/letsencrypt/live/${HOST}/fullchain.pem" ]] && ssl=1
  log "STATUS host=${HOST} vhost=${vhost} ssl=${ssl}"
  [[ "$vhost" -eq 1 ]] || exit 2
  [[ "$ssl" -eq 1 ]] || exit 3
}

cmd_remove() {
  rm -f "$SITE_LINK" "$SITE_FILE"
  nginx_reload
  log "REMOVE done"
}

cmd_drop_db() {
  local db="${DB_PREFIX}${SLUG//-/_}"
  [[ "$db" =~ ^epharma_[a-z0-9_]+$ ]] || die "bad database"
  [[ "$db" != "epharma_central" && "$db" != "epharma_staging_central" ]] || die "refusing central database"
  mysql --batch -e "DROP DATABASE IF EXISTS \`${db}\`"
  log "DROP_DB ${db}"
}

cmd_add() {
  if already_served && [[ ! -f "$SITE_FILE" ]]; then
    log "HOST_ALREADY_PRESENT"
    if [[ -f "/etc/letsencrypt/live/${HOST}/fullchain.pem" ]]; then
      log "SSL_OK"
      exit 0
    fi
    log "SSL_FAIL existing host has no dedicated certificate"
    exit 4
  fi
  mkdir -p "$ACME_DIR"
  cat >"$SITE_FILE" <<NGX
server {
    listen 80;
    listen [::]:80;
    server_name ${HOST};
    root ${WEBROOT};
    index index.php;
    location /.well-known/acme-challenge/ { root ${WEBROOT}; allow all; }
    location / { try_files \$uri \$uri/ /index.php?\$query_string; }
    location ~ \\.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:${PHP_FPM_SOCK};
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }
    location ~ /\\.(?!well-known).* { deny all; }
}
NGX
  ln -sfn "$SITE_FILE" "$SITE_LINK"
  nginx_reload
  log "VHOST_OK"
  if ! certbot certonly --webroot -w "$WEBROOT" -d "$HOST" --non-interactive --agree-tos --email "$CERTBOT_EMAIL" --keep-until-expiring; then
    log "SSL_FAIL certbot failed"
    exit 4
  fi
  write_ssl "/etc/letsencrypt/live/${HOST}"
  nginx_reload
  log "SSL_OK"
}

case "$ACTION" in
  add) cmd_add ;;
  remove) cmd_remove ;;
  status) cmd_status ;;
  drop-db) cmd_drop_db ;;
esac
