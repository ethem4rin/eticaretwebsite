<footer class="footer">
    <div class="container">
        <div class="footer__grid">
            <div class="footer__brand">
                <h3 class="footer__logo">Élégance</h3>
                <p>Timeless style for the modern individual.</p>
            </div>
            <div class="footer__links">
                <h4>Shop</h4>
                <ul>
                    <li><a href="<?= SITE_URL ?>/products.php">All Products</a></li>
                    <li><a href="<?= SITE_URL ?>/products.php?category_id=1">Men</a></li>
                    <li><a href="<?= SITE_URL ?>/products.php?category_id=2">Women</a></li>
                    <li><a href="<?= SITE_URL ?>/products.php?category_id=3">Accessories</a></li>
                </ul>
            </div>
            <div class="footer__links">
                <h4>Account</h4>
                <ul>
                    <li><a href="<?= SITE_URL ?>/login.php">Login</a></li>
                    <li><a href="<?= SITE_URL ?>/register.php">Register</a></li>
                    <li><a href="<?= SITE_URL ?>/cart.php">Cart</a></li>
                </ul>
            </div>
        </div>
        <div class="footer__bottom">
            <p>&copy; <?= date('Y') ?> Élégance. All rights reserved.</p>
        </div>
    </div>
</footer>
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
</main>
</body>
</html>
