<?php
require_once '../includes/header.php';

// Fetch Active Languages
$languages = $pdo->query("SELECT * FROM languages WHERE is_active = 1 ORDER BY name ASC")->fetchAll();
?>

<style>
    .lang-card {
        cursor: pointer;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border: 2px solid transparent;
        overflow: hidden;
    }
    .lang-card:hover {
        transform: scale(1.05) translateY(-10px);
        border-color: var(--app-primary-blue);
        box-shadow: 0 15px 45px rgba(0,0,0,0.15) !important;
    }
    .flag-img {
        width: 100%;
        height: 120px;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    .lang-card:hover .flag-img {
        transform: scale(1.1);
    }
    .lang-name {
        font-weight: 800;
        letter-spacing: -0.5px;
    }
    .current-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        z-index: 10;
    }
    #google_translate_element {
        display: none !important;
    }
    .goog-te-banner-frame {
        display: none !important;
    }
    body {
        top: 0 !important;
    }
</style>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="text-center py-5">
            <div class="display-3 text-primary mb-3"><i class="bi bi-globe-americas"></i></div>
            <h1 class="fw-bold display-5">Select Your Tongue</h1>
            <p class="text-muted lead">The universe speaks many languages. Explore the nebula in your own.</p>
        </div>

        <div class="row g-4 justify-content-center">
            <?php foreach($languages as $l): ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card lang-card border-0 shadow-sm rounded-5 text-center p-0 position-relative" onclick="switchLanguage('<?= $l['code'] ?>', '<?= $l['is_rtl'] ?>')">
                    <img src="<?= $l['flag_url'] ?>" class="flag-img rounded-top-5">
                    <div class="p-4 bg-white rounded-bottom-5">
                        <h4 class="lang-name mb-0 text-dark"><?= htmlspecialchars($l['name']) ?></h4>
                        <small class="text-primary text-uppercase fw-bold small opacity-75"><?= $l['code'] ?></small>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-5 p-5 rounded-5 text-center shadow-lg" style="background: linear-gradient(135deg, #141e30 0%, #243b55 100%); color: white;">
            <h3 class="fw-bold mb-3">Seamless Intelligence</h3>
            <p class="opacity-75 mb-0">Our platform uses advanced neural translation to ensure every link, button, and description is accurately localized for your mission.</p>
        </div>
    </div>
</div>

<script>
    function switchLanguage(code, rtl) {
        // Set Language Cookie for Google Translate
        document.cookie = `googtrans=/en/${code}; path=/; domain=${window.location.hostname}`;
        document.cookie = `googtrans=/en/${code}; path=/;`;
        
        // Handle RTL Direction
        if (rtl == 1) {
            localStorage.setItem('app_direction', 'rtl');
        } else {
            localStorage.setItem('app_direction', 'ltr');
        }

        // Redirect to Home with translation active
        window.location.href = '../index.php';
    }
</script>

<?php require_once '../includes/footer.php'; ?>
