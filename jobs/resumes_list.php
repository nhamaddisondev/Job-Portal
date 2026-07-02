<?php
require '../config/config.php';
require '../includes/public-helpers.php';

$jobId = (int) ($_GET['job_id'] ?? 0);
$resumes = [];

if (db_available($conn) && table_exists($conn, 'applications') && $jobId > 0) {
    try {
        $stmt = $conn->prepare("
            SELECT a.*, u.fullname, u.username, u.email, u.contact
            FROM applications a
            LEFT JOIN users u ON a.employee_id = u.id OR a.user_id = u.id
            WHERE a.job_id = :job_id
            ORDER BY a.id DESC
        ");
        $stmt->execute([':job_id' => $jobId]);
        $resumes = $stmt->fetchAll(PDO::FETCH_OBJ);
    } catch (Throwable $e) {
        $resumes = [];
    }
}

require '../includes/header.php';
?>

<main class="bg-slate-50">
    <section class="mx-auto max-w-screen-xl px-4 py-10">
        <div class="mb-8">
            <p class="text-sm font-semibold uppercase tracking-normal text-sky-600">Applicants</p>
            <h1 class="mt-3 text-4xl font-bold text-slate-950">Resumes / Applications</h1>
            <p class="mt-3 text-slate-600">Review applicants for this position.</p>
        </div>

        <?php if (!$resumes): ?>
            <div class="rounded-lg border border-slate-200 bg-white p-8 text-center shadow-sm">
                <p class="text-slate-600">No applications received yet for this job.</p>
                <a href="<?php echo $base_url; ?>/users/employer_dashboard.php" class="mt-4 inline-flex rounded-md bg-sky-600 px-5 py-3 text-sm font-semibold text-white hover:bg-sky-700">Back to Dashboard</a>
            </div>
        <?php else: ?>
            <div class="grid gap-5">
                <?php foreach ($resumes as $resume): ?>
                    <article class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <h2 class="text-xl font-bold text-slate-950"><?php echo h($resume->fullname ?: $resume->username ?: 'Applicant'); ?></h2>
                                <p class="text-sm text-slate-600"><?php echo h($resume->email ?? ''); ?> <?php echo $resume->contact ? '&middot; ' . h($resume->contact) : ''; ?></p>
                            </div>
                            <?php $status = $resume->status ?? 'pending'; ?>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold
                                <?php echo $status === 'accepted' ? 'bg-emerald-50 text-emerald-700' : ($status === 'rejected' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700'); ?>">
                                <?php echo h(ucfirst($status)); ?>
                            </span>
                        </div>
                        <p class="mt-3 text-sm text-slate-500">Applied: <?php echo h(format_date($resume->created_at ?? '')); ?></p>
                        <div class="mt-4 flex gap-3">
                            <a href="<?php echo $base_url; ?>/users/public-profile.php?id=<?php echo (int) ($resume->employee_id ?: $resume->user_id ?: 0); ?>" class="rounded-md border border-sky-600 px-4 py-2 text-sm font-semibold text-sky-600 hover:bg-sky-50">View Profile</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php require '../includes/footer.php'; ?>