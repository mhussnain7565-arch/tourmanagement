document.addEventListener("DOMContentLoaded", function () {
    
    // 1. Elements
    const sidebar = document.getElementById("sidebar");
    const toggleBtn = document.getElementById("toggle-sidebar");

    // 1. THEME LOGIC (Synchronized)
    // ---------------------------------------------------------
    
    function applyTheme(themeName) {
        const html = document.documentElement;
        html.setAttribute('data-bs-theme', themeName);
        localStorage.setItem("theme", themeName);

        // Update Theme Icons
        const themeIcon = document.querySelector("#theme-icon");
        if (themeIcon) {
            themeIcon.className = themeName === 'dark' ? "bi bi-sun-fill" : "bi bi-moon-stars-fill";
        }
    }

    // Toggle Button Click Event
    const themeToggle = document.getElementById("theme-toggle");
    if (themeToggle) {
        themeToggle.addEventListener("click", function(e) {
            e.preventDefault();
            const currentTheme = document.documentElement.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            applyTheme(newTheme);
        });
    }

    // Initialize on Load
    const savedTheme = localStorage.getItem("theme") || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    applyTheme(savedTheme);

    // ---------------------------------------------------------
    // 2. SIDEBAR COLLAPSE LOGIC (Keep existing working logic)
    // ---------------------------------------------------------
    if (toggleBtn && sidebar) {
        // Check saved state
        if (localStorage.getItem("sidebar-state") === "collapsed") {
            sidebar.classList.add("collapsed");
        }

        toggleBtn.addEventListener("click", function () {
            sidebar.classList.toggle("collapsed");
            
            if (sidebar.classList.contains("collapsed")) {
                localStorage.setItem("sidebar-state", "collapsed");
                // Close open submenus
                document.querySelectorAll('#sidebar .collapse.show').forEach(function(el) {
                    var bsCollapse = bootstrap.Collapse.getInstance(el);
                    if (bsCollapse) bsCollapse.hide();
                    else new bootstrap.Collapse(el).hide();
                });
            } else {
                localStorage.setItem("sidebar-state", "expanded");
            }
        });
    }
});