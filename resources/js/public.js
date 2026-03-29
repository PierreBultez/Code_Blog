// Async font loading (CSP-safe replacement for inline onload handlers)
document.querySelectorAll('link[data-async-font]').forEach(link => {
    if (link.sheet) {
        link.media = 'all';
    } else {
        link.addEventListener('load', () => { link.media = 'all'; });
    }
});

// Register Alpine components via alpine:init — works regardless of Alpine source (npm or Livewire)
document.addEventListener('alpine:init', () => {

    // Animated counter with requestAnimationFrame
    Alpine.data('animateCounter', (target) => ({
        count: 0,
        target: target,
        start() {
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                this.count = this.target;
                return;
            }
            const startTime = performance.now();
            const duration = 1500;
            const step = (now) => {
                const progress = Math.min((now - startTime) / duration, 1);
                this.count = Math.floor(progress * this.target);
                if (progress < 1) requestAnimationFrame(step);
            };
            step(startTime);
        },
    }));

    // Dark mode toggle with localStorage persistence
    Alpine.data('darkModeToggle', () => ({
        dark: localStorage.getItem('theme') === 'dark',
        init() {
            this.$watch('dark', (val) => {
                localStorage.setItem('theme', val ? 'dark' : 'light');
                document.documentElement.classList.toggle('dark', val);
            });
        },
        toggle() {
            this.dark = !this.dark;
        },
    }));

});

// Load Alpine CSP build only on pages without Livewire (Livewire provides its own Alpine)
if (!document.querySelector('[wire\\:id], [wire\\:snapshot]')) {
    Promise.all([
        import('@alpinejs/csp'),
        import('@alpinejs/intersect'),
    ]).then(([{ default: Alpine }, { default: intersect }]) => {
        Alpine.plugin(intersect);
        window.Alpine = Alpine;
        Alpine.start();
    });
}

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
