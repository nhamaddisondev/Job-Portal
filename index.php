<?php
require 'config/config.php';
require 'includes/public-helpers.php';

$stats = [
    'jobs' => 0,
    'companies' => 0,
    'seekers' => 0,
];
$latestJobs = [];
$categories = [];

if (db_available($conn)) {
    try {
        if (table_exists($conn, 'jobs')) {
            $where = has_column($conn, 'jobs', 'status') ? 'WHERE status = 1' : '';
            $stats['jobs'] = (int) $conn->query("SELECT COUNT(*) FROM jobs $where")->fetchColumn();
            $latestJobs = $conn->query("SELECT * FROM jobs $where ORDER BY id DESC LIMIT 6")->fetchAll(PDO::FETCH_OBJ);

            if (has_column($conn, 'jobs', 'job_category')) {
                $categories = $conn->query("SELECT DISTINCT job_category AS name FROM jobs WHERE TRIM(job_category) <> '' ORDER BY job_category ASC LIMIT 8")->fetchAll(PDO::FETCH_OBJ);
            }
        }

        if (table_exists($conn, 'categories')) {
            $categories = $conn->query('SELECT id, name FROM categories ORDER BY name ASC LIMIT 8')->fetchAll(PDO::FETCH_OBJ);
        }

        if (table_exists($conn, 'employers')) {
            $stats['companies'] = (int) $conn->query('SELECT COUNT(*) FROM employers')->fetchColumn();
        } elseif (table_exists($conn, 'jobs') && has_column($conn, 'jobs', 'company_name')) {
            $stats['companies'] = (int) $conn->query("SELECT COUNT(DISTINCT company_name) FROM jobs WHERE TRIM(company_name) <> ''")->fetchColumn();
        }

        if (table_exists($conn, 'users')) {
            $stats['seekers'] = (int) $conn->query("SELECT COUNT(*) FROM users WHERE UPPER(type) = 'JOB SEEKER' OR role = 'employee'")->fetchColumn();
        }
    } catch (Throwable $e) {
        $latestJobs = [];
        $categories = [];
    }
}

require 'includes/header.php';
?>

<main class="bg-slate-50">
    <section class="border-b border-slate-200 bg-white">
        <div class="mx-auto grid min-h-[560px] max-w-screen-xl gap-10 px-4 py-12 lg:grid-cols-[1fr_420px] lg:items-center">
            <div>
                <p class="text-sm font-semibold uppercase tracking-normal text-sky-600">Online Job Portal</p>
                <h1 class="mt-3 max-w-3xl text-4xl font-bold tracking-normal text-slate-950 md:text-6xl">Find work and hire talent from one practical portal</h1>
                <p class="mt-5 max-w-2xl text-lg leading-8 text-slate-600">Search approved jobs, browse companies, and connect with job seekers through a simple local recruitment system.</p>

                <form action="<?php echo $base_url; ?>/findjobs.php" method="GET" class="mt-8 grid gap-3 rounded-lg border border-slate-200 bg-slate-50 p-3 shadow-sm md:grid-cols-[1fr_auto]">
                    <label for="q" class="sr-only">Search jobs</label>
                    <input id="q" name="q" type="search" placeholder="Job title, company, or category" class="w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30">
                    <button type="submit" class="rounded-md bg-sky-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-sky-700">Search Jobs</button>
                </form>
            </div>

            <div class="grid gap-4">
                <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-semibold text-slate-500">Available Jobs</p>
                    <p class="mt-2 text-4xl font-bold text-slate-950"><?php echo (int) $stats['jobs']; ?></p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-semibold text-slate-500">Companies</p>
                        <p class="mt-2 text-3xl font-bold text-slate-950"><?php echo (int) $stats['companies']; ?></p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-semibold text-slate-500">Job Seekers</p>
                        <p class="mt-2 text-3xl font-bold text-slate-950"><?php echo (int) $stats['seekers']; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-screen-xl px-4 py-12">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-normal text-sky-600">Latest openings</p>
                <h2 class="mt-2 text-3xl font-bold text-slate-950">Recently posted jobs</h2>
            </div>
            <a href="<?php echo $base_url; ?>/findjobs.php" class="rounded-md border border-sky-600 px-4 py-2 text-sm font-semibold text-sky-600 hover:bg-sky-50">View all jobs</a>
        </div>

        <div class="mt-6 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            <?php if (!$latestJobs): ?>
                <div class="rounded-lg border border-slate-200 bg-white p-6 text-slate-600 shadow-sm md:col-span-2 lg:col-span-3">
                    No approved jobs are available yet.
                </div>
            <?php else: ?>
                <?php foreach ($latestJobs as $job): ?>
                    <article class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-semibold text-sky-600"><?php echo h(field($job, 'job_category', 'General')); ?></p>
                        <h3 class="mt-2 text-xl font-bold text-slate-950"><?php echo h(field($job, 'job_title', 'Untitled job')); ?></h3>
                        <p class="mt-2 text-sm text-slate-600"><?php echo h(field($job, 'company_name', 'Company not listed')); ?></p>
                        <div class="mt-4 flex flex-wrap gap-2 text-xs font-medium text-slate-600">
                            <span class="rounded-full bg-slate-100 px-3 py-1"><?php echo h(field($job, 'job_region', 'Remote / Any')); ?></span>
                            <span class="rounded-full bg-slate-100 px-3 py-1"><?php echo h(field($job, 'job_type', 'Job')); ?></span>
                        </div>
                        <a href="<?php echo $base_url; ?>/jobs/job-single.php?id=<?php echo (int) field($job, 'id', 0); ?>" class="mt-5 inline-flex rounded-md bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">View details</a>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <section class="border-y border-slate-200 bg-white">
        <div class="mx-auto max-w-screen-xl px-4 py-12">
            <h2 class="text-3xl font-bold text-slate-950">Browse by category</h2>
            <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <?php if (!$categories): ?>
                    <p class="text-slate-600">Categories will appear here after jobs or categories are added.</p>
                <?php else: ?>
                    <?php foreach ($categories as $category): ?>
                        <?php $categoryName = field($category, 'name', field($category, 'job_category', 'General')); ?>
                        <a href="<?php echo $base_url; ?>/categories/show-jobs.php?category=<?php echo urlencode($categoryName); ?>" class="rounded-md border border-slate-200 bg-slate-50 px-4 py-3 font-semibold text-slate-800 hover:border-sky-300 hover:bg-sky-50">
                            <?php echo h($categoryName); ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php require 'includes/footer.php'; ?>
