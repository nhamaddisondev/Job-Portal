<?php require '../../config/config.php'; ?>

<?php
if (!isset($_SESSION['adminname'])) {
    header("Location: " . ADMINURL . "/admins/login-admins.php");
    exit();
}

$pageTitle = "All Jobs";
$breadcrumb = "Systems";

function h($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

// Inputs (filters)
$q         = trim($_GET['q'] ?? '');
$categoryF = trim($_GET['category'] ?? '');
$employerF = trim($_GET['employer'] ?? '');

// Pagination
$limit   = 10;
$page    = (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) ? (int)$_GET['page'] : 1;
$offset  = ($page - 1) * $limit;
$counter = $offset + 1;

// Build WHERE
$where  = [];
$params = [];
if ($categoryF !== '') { $where[] = "job_category = :cat";      $params[':cat'] = $categoryF; }
if ($employerF !== '') { $where[] = "company_name = :emp";      $params[':emp'] = $employerF; }
if ($q !== '')         { $where[] = "(job_title LIKE :q OR job_category LIKE :q OR company_name LIKE :q)"; $params[':q'] = '%'.$q.'%'; }
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Distinct filters (dropdowns)
$categories = $conn->query("SELECT DISTINCT job_category FROM jobs WHERE TRIM(job_category) <> '' ORDER BY job_category ASC")->fetchAll(PDO::FETCH_COLUMN);
$employers  = $conn->query("SELECT DISTINCT company_name FROM jobs WHERE TRIM(company_name) <> '' ORDER BY company_name ASC")->fetchAll(PDO::FETCH_COLUMN);

// Totals (with filters)
$totalStmt = $conn->prepare("SELECT COUNT(*) AS total FROM jobs $whereSql");
$totalStmt->execute($params);
$totalRecords = (int)$totalStmt->fetch(PDO::FETCH_OBJ)->total;
$totalPages   = max(1, (int)ceil($totalRecords / $limit));

// Clamp page
if ($page > $totalPages) {
  $page   = $totalPages;
  $offset = ($page - 1) * $limit;
  $counter = $offset + 1;
}

// Page of jobs (with filters)
$listSql = "
  SELECT id, job_title, job_category, company_name, application_deadline, status
  FROM jobs
  $whereSql
  ORDER BY id DESC
  LIMIT :limit OFFSET :offset
";
$stmt = $conn->prepare($listSql);
foreach ($params as $k=>$v) { $stmt->bindValue($k, $v); }
$stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$jobs = $stmt->fetchAll(PDO::FETCH_OBJ);

// Preserve filters in pagination
$qs = [];
if ($q !== '')         $qs['q'] = $q;
if ($categoryF !== '') $qs['category'] = $categoryF;
if ($employerF !== '') $qs['employer'] = $employerF;
$baseQS = http_build_query($qs);
$sep    = $baseQS ? '&' : '';

require "../layouts/header.php";
?>

<div class="flex flex-col">
  <div class="bg-white shadow-md rounded-lg overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
      <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="mb-2 md:mb-0">
          <h2 class="text-lg font-semibold text-gray-800">All Jobs</h2>
          <p class="text-sm text-gray-500">Total: <?= (int)$totalRecords ?></p>
        </div>
        <form class="flex flex-wrap items-center gap-2" method="get" action="show-jobs.php">
          <select class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" id="category" name="category">
            <option value="">All categories</option>
            <?php foreach ($categories as $c): ?>
              <option value="<?= h($c) ?>" <?= ($c === $categoryF ? 'selected' : '') ?>><?= h($c) ?></option>
            <?php endforeach; ?>
          </select>
          <select class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" id="employer" name="employer">
            <option value="">All employers</option>
            <?php foreach ($employers as $e): ?>
              <option value="<?= h($e) ?>" <?= ($e === $employerF ? 'selected' : '') ?>><?= h($e) ?></option>
            <?php endforeach; ?>
          </select>
          <input type="text" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" id="q" name="q" placeholder="Search title, category, employer…" value="<?= h($q) ?>">
          <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Filter</button>
          <?php if ($q !== '' || $categoryF !== '' || $employerF !== ''): ?>
            <a href="show-jobs.php" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500">Reset</a>
          <?php endif; ?>
        </form>
      </div>
    </div>

    <div class="p-0">
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
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Action</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php if (!$jobs): ?>
              <tr>
                <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No jobs found.</td>
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
                    <?php if ($job->status === 'active'): ?>
                      <a href="<?= ADMINURL ?>/jobs-admins/status-jobs.php?id=<?= (int)$job->id; ?>&status=active&r=<?= urlencode($_SERVER['REQUEST_URI']); ?>" class="px-3 py-1 border border-red-300 text-red-600 rounded-md text-xs hover:bg-red-50" title="Mark as Unverified">Unverify</a>
                    <?php else: ?>
                      <a href="<?= ADMINURL ?>/jobs-admins/status-jobs.php?id=<?= (int)$job->id; ?>&status=pending&r=<?= urlencode($_SERVER['REQUEST_URI']); ?>" class="px-3 py-1 border border-green-300 text-green-600 rounded-md text-xs hover:bg-green-50" title="Mark as Verified">Verify</a>
                    <?php endif; ?>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <a href="<?= ADMINURL ?>/jobs-admins/delete-jobs.php?id=<?= (int)$job->id; ?>" class="px-3 py-1 bg-red-600 text-white rounded-md text-xs hover:bg-red-700" title="Delete job" onclick="return confirm('Delete this job posting? This action cannot be undone.');">Delete</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <nav class="p-4">
        <ul class="flex justify-center items-center gap-1">
          <li>
            <a class="px-3 py-1 border border-gray-300 rounded-md <?= ($page <= 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50') ?>" href="?<?= $baseQS . $sep ?>page=<?= max(1, $page - 1) ?>">Previous</a>
          </li>
          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li>
              <a class="px-3 py-1 border border-gray-300 rounded-md <?= ($page === $i ? 'bg-blue-600 text-white' : 'hover:bg-gray-50') ?>" href="?<?= $baseQS . $sep ?>page=<?= $i ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>
          <li>
            <a class="px-3 py-1 border border-gray-300 rounded-md <?= ($page >= $totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50') ?>" href="?<?= $baseQS . $sep ?>page=<?= min($totalPages, $page + 1) ?>">Next</a>
          </li>
        </ul>
      </nav>
    </div>
  </div>
</div>

<?php require '../layouts/footer.php'; ?>