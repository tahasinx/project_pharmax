/**
 * Apply brand theme tokens to the document (accent, shape, typography).
 * Used by ThemeHost (shared Inertia props) and platform Settings live preview.
 */
export function applyBrandTheme(theme = {}) {
    if (typeof document === 'undefined') {
        return;
    }

    const root = document.documentElement;
    const body = document.body;
    const brand = {
        primary: '#5156be',
        primary_rgb: '81, 86, 190',
        font_family: 'IBM Plex Sans',
        font_href: '',
        font_size: 16,
        font_weight: 400,
        radius: '10px',
        shape: 'rounded',
        ...theme,
    };

    const hexToRgb = (hex) => {
        const clean = String(hex || '').replace('#', '');
        if (!/^[0-9a-fA-F]{6}$/.test(clean)) {
            return '81, 86, 190';
        }
        return [
            parseInt(clean.slice(0, 2), 16),
            parseInt(clean.slice(2, 4), 16),
            parseInt(clean.slice(4, 6), 16),
        ].join(', ');
    };

    const primary = brand.primary || '#5156be';
    const rgb = brand.primary_rgb || hexToRgb(primary);
    const baseWeight = Number(brand.font_weight || 400);
    // Emphasis steps stay relative — never jump a 400 setting to feel like 600 everywhere.
    const medium = Math.min(900, baseWeight + 100);
    const strong = Math.min(900, baseWeight + 200);
    const radius = brand.radius || '10px';
    const family = brand.font_family || 'IBM Plex Sans';
    const size = `${brand.font_size || 16}px`;
    const weight = String(baseWeight);
    const shape = brand.shape || 'rounded';
    const stack = `"${family}", sans-serif`;

    const pairs = {
        '--pf-accent': primary,
        '--pf-accent-rgb': rgb,
        '--pf-primary': primary,
        '--pf-font': stack,
        '--pf-size': size,
        '--pf-weight': weight,
        '--pf-weight-medium': String(medium),
        '--pf-weight-strong': String(strong),
        '--pf-radius': radius,
        '--bs-primary': primary,
        '--bs-primary-rgb': rgb,
        '--bs-link-color': primary,
        '--bs-link-hover-color': primary,
        '--bs-border-radius': radius,
        '--bs-border-radius-sm': radius,
        '--bs-border-radius-lg': radius,
        '--bs-body-font-family': stack,
        '--bs-font-sans-serif': stack,
        '--bs-sidebar-menu-item-active-color': primary,
        '--bs-sidebar-menu-sub-item-active-color': primary,
        '--bs-sidebar-menu-item-active-bg-color': `rgba(${rgb}, 0.12)`,
        '--bs-topbar-user-bg': primary,
    };

    Object.entries(pairs).forEach(([key, value]) => {
        root.style.setProperty(key, value);
        body.style.setProperty(key, value);
    });

    root.style.fontSize = size;
    root.style.fontFamily = stack;
    body.style.fontSize = size;
    body.style.fontWeight = weight;
    body.style.fontFamily = stack;

    root.setAttribute('data-app-shape', shape);
    body.setAttribute('data-app-shape', shape);

    const href = String(brand.font_href || '').trim();
    let link = document.getElementById('epharma-font');
    if (href) {
        if (!link) {
            link = document.createElement('link');
            link.id = 'epharma-font';
            link.rel = 'stylesheet';
            document.head.appendChild(link);
        }
        if (link.href !== href) {
            link.href = href;
        }
    } else if (link) {
        link.remove();
    }

    // Keep document chrome in sync when identity/theme props refresh (Inertia).
    if (brand.app_name) {
        document.title = String(brand.app_name);
        const meta = document.querySelector('meta[name="app-name"]');
        if (meta) {
            meta.setAttribute('content', String(brand.app_name));
        }
    }
    if (brand.favicon) {
        const icon = document.querySelector('link[rel="icon"]');
        if (icon) {
            icon.setAttribute('href', String(brand.favicon));
        }
    }
}

export function shapeRadius(shape) {
    if (shape === 'flat') return '0px';
    if (shape === 'default') return '4px';
    return '10px';
}
