<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

require_once __DIR__ . '/includes/db.php';

$errors = [];
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $full_name        = trim($_POST['full_name'] ?? '');
        $email            = trim($_POST['email'] ?? '');
        $password         = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $address          = trim($_POST['address'] ?? '');

        $formData = compact('full_name', 'email', 'address');

        if (empty($full_name)) {
            $errors[] = 'Full name is required.';
        } elseif (strlen($full_name) < 2) {
            $errors[] = 'Full name must be at least 2 characters.';
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email address is required.';
        }

        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }

        if ($password !== $confirm_password) {
            $errors[] = 'Passwords do not match.';
        }

        if (empty($errors)) {
            $pdo = getDB();
            $checkStmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $checkStmt->execute([$email]);
            if ($checkStmt->fetch()) {
                $errors[] = 'An account with this email already exists.';
            }
        }

        if (empty($errors)) {
            $pdo = getDB();
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insertStmt = $pdo->prepare('INSERT INTO users (full_name, email, password, address) VALUES (?, ?, ?, ?)');
            $insertStmt->execute([$full_name, $email, $hash, $address]);
            header('Location: ' . SITE_URL . '/login.php?registered=1');
            exit;
        }
    }
}

$csrf = generateCsrfToken();
include __DIR__ . '/includes/header.php';
?>

<div class="auth-page">
    <div class="auth-card auth-card--wide">
        <div class="auth-card__header">
            <h1 class="auth-card__title">Create Account</h1>
            <p class="auth-card__subtitle">Join Élégance today</p>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="alert alert--error">
            <?php foreach ($errors as $e): ?>
                <p><?= htmlspecialchars($e) ?></p>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="" class="auth-form" novalidate>
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

            <div class="form-row">
                <div class="form-group">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input type="text" id="full_name" name="full_name" class="form-control"
                           value="<?= htmlspecialchars($formData['full_name'] ?? '') ?>"
                           placeholder="Jane Doe" required autocomplete="name">
                </div>
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control"
                           value="<?= htmlspecialchars($formData['email'] ?? '') ?>"
                           placeholder="you@example.com" required autocomplete="email">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password" class="form-label">Password <span class="form-hint">(min. 8 chars)</span></label>
                    <div class="input-password-wrap">
                        <input type="password" id="password" name="password" class="form-control"
                               placeholder="••••••••" required autocomplete="new-password">
                        <button type="button" class="password-toggle" data-target="password" aria-label="Toggle password">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <div class="input-password-wrap">
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                               placeholder="••••••••" required autocomplete="new-password">
                        <button type="button" class="password-toggle" data-target="confirm_password" aria-label="Toggle confirm password">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="address" class="form-label">Shipping Address <span class="form-hint">(optional)</span></label>
                <textarea id="address" name="address" class="form-control form-textarea"
                          placeholder="123 Main Street, City, Country" rows="3"
                          autocomplete="street-address"><?= htmlspecialchars($formData['address'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn btn--primary btn--full">Create Account</button>
        </form>

        <p class="auth-card__footer">
            Already have an account? <a href="<?= SITE_URL ?>/login.php">Sign in</a>
        </p>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
