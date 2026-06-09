<?php require '../../config/config.php'; ?>

<?php
if (isset($_SESSION['adminname'])) {
    header("location : " . ADMINURL . "/admins/login-admins.php");
    exit();
}

$pageTitle = "Categories";
$breadcrumb = "Systems";

require '../../admin/layouts/header.php';

function h($v)
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$counter = $offset + 1;

$totalStmt = $conn->query("SELECT COUNT(*) FROM job_regions");
$totalRecords = (int) $totalStmt->fetchColumn();
$totalPages = ceil($totalRecords / $limit);
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $limit;
    $counter = $offset + 1;
}

$stmt = $conn->prepare("SELECT * FROM job_regions ORDER BY id DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$jobregions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$flash = null;
if (isset($_GET['error'])) {
    $flash = ['type' => 'danger', 'msg' => h($_GET['error'])];
} elseif (isset($_GET['deleted'])) {
    $flash = ['type' => 'success', 'msg' => "Job region deleted."];
} elseif (isset($_GET['updated'])) {
    $flash = ['type' => 'success', 'msg' => "Job region updated."];
} elseif (isset($_GET['created'])) {
    $flash = ['type' => 'success', 'msg' => "Job region created."];
}
?>

<div class="max-w-6xl mx-auto p-4">
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <!-- Header -->
        <div class="flex justify-between items-center p-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">Categories</h2>
            <a href="<?= ADMINURL ?>/categories-admins/create-category.php"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add New Category
            </a>
        </div>

        <!-- Flash Message -->
        <?php if ($flash): ?>
            <div class="p-4">
                <div class="flex items-center p-4 rounded-md <?= $flash['class'] === 'alert-success' ? 'bg-green-100 text-green-700' : ($flash['class'] === 'alert-danger' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700') ?>"
                    role="alert">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span><?= $flash['msg'] ?></span>
                    <button type="button" class="ml-auto text-xl font-bold leading-none"
                        onclick="this.parentElement.parentElement.style.display='none'">
                        &times;
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">#</th>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category
                        </th>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-48">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!$categories): ?>
                        <tr>
                            <td colspan="3" class="p-4 text-center text-gray-500">No categories found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categories as $category): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="p-3 whitespace-nowrap"><?= (int) $counter++; ?></td>
                                <td class="p-3 whitespace-nowrap"><?= h($category->name); ?></td>
                                <td class="p-3 whitespace-nowrap">
                                    <a href="<?= ADMINURL ?>/categories-admins/update-category.php?id=<?= (int) $category->id ?>"
                                        class="px-3 py-1 bg-gray-600 text-white rounded-md text-sm hover:bg-gray-700 transition-colors">
                                        Update
                                    </a>
                                    <a href="<?= ADMINURL ?>/categories-admins/delete-category.php?id=<?= (int) $category->id ?>"
                                        class="px-3 py-1 bg-red-600 text-white rounded-md text-sm hover:bg-red-700 transition-colors ml-2"
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
            <ul class="flex justify-center items-center space-x-2">
                <li>
                    <a class="px-3 py-1 border border-gray-300 rounded-md text-gray-600 hover:bg-gray-100 <?= ($page <= 1 ? 'opacity-50 cursor-not-allowed' : '') ?>"
                        href="?page=<?= max(1, $page - 1) ?>">
                        Previous
                    </a>
                </li>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li>
                        <a class="px-3 py-1 border border-gray-300 rounded-md <?= ($page === $i ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100') ?>"
                            href="?page=<?= $i ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <li>
                    <a class="px-3 py-1 border border-gray-300 rounded-md text-gray-600 hover:bg-gray-100 <?= ($page >= $totalPages ? 'opacity-50 cursor-not-allowed' : '') ?>"
                        href="?page=<?= min($totalPages, $page + 1) ?>">
                        Next
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>