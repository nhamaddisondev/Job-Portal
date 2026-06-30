<?php
require '../../config/config.php';

if(isset($_SESSION['adminname'])) {
    header("location : " . ADMINURL . "/admins/login-admins.php");
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

//Page of jobs (with filters)
$listSql = "
  SELECT id, company_name, industry
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
                    <td class="px-4 py-3 whitespace-nowrap"><?= h($u->fullname); ?></td>
                    <td class="px-4 py-3 whitespace-nowrap"><?= h($u->email); ?></td>
                    <td class="px-4 py-3 whitespace-nowrap"><?= h($u->contact); ?></td>
                    <td class="px-4 py-3 whitespace-nowrap"><?= h($u->industry); ?></td>
                    <td class="px-4 py-3 whitespace-nowrap"><?= h($u->address_line); ?></td>
                    <td class="px-4 py-3 whitespace-nowrap"><?= h($u->established_year); ?></td>
                    <td class="px-4 py-3 whitespace-nowrap">
                      <button 
                        type="button"
                        class="px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        onclick="openEmployerModal(
                          <?= (int)$u->id; ?>,
                          '<?= h($u->fullname); ?>',
                          '<?= h($u->username); ?>',
                          '<?= h($u->email); ?>',
                          '<?= h($u->contact); ?>',
                          '<?= h($u->user_created_at); ?>',
                          '<?= h($u->img); ?>',
                          '<?= h($u->company_website); ?>',
                          '<?= h($u->industry); ?>',
                          '<?= h($u->address_line); ?>',
                          '<?= h($u->postal_code); ?>',
                          '<?= h($u->established_year); ?>',
                          '<?= h($u->operating_hours); ?>',
                          '<?= h($u->business_reg_no); ?>',
                          '<?= h($u->company_size); ?>',
                          '<?= h($u->org_type); ?>',
                          '<?= h($u->company_created_at); ?>',
                          '<?= h($u->updated_at); ?>'
                        )"
                      >View Details</button>
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

<!-- Modal: Employer Details -->
<div id="employerModal" class="fixed inset-0 z-50 overflow-y-auto hidden bg-black bg-opacity-50" aria-labelledby="employerModalLabel" aria-hidden="true">
  <div class="flex items-center justify-center min-h-screen p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
      <div class="flex justify-between items-center p-4 border-b border-gray-200 bg-gray-50">
        <h5 class="text-xl font-semibold" id="employerModalLabel">Employer Details</h5>
        <button type="button" onclick="closeEmployerModal()" class="text-gray-500 hover:text-gray-700 focus:outline-none">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <div class="p-6">
        <div class="flex items-start gap-4 mb-4">
          <img id="empLogo" src="" class="w-16 h-16 rounded border object-cover" alt="Logo">
          <div>
            <h5 class="mt-0 mb-1 text-lg font-semibold"><span id="empFullname">—</span> <small class="text-gray-500">(<span id="empUsername">—</span>)</small></h5>
            <div class="text-sm text-gray-500">Created: <span id="empUserCreated">—</span></div>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <h6 class="border-b border-gray-200 pb-2 mb-2 font-medium">Account</h6>
            <dl class="grid grid-cols-2 gap-2">
              <dt class="text-gray-500">Email</dt>
              <dd id="empEmail">—</dd>
              <dt class="text-gray-500">Contact</dt>
              <dd id="empContact">—</dd>
            </dl>
          </div>
          <div>
            <h6 class="border-b border-gray-200 pb-2 mb-2 font-medium">Company</h6>
            <dl class="grid grid-cols-2 gap-2">
              <dt class="text-gray-500">Website</dt>
              <dd id="empWebsite">—</dd>
              <dt class="text-gray-500">Industry</dt>
              <dd id="empIndustry">—</dd>
            </dl>
          </div>
        </div>

        <hr class="my-4 border-gray-200" />

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <dl class="grid grid-cols-2 gap-2">
              <dt class="text-gray-500">Address</dt>
              <dd id="empAddress">—</dd>
              <dt class="text-gray-500">Postal Code</dt>
              <dd id="empPostal">—</dd>
              <dt class="text-gray-500">Established Year</dt>
              <dd id="empEstYear">—</dd>
              <dt class="text-gray-500">Operating Hours</dt>
              <dd id="empHours">—</dd>
            </dl>
          </div>
          <div>
            <dl class="grid grid-cols-2 gap-2">
              <dt class="text-gray-500">Business Reg. No</dt>
              <dd id="empBRN">—</dd>
              <dt class="text-gray-500">Company Size</dt>
              <dd id="empCSize">—</dd>
              <dt class="text-gray-500">Organization Type</dt>
              <dd id="empOrgType">—</dd>
              <dt class="text-gray-500">Company Details Created:</dt>
              <dd id="empCrt">—</dd>
              <dt class="text-gray-500">Company Details Updated:</dt>
              <dd id="empUpd">—</dd>
            </dl>
          </div>
        </div>
      </div>

      <div class="flex justify-end p-4 border-t border-gray-200">
        <button type="button" onclick="closeEmployerModal()" class="px-4 py-2 border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- JavaScript for Modal -->
<script>
  function openEmployerModal(id, fullname, username, email, contact, usercreated, img, website, industry, address, postal, estyear, hours, brn, csize, orgtype, ccreated, cupdated) {
    document.getElementById('empLogo').src = img ? '<?= $siteRoot ?>/users/user-images/' + img : '';
    document.getElementById('empFullname').textContent = fullname;
    document.getElementById('empUsername').textContent = username;
    document.getElementById('empEmail').textContent = email;
    document.getElementById('empContact').textContent = contact;
    document.getElementById('empUserCreated').textContent = usercreated;
    document.getElementById('empWebsite').textContent = website;
    document.getElementById('empIndustry').textContent = industry;
    document.getElementById('empAddress').textContent = address;
    document.getElementById('empPostal').textContent = postal;
    document.getElementById('empEstYear').textContent = estyear;
    document.getElementById('empHours').textContent = hours;
    document.getElementById('empBRN').textContent = brn;
    document.getElementById('empCSize').textContent = csize;
    document.getElementById('empOrgType').textContent = orgtype;
    document.getElementById('empCrt').textContent = ccreated;
    document.getElementById('empUpd').textContent = cupdated;
    document.getElementById('employerModal').classList.remove('hidden');
  }

  function closeEmployerModal() {
    document.getElementById('employerModal').classList.add('hidden');
  }
</script>
<?php
require '../layouts/footer.php';
?>