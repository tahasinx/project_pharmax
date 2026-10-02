# Migrations layout

Only two **active** paths. Do not put new files in the root of this folder.

```
database/migrations/
├── README.md          ← you are here
├── central/           ← platform (admin) DB only
├── tenant/            ← each pharmacy company DB
└── _legacy/           ← frozen history — never run in production
```

| Folder | Connection | Command |
|--------|------------|---------|
| `central/` | `mysql_central` / central DB | `php artisan migrate --database=mysql_central --path=database/migrations/central` |
| `tenant/` | default / per-company DB | provision + platform schema upgrade, or `php artisan migrate --path=database/migrations/tenant` |
| `_legacy/` | — | Do **not** migrate. Kept only for frozen history if needed. |

## Rules

1. **Platform / SaaS change** → `database/migrations/central/`
2. **Pharmacy app change** → `database/migrations/tenant/`
3. **Never** add SQL dumps here — PHP `Schema` migrations only
4. **Never** add new files under `_legacy/` or the migrations root

```bash
php artisan make:migration create_foo_table --path=database/migrations/central
php artisan make:migration add_bar_to_medicines_table --path=database/migrations/tenant
```
