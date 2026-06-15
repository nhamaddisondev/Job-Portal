<?php require '../../config/config.php'; ?>

<?php 

if(isset($_SESSION['adminname'])) {
    header("location : " . ADMINURL . "/admins/login-admins.php");
    exit();
}

$pageTitle = "Pending Jobs";
$breadcrumb = "Systems";

function h($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

// Pagination
$limit   = 10;
$page    = (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) ? (int)$_GET['page'] : 1;
$offset  = ($page - 1) * $limit;
$counter = $offset + 1;

// Totals (status = 0 pending)
$totalStmt    = $conn->query("SELECT COUNT(*) AS total FROM jobs WHERE status = 0");
$totalRecords = (int)$totalStmt->fetch(PDO::FETCH_OBJ)->total;
$totalPages   = max(1, (int)ceil($totalRecords / $limit));

// Clamp page if overshoot
if ($page > $totalPages) {
  $page   = $totalPages;
  $offset = ($page - 1) * $limit;
  $counter = $offset + 1;
}

// Page of pending jobs
$stmt = $conn->prepare("
  SELECT id, job_title, job_category, company_name, application_deadline, status
  FROM jobs
  WHERE status = 0
  ORDER BY id DESC
  LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$jobs = $stmt->fetchAll(PDO::FETCH_OBJ);

require "../layouts/header.php";
?>

<div class="flex flex-col">
  <div class="bg-white shadow-md rounded-lg overflow-hidden">
    <!-- Card Header -->
    <div class="px-6 py-4 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-gray-800">Pending Job Postings</h2>
          <p class="text-sm text-gray-500">Awaiting approval</p>
        </div>
        <span class="px-3 py-1 text-sm font-medium text-yellow-800 bg-yellow-100 rounded-full">
          Total: <?= (int)$totalRecords ?> pending
        </span>
      </div>
    </div>

    <!-- Card Body -->
    <div class="p-0">
      <?php if (!empty($_SESSION['admin_flash'])): ?>
        <?php $f = $_SESSION['admin_flash']; unset($_SESSION['admin_flash']); ?>
        <div class="p-4">
          <div class="p-4 mb-4 text-sm text-<?= h($f['type']) === 'success' ? 'green' : 'red' ?>-800 bg-<?= h($f['type']) === 'success' ? 'green' : 'red' ?>-100 rounded-lg" role="alert">
            <?= h($f['text']) ?>
          </div>
        </div>
      <?php endif; ?>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">#</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employer</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-36">Deadline</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Status</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-48">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php if (!$jobs): ?>
              <tr>
                <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No pending jobs.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($jobs as $job): ?>
                <tr class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= $counter++; ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= h($job->job_title); ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= h($job->job_category); ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= h($job->company_name); ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <?php $ts = strtotime($job->application_deadline ?: ''); echo $ts ? date('j M, Y', $ts) : '—'; ?>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <a
                      href="<?= ADMINURL ?>/jobs-admins/status-jobs.php?id=<?= (int)$job->id ?>&status=0&r=<?= urlencode($_SERVER['REQUEST_URI']) ?>"
                      class="px-3 py-1 border border-green-300 text-green-600 rounded-md text-xs hover:bg-green-50"
                      title="Mark as Verified"
                    >Verify</a>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 flex gap-2">
                    <a
                      href="<?= ADMINURL ?>/jobs-admins/view-pendingjob.php?id=<?= (int)$job->id; ?>"
                      class="px-3 py-1 bg-blue-600 text-white rounded-md text-xs hover:bg-blue-700"
                      title="View job details"
                    >
                      <i class="fa fa-eye"></i> View
                    </a>
                    <a
                      href="<?= ADMINURL ?>/jobs-admins/delete-jobs.php?id=<?= (int)$job->id; ?>"
                      class="px-3 py-1 bg-red-600 text-white rounded-md text-xs hover:bg-red-700"
                      title="Delete job"
                      onclick="return confirm('Delete this pending job posting? This action cannot be undone.');"
                    >
                      <i class="fa fa-trash"></i>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
        <nav class="p-4">
          <ul class="flex justify-center items-center gap-1">
            <li>
              <a 
                class="px-3 py-1 border border-gray-300 rounded-md <?= ($page <= 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50') ?>"
                href="?page=<?= max(1, $page - 1) ?>"
              >Previous</a>
            </li>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
              <li>
                <a 
                  class="px-3 py-1 border border-gray-300 rounded-md <?= ($page === $i ? 'bg-blue-600 text-white' : 'hover:bg-gray-50') ?>"
                  href="?page=<?= $i ?>"
                ><?= $i ?></a>
              </li>
            <?php endfor; ?>
            <li>
              <a 
                class="px-3 py-1 border border-gray-300 rounded-md <?= ($page >= $totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50') ?>"
                href="?page=<?= min($totalPages, $page + 1) ?>"
              >Next</a>
            </li>
          </ul>
        </nav>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require '../layouts/footer.php'; ?>