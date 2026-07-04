<?php require '../../config/config.php'; ?>

<?php
if (!isset($_SESSION['adminname'])) {
    header("Location: " . ADMINURL . "/admins/login-admins.php");
    exit();
}

function h($v)
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ' . ADMINURL . '/categories-admins/show-categories.php?error=' . urlencode('Invalid category ID.'));
    exit();
}

$id = (int) $_GET['id'];
try {
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$category) {
        header('Location: ' . ADMINURL . '/categories-admins/show-categories.php?error=' . urlencode('Category not found.'));
        exit();
    }
} catch (Exception $e) {
    header('Location: ' . ADMINURL . '/categories-admins/show-categories.php?error=' . urlencode('Error fetching category.'));
    exit();
}

$pageTitle = "Update Category";
$breadcrumb = "Systems";
$errors = [];
$nameVal = h($category['name']);

if (isset($_POST['submit'])) {
    $nameVal = trim($_POST['name'] ?? '');
    $nameVal = h($nameVal);

    if ($nameVal === '') {
        $errors[] = "Name is required.";
    } elseif (mb_strlen($nameVal) > 255) {
        $errors[] = "Name must be 255 characters or less.";
    }
    try {
        $chk = $conn->prepare("SELECT 1 FROM categories WHERE LOWER(name) = LOWER(:name) AND id != :id LIMIT 1");
        $chk->execute([':name' => $nameVal, ':id' => $id]);
        if ($chk->fetch()) {
            $errors[] = "Name already exists.";
        } else {
            $update = $conn->prepare("UPDATE categories SET name = :name WHERE id = :id");
            $update->execute([':name' => $nameVal, ':id' => $id]);
            header("Location: " . ADMINURL . "/categories-admins/show-categories.php?updated=1", true, 303);
            exit();
        }
    } catch (Exception $e) {
        $errors[] = "Error validating category name.";
    }
}

require "../../admin/layouts/header.php";
?>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h2 class="card-title h6 mb-0">Update Category</h2>
                <a href="<?= ADMINURL ?>/categories-admins/show-categories.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Categories
                </a>
            </div>

            <div class="card-body">
                <?php if ($errors): ?>
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0">
                            <?php foreach ($errors as $err): ?>
                                <li><?= h($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="update-category.php?id=<?= (int) $id ?>" novalidate>
                    <div class="form-group">
                        <label for="catName" class="mb-1">Category Name</label>
                        <input type="text" name="name" id="catName" class="form-control" placeholder="e.g., Engineering"
                            value="<?= h($nameVal) ?>" required>
                    </div>

                    <button type="submit" name="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Update
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require "../../admin/layouts/footer.php"; ?>