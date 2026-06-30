<?php

require '../../config/config.php';

if (isset($_SESSION['adminname'])) {
    header("location : " . ADMINURL . "/admins/login-admins.php");
    exit();
}

$pageTitle = "Job Seekers";
$breadcrumb = "Users";

function h($v)
{
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

//Pagination
$limit = 10; // Number of records per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1; // Current page number
$offset = ($page - 1) * $limit; // Offset for the current page
$counter = $offset + 1; // Row counter

//Totals 
$totalStmt = $conn->query("SELECT COUNT(*) AS total FROM users WHERE UPPER(type) = 'JOB SEEKER'");
$totalRecords = (int) $totalStmt->fetch(PDO::FETCH_OBJ)->total;
$totalPages = max(1, (int) ceil($totalRecords / $limit));

//Clamp page if overshoot
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $limit;
    $counter = $offset + 1;
}

//Page of job seekers
$stmt = $conn->prepare("
  SELECT id, fullname, username, email, contact
  FROM users
  WHERE UPPER(type) = 'JOB SEEKER'
  ORDER BY id ASC
  LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$jobSeekers = $stmt->fetchAll(PDO::FETCH_OBJ);
?>

<?php require '../layouts/header.php'; ?>

<div class="p-4">
    <div class="max-w-full">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <!-- Card Header -->
            <div class="p-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold mb-0">Job Seekers</h2>
                    <!-- Reserved for future actions (export, filters) -->
                </div>
            </div>

            <!-- Card Body -->
            <div class="p-0">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">
                                    #</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Full Name</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Username</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Email</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Contact</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (!$jobSeekers): ?>
                                <tr>
                                    <td colspan="5" class="px-4 py-4 text-center text-gray-500">No job seekers found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($jobSeekers as $user): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 whitespace-nowrap"><?= $counter++; ?></td>
                                        <td class="px-4 py-3 whitespace-nowrap"><?= h($user->fullname); ?></td>
                                        <td class="px-4 py-3 whitespace-nowrap"><?= h($user->username); ?></td>
                                        <td class="px-4 py-3 whitespace-nowrap"><?= h($user->email); ?></td>
                                        <td class="px-4 py-3 whitespace-nowrap"><?= h($user->contact); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav class="p-3 border-t border-gray-200">
                    <ul class="flex justify-center space-x-1">
                        <li>
                            <a class="px-3 py-1 border border-gray-300 rounded-md <?= ($page <= 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50') ?>"
                                href="?page=<?= max(1, $page - 1) ?>">Previous</a>
                        </li>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li>
                                <a class="px-3 py-1 border border-gray-300 rounded-md <?= ($page === $i ? 'bg-indigo-600 text-white' : 'hover:bg-gray-50') ?>"
                                    href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li>
                            <a class="px-3 py-1 border border-gray-300 rounded-md <?= ($page >= $totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50') ?>"
                                href="?page=<?= min($totalPages, $page + 1) ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<?php require '../layouts/footer.php'; ?>