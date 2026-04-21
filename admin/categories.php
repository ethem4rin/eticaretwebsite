<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
requireAdmin();
require_once dirname(__DIR__) . '/includes/db.php';

$pdo = getDB();
$errors = [];
$editCat = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token.';
    } else {
        $action = $_POST['form_action'] ?? '';
        $name   = trim($_POST['category_name'] ?? '');

        if (($action === 'add' || $action === 'edit') && empty($name)) {
            $errors[] = 'Category name is required.';
        }

        if (empty($errors)) {
            if ($action === 'add') {
                $pdo->prepare('INSERT INTO categories (category_name) VALUES (?)')->execute([$name]);
                header('Location: ' . SITE_URL . '/admin/categories.php?saved=1');
                exit;
            } elseif ($action === 'edit') {
                $id = (int)($_POST['category_id'] ?? 0);
                $pdo->prepare('UPDATE categories SET category_name = ? WHERE id = ?')->execute([$name, $id]);
                header('Location: ' . SITE_URL . '/admin/categories.php?saved=1');
                exit;
            } elseif ($action === 'delete') {
                $id = (int)($_POST['category_id'] ?? 0);
                $pdo->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
                header('Location: ' . SITE_URL . '/admin/categories.php?deleted=1');
                exit;
            }
        }
    }
}

if (($_GET['action'] ?? '') === 'edit' && !empty($_GET['id'])) {
    $editStmt = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
    $editStmt->execute([(int)$_GET['id']]);
    $editCat = $editStmt->fetch();
}

$categories = $pdo->query('SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY c.category_name')->fetchAll();
$csrf = generateCsrfToken();

include dirname(__DIR__) . '/includes/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <div class="admin-main">
        <div class="admin-topbar">
            <h1 class="admin-page-title">Categories</h1>
        </div>

        <?php if (isset($_GET['saved'])): ?>
            <div class="alert alert--success">Category saved.</div>
        <?php elseif (isset($_GET['deleted'])): ?>
            <div class="alert alert--success">Category deleted.</div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert--error"><?php foreach ($errors as $e) echo '<p>' . htmlspecialchars($e) . '</p>'; ?></div>
        <?php endif; ?>

        <!-- Add / Edit Form -->
        <div class="admin-card">
            <div class="admin-card__header">
                <h2 class="admin-card__title"><?= $editCat ? 'Edit Category' : 'Add Category' ?></h2>
                <?php if ($editCat): ?>
                    <a href="<?= SITE_URL ?>/admin/categories.php" class="btn btn--secondary btn--sm">Cancel</a>
                <?php endif; ?>
            </div>
            <form method="POST" action="" class="admin-form">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <input type="hidden" name="form_action" value="<?= $editCat ? 'edit' : 'add' ?>">
                <?php if ($editCat): ?>
                    <input type="hidden" name="category_id" value="<?= $editCat['id'] ?>">
                <?php endif; ?>

                <div class="form-group" style="max-width:400px">
                    <label class="form-label">Category Name *</label>
                    <input type="text" name="category_name" class="form-control"
                           value="<?= htmlspecialchars($editCat['category_name'] ?? '') ?>" required>
                </div>
                <button type="submit" class="btn btn--primary">
                    <?= $editCat ? 'Update Category' : 'Add Category' ?>
                </button>
            </form>
        </div>

        <!-- Categories Table -->
        <div class="admin-card">
            <div class="admin-card__header">
                <h2 class="admin-card__title">All Categories</h2>
            </div>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Products</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categories)): ?>
                        <tr><td colspan="4" class="admin-table__empty">No categories yet.</td></tr>
                        <?php else: ?>
                        <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td><?= $cat['id'] ?></td>
                            <td><?= htmlspecialchars($cat['category_name']) ?></td>
                            <td><?= $cat['product_count'] ?></td>
                            <td class="admin-table__actions">
                                <a href="?action=edit&id=<?= $cat['id'] ?>" class="btn btn--secondary btn--sm">Edit</a>
                                <form method="POST" action="" style="display:inline"
                                      onsubmit="return confirm('Delete this category? Products will lose their category.')">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                    <input type="hidden" name="form_action" value="delete">
                                    <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                                    <button type="submit" class="btn btn--danger btn--sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
