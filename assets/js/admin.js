document.addEventListener('DOMContentLoaded', () => {
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (!reducedMotion && window.Lenis) {
        const lenis = new Lenis({ duration: 0.9, smoothWheel: true });
        window.pinkFlowAdminLenis = lenis;
        const raf = (time) => { lenis.raf(time); requestAnimationFrame(raf); };
        requestAnimationFrame(raf);
    }
    if (!reducedMotion && window.gsap) {
        gsap.from('.sidebar', { x: -16, opacity: 0, duration: 0.55, ease: 'power2.out' });
        gsap.from('.admin-main > *', { y: 16, opacity: 0, duration: 0.55, stagger: 0.08, delay: 0.08, ease: 'power2.out' });
    }
});
