import fs from 'node:fs';
import postcss from 'postcss';

const jobs = [
    ['public/minia/assets/css/bootstrap.min.css', 'public/minia/assets/css/bootstrap.scoped.css'],
    ['public/minia/assets/css/icons.min.css', 'public/minia/assets/css/icons.scoped.css'],
    ['public/minia/assets/css/app.min.css', 'public/minia/assets/css/app.scoped.css'],
];

const keyframeNames = new Set(['keyframes', '-webkit-keyframes', '-moz-keyframes', '-o-keyframes']);

function prefixSelector(selector) {
    const parts = selector.split(',').map((part) => {
        const item = part.trim();
        if (item.startsWith('body.minia-app')) {
            return item;
        }
        if (item === ':root' || item.startsWith(':root ') || item.startsWith(':root,') || item.startsWith(':root.') || item.startsWith(':root:') || item.startsWith(':root[')) {
            return item.replace(':root', 'body.minia-app');
        }
        if (item === 'html' || item.startsWith('html ') || item.startsWith('html.') || item.startsWith('html:') || item.startsWith('html[') || item.startsWith('html,')) {
            return item.replace(/^html/, 'body.minia-app');
        }
        if (item === 'body' || /^body([\s.:>[#]|$)/.test(item)) {
            return item.replace(/^body/, 'body.minia-app');
        }
        return `body.minia-app ${item}`;
    });
    return parts.join(',\n');
}

for (const [source, target] of jobs) {
    const root = postcss.parse(fs.readFileSync(source, 'utf8'));
    root.walkRules((rule) => {
        if (rule.parent?.type === 'atrule' && keyframeNames.has(rule.parent.name)) {
            return;
        }
        rule.selector = prefixSelector(rule.selector);
    });
    fs.writeFileSync(target, root.toString());
    console.log(`${target} ${fs.statSync(target).size}`);
}
