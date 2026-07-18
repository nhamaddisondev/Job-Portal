<?php
require '../config/config.php';
require '../includes/public-helpers.php';

if (!isset($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'employer') {
    header('Location: ' . BASEURL . '/auth/login.php');
    exit();
}

$userId = (int) $_SESSION['id'];
$jobs = [];

if (db_available($conn) && table_exists($conn, 'jobs')) {
    try {
        $stmt = $conn->prepare("SELECT * FROM jobs WHERE company_id = :id ORDER BY id DESC");
        $stmt->execute([':id' => $userId]);
        $jobs = $stmt->fetchAll(PDO::FETCH_OBJ);
    } catch (Throwable $e) {
        $jobs = [];
    }
}

require '../includes/header.php';
?>

<main class="bg-slate-50">
    <section class="mx-auto max-w-screen-xl px-4 py-10">
        <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-normal text-sky-600">Employer</p>
                <h1 class="mt-3 text-4xl font-bold text-slate-950">My Posted Jobs</h1>
            </div>
            <a href="<?php echo $base_url; ?>/jobs/post-job.php" class="rounded-md bg-sky-600 px-5 py-3 text-sm font-semibold text-white hover:bg-sky-700">Post New Job</a>
        </div>

        <?php if (!$jobs): ?>
            <div class="rounded-lg border border-slate-200 bg-white p-8 text-center shadow-sm">
                <p class="text-slate-600">You haven't posted any jobs yet.</p>
                <a href="<?php echo $base_url; ?>/jobs/post-job.php" class="mt-4 inline-flex rounded-md bg-sky-600 px-5 py-3 text-sm font-semibold text-white hover:bg-sky-700">Post Your First Job</a>
            </div>
        <?php else: ?>
            <div class="grid gap-5">
                <?php foreach ($jobs as $job): ?>
                    <article class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <h2 class="text-xl font-bold text-slate-950"><?php echo h(field($job, 'job_title', 'Untitled')); ?></h2>
                                <p class="text-sm text-slate-600"><?php echo h(field($job, 'job_category', 'General')); ?> &middot; <?php echo h(field($job, 'job_region', 'Any region')); ?></p>
                            </div>
                            <?php $status = (int) field($job, 'status', 0); ?>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold <?php echo $status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'; ?>">
                                <?php echo $status === 'active' ? 'Active' : 'Pending'; ?>
                            </span>
                        </div>
                        <p class="mt-3 text-sm text-slate-600">Deadline: <?php echo h(format_date(field($job, 'application_deadline'))); ?></p>
                        <div class="mt-4 flex gap-3">
                            <a href="<?php echo $base_url; ?>/jobs/job-single.php?id=<?php echo (int) field($job, 'id', 0); ?>" class="rounded-md border border-sky-600 px-4 py-2 text-sm font-semibold text-sky-600 hover:bg-sky-50">View</a>
                            <a href="<?php echo $base_url; ?>/jobs/job-update.php?id=<?php echo (int) field($job, 'id', 0); ?>" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Edit</a>
                            <a href="<?php echo $base_url; ?>/jobs/job-delete.php?id=<?php echo (int) field($job, 'id', 0); ?>" class="rounded-md border border-red-300 px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50" onclick="return confirm('Delete this job? This cannot be undone.');">Delete</a>
                            <a href="<?php echo $base_url; ?>/jobs/resumes_list.php?job_id=<?php echo (int) field($job, 'id', 0); ?>" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Applicants</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php require '../includes/footer.php'; ?>