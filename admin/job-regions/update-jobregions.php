<?php require '../../config/config.php'; ?>

<?php
if (isset($_SESSION['adminname'])) {
    header("location : " . ADMINURL . "/admins/login-admins.php");
    exit();
}

// -------- Helpers --------
function h($v)
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

// -------- Validate & fetch category --------
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header("Location: " . ADMINURL . "/categories-admins/show-categories.php?error=" . urlencode("Invalid category ID."));
    exit;
}
$id = (int) $_GET['id'];

try {
    $stmt = $conn->prepare("SELECT id, name FROM categories WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $category = $stmt->fetch(PDO::FETCH_OBJ);
    if (!$category) {
        header("Location: " . ADMINURL . "/categories-admins/show-categories.php?error=" . urlencode("Category not found."));
        exit;
    }
} catch (Exception $e) {
    header("Location: " . ADMINURL . "/categories-admins/show-categories.php?error=" . urlencode("Unable to load category."));
    exit;
}

// -------- Page context for header --------
$pageTitle = "Update Category";
$breadcrumb = "System";

$errors = [];
$nameVal = $category->name; // sticky with current value by default

// -------- Handle submit --------
if (isset($_POST['submit'])) {
    $name = trim($_POST['name'] ?? '');
    $nameVal = $name;

    if ($name === '') {
        $errors[] = "Please enter a category name.";
    } else {
        try {
            // Optional: prevent duplicates (case-insensitive), excluding current ID
            $dup = $conn->prepare("SELECT 1 FROM categories WHERE LOWER(name) = LOWER(:name) AND id <> :id LIMIT 1");
            $dup->execute([':name' => $name, ':id' => $id]);

            if ($dup->fetch()) {
                $errors[] = "Another category with this name already exists.";
            } else {
                $update = $conn->prepare("UPDATE categories SET name = :name WHERE id = :id");
                $update->execute([':name' => $name, ':id' => $id]);

                header("Location: " . ADMINURL . "/categories-admins/show-categories.php?updated=1");
                exit;
            }
        } catch (Exception $e) {
            $errors[] = "Unable to update category. Please try again.";
        }
    }
}

require "../layouts/header.php";
?>

<div class="max-w-4xl mx-auto p-4">
    <div class="bg-white shadow-md rounded-lg p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Update Category</h2>
            <a href="<?= ADMINURL ?>/categories-admins/show-categories.php"
                class="px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Categories
            </a>
        </div>

        <!-- Errors -->
        <?php if ($errors): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md mb-6" role="alert">
                <ul class="list-disc pl-5 m-0">
                    <?php foreach ($errors as $err): ?>
                        <li><?= h($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form method="POST" action="update-category.php?id=<?= (int) $id ?>" novalidate>
            <div class="mb-4">
                <label for="catName" class="block text-sm font-medium text-gray-700 mb-1">Category Name</label>
                <input type="text" name="name" id="catName"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="e.g., Engineering" value="<?= h($nameVal) ?>" required>
            </div>

            <button type="submit" name="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                </svg>
                Update
            </button>
        </form>
    </div>
</div>

<?php require "../layouts/footer.php"; ?>