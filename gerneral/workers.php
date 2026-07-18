<?php
require '../config/config.php';
require '../includes/public-helpers.php';

$q = trim($_GET['q'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;
$workers = [];
$totalRecords = 0;
$totalPages = 1;

if (db_available($conn) && table_exists($conn, 'users')) {
    try {
        $where = ["type = 'employee'"];
        $params = [];

        if ($q !== '') {
            $search = [];
            foreach (['fullname', 'name', 'username', 'email'] as $column) {
                if (has_column($conn, 'users', $column)) {
                    $search[] = "$column LIKE :q";
                }
            }
            if ($search) {
                $where[] = '(' . implode(' OR ', $search) . ')';
                $params[':q'] = '%' . $q . '%';
            }
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);
        $count = $conn->prepare("SELECT COUNT(*) FROM users $whereSql");
        $count->execute($params);
        $totalRecords = (int) $count->fetchColumn();
        $totalPages = max(1, (int) ceil($totalRecords / $limit));

        $stmt = $conn->prepare("SELECT * FROM users $whereSql ORDER BY id DESC LIMIT :limit OFFSET :offset");
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $workers = $stmt->fetchAll(PDO::FETCH_OBJ);
    } catch (Throwable $e) {
        $workers = [];
    }
}

require '../includes/header.php';
?>

<main class="bg-slate-50">
    <section class="border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-screen-xl px-4 py-10">
            <p class="text-sm font-semibold uppercase tracking-normal text-sky-600">Job Seekers</p>
            <h1 class="mt-3 text-4xl font-bold text-slate-950">Browse available talent</h1>
            <form method="GET" action="workers.php" class="mt-6 grid gap-3 rounded-lg border border-slate-200 bg-slate-50 p-4 md:grid-cols-[1fr_auto]">
                <input type="search" name="q" value="<?php echo h($q); ?>" placeholder="Search name, username, or email" class="rounded-md border border-slate-300 px-4 py-3 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30">
                <button type="submit" class="rounded-md bg-sky-600 px-5 py-3 text-sm font-semibold text-white hover:bg-sky-700">Search</button>
            </form>
        </div>
    </section>

    <section class="mx-auto max-w-screen-xl px-4 py-10">
        <p class="mb-5 text-sm text-slate-600"><?php echo (int) $totalRecords; ?> job seeker<?php echo $totalRecords === 1 ? '' : 's'; ?> found</p>
        <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            <?php if (!$workers): ?>
                <div class="rounded-lg border border-slate-200 bg-white p-6 text-slate-600 shadow-sm md:col-span-2 lg:col-span-3">No job seekers found.</div>
            <?php else: ?>
                <?php foreach ($workers as $worker): ?>
                    <?php $name = field($worker, 'fullname', field($worker, 'name', field($worker, 'username', 'Job Seeker'))); ?>
                    <article class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-900 text-lg font-bold text-white">
                                <?php echo h(strtoupper(substr((string) $name, 0, 1))); ?>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-slate-950"><?php echo h($name); ?></h2>
                                <p class="text-sm text-slate-600">@<?php echo h(field($worker, 'username', 'profile')); ?></p>
                            </div>
                        </div>
                        <div class="mt-5 space-y-2 text-sm text-slate-600">
                            <p><?php echo h(field($worker, 'email', 'Email not listed')); ?></p>
                            <p><?php echo h(field($worker, 'contact', 'Contact not listed')); ?></p>
                        </div>
                        <a href="<?php echo $base_url; ?>/users/public-profile.php?id=<?php echo (int) field($worker, 'id', 0); ?>" class="mt-5 inline-flex rounded-md border border-sky-600 px-4 py-2 text-sm font-semibold text-sky-600 hover:bg-sky-50">View profile</a>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav class="mt-8 flex justify-center gap-2">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?<?php echo http_build_query(array_filter(['q' => $q, 'page' => $i], fn($value) => $value !== '')); ?>" class="rounded-md border px-3 py-2 text-sm font-semibold <?php echo $page === $i ? 'border-sky-600 bg-sky-600 text-white' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50'; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    </section>
</main>

<?php require '../includes/footer.php'; ?>
