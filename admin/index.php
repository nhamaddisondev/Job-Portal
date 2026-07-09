<?php
session_start();
require '../config/config.php';
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . ADMINURL . '/login-admins.php');
    exit();
}

$pageTitle = "Dashboard";
$breadcrumb = "Home";

$stats = [
    'jobs' => 0,
    'pending_jobs' => 0,
    'employers' => 0,
    'jobseekers' => 0,
    'categories' => 0,
    'regions' => 0,
    'applications' => 0,
];

try {
    $stats['jobs'] = (int) $conn->query("SELECT COUNT(*) FROM jobs")->fetchColumn();
    $stats['pending_jobs'] = (int) $conn->query("SELECT COUNT(*) FROM jobs WHERE status = 0")->fetchColumn();
    $stats['employers'] = (int) $conn->query("SELECT COUNT(*) FROM employers")->fetchColumn();
    $stats['jobseekers'] = (int) $conn->query("SELECT COUNT(*) FROM users WHERE UPPER(type) = 'JOB SEEKER' OR role = 'employee'")->fetchColumn();
    $stats['categories'] = (int) $conn->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    $stats['regions'] = (int) $conn->query("SELECT COUNT(*) FROM job_regions")->fetchColumn();
    $stats['applications'] = (int) $conn->query("SELECT COUNT(*) FROM applications")->fetchColumn();
} catch (Throwable $e) {
    // Ignore errors
}

require '../admin/layouts/header.php';
?>

<main class="w-full max-w-screen-xl mx-auto px-4 py-10">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-950">Dashboard</h1>
        <p class="mt-1 text-slate-600">Overview of your job portal.</p>
    </div>

    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">Total Jobs</p>
            <p class="mt-2 text-4xl font-bold text-slate-950"><?php echo (int) $stats['jobs']; ?></p>
        </div>

        <div class="rounded-lg border border-amber-200 bg-amber-50 p-6 shadow-sm">
            <p class="text-sm font-semibold text-amber-700">Pending Jobs</p>
            <p class="mt-2 text-4xl font-bold text-amber-900"><?php echo (int) $stats['pending_jobs']; ?></p>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">Employers</p>
            <p class="mt-2 text-4xl font-bold text-slate-950"><?php echo (int) $stats['employers']; ?></p>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">Job Seekers</p>
            <p class="mt-2 text-4xl font-bold text-slate-950"><?php echo (int) $stats['jobseekers']; ?></p>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">Categories</p>
            <p class="mt-2 text-4xl font-bold text-slate-950"><?php echo (int) $stats['categories']; ?></p>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">Regions</p>
            <p class="mt-2 text-4xl font-bold text-slate-950"><?php echo (int) $stats['regions']; ?></p>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">Applications</p>
            <p class="mt-2 text-4xl font-bold text-slate-950"><?php echo (int) $stats['applications']; ?></p>
        </div>
    </div>

    <div class="mt-8 grid gap-5 lg:grid-cols-2">
        <a href="<?php echo ADMINURL; ?>/jobs-admins/show-jobs.php" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm hover:border-sky-300 transition">
            <h2 class="text-lg font-bold text-slate-950">Manage Jobs</h2>
            <p class="mt-1 text-sm text-slate-600">View, approve, and manage all job postings.</p>
        </a>
        <a href="<?php echo ADMINURL; ?>/jobs-admins/pending-jobs.php" class="rounded-lg border border-amber-200 bg-amber-50 p-6 shadow-sm hover:border-amber-400 transition">
            <h2 class="text-lg font-bold text-amber-900">Pending Approvals</h2>
            <p class="mt-1 text-sm text-amber-700">Review and approve jobs waiting for verification.</p>
        </a>
        <a href="<?php echo ADMINURL; ?>/categories-admins/show-categories.php" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm hover:border-sky-300 transition">
            <h2 class="text-lg font-bold text-slate-950">Categories</h2>
            <p class="mt-1 text-sm text-slate-600">Organize job categories.</p>
        </a>
        <a href="<?php echo ADMINURL; ?>/job-regions/show-jobregions.php" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm hover:border-sky-300 transition">
            <h2 class="text-lg font-bold text-slate-950">Regions</h2>
            <p class="mt-1 text-sm text-slate-600">Manage job regions.</p>
        </a>
    </div>
</main>

<?php require '../admin/layouts/footer.php'; ?>