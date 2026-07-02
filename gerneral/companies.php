<?php
require '../config/config.php';
require '../includes/public-helpers.php';

$q = trim($_GET['q'] ?? '');
$industry = trim($_GET['industry'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;
$companies = [];
$industries = [];
$totalRecords = 0;
$totalPages = 1;

if (db_available($conn)) {
    try {
        if (table_exists($conn, 'employers')) {
            $where = [];
            $params = [];
            if ($q !== '') {
                $where[] = '(company_name LIKE :q OR industry LIKE :q)';
                $params[':q'] = '%' . $q . '%';
            }
            if ($industry !== '' && has_column($conn, 'employers', 'industry')) {
                $where[] = 'industry = :industry';
                $params[':industry'] = $industry;
            }
            $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
            $count = $conn->prepare("SELECT COUNT(*) FROM employers $whereSql");
            $count->execute($params);
            $totalRecords = (int) $count->fetchColumn();
            $totalPages = max(1, (int) ceil($totalRecords / $limit));

            $stmt = $conn->prepare("SELECT * FROM employers $whereSql ORDER BY id DESC LIMIT :limit OFFSET :offset");
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $companies = $stmt->fetchAll(PDO::FETCH_OBJ);

            if (has_column($conn, 'employers', 'industry')) {
                $industries = $conn->query("SELECT DISTINCT industry FROM employers WHERE TRIM(industry) <> '' ORDER BY industry ASC")->fetchAll(PDO::FETCH_COLUMN);
            }
        } elseif (table_exists($conn, 'jobs') && has_column($conn, 'jobs', 'company_name')) {
            $where = ["TRIM(company_name) <> ''"];
            $params = [];
            if ($q !== '') {
                $where[] = 'company_name LIKE :q';
                $params[':q'] = '%' . $q . '%';
            }
            if (has_column($conn, 'jobs', 'status')) {
                $where[] = 'status = 1';
            }
            $whereSql = 'WHERE ' . implode(' AND ', $where);
            $count = $conn->prepare("SELECT COUNT(DISTINCT company_name) FROM jobs $whereSql");
            $count->execute($params);
            $totalRecords = (int) $count->fetchColumn();
            $totalPages = max(1, (int) ceil($totalRecords / $limit));
            $stmt = $conn->prepare("SELECT company_name, MAX(company_email) AS email, COUNT(*) AS open_jobs FROM jobs $whereSql GROUP BY company_name ORDER BY company_name ASC LIMIT :limit OFFSET :offset");
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $companies = $stmt->fetchAll(PDO::FETCH_OBJ);
        }
    } catch (Throwable $e) {
        $companies = [];
    }
}

require '../includes/header.php';
?>

<main class="bg-slate-50">
    <section class="border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-screen-xl px-4 py-10">
            <p class="text-sm font-semibold uppercase tracking-normal text-sky-600">Companies</p>
            <h1 class="mt-3 text-4xl font-bold text-slate-950">Browse employers</h1>
            <form method="GET" action="companies.php" class="mt-6 grid gap-3 rounded-lg border border-slate-200 bg-slate-50 p-4 md:grid-cols-[1fr_260px_auto]">
                <input type="search" name="q" value="<?php echo h($q); ?>" placeholder="Search company or industry" class="rounded-md border border-slate-300 px-4 py-3 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30">
                <select name="industry" class="rounded-md border border-slate-300 px-4 py-3 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30">
                    <option value="">All industries</option>
                    <?php foreach ($industries as $item): ?>
                        <option value="<?php echo h($item); ?>" <?php echo $item === $industry ? 'selected' : ''; ?>><?php echo h($item); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="rounded-md bg-sky-600 px-5 py-3 text-sm font-semibold text-white hover:bg-sky-700">Filter</button>
            </form>
        </div>
    </section>

    <section class="mx-auto max-w-screen-xl px-4 py-10">
        <p class="mb-5 text-sm text-slate-600"><?php echo (int) $totalRecords; ?> compan<?php echo $totalRecords === 1 ? 'y' : 'ies'; ?> found</p>
        <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            <?php if (!$companies): ?>
                <div class="rounded-lg border border-slate-200 bg-white p-6 text-slate-600 shadow-sm md:col-span-2 lg:col-span-3">No companies found.</div>
            <?php else: ?>
                <?php foreach ($companies as $company): ?>
                    <article class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-md bg-sky-100 text-lg font-bold text-sky-700">
                                <?php echo h(strtoupper(substr((string) field($company, 'company_name', 'C'), 0, 1))); ?>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-slate-950"><?php echo h(field($company, 'company_name', 'Company')); ?></h2>
                                <p class="text-sm text-slate-600"><?php echo h(field($company, 'industry', 'Industry not listed')); ?></p>
                            </div>
                        </div>
                        <div class="mt-5 space-y-2 text-sm text-slate-600">
                            <?php if (field($company, 'company_website')): ?>
                                <p><a href="<?php echo h(field($company, 'company_website')); ?>" class="font-semibold text-sky-600 hover:text-sky-700" target="_blank" rel="noopener">Company website</a></p>
                            <?php endif; ?>
                            <p><?php echo h(field($company, 'address_line', field($company, 'email', 'Contact details not listed'))); ?></p>
                            <p><?php echo (int) field($company, 'open_jobs', 0); ?> open job<?php echo (int) field($company, 'open_jobs', 0) === 1 ? '' : 's'; ?></p>
                        </div>
                        <a href="<?php echo $base_url; ?>/findjobs.php?q=<?php echo urlencode(field($company, 'company_name', '')); ?>" class="mt-5 inline-flex rounded-md border border-sky-600 px-4 py-2 text-sm font-semibold text-sky-600 hover:bg-sky-50">View jobs</a>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav class="mt-8 flex justify-center gap-2">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?<?php echo http_build_query(array_filter(['q' => $q, 'industry' => $industry, 'page' => $i], fn($value) => $value !== '')); ?>" class="rounded-md border px-3 py-2 text-sm font-semibold <?php echo $page === $i ? 'border-sky-600 bg-sky-600 text-white' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50'; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    </section>
</main>

<?php require '../includes/footer.php'; ?>
