<?php
require_once __DIR__ . '/../core/session.php';

// 1. Fetch System Settings
$settings = [];
$stmt = $pdo->query("SELECT * FROM system_settings");
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// 2. Identify Current Page & Security Check
$base_path = parse_url(BASE_URL, PHP_URL_PATH);
$current_url = substr($_SERVER['SCRIPT_NAME'], strlen($base_path)); 
// Clean URL for DB matching (assuming DB stores relative paths)
$db_url_match = $current_url; 
// If your script is in a folder, the DB url should match "dashboards/super_admin/file.php"

// Fetch Page Info
$pageStmt = $pdo->prepare("SELECT * FROM sys_pages WHERE page_url LIKE ? LIMIT 1");
$pageStmt->execute(["%$current_url%"]); 
$currentPageData = $pageStmt->fetch();

$pageTitle = $currentPageData['page_name'] ?? 'Dashboard';
$pageId = $currentPageData['id'] ?? 0;

// 3. Security Access Check (The Gatekeeper)
if ($pageId > 0 && $_SESSION['role'] !== 'super_admin') {
    $accessStmt = $pdo->prepare("SELECT * FROM role_access WHERE role_key = ? AND page_id = ?");
    $accessStmt->execute([$_SESSION['role'], $pageId]);
    if ($accessStmt->rowCount() == 0) {
        die('<div class="alert alert-danger m-5">⛔ Access Denied: You do not have permission to view this page.</div>');
    }
}

// 4. Breadcrumb Logic (Recursive Upwards)
$breadcrumbs = [];
if ($currentPageData) {
    $crumbId = $currentPageData['id'];
    while($crumbId != 0) {
        $crumbStmt = $pdo->prepare("SELECT id, parent_id, page_name, page_url FROM sys_pages WHERE id = ?");
        $crumbStmt->execute([$crumbId]);
        $crumb = $crumbStmt->fetch();
        array_unshift($breadcrumbs, $crumb); // Add to beginning
        $crumbId = $crumb['parent_id'];
    }
}
?>
<!DOCTYPE html>
<html lang="en"> <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | <?= htmlspecialchars($settings['system_name']) ?></title>
    
    <script>
        // Immediately check local storage to prevent "White Flash"
        const storedTheme = localStorage.getItem('theme');
        if (storedTheme) {
            document.documentElement.setAttribute('data-bs-theme', storedTheme);
        } else {
            // Default to system preference if no choice made
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            document.documentElement.setAttribute('data-bs-theme', systemTheme);
        }
    </script>

    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/bootstrap-icons.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/adminlte.min.css" />
    
    <style> 
        :root {
            --app-primary-blue: #2563eb;
            --app-primary-blue-rgb: 37, 99, 235;
            --app-accent-blue: #3b82f6;
            --app-sidebar-width: 280px;
            --app-glass-blur: blur(15px) saturate(180%);
        }

        /* Light Mode Variables */
        [data-bs-theme="light"] {
            --app-sidebar-bg: rgba(255, 255, 255, 0.8);
            --app-sidebar-text: #1e293b;
            --app-sidebar-text-rgb: 30, 41, 59;
            --app-sidebar-border: rgba(0, 0, 0, 0.1);
            --app-nav-active-bg: rgba(37, 99, 235, 0.1);
            --app-nav-hover-bg: rgba(0, 0, 0, 0.05);
            --app-header-bg: rgba(255, 255, 255, 0.8);
        }

        /* Dark Mode Variables */
        [data-bs-theme="dark"] {
            --app-sidebar-bg: rgba(15, 23, 42, 0.85);
            --app-sidebar-text: #f8fafc;
            --app-sidebar-text-rgb: 248, 250, 252;
            --app-sidebar-border: rgba(255, 255, 255, 0.1);
            --app-nav-active-bg: rgba(59, 130, 246, 0.15);
            --app-nav-hover-bg: rgba(255, 255, 255, 0.05);
            --app-header-bg: rgba(15, 23, 42, 0.8);
        }

        .app-brand-logo { height: 30px; width: auto; } 
        .user-image { width: 30px; height: 30px; object-fit: cover; }
        
        /* Premium Navbar Adjustments */
        .app-header {
            background: var(--app-header-bg) !important;
            backdrop-filter: var(--app-glass-blur);
            -webkit-backdrop-filter: var(--app-glass-blur);
            border-bottom: 1px solid var(--app-sidebar-border) !important;
        }
        
        .breadcrumb-item.active {
            color: var(--app-primary-blue);
            font-weight: 600;
        }

        /* Smooth Theme Transition */
        body {
            transition: background-color 0.3s ease, color 0.3s ease;
        }
    </style>
    <!-- Language Translation Engine -->
    <script type="text/javascript">
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({
                pageLanguage: 'en',
                layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
                autoDisplay: false
            }, 'google_translate_element');
        }
    </script>
    <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    
    <script>
        // Apply Directionality and Neural Switch
        document.addEventListener('DOMContentLoaded', function() {
            const dir = localStorage.getItem('app_direction') || 'ltr';
            document.documentElement.setAttribute('dir', dir);
            if(dir === 'rtl') {
                document.body.classList.add('rtl-mode');
            }
        });
    </script>

    <style>
        /* Hide Google Translate Banner & Widget UI */
        iframe.goog-te-banner-frame { display: none !important; }
        body { top: 0px !important; }
        .goog-te-gadget { font-family: inherit !important; font-size: 0 !important; }
        .goog-te-gadget-simple { border: none !important; background: transparent !important; padding: 0 !important; }
        #google_translate_element { display: none; }
        
        /* RTL Mode Adjustments */
        [dir="rtl"] .sidebar-expand-lg .app-sidebar { right: 0; left: auto; }
        [dir="rtl"] .app-header { margin-right: var(--app-sidebar-width); margin-left: 0; }
        [dir="rtl"] .app-main { margin-right: var(--app-sidebar-width); margin-left: 0; }
        [dir="rtl"] .ms-auto { margin-right: auto !important; margin-left: 0 !important; }
        [dir="rtl"] .me-auto { margin-left: auto !important; margin-right: 0 !important; }
    </style>
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<div class="app-wrapper">
    <nav class="app-header navbar navbar-expand bg-body">
        <div class="container-fluid">
            <ul class="navbar-nav">
                <li class="nav-item"> <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button"><i class="bi bi-list"></i></a> </li>
                <li class="nav-item d-none d-md-block"> <a href="#" class="nav-link"><?= $pageTitle ?></a> </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                 <li class="nav-item">
                    <button class="btn btn-link nav-link" id="theme-toggle" type="button">
                        <i class="bi bi-sun-fill" id="theme-icon"></i>
                    </button>
                </li>
                <li class="nav-item dropdown user-menu">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <img src="<?= !empty($_SESSION['avatar']) ? $_SESSION['avatar'] : BASE_URL.'assets/img/avatar.png' ?>" class="user-image rounded-circle shadow" alt="User Image">
                        <span class="d-none d-md-inline ms-1"><?= htmlspecialchars($_SESSION['name']) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                        <li class="user-header text-bg-primary">
                            <img src="<?= !empty($_SESSION['avatar']) ? $_SESSION['avatar'] : BASE_URL.'assets/img/avatar.png' ?>" class="rounded-circle shadow" alt="User Image">
                            <p>
                                <?= htmlspecialchars($_SESSION['name']) ?>
                                <small><?= ucfirst(str_replace('_', ' ', $_SESSION['role'])) ?></small>
                            </p>
                        </li>
                        <li class="user-footer"> 
                            <a href="<?= BASE_URL ?>profile.php" class="btn btn-default btn-flat">Profile</a>
                            <a href="<?= BASE_URL ?>logout.php" class="btn btn-default btn-flat float-end">Sign out</a> 
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
    
    <?php include 'sidebar.php'; ?>
    
    <main class="app-main">
        <div class="app-content-header">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6"><h3 class="mb-0"><?= $pageTitle ?></h3></div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
                            <?php foreach($breadcrumbs as $b): ?>
                                <li class="breadcrumb-item <?= ($b['id'] == $pageId) ? 'active' : '' ?>">
                                    <?= htmlspecialchars($b['page_name']) ?>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="app-content">
            <div class="container-fluid">