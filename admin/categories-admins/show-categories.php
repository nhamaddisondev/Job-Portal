<?php require '../../config/config.php'; ?>

<?php
if (!isset($_SESSION['adminname'])) {
    header("Location: " . ADMINURL . "/admins/login-admins.php");
    exit();
}

$pageTitle = "Categories";
$breadcrumb = "Systems";

require "../../admin/layouts/header.php";

function h($v)
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$page = max(1, $page); // Ensure page is at least 1
$offset = ($page - 1) * $limit;
$counter = $offset + 1;

$totalStmt = $conn->query("SELECT COUNT(*) FROM categories");
$totalRecords = (int) $totalStmt->fetchColumn();
$totalPages = ceil($totalRecords / $limit);
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $limit;
    $counter = $offset + 1;
}

$stmt = $conn->prepare("SELECT * FROM categories ORDER BY id DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_OBJ);

$flash = null;
if (isset($_GET['error'])) {
    $flash = ['type' => 'danger', 'msg' => h($_GET['error'])];
} elseif (isset($_GET['deleted'])) {
    $flash = ['type' => 'success', 'msg' => "Category deleted."];
} elseif (isset($_GET['updated'])) {
    $flash = ['type' => 'success', 'msg' => "Category updated."];
} elseif (isset($_GET['created'])) {
    $flash = ['type' => 'success', 'msg' => "Category created."];
}
?>

<div class="flex flex-col">
    <div class="bg-white rounded-lg shadow-md">
        <!-- Card Header -->
        <div class="flex items-center justify-between p-4 border-b">
            <h2 class="text-lg font-semibold text-gray-800">Categories</h2>
            <a href="<?= ADMINURL ?>/categories-admins/create-category.php"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add New Category
            </a>
        </div>

        <!-- Card Body -->
        <div class="p-0">
            <!-- Flash Message -->
            <?php if ($flash): ?>
                <div class="p-4">
                    <div
                        class="flex items-center p-4 rounded-md <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?> relative">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd" />
                        </svg>
                        <?= $flash['msg'] ?>
                        <button type="button" class="absolute top-2 right-2 text-xl font-bold leading-none"
                            onclick="this.parentElement.style.display='none'" aria-label="Close">
                            &times;
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">#</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-48">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (!$categories): ?>
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-gray-500">No categories found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td class="px-4 py-2 whitespace-nowrap"><?= (int) $counter++; ?></td>
                                    <td class="px-4 py-2 whitespace-nowrap"><?= h($category->name); ?></td>
                                    <td class="px-4 py-2 whitespace-nowrap space-x-2">
                                        <a href="<?= ADMINURL ?>/categories-admins/update-category.php?id=<?= (int) $category->id ?>"
                                            class="px-3 py-1 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition">
                                            Update
                                        </a>
                                        <a href="<?= ADMINURL ?>/categories-admins/delete-category.php?id=<?= (int) $category->id ?>"
                                            class="px-3 py-1 bg-red-600 text-white rounded-md hover:bg-red-700 transition"
                                            onclick="return confirm('Delete this category? This action cannot be undone.');">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <nav class="p-4">
                <ul class="flex justify-center space-x-2">
                    <li>
                        <a href="?page=<?= max(1, $page - 1) ?>"
                            class="px-3 py-1 border rounded-md <?= ($page <= 1 ? 'pointer-events-none opacity-50' : 'hover:bg-gray-100') ?>">
                            Previous
                        </a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li>
                            <a href="?page=<?= $i ?>"
                                class="px-3 py-1 border rounded-md <?= ($page === $i ? 'bg-blue-600 text-white' : 'hover:bg-gray-100') ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    <li>
                        <a href="?page=<?= min($totalPages, $page + 1) ?>"
                            class="px-3 py-1 border rounded-md <?= ($page >= $totalPages ? 'pointer-events-none opacity-50' : 'hover:bg-gray-100') ?>">
                            Next
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>
<?php require "../../admin/layouts/footer.php"; ?>