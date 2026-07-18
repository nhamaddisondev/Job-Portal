<?php
require '../config/config.php';
require '../includes/public-helpers.php';

if (!isset($_SESSION['id'])) {
    header('Location: ' . BASEURL . '/auth/login.php');
    exit();
}

$userId = (int) $_SESSION['id'];
$role = $_SESSION['role'] ?? '';
$isEmployer = ($role === 'employer');
$isEmployee = ($role === 'employee' || ($_SESSION['type'] ?? '') === 'Job Seeker');

// Stats
$postedJobs = 0;
$activeJobs = 0;
$totalApplications = 0;

if (db_available($conn) && table_exists($conn, 'jobs')) {
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM jobs WHERE company_id = :id");
        $stmt->execute([':id' => $userId]);
        $postedJobs = (int) $stmt->fetchColumn();

        if (has_column($conn, 'jobs', 'status')) {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM jobs WHERE company_id = :id AND status = 'active'");
            $stmt->execute([':id' => $userId]);
            $activeJobs = (int) $stmt->fetchColumn();
        }
    } catch (Throwable $e) {
        // Ignore
    }
}

if ($isEmployer && db_available($conn) && table_exists($conn, 'applications')) {
    try {
        $stmt = $conn->prepare("
            SELECT COUNT(*) FROM applications a
            INNER JOIN jobs j ON a.job_id = j.id
            WHERE j.company_id = :id
        ");
        $stmt->execute([':id' => $userId]);
        $totalApplications = (int) $stmt->fetchColumn();
    } catch (Throwable $e) {
        // Ignore
    }
}

// For employee: applied jobs count
$appliedCount = 0;
$savedCount = 0;
if ($isEmployee && db_available($conn)) {
    try {
        if (table_exists($conn, 'applications')) {
            $columns = table_columns($conn, 'applications');
            $empCol = isset($columns['employee_id']) ? 'employee_id' : (isset($columns['user_id']) ? 'user_id' : null);
            if ($empCol) {
                $stmt = $conn->prepare("SELECT COUNT(*) FROM applications WHERE $empCol = :id");
                $stmt->execute([':id' => $userId]);
                $appliedCount = (int) $stmt->fetchColumn();
            }
        }
        if (table_exists($conn, 'saved_jobs')) {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM saved_jobs WHERE user_id = :id");
            $stmt->execute([':id' => $userId]);
            $savedCount = (int) $stmt->fetchColumn();
        }
    } catch (Throwable $e) {
        // Ignore
    }
}

require '../includes/header.php';
?>

<main class="bg-slate-50">
    <section class="border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-screen-xl px-4 py-10">
            <p class="text-sm font-semibold uppercase tracking-normal text-sky-600">Dashboard</p>
            <h1 class="mt-3 text-4xl font-bold text-slate-950">Welcome back<?php echo isset($_SESSION['username']) ? ', ' . h($_SESSION['username']) : ''; ?></h1>
            <p class="mt-3 text-slate-600">
                <?php echo $isEmployer ? 'Manage your job postings and review applicants.' : 'Track your applications and saved jobs.'; ?>
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-screen-xl px-4 py-10">
        <?php if ($isEmployer): ?>
            <div class="grid gap-5 sm:grid-cols-3">
                <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-semibold text-slate-500">Posted Jobs</p>
                    <p class="mt-2 text-4xl font-bold text-slate-950"><?php echo (int) $postedJobs; ?></p>
                </div>
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-6 shadow-sm">
                    <p class="text-sm font-semibold text-emerald-700">Active Jobs</p>
                    <p class="mt-2 text-4xl font-bold text-emerald-900"><?php echo (int) $activeJobs; ?></p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-semibold text-slate-500">Applications Received</p>
                    <p class="mt-2 text-4xl font-bold text-slate-950"><?php echo (int) $totalApplications; ?></p>
                </div>
            </div>

            <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <a href="<?php echo $base_url; ?>/jobs/post-job.php" class="rounded-lg border border-sky-200 bg-sky-50 p-6 shadow-sm hover:border-sky-400 transition">
                    <h2 class="text-lg font-bold text-sky-800">Post a New Job</h2>
                    <p class="mt-1 text-sm text-sky-600">Create and publish a job listing.</p>
                </a>
                <a href="<?php echo $base_url; ?>/users/postedJobs.php" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm hover:border-sky-300 transition">
                    <h2 class="text-lg font-bold text-slate-950">My Posted Jobs</h2>
                    <p class="mt-1 text-sm text-slate-600">View and manage your job listings.</p>
                </a>
                <a href="<?php echo $base_url; ?>/users/show-applicants.php" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm hover:border-sky-300 transition">
                    <h2 class="text-lg font-bold text-slate-950">Applicants</h2>
                    <p class="mt-1 text-sm text-slate-600">Review applications from job seekers.</p>
                </a>
                <a href="<?php echo $base_url; ?>/users/update-profile.php" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm hover:border-sky-300 transition">
                    <h2 class="text-lg font-bold text-slate-950">Edit Profile</h2>
                    <p class="mt-1 text-sm text-slate-600">Update your company or personal details.</p>
                </a>
                <a href="<?php echo $base_url; ?>/users/employer_insights.php" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm hover:border-sky-300 transition">
                    <h2 class="text-lg font-bold text-slate-950">Insights</h2>
                    <p class="mt-1 text-sm text-slate-600">View analytics and hiring activity.</p>
                </a>
            </div>
        <?php else: ?>
            <div class="grid gap-5 sm:grid-cols-3">
                <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-semibold text-slate-500">Applied Jobs</p>
                    <p class="mt-2 text-4xl font-bold text-slate-950"><?php echo (int) $appliedCount; ?></p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-semibold text-slate-500">Saved Jobs</p>
                    <p class="mt-2 text-4xl font-bold text-slate-950"><?php echo (int) $savedCount; ?></p>
                </div>
            </div>

            <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <a href="<?php echo $base_url; ?>/findjobs.php" class="rounded-lg border border-sky-200 bg-sky-50 p-6 shadow-sm hover:border-sky-400 transition">
                    <h2 class="text-lg font-bold text-sky-800">Browse Jobs</h2>
                    <p class="mt-1 text-sm text-sky-600">Search and find your next opportunity.</p>
                </a>
                <a href="<?php echo $base_url; ?>/users/applied_jobs.php" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm hover:border-sky-300 transition">
                    <h2 class="text-lg font-bold text-slate-950">My Applications</h2>
                    <p class="mt-1 text-sm text-slate-600">Track all your job applications.</p>
                </a>
                <a href="<?php echo $base_url; ?>/users/saved_jobs.php" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm hover:border-sky-300 transition">
                    <h2 class="text-lg font-bold text-slate-950">Saved Jobs</h2>
                    <p class="mt-1 text-sm text-slate-600">View jobs you've bookmarked.</p>
                </a>
                <a href="<?php echo $base_url; ?>/users/update-profile.php" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm hover:border-sky-300 transition">
                    <h2 class="text-lg font-bold text-slate-950">Edit Profile</h2>
                    <p class="mt-1 text-sm text-slate-600">Update your personal details.</p>
                </a>
                <a href="<?php echo $base_url; ?>/users/application_status.php" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm hover:border-sky-300 transition">
                    <h2 class="text-lg font-bold text-slate-950">Application Status</h2>
                    <p class="mt-1 text-sm text-slate-600">Check the status of your applications.</p>
                </a>
                <a href="<?php echo $base_url; ?>/users/my_availability.php" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm hover:border-sky-300 transition">
                    <h2 class="text-lg font-bold text-slate-950">My Availability</h2>
                    <p class="mt-1 text-sm text-slate-600">Manage your availability schedule.</p>
                </a>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php require '../includes/footer.php'; ?>