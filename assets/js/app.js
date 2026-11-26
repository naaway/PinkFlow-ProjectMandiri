document.addEventListener('DOMContentLoaded', () => {
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Navigasi mobile
    const toggle = document.querySelector('.menu-toggle');
    const nav = document.querySelector('.nav nav');

    if (toggle && nav) {
        toggle.addEventListener('click', () => {
            nav.classList.toggle('open');
            toggle.setAttribute(
                'aria-expanded',
                nav.classList.contains('open') ? 'true' : 'false'
            );
        });
    }

    // Lenis hanya mengatur scroll; tidak menjalankan animasi saat pindah halaman.
    if (!reducedMotion && window.Lenis) {
        const lenis = new Lenis({
            duration: 1.05,
            smoothWheel: true,
            wheelMultiplier: 0.9,
            touchMultiplier: 1.15
        });

        window.pinkFlowLenis = lenis;

        const raf = (time) => {
            lenis.raf(time);
            requestAnimationFrame(raf);
        };

        requestAnimationFrame(raf);
    }

    // Sengaja tidak ada entrance animation atau perubahan teks tombol.
    // Ini mencegah kilatan saat halaman dibuka dan tombol ditekan.
});
