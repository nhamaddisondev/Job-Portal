<?php
require '../config/config.php';
require '../includes/public-helpers.php';

if (!isset($_SESSION['id'])) {
    header('Location: ' . BASEURL . '/auth/login.php');
    exit();
}

$userId = (int) $_SESSION['id'];
$applications = [];

if (db_available($conn) && table_exists($conn, 'applications')) {
    try {
        $columns = table_columns($conn, 'applications');
        $empCol = isset($columns['employee_id']) ? 'employee_id' : (isset($columns['user_id']) ? 'user_id' : null);

        if ($empCol && isset($columns['job_id'])) {
            $stmt = $conn->prepare("
                SELECT a.*, j.job_title, j.company_name, j.job_category, j.job_region, j.job_type, j.application_deadline, j.job_description
                FROM applications a
                LEFT JOIN jobs j ON a.job_id = j.id
                WHERE a.$empCol = :id
                ORDER BY a.id DESC
            ");
            $stmt->execute([':id' => $userId]);
            $applications = $stmt->fetchAll(PDO::FETCH_OBJ);
        }
    } catch (Throwable $e) {
        $applications = [];
    }
}

require '../includes/header.php';
?>

<main class="bg-slate-50">
    <section class="mx-auto max-w-screen-xl px-4 py-10">
        <div class="mb-8">
            <p class="text-sm font-semibold uppercase tracking-normal text-sky-600">My Activity</p>
            <h1 class="mt-3 text-4xl font-bold text-slate-950">My Applications</h1>
            <p class="mt-3 text-slate-600">Track all the jobs you've applied for.</p>
        </div>

        <?php if (!$applications): ?>
            <div class="rounded-lg border border-slate-200 bg-white p-8 text-center shadow-sm">
                <p class="text-slate-600">You haven't applied for any jobs yet.</p>
                <a href="<?php echo $base_url; ?>/findjobs.php" class="mt-4 inline-flex rounded-md bg-sky-600 px-5 py-3 text-sm font-semibold text-white hover:bg-sky-700">Browse Jobs</a>
            </div>
        <?php else: ?>
            <div class="grid gap-5">
                <?php foreach ($applications as $app): ?>
                    <article class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <h2 class="text-xl font-bold text-slate-950"><?php echo h(field($app, 'job_title', 'Untitled')); ?></h2>
                                <p class="text-sm text-slate-600"><?php echo h(field($app, 'company_name', 'Company not listed')); ?> &middot; <?php echo h(field($app, 'job_category', 'General')); ?></p>
                            </div>
                            <?php $status = field($app, 'status', 'pending'); ?>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold
                                <?php echo $status === 'accepted' ? 'bg-emerald-50 text-emerald-700' : ($status === 'rejected' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700'); ?>">
                                <?php echo h(ucfirst($status)); ?>
                            </span>
                        </div>
                        <div class="mt-4 flex flex-wrap gap-2 text-sm text-slate-600">
                            <span><?php echo h(field($app, 'job_region', 'Any region')); ?></span>
                            <span>&middot;</span>
                            <span><?php echo h(field($app, 'job_type', 'Job')); ?></span>
                            <span>&middot;</span>
                            <span>Deadline: <?php echo h(format_date(field($app, 'application_deadline'))); ?></span>
                        </div>
                        <div class="mt-4 flex gap-3">
                            <a href="<?php echo $base_url; ?>/jobs/job-single.php?id=<?php echo (int) field($app, 'job_id', 0); ?>" class="rounded-md border border-sky-600 px-4 py-2 text-sm font-semibold text-sky-600 hover:bg-sky-50">View Job</a>
                            <a href="<?php echo $base_url; ?>/users/withdraw-application.php?id=<?php echo (int) field($app, 'id', 0); ?>" class="rounded-md border border-red-300 px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50" onclick="return confirm('Withdraw your application?');">Withdraw</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php require '../includes/footer.php'; ?>