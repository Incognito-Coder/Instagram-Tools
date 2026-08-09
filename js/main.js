if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('sw.js', { scope: './' })
            .then(reg => {
                console.log('InstaTaker ServiceWorker registered cleanly with scope:', reg.scope);
                reg.update();
            })
            .catch(err => {
                console.warn('ServiceWorker registration omitted or failed:', err);
            });
    });
}
