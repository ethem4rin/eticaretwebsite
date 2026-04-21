<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';
$cartCount = getCartCount();
$csrf = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="site-url" content="<?= SITE_URL ?>">
    <title><?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>
<header class="site-header" id="site-header">
    <div class="container header__inner">
        <a href="<?= SITE_URL ?>/index.php" class="header__logo">Élégance</a>

        <nav class="nav" id="main-nav">
            <a href="<?= SITE_URL ?>/index.php" class="nav__link">Home</a>
            <a href="<?= SITE_URL ?>/products.php" class="nav__link">Shop</a>
            <?php if (isAdmin()): ?>
                <a href="<?= SITE_URL ?>/admin/index.php" class="nav__link nav__link--admin">Admin</a>
            <?php endif; ?>
        </nav>

        <div class="header__actions">
            <a href="<?= SITE_URL ?>/cart.php" class="header__cart" aria-label="Cart">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
                <span class="cart-badge" id="cart-badge"><?= $cartCount > 0 ? $cartCount : '' ?></span>
            </a>

            <?php if (isLoggedIn()): ?>
                <div class="header__user">
                    <span class="header__username"><?= htmlspecialchars($_SESSION['full_name'] ?? 'Account') ?></span>
                    <a href="<?= SITE_URL ?>/logout.php" class="nav__link">Logout</a>
                </div>
            <?php else: ?>
                <a href="<?= SITE_URL ?>/login.php" class="nav__link">Login</a>
                <a href="<?= SITE_URL ?>/register.php" class="btn btn--primary btn--sm">Register</a>
            <?php endif; ?>
        </div>

        <button class="hamburger" id="hamburger" aria-label="Toggle menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
    </div>
</header>
<main class="main-content">
