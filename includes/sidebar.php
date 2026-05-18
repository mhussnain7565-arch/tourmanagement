<style>
    /* Premium Sidebar - Dynamic Theme Support */
    .app-sidebar {
        background: var(--app-sidebar-bg) !important;
        backdrop-filter: var(--app-glass-blur);
        -webkit-backdrop-filter: var(--app-glass-blur);
        border-right: 1px solid var(--app-sidebar-border) !important;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        color: var(--app-sidebar-text) !important;
    }

    .sidebar-brand {
        background: rgba(var(--app-primary-blue-rgb), 0.03) !important;
        border-bottom: 1px solid var(--app-sidebar-border) !important;
        padding: 1.5rem 1rem !important;
    }

    .brand-text {
        font-weight: 700 !important;
        letter-spacing: -0.5px;
        background: linear-gradient(135deg, var(--app-primary-blue) 0%, var(--app-accent-blue) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    [data-bs-theme="dark"] .brand-text {
        background: linear-gradient(135deg, #fff 0%, #cbd5e1 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .nav-link {
        border-radius: 12px !important;
        margin: 0.2rem 0.8rem !important;
        padding: 0.8rem 1rem !important;
        transition: all 0.3s ease !important;
        border: 1px solid transparent !important;
        color: var(--app-sidebar-text) !important;
    }

    .nav-link:hover {
        background: var(--app-nav-hover-bg) !important;
        transform: translateX(5px);
        color: var(--app-primary-blue) !important;
    }

    .nav-link.active {
        background: var(--app-nav-active-bg) !important;
        border: 1px solid rgba(var(--app-primary-blue-rgb), 0.2) !important;
        color: var(--app-primary-blue) !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .nav-icon {
        font-size: 1.2rem !important;
        margin-right: 12px !important;
        transition: transform 0.3s ease;
        color: var(--app-sidebar-text);
        opacity: 0.7;
    }

    .nav-link:hover .nav-icon, .nav-link.active .nav-icon {
        transform: scale(1.1);
        color: var(--app-primary-blue) !important;
        opacity: 1;
    }

    .sidebar-menu p {
        font-weight: 500;
        font-size: 0.95rem;
    }

    /* Custom Scrollbar */
    .sidebar-wrapper::-webkit-scrollbar {
        width: 4px;
    }
    .sidebar-wrapper::-webkit-scrollbar-thumb {
        background: var(--app-sidebar-border);
        border-radius: 10px;
    }
</style>

<aside class="app-sidebar shadow">
    <div class="sidebar-brand">
        <a href="<?= BASE_URL ?>index.php" class="brand-link d-flex align-items-center">
            <img src="<?= BASE_URL ?>assets/img/logo.png" alt="Logo" class="brand-image shadow-sm rounded-circle me-2" style="width: 35px; height: 35px; object-fit: cover;">
            <span class="brand-text h4 mb-0 fw-bold">Tour Management</span>
        </a>
    </div>
    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">

                <?php
                function buildMenu($pdo, $parentId = 0, $userRole, $currentUrl)
                {
                    // Fetch pages visible to this role
                    $sql = "
                        SELECT p.* FROM sys_pages p
                        JOIN role_access ra ON p.id = ra.page_id
                        WHERE p.parent_id = ? AND ra.role_key = ?
                        ORDER BY p.sort_order ASC
                    ";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$parentId, $userRole]);
                    $items = $stmt->fetchAll();

                    foreach ($items as $item) {
                        // Check children
                        $childStmt = $pdo->prepare("SELECT COUNT(*) FROM sys_pages WHERE parent_id = ?");
                        $childStmt->execute([$item['id']]);
                        $hasChildren = $childStmt->fetchColumn() > 0;

                        // Check Active State (Logic: Is current URL this page OR a child of this page?)
                        // Simplify: Check if URL matches
                        $isActive = (strpos($currentUrl, $item['page_url']) !== false && $item['page_url'] !== '#');
                        $menuOpen = $isActive ? 'menu-open' : '';
                        $activeClass = $isActive ? 'active' : '';

                        echo '<li class="nav-item ' . $menuOpen . '">';
                        echo '<a href="' . ($hasChildren ? '#' : BASE_URL . $item['page_url']) . '" class="nav-link ' . $activeClass . '">';
                        echo '<i class="nav-icon ' . $item['icon_class'] . '"></i>';
                        echo '<p>' . htmlspecialchars($item['page_name']);
                        if ($hasChildren) {
                            echo '<i class="nav-arrow bi bi-chevron-right"></i>';
                        }
                        echo '</p></a>';

                        if ($hasChildren) {
                            echo '<ul class="nav nav-treeview">';
                            buildMenu($pdo, $item['id'], $userRole, $currentUrl);
                            echo '</ul>';
                        }
                        echo '</li>';
                    }
                }

                // Get current relative URL for highlighting
                $base_path = parse_url(BASE_URL, PHP_URL_PATH);
                $cur = substr($_SERVER['SCRIPT_NAME'], strlen($base_path));
                buildMenu($pdo, 0, $_SESSION['role'], $cur);
                ?>

            </ul>
        </nav>
    </div>
</aside>