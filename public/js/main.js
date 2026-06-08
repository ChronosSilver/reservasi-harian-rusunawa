// Script untuk Dropdown Profil
document.addEventListener('DOMContentLoaded', function() {
    const profileBtn = document.getElementById('profileBtn');
    const dropdownMenu = document.getElementById('dropdownMenu');
    
    if(profileBtn && dropdownMenu) {
        profileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdownMenu.classList.toggle('show');
            const isExpanded = profileBtn.getAttribute('aria-expanded') === 'true' || false;
            profileBtn.setAttribute('aria-expanded', !isExpanded);
        });

        // Tutup dropdown jika klik di luar
        document.addEventListener('click', function(e) {
            if (!profileBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.classList.remove('show');
                profileBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // Script untuk Hide/Unhide Password
    const passwordToggles = document.querySelectorAll('.password-toggle');
    passwordToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const eyeIcon = this.querySelector('.eye-icon');
            const eyeOffIcon = this.querySelector('.eye-off-icon');

            if (input.type === 'password') {
                input.type = 'text';
                eyeIcon.style.display = 'none';
                eyeOffIcon.style.display = 'block';
            } else {
                input.type = 'password';
                eyeIcon.style.display = 'block';
                eyeOffIcon.style.display = 'none';
            }
        });
    });

    // Script untuk Mobile Menu Toggle (Hamburger)
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const navLinksContainer = document.getElementById('navLinks');

    if (mobileMenuToggle && navLinksContainer) {
        mobileMenuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            navLinksContainer.classList.toggle('active');
        });

        // Tutup menu mobile jika klik di luar navbar
        document.addEventListener('click', function(e) {
            const isClickInsideNavbar = e.target.closest('.navbar');
            if (!isClickInsideNavbar && navLinksContainer.classList.contains('active')) {
                navLinksContainer.classList.remove('active');
            }
        });

        // Tutup menu setelah klik salah satu link (khusus link anchor /#)
        const navItems = navLinksContainer.querySelectorAll('.nav-item');
        navItems.forEach(function(item) {
            item.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    navLinksContainer.classList.remove('active');
                }
            });
        });
    }

    // Script untuk Clear Input (Nama, Email)
    const inputWrappers = document.querySelectorAll('.input-wrapper');
    inputWrappers.forEach(function(wrapper) {
        const input = wrapper.querySelector('input');
        const clearBtn = wrapper.querySelector('.clear-input');

        if (input && clearBtn) {
            // Tampilkan tombol clear jika input ada isinya (saat diload)
            if (input.value.length > 0) {
                clearBtn.style.display = 'block';
            }

            // Event listener saat user mengetik
            input.addEventListener('input', function() {
                if (this.value.length > 0) {
                    clearBtn.style.display = 'block';
                } else {
                    clearBtn.style.display = 'none';
                }
            });

            // Event listener saat tombol clear di klik
            clearBtn.addEventListener('click', function() {
                input.value = '';
                clearBtn.style.display = 'none';
                input.focus(); // kembalikan fokus kursor ke input
            });
        }
    });

    // Script untuk ScrollSpy (Navigasi Beranda & Katalog Kamar)
    if (window.location.pathname === '/') {
        const sections = document.querySelectorAll('section');
        const navLinks = document.querySelectorAll('.nav-links .nav-item');

        window.addEventListener('scroll', () => {
            let current = 'beranda'; // Default section

            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                // Offset 150px agar menu berubah sebelum section menyentuh batas atas secara penuh
                if (window.pageYOffset >= (sectionTop - 150)) {
                    const sectionId = section.getAttribute('id');
                    if (sectionId) {
                        current = sectionId;
                    }
                }
            });

            navLinks.forEach(link => {
                link.classList.remove('active');
                
                // Cek href dari link untuk mencocokkan dengan ID section saat ini
                const href = link.getAttribute('href');
                if (current === 'beranda' && href === '/#beranda') {
                    link.classList.add('active');
                } else if (current === 'kamar' && href === '/#kamar') {
                    link.classList.add('active');
                }
            });

            // Pastikan jika berada di paling atas, Beranda yang aktif
            if (window.pageYOffset < 50) {
                navLinks.forEach(link => link.classList.remove('active'));
                const homeLink = document.querySelector('.nav-links .nav-item[href="/#beranda"]');
                if (homeLink) homeLink.classList.add('active');
            }
        });

        // Perlakuan spesial untuk tombol Beranda: cegah reload, ganti jadi smooth scroll ke atas
        const homeLink = document.querySelector('.nav-links .nav-item[href="/#beranda"]');
        if (homeLink) {
            homeLink.addEventListener('click', function(e) {
                e.preventDefault(); // Mencegah browser melakukan reload
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }
        
        // Animasi Scroll (Intersection Observer)
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.15 // Memicu animasi ketika 15% elemen masuk layar
        };

        const scrollObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                // Tambahkan is-visible saat masuk layar, hilangkan saat keluar layar (agar animasi bisa berulang)
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                } else {
                    entry.target.classList.remove('is-visible');
                }
            });
        }, observerOptions);

        const animatedElements = document.querySelectorAll('.animate-on-scroll');
        animatedElements.forEach(el => scrollObserver.observe(el));
    }
});
