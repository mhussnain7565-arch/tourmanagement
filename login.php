<?php
require_once 'core/session.php';
require_once 'core/auth.php';
$auth = new Auth($pdo);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier']);
    $password = $_POST['password'];

    if ($auth->login($identifier, $password)) {
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid Credentials or Account Suspended.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Tour Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" />
    <style>
        :root {
            --primary-blue: #2563eb;
            --accent-blue: #3b82f6;
        }
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Source Sans 3', sans-serif;
            overflow: hidden;
        }
        .bg-video {
            position: fixed;
            right: 0;
            bottom: 0;
            min-width: 100%;
            min-height: 100%;
            z-index: -1;
            background: url('assets/img/login_bg.png') no-repeat center center fixed;
            background-size: cover;
            filter: brightness(0.7);
        }
        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 100%;
            background: linear-gradient(90deg, rgba(0,0,0,0.4) 0%, rgba(0,0,0,0) 100%);
            z-index: 0;
        }
        .login-container {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: flex-end; /* Move back to right */
            padding-right: 8%; /* Balanced padding */
            position: relative;
            z-index: 10;
        }
        .login-card {
            width: 100%;
            max-width: 360px; /* Reduced to 'Medium' size */
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(15px);
            border-radius: 25px;
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 20px 40px rgba(0,0,0,0.25);
            padding: 35px;
            margin: 20px;
            animation: slideInRight 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }
        @keyframes slideInRight {
            from { transform: translateX(50px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .brand-logo {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            object-fit: cover;
            margin-bottom: 20px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--accent-blue) 100%);
            border: none;
            padding: 12px;
            border-radius: 15px;
            font-weight: 700;
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(37, 99, 235, 0.4);
        }
        .form-control {
            border-radius: 15px;
            padding: 12px 20px;
            background: rgba(0,0,0,0.03);
            border: 1px solid rgba(0,0,0,0.05);
        }
        .form-control:focus {
            background: #fff;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
            border-color: var(--primary-blue);
        }
        .welcome-text {
            color: white;
            position: absolute;
            left: 8%;
            top: 50%;
            transform: translateY(-50%);
            max-width: 500px;
            z-index: 10;
        }
        .welcome-text h1 {
            font-size: 4.5rem;
            font-weight: 900;
            line-height: 1;
            margin-bottom: 20px;
            text-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
    </style>
</head>
<body>
    <div class="bg-video"></div>
    <div class="overlay"></div>

    <div class="welcome-text d-none d-lg-block">
        <h1>Explore the Peaks</h1>
        <p class="lead fw-bold opacity-75">Join the most advanced tour management network. Your journey starts here.</p>
    </div>

    <div class="login-container">
        <div class="login-card">
            <div class="text-center mb-4">
                <img src="assets/img/logo.png" class="brand-logo" alt="Logo">
                <h2 class="fw-bold text-dark">Welcome Back</h2>
                <p class="text-muted small">Please enter your mission credentials</p>
            </div>

            <?php if($error): ?>
                <div class="alert alert-danger rounded-4 py-2 small text-center mb-4 border-0"><?= $error ?></div>
            <?php endif; ?>

            <form action="" method="post">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted text-uppercase">Identity Identifier</label>
                    <div class="input-group">
                        <input type="text" name="identifier" class="form-control" placeholder="Email / CNIC / Reg No" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">Security Key</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>

                <div class="d-grid gap-2 mb-4">
                    <button type="submit" class="btn btn-primary">Authorize Session</button>
                </div>

                <div class="text-center">
                    <p class="mb-0 text-muted small">New to the network? <a href="register.php" class="text-primary fw-bold text-decoration-none">Register Membership</a></p>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>