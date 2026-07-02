<?php
require 'config/config.php';
require 'includes/public-helpers.php';

$q = trim($_GET['q'] ?? '');
$category = trim($_GET['category'] ?? '');
$region = trim($_GET['region'] ?? '');
$type = trim($_GET['type'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$limit = 9;
$offset = ($page - 1) * $limit;
$jobs = [];
$categories = [];
$regions = [];
$types = [];
$totalRecords = 0;
$totalPages = 1;

if (db_available($conn) && table_exists($conn, 'jobs')) {
    try {
        $where = [];
        $params = [];

        if (has_column($conn, 'jobs', 'status')) {
            $where[] = 'status = :status';
            $params[':status'] = 1;
        }

        if ($q !== '') {
            $searchParts = [];
            foreach (['job_title', 'company_name', 'job_category', 'job_region'] as $column) {
                if (has_column($conn, 'jobs', $column)) {
                    $searchParts[] = "$column LIKE :q";
                }
            }
            if ($searchParts) {
                $where[] = '(' . implode(' OR ', $searchParts) . ')';
                $params[':q'] = '%' . $q . '%';
            }
        }

        if ($category !== '' && has_column($conn, 'jobs', 'job_category')) {
            $where[] = 'job_category = :category';
            $params[':category'] = $category;
        }

        if ($region !== '' && has_column($conn, 'jobs', 'job_region')) {
            $where[] = 'job_region = :region';
            $params[':region'] = $region;
        }

        if ($type !== '' && has_column($conn, 'jobs', 'job_type')) {
            $where[] = 'job_type = :type';
            $params[':type'] = $type;
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $countStmt = $conn->prepare("SELECT COUNT(*) FROM jobs $whereSql");
        $countStmt->execute($params);
        $totalRecords = (int) $countStmt->fetchColumn();
        $totalPages = max(1, (int) ceil($totalRecords / $limit));

        if ($page > $totalPages) {
            $page = $totalPages;
            $offset = ($page - 1) * $limit;
        }

        $stmt = $conn->prepare("SELECT * FROM jobs $whereSql ORDER BY id DESC LIMIT :limit OFFSET :offset");
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $jobs = $stmt->fetchAll(PDO::FETCH_OBJ);

        if (has_column($conn, 'jobs', 'job_category')) {
            $categories = $conn->query("SELECT DISTINCT job_category FROM jobs WHERE TRIM(job_category) <> '' ORDER BY job_category ASC")->fetchAll(PDO::FETCH_COLUMN);
        }
        if (has_column($conn, 'jobs', 'job_region')) {
            $regions = $conn->query("SELECT DISTINCT job_region FROM jobs WHERE TRIM(job_region) <> '' ORDER BY job_region ASC")->fetchAll(PDO::FETCH_COLUMN);
        }
        if (has_column($conn, 'jobs', 'job_type')) {
            $types = $conn->query("SELECT DISTINCT job_type FROM jobs WHERE TRIM(job_type) <> '' ORDER BY job_type ASC")->fetchAll(PDO::FETCH_COLUMN);
        }
    } catch (Throwable $e) {
        $jobs = [];
    }
}

$baseParams = array_filter([
    'q' => $q,
    'category' => $category,
    'region' => $region,
    'type' => $type,
], fn($value) => $value !== '');

require 'includes/header.php';
?>

<main class="bg-slate-50">
    <section class="border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-screen-xl px-4 py-10">
            <p class="text-sm font-semibold uppercase tracking-normal text-sky-600">Find Jobs</p>
            <h1 class="mt-3 text-4xl font-bold text-slate-950">Search approved openings</h1>
            <p class="mt-3 max-w-2xl text-slate-600">Filter by title, company, category, region, or job type.</p>

            <form method="GET" action="findjobs.php" class="mt-6 grid gap-3 rounded-lg border border-slate-200 bg-slate-50 p-4 lg:grid-cols-[1.4fr_1fr_1fr_1fr_auto]">
                <input type="search" name="q" value="<?php echo h($q); ?>" placeholder="Search jobs" class="rounded-md border border-slate-300 px-4 py-3 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30">
                <select name="category" class="rounded-md border border-slate-300 px-4 py-3 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30">
                    <option value="">All categories</option>
                    <?php foreach ($categories as $item): ?>
                        <option value="<?php echo h($item); ?>" <?php echo $item === $category ? 'selected' : ''; ?>><?php echo h($item); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="region" class="rounded-md border border-slate-300 px-4 py-3 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30">
                    <option value="">All regions</option>
                    <?php foreach ($regions as $item): ?>
                        <option value="<?php echo h($item); ?>" <?php echo $item === $region ? 'selected' : ''; ?>><?php echo h($item); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="type" class="rounded-md border border-slate-300 px-4 py-3 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30">
                    <option value="">All types</option>
                    <?php foreach ($types as $item): ?>
                        <option value="<?php echo h($item); ?>" <?php echo $item === $type ? 'selected' : ''; ?>><?php echo h($item); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="rounded-md bg-sky-600 px-5 py-3 text-sm font-semibold text-white hover:bg-sky-700">Filter</button>
            </form>
        </div>
    </section>

    <section class="mx-auto max-w-screen-xl px-4 py-10">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-slate-600"><?php echo (int) $totalRecords; ?> job<?php echo $totalRecords === 1 ? '' : 's'; ?> found</p>
            <?php if ($baseParams): ?>
                <a href="<?php echo $base_url; ?>/findjobs.php" class="text-sm font-semibold text-sky-600 hover:text-sky-700">Clear filters</a>
            <?php endif; ?>
        </div>

        <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            <?php if (!$jobs): ?>
                <div class="rounded-lg border border-slate-200 bg-white p-6 text-slate-600 shadow-sm md:col-span-2 lg:col-span-3">
                    No jobs match your search yet.
                </div>
            <?php else: ?>
                <?php foreach ($jobs as $job): ?>
                    <article class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-sky-600"><?php echo h(field($job, 'job_category', 'General')); ?></p>
                                <h2 class="mt-2 text-xl font-bold text-slate-950"><?php echo h(field($job, 'job_title', 'Untitled job')); ?></h2>
                            </div>
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Open</span>
                        </div>
                        <p class="mt-3 text-sm font-medium text-slate-700"><?php echo h(field($job, 'company_name', 'Company not listed')); ?></p>
                        <p class="mt-3 text-sm leading-6 text-slate-600"><?php echo h(excerpt(field($job, 'job_description', ''), 135)); ?></p>
                        <div class="mt-4 flex flex-wrap gap-2 text-xs font-medium text-slate-600">
                            <span class="rounded-full bg-slate-100 px-3 py-1"><?php echo h(field($job, 'job_region', 'Any region')); ?></span>
                            <span class="rounded-full bg-slate-100 px-3 py-1"><?php echo h(field($job, 'job_type', 'Job')); ?></span>
                            <span class="rounded-full bg-slate-100 px-3 py-1"><?php echo h(field($job, 'salary', 'Salary not listed')); ?></span>
                        </div>
                        <div class="mt-5 flex items-center justify-between gap-3">
                            <span class="text-xs text-slate-500">Deadline: <?php echo h(format_date(field($job, 'application_deadline'))); ?></span>
                            <a href="<?php echo $base_url; ?>/jobs/job-single.php?id=<?php echo (int) field($job, 'id', 0); ?>" class="rounded-md bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Details</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav class="mt-8 flex justify-center gap-2">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php $params = array_merge($baseParams, ['page' => $i]); ?>
                    <a href="?<?php echo http_build_query($params); ?>" class="rounded-md border px-3 py-2 text-sm font-semibold <?php echo $page === $i ? 'border-sky-600 bg-sky-600 text-white' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50'; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    </section>
</main>

<?php require 'includes/footer.php'; ?>
