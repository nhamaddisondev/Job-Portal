<?php
require '../config/config.php';
require '../includes/public-helpers.php';

$jobId = (int) ($_GET['id'] ?? 0);
$job = null;
$specifications = [];
$message = trim($_GET['message'] ?? '');
$error = trim($_GET['error'] ?? '');

if ($jobId > 0 && db_available($conn) && table_exists($conn, 'jobs')) {
    try {
        $where = 'id = :id';
        $params = [':id' => $jobId];
        if (has_column($conn, 'jobs', 'status')) {
            $where .= ' AND status = :status';
            $params[':status'] = 1;
        }

        $stmt = $conn->prepare("SELECT * FROM jobs WHERE $where LIMIT 1");
        $stmt->execute($params);
        $job = $stmt->fetch(PDO::FETCH_OBJ);

        if ($job && table_exists($conn, 'job_specifications')) {
            $specStmt = $conn->prepare('SELECT * FROM job_specifications WHERE job_id = :job_id ORDER BY id ASC');
            $specStmt->execute([':job_id' => $jobId]);
            $specifications = $specStmt->fetchAll(PDO::FETCH_OBJ);
        }
    } catch (Throwable $e) {
        $job = null;
    }
}

require '../includes/header.php';
?>

<main class="bg-slate-50">
    <?php if (!$job): ?>
        <section class="mx-auto max-w-screen-md px-4 py-16 text-center">
            <h1 class="text-3xl font-bold text-slate-950">Job not found</h1>
            <p class="mt-3 text-slate-600">This job may be unavailable, pending approval, or removed.</p>
            <a href="<?php echo $base_url; ?>/findjobs.php" class="mt-6 inline-flex rounded-md bg-sky-600 px-5 py-3 text-sm font-semibold text-white hover:bg-sky-700">Back to jobs</a>
        </section>
    <?php else: ?>
        <section class="border-b border-slate-200 bg-white">
            <div class="mx-auto max-w-screen-xl px-4 py-10">
                <?php if ($message): ?>
                    <div class="mb-5 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?php echo h($message); ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="mb-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><?php echo h($error); ?></div>
                <?php endif; ?>

                <p class="text-sm font-semibold uppercase tracking-normal text-sky-600"><?php echo h(field($job, 'job_category', 'Job opening')); ?></p>
                <div class="mt-3 flex flex-wrap items-start justify-between gap-6">
                    <div>
                        <h1 class="max-w-4xl text-4xl font-bold text-slate-950"><?php echo h(field($job, 'job_title', 'Untitled job')); ?></h1>
                        <p class="mt-3 text-lg text-slate-600"><?php echo h(field($job, 'company_name', 'Company not listed')); ?></p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <form action="<?php echo $base_url; ?>/jobs/job-save.php" method="POST">
                            <input type="hidden" name="job_id" value="<?php echo (int) field($job, 'id', 0); ?>">
                            <button type="submit" class="rounded-md border border-sky-600 px-5 py-3 text-sm font-semibold text-sky-600 hover:bg-sky-50">Save Job</button>
                        </form>
                        <form action="<?php echo $base_url; ?>/jobs/submit_cv_availability.php" method="POST">
                            <input type="hidden" name="job_id" value="<?php echo (int) field($job, 'id', 0); ?>">
                            <button type="submit" class="rounded-md bg-sky-600 px-5 py-3 text-sm font-semibold text-white hover:bg-sky-700">Apply Now</button>
                        </form>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap gap-2 text-sm font-medium text-slate-700">
                    <span class="rounded-full bg-slate-100 px-3 py-1"><?php echo h(field($job, 'job_region', 'Any region')); ?></span>
                    <span class="rounded-full bg-slate-100 px-3 py-1"><?php echo h(field($job, 'job_type', 'Job')); ?></span>
                    <span class="rounded-full bg-slate-100 px-3 py-1"><?php echo h(field($job, 'work_arrangement', 'Work arrangement not listed')); ?></span>
                    <span class="rounded-full bg-slate-100 px-3 py-1"><?php echo h(field($job, 'salary', 'Salary not listed')); ?></span>
                </div>
            </div>
        </section>

        <section class="mx-auto grid max-w-screen-xl gap-8 px-4 py-10 lg:grid-cols-[1fr_320px]">
            <div class="space-y-8">
                <?php foreach ([
                    'job_description' => 'Job Description',
                    'responsibilities' => 'Responsibilities',
                    'education_experience' => 'Education & Experience',
                    'other_benefits' => 'Other Benefits',
                    'inclusivity_notes' => 'Inclusivity Notes',
                ] as $field => $label): ?>
                    <?php if (trim((string) field($job, $field, '')) !== ''): ?>
                        <article class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                            <h2 class="text-2xl font-bold text-slate-950"><?php echo h($label); ?></h2>
                            <div class="mt-4 leading-7 text-slate-700"><?php echo html_entity_decode((string) field($job, $field), ENT_QUOTES, 'UTF-8'); ?></div>
                        </article>
                    <?php endif; ?>
                <?php endforeach; ?>

                <?php if ($specifications): ?>
                    <article class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-2xl font-bold text-slate-950">Application Questions</h2>
                        <ul class="mt-4 space-y-3">
                            <?php foreach ($specifications as $spec): ?>
                                <li class="rounded-md bg-slate-50 p-4 text-slate-700"><?php echo h(field($spec, 'question_text', field($spec, 'question', 'Question'))); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </article>
                <?php endif; ?>
            </div>

            <aside class="h-fit rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-bold text-slate-950">Job Summary</h2>
                <dl class="mt-5 space-y-4 text-sm">
                    <div>
                        <dt class="font-semibold text-slate-500">Deadline</dt>
                        <dd class="mt-1 text-slate-900"><?php echo h(format_date(field($job, 'application_deadline'))); ?></dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-slate-500">Vacancy</dt>
                        <dd class="mt-1 text-slate-900"><?php echo h(field($job, 'vacancy', 'Not listed')); ?></dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-slate-500">Experience</dt>
                        <dd class="mt-1 text-slate-900"><?php echo h(field($job, 'experience', 'Not listed')); ?></dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-slate-500">Contact</dt>
                        <dd class="mt-1 text-slate-900"><?php echo h(field($job, 'company_email', 'Not listed')); ?></dd>
                    </div>
                </dl>
            </aside>
        </section>
    <?php endif; ?>
</main>

<?php require '../includes/footer.php'; ?>
