import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';

Alpine.plugin(intersect);

window.Alpine = Alpine;

Alpine.start();

// Lazy-load Prism.js only on pages with code blocks
if (document.querySelector('pre code')) {
    window.Prism = window.Prism || {};
    window.Prism.manual = true;

    import('prismjs').then(async ({ default: Prism }) => {
        await import('prismjs/themes/prism-okaidia.css');
        // Dependencies: markup → css, markup-templating → php; javascript → typescript
        await import('prismjs/components/prism-markup');
        await import('prismjs/components/prism-css');
        await import('prismjs/components/prism-javascript');
        await import('prismjs/components/prism-markup-templating');
        await Promise.all([
            import('prismjs/components/prism-php'),
            import('prismjs/components/prism-bash'),
            import('prismjs/components/prism-json'),
            import('prismjs/components/prism-yaml'),
            import('prismjs/components/prism-typescript'),
            import('prismjs/components/prism-sql'),
            import('prismjs/components/prism-docker'),
        ]);
        Prism.highlightAll();
    });
}
