<?php
require '../../config/config.php';

if (!isset($_SESSION['adminname'])) {
    header("Location: " . ADMINURL . "/admins/login-admins.php");
    exit();
}

//Page Context
$pageTitle = "Registered Employers";
$breadcrumb = "Users";

//Helpers
function h($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

//Inputs
$q = trim($_GET['q'] ?? '');
$industry = trim($_GET['industry'] ?? '');

//Pagination
$limit   = 10;
$page    = (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) ? (int)$_GET['page'] : 1;
$offset  = ($page - 1) * $limit;
$counter = $offset + 1;

//Distinct industries for filter dropdown
$industries = [];
if (table_exists($conn, 'employers')) {
    try {
        $indStmt = $conn->query("SELECT DISTINCT industry FROM employers WHERE TRIM(industry) <> '' ORDER BY industry ASC");
        $industries = $indStmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Throwable $e) {
        $industries = [];
    }
}

//Build WHERE
$where  = [];
$params = [];
if ($industry !== '') { $where[] = "industry = :ind";      $params[':ind'] = $industry; }
if ($q !== '')         { $where[] = "(company_name LIKE :q OR industry LIKE :q)"; $params[':q'] = '%'.$q.'%'; }
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

//Totals (with filters)
$totalStmt = $conn->prepare("SELECT COUNT(*) AS total FROM employers $whereSql");
$totalStmt->execute($params);
$totalRecords = (int)$totalStmt->fetch(PDO::FETCH_OBJ)->total;
$totalPages   = max(1, (int)ceil($totalRecords / $limit));

//Clamp page if overshoot
if ($page > $totalPages) {
  $page   = $totalPages;
  $offset = ($page - 1) * $limit;
  $counter = $offset + 1;
}

//Page of employers (with filters)
$listSql = "
  SELECT id, fullname, username, email, contact, industry, company_name, address_line, img, established_year
  FROM employers
  $whereSql
  ORDER BY id DESC
  LIMIT :limit OFFSET :offset
";
$stmt = $conn->prepare($listSql);
foreach ($params as $k=>$v) { $stmt->bindValue($k, $v); }
$stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$employers = $stmt->fetchAll(PDO::FETCH_OBJ);

// Preserve filters in pagination
$qs = [];
if ($q !== '') $qs['q'] = $q;
if ($industry !== '') $qs['industry'] = $industry;
$baseQS = http_build_query($qs);
$sep = $baseQS ? '&' : '';

$siteRoot = preg_replace('#/admin/?$#', '', rtrim(ADMINURL, '/'));

require '../layouts/header.php';
?>
<div class="p-4">
  <div class="max-w-full">
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
      <!-- Card Header -->
      <div class="p-4 border-b border-gray-200">
        <div class="flex flex-wrap items-center justify-between">
          <h2 class="text-lg font-semibold mb-2 md:mb-0">Registered Employers</h2>

          <!-- Filters/Search -->
          <form class="flex flex-wrap items-center gap-2" method="get" action="show-employers.php">
            <div>
              <label for="industry" class="sr-only">Industry</label>
              <select class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" id="industry" name="industry">
                <option value="">All industries</option>
                <?php foreach ($industries as $opt): ?>
                  <option value="<?= h($opt) ?>" <?= ($opt === $industry ? 'selected' : '') ?>>
                    <?= h($opt) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label for="q" class="sr-only">Search</label>
              <input type="text" class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" id="q" name="q"
                     placeholder="Search name or industry…" value="<?= h($q) ?>">
            </div>
            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Filter</button>
            <?php if ($q !== '' || $industry !== ''): ?>
              <a href="show-employers.php" class="px-4 py-2 border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">Reset</a>
            <?php endif; ?>
          </form>
        </div>
      </div>

      <!-- Card Body -->
      <div class="p-0">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">#</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Industry</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Established</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-28">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <?php if (!$employers): ?>
                <tr>
                  <td colspan="8" class="px-4 py-4 text-center text-gray-500">No employers to show.</td>
                </tr>
              <?php else: ?>
                <?php foreach($employers as $u): ?>
                  <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 whitespace-nowrap"><?= $counter++; ?></td>
                    <td class="px-4 py-3 whitespace-nowrap"><?= h($u->fullname ?? $u->company_name ?? '—'); ?></td>
                    <td class="px-4 py-3 whitespace-nowrap"><?= h($u->email ?? '—'); ?></td>
                    <td class="px-4 py-3 whitespace-nowrap"><?= h($u->contact ?? '—'); ?></td>
                    <td class="px-4 py-3 whitespace-nowrap"><?= h($u->industry ?? '—'); ?></td>
                    <td class="px-4 py-3 whitespace-nowrap"><?= h($u->address_line ?? '—'); ?></td>
                    <td class="px-4 py-3 whitespace-nowrap"><?= h($u->established_year ?? '—'); ?></td>
                    <td class="px-4 py-3 whitespace-nowrap">
                      <button type="button" class="px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        onclick="alert('Employer ID: <?= (int)$u->id; ?>')">View</button>
                    </td>
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
              <a class="px-3 py-1 border border-gray-300 rounded-md <?= ($page <= 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50') ?>" href="?<?= $baseQS . $sep ?>page=<?= max(1, $page - 1) ?>">Previous</a>
            </li>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
              <li>
                <a class="px-3 py-1 border border-gray-300 rounded-md <?= ($page === $i ? 'bg-indigo-600 text-white' : 'hover:bg-gray-50') ?>" href="?<?= $baseQS . $sep ?>page=<?= $i ?>"><?= $i ?></a>
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
</div>

<?php
require '../layouts/footer.php';
?>