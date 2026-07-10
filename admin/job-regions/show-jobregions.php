<?php require '../../config/config.php'; ?>

<?php
if (!isset($_SESSION['adminname'])) {
    header("Location: " . ADMINURL . "/admins/login-admins.php");
    exit();
}

$pageTitle = "Job Regions";
$breadcrumb = "Systems";

require '../../admin/layouts/header.php';

function h($v)
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$page = max(1, $page); // Ensure page is at least 1
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
$jobregions = $stmt->fetchAll(PDO::FETCH_OBJ);

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
            <h2 class="text-xl font-semibold text-gray-800">Job Regions</h2>
            <a href="<?= ADMINURL ?>/job-regions/create-jobregions.php"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add New Region
            </a>
        </div>

        <!-- Flash Message -->
        <?php if ($flash): ?>
            <div class="p-4">
                <div class="flex items-center p-4 rounded-md <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>"
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
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-48">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!$jobregions): ?>
                        <tr>
                            <td colspan="5" class="p-4 text-center text-gray-500">No job regions found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($jobregions as $region): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="p-3 whitespace-nowrap"><?= (int) $counter++; ?></td>
                                <td class="p-3 whitespace-nowrap"><?= h($region->name); ?></td>
                                <td class="p-3 whitespace-nowrap"><?= h($region->code ?? '—'); ?></td>
                                <td class="p-3 whitespace-nowrap">
                                    <?php if ((int) ($region->status ?? 1) === 1): ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-3 whitespace-nowrap">
                                    <a href="<?= ADMINURL ?>/job-regions/update-jobregions.php?id=<?= (int) $region->id ?>"
                                        class="px-3 py-1 bg-gray-600 text-white rounded-md text-sm hover:bg-gray-700 transition-colors">
                                        Update
                                    </a>
                                    <a href="<?= ADMINURL ?>/job-regions/delete-jobregions.php?id=<?= (int) $region->id ?>"
                                        class="px-3 py-1 bg-red-600 text-white rounded-md text-sm hover:bg-red-700 transition-colors ml-2"
                                        onclick="return confirm('Delete this region? This action cannot be undone.');">
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

<?php require '../../admin/layouts/footer.php'; ?>