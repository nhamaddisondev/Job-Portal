<?php
require '../config/config.php';
require '../includes/public-helpers.php';

if (!isset($_SESSION['id'])) {
    header('Location: ' . BASEURL . '/auth/login.php');
    exit();
}

$userId = (int) $_SESSION['id'];
$savedJobs = [];

if (db_available($conn) && table_exists($conn, 'saved_jobs') && table_exists($conn, 'jobs')) {
    try {
        $stmt = $conn->prepare("
            SELECT s.*, j.job_title, j.company_name, j.job_category, j.job_region, j.job_type, j.salary, j.application_deadline
            FROM saved_jobs s
            INNER JOIN jobs j ON s.job_id = j.id
            WHERE s.user_id = :id
            ORDER BY s.id DESC
        ");
        $stmt->execute([':id' => $userId]);
        $savedJobs = $stmt->fetchAll(PDO::FETCH_OBJ);
    } catch (Throwable $e) {
        $savedJobs = [];
    }
}

require '../includes/header.php';
?>

<main class="bg-slate-50">
    <section class="mx-auto max-w-screen-xl px-4 py-10">
        <div class="mb-8">
            <p class="text-sm font-semibold uppercase tracking-normal text-sky-600">My Activity</p>
            <h1 class="mt-3 text-4xl font-bold text-slate-950">Saved Jobs</h1>
            <p class="mt-3 text-slate-600">Jobs you've bookmarked for later.</p>
        </div>

        <?php if (!$savedJobs): ?>
            <div class="rounded-lg border border-slate-200 bg-white p-8 text-center shadow-sm">
                <p class="text-slate-600">You haven't saved any jobs yet.</p>
                <a href="<?php echo $base_url; ?>/findjobs.php" class="mt-4 inline-flex rounded-md bg-sky-600 px-5 py-3 text-sm font-semibold text-white hover:bg-sky-700">Browse Jobs</a>
            </div>
        <?php else: ?>
            <div class="grid gap-5">
                <?php foreach ($savedJobs as $job): ?>
                    <article class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <h2 class="text-xl font-bold text-slate-950"><?php echo h(field($job, 'job_title', 'Untitled')); ?></h2>
                                <p class="text-sm text-slate-600"><?php echo h(field($job, 'company_name', 'Company not listed')); ?> &middot; <?php echo h(field($job, 'job_category', 'General')); ?></p>
                            </div>
                        </div>
                        <div class="mt-4 flex flex-wrap gap-2 text-sm text-slate-600">
                            <span><?php echo h(field($job, 'job_region', 'Any region')); ?></span>
                            <span>&middot;</span>
                            <span><?php echo h(field($job, 'job_type', 'Job')); ?></span>
                            <span>&middot;</span>
                            <span><?php echo h(field($job, 'salary', 'Salary not listed')); ?></span>
                        </div>
                        <div class="mt-4 flex gap-3">
                            <a href="<?php echo $base_url; ?>/jobs/job-single.php?id=<?php echo (int) field($job, 'job_id', 0); ?>" class="rounded-md bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700">View Details</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php require '../includes/footer.php'; ?>