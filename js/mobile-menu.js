document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu elements
    const menuToggle = document.getElementById('menuToggle');
    const mainNav = document.getElementById('mainNav');
    let overlay = null;

    // Initialize mobile menu
    function initMobileMenu() {
        if (!menuToggle || !mainNav) return;

        // Create overlay for menu background
        createOverlay();

        // Add click event to toggle menu
        menuToggle.addEventListener('click', toggleMenu);

        // Add accessibility attributes
        menuToggle.setAttribute('aria-label', 'Toggle navigation menu');
        menuToggle.setAttribute('aria-expanded', 'false');
        menuToggle.setAttribute('aria-controls', 'mainNav');

        // Add scroll event listener for fixed menu toggle
        window.addEventListener('scroll', handleScroll);

        // Handle window resize
        window.addEventListener('resize', handleResize);

        // Initial check if menu fits
        checkIfMenuFits();

        // Initial position check
        handleScroll();
    }

    // Function to check if menu items fit in the available space
    function checkIfMenuFits() {
        if (!menuToggle || !mainNav) return;

        // Get header width
        const header = document.querySelector('header');
        if (!header) return;

        const headerWidth = header.offsetWidth;
        const logoContainer = header.querySelector('.logo-container');
        const logoWidth = logoContainer ? logoContainer.offsetWidth + 30 : 0; // Add some margin

        // Calculate available space
        const availableSpace = headerWidth - logoWidth - 40; // 40px for padding

        // Get total width of all nav items
        let totalNavWidth = 0;
        const navItems = mainNav.querySelectorAll('ul > li');

        navItems.forEach(item => {
            // Temporarily make the item visible to measure it
            const originalDisplay = item.style.display;
            item.style.display = 'block';
            totalNavWidth += item.offsetWidth;
            item.style.display = originalDisplay;
        });

        // Determine if menu fits
        const menuFits = totalNavWidth <= availableSpace;

        // Show/hide hamburger based on fit
        if (menuFits && window.innerWidth > 600) { // Still use hamburger on very small screens
            // Menu fits, use normal navigation
            menuToggle.style.display = 'none';
            mainNav.classList.remove('mobile-hidden');
            mainNav.classList.remove('menu-visible');
            mainNav.classList.add('horizontal-nav');

            // Hide overlay if it's showing
            if (overlay) {
                overlay.style.opacity = '0';
                setTimeout(() => {
                    overlay.style.display = 'none';
                }, 300);
            }
        } else {
            // Menu doesn't fit, use hamburger
            menuToggle.style.display = 'flex';
            mainNav.classList.add('mobile-hidden');
            mainNav.classList.remove('horizontal-nav');
        }
    }

    // Function to handle scroll behavior
    function handleScroll() {
        if (!menuToggle) return;

        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const headerHeight = document.querySelector('header').offsetHeight;

        // Fix the button to the viewport when scrolled past header
        if (scrollTop > headerHeight - 60) {
            menuToggle.classList.add('menu-toggle-fixed');
        } else {
            menuToggle.classList.remove('menu-toggle-fixed');
        }
    }

    // Function to create overlay for menu background
    function createOverlay() {
        overlay = document.createElement('div');
        overlay.className = 'menu-overlay';
        overlay.style.position = 'fixed';
        overlay.style.top = '0';
        overlay.style.left = '0';
        overlay.style.right = '0';
        overlay.style.bottom = '0';
        overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.7)';
        overlay.style.zIndex = '90';
        overlay.style.display = 'none';
        overlay.style.opacity = '0';
        overlay.style.transition = 'opacity 0.3s ease';

        document.body.appendChild(overlay);

        // Add click event to close menu when overlay is clicked
        overlay.addEventListener('click', function() {
            closeMenu();
        });
    }

    // Function to toggle menu open/closed
    function toggleMenu() {
        const isHidden = mainNav.classList.contains('mobile-hidden');

        if (isHidden) {
            openMenu();
        } else {
            closeMenu();
        }
    }

    // Function to open menu
    function openMenu() {
        // Remove mobile-hidden class
        mainNav.classList.remove('mobile-hidden');

        // Add visible class for CSS transitions
        mainNav.classList.add('menu-visible');

        // Show overlay with fade-in
        if (overlay) {
            overlay.style.display = 'block';
            setTimeout(function() {
                overlay.style.opacity = '1';
            }, 10);
        }

        // Change toggle button appearance
        if (menuToggle) {
            menuToggle.setAttribute('aria-expanded', 'true');
            menuToggle.innerHTML = '&times;'; // Change to X
        }
    }

    // Function to close menu
    function closeMenu() {
        // Remove visible class
        mainNav.classList.remove('menu-visible');

        // Add mobile-hidden class
        mainNav.classList.add('mobile-hidden');

        // Hide overlay with fade-out
        if (overlay) {
            overlay.style.opacity = '0';
            setTimeout(function() {
                overlay.style.display = 'none';
            }, 300);
        }

        // Restore toggle button appearance
        if (menuToggle) {
            menuToggle.setAttribute('aria-expanded', 'false');
            menuToggle.innerHTML = '☰'; // Change back to hamburger
        }
    }

    // Function to handle window resize
    function handleResize() {
        // Check if menu fits in the available space
        checkIfMenuFits();

        // If we're in mobile view and menu is not currently visible
        if (menuToggle.style.display !== 'none' && mainNav && !mainNav.classList.contains('menu-visible')) {
            mainNav.classList.add('mobile-hidden');
        }

        // Hide overlay when switching to desktop
        if (menuToggle.style.display === 'none' && overlay) {
            overlay.style.display = 'none';
        }
    }

    // Initialize the mobile menu
    initMobileMenu();
});
