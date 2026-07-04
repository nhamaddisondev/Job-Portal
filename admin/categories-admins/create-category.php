<?php require '../../config/config.php'; ?>

<?php

if (!isset($_SESSION['adminname'])) {
    header("Location: " . ADMINURL . "/admins/login-admins.php");
    exit();
}

$pageTitle = "Create Category";
$breadcrumb = "Systems";
$errors = [];
$nameVal = "";

function h($v)
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $nameVal = trim($_POST['name'] ?? '');

    if ($nameVal === '') {
        $errors[] = 'Category name is required.';
    } else {
        $stmt = $conn->prepare("SELECT id FROM categories WHERE name = ?");
        $stmt->execute([$nameVal]);
        if ($stmt->fetch()) {
            $errors[] = 'Category name already exists.';
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        if ($stmt->execute([$nameVal])) {
            header('Location: ' . ADMINURL . '/categories-admins/show-categories.php?created=1');
            exit();
        } else {
            $errors[] = 'Failed to create category. Please try again.';
        }
    }
}

require '../../admin/layouts/header.php';
?>

<div class="flex flex-col lg:flex-row">
    <div class="w-full lg:w-3/4">
        <div class="bg-white rounded-lg shadow-md">
            <div class="flex items-center justify-between p-4 border-b">
                <h2 class="text-lg font-semibold text-gray-800">Create Category</h2>
                <a href="<?= ADMINURL ?>/categories-admins/show-categories.php"
                    class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Categories
                </a>
            </div>

            <div class="p-4">
                <?php if ($errors): ?>
                    <div class="p-4 mb-4 bg-red-100 text-red-700 rounded-md" role="alert">
                        <ul class="m-0 list-disc pl-5">
                            <?php foreach ($errors as $err): ?>
                                <li><?= h($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="create-category.php" novalidate>
                    <div class="mb-4">
                        <label for="catName" class="block mb-2 text-sm font-medium text-gray-700">Category Name</label>
                        <input type="text" name="name" id="catName"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="e.g., Engineering" value="<?= h($nameVal) ?>" required>
                    </div>

                    <button type="submit" name="submit"
                        class="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700 transition">
                        <i class="fas fa-check mr-1"></i> Add
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require '../../admin/layouts/footer.php'; ?>