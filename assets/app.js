import './bootstrap.js';
import './styles/app.scss';

/*
 * Suivi Plaidoyer - Main JavaScript file
 * Handles interactions and enhancements for the public interface
 */

// Global utilities
window.SuiviPlaidoyer = {
    // Initialize all components
    init() {
        this.initSmoothScrolling();
        this.initTooltips();
        this.initAnimations();
        this.initSearchFilters();
        this.initLoadingStates();
        this.initDropdowns();
        console.log('Suivi Plaidoyer initialized 🤝');
    },

    // Smooth scrolling for anchor links
    initSmoothScrolling() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    },

    // Initialize Bootstrap tooltips
    initTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    },

    // Initialize dropdown hover functionality
    initDropdowns() {
        // Enhanced dropdown behavior for navbar
        const navDropdowns = document.querySelectorAll('.navbar-nav .dropdown');

        navDropdowns.forEach(dropdown => {
            const dropdownToggle = dropdown.querySelector('.dropdown-toggle');
            const dropdownMenu = dropdown.querySelector('.dropdown-menu');

            if (dropdownToggle && dropdownMenu) {
                // Show dropdown on hover
                dropdown.addEventListener('mouseenter', () => {
                    dropdownMenu.classList.add('show');
                    dropdownToggle.setAttribute('aria-expanded', 'true');
                });

                // Hide dropdown when leaving
                dropdown.addEventListener('mouseleave', () => {
                    dropdownMenu.classList.remove('show');
                    dropdownToggle.setAttribute('aria-expanded', 'false');
                });

                // Keep click functionality for mobile
                dropdownToggle.addEventListener('click', (e) => {
                    e.preventDefault();
                    const isShown = dropdownMenu.classList.contains('show');

                    // Close all other dropdowns
                    navDropdowns.forEach(otherDropdown => {
                        if (otherDropdown !== dropdown) {
                            otherDropdown.querySelector('.dropdown-menu').classList.remove('show');
                            otherDropdown.querySelector('.dropdown-toggle').setAttribute('aria-expanded', 'false');
                        }
                    });

                    // Toggle current dropdown
                    if (isShown) {
                        dropdownMenu.classList.remove('show');
                        dropdownToggle.setAttribute('aria-expanded', 'false');
                    } else {
                        dropdownMenu.classList.add('show');
                        dropdownToggle.setAttribute('aria-expanded', 'true');
                    }
                });
            }
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.navbar-nav .dropdown')) {
                navDropdowns.forEach(dropdown => {
                    dropdown.querySelector('.dropdown-menu').classList.remove('show');
                    dropdown.querySelector('.dropdown-toggle').setAttribute('aria-expanded', 'false');
                });
            }
        });
    },

    // Fade in animations for cards
    initAnimations() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Observe all cards
        document.querySelectorAll('.card, .stats-card, .category-card, .city-card, .list-card').forEach(card => {
            observer.observe(card);
        });
    },

    // Enhanced search and filter functionality
    initSearchFilters() {
        // Debounce function for search inputs
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Search functionality for cities
        const citySearch = document.getElementById('citySearch');
        if (citySearch) {
            const debouncedSearch = debounce(() => {
                this.filterCities();
            }, 300);

            citySearch.addEventListener('input', debouncedSearch);
        }
    },

    // Filter cities based on search and criteria
    filterCities() {
        const searchInput = document.getElementById('citySearch');
        const filterButtons = document.querySelectorAll('[data-filter]');
        const cityItems = document.querySelectorAll('.city-item');
        const noResults = document.getElementById('noResults');

        if (!searchInput || !cityItems.length) return;

        const searchTerm = searchInput.value.toLowerCase();
        const activeFilter = document.querySelector('[data-filter].active')?.dataset.filter || 'all';
        let visibleCount = 0;

        cityItems.forEach(item => {
            const cityName = item.dataset.cityName || '';
            const hasCommitments = item.dataset.hasCommitments === 'true';

            let showItem = true;

            // Apply search filter
            if (searchTerm && !cityName.includes(searchTerm)) {
                showItem = false;
            }

            // Apply commitment filter
            if (activeFilter === 'with-commitments' && !hasCommitments) {
                showItem = false;
            } else if (activeFilter === 'no-commitments' && hasCommitments) {
                showItem = false;
            }

            if (showItem) {
                item.style.display = 'block';
                item.classList.add('fade-in');
                visibleCount++;
            } else {
                item.style.display = 'none';
                item.classList.remove('fade-in');
            }
        });

        // Show/hide no results message
        if (noResults) {
            if (visibleCount === 0) {
                noResults.style.display = 'block';
                noResults.classList.add('fade-in');
            } else {
                noResults.style.display = 'none';
                noResults.classList.remove('fade-in');
            }
        }
    },

    // Loading states for buttons
    initLoadingStates() {
        document.querySelectorAll('.btn[data-loading]').forEach(btn => {
            btn.addEventListener('click', function() {
                if (this.dataset.loading === 'true') return;

                this.dataset.loading = 'true';
                const originalText = this.innerHTML;
                this.innerHTML = '<span class="loading-spinner me-2"></span>Chargement...';
                this.disabled = true;

                // Reset after 3 seconds (adjust based on actual loading time)
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.disabled = false;
                    this.dataset.loading = 'false';
                }, 3000);
            });
        });
    },

    // Utility function to show notifications
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(notification);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    },

    // Format numbers with French locale
    formatNumber(number) {
        return new Intl.NumberFormat('fr-FR').format(number);
    },

    // Format dates with French locale
    formatDate(date) {
        return new Intl.DateTimeFormat('fr-FR', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }).format(new Date(date));
    }
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.SuiviPlaidoyer.init();
});

// Handle navigation active states
document.addEventListener('DOMContentLoaded', () => {
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');

    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPath) {
            link.classList.add('active');
        }
    });
});
