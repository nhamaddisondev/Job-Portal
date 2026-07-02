<?php
require '../config/config.php';
require '../includes/public-helpers.php';

if (!isset($_SESSION['id'])) {
    header('Location: ' . BASEURL . '/auth/login.php');
    exit();
}

$userId = (int) $_SESSION['id'];
$isEmployer = ($_SESSION['role'] ?? '') === 'employer';

$totalJobs = 0;
$activeJobs = 0;
$totalViews = 0;
$totalApplications = 0;
$recentApplications = [];

if ($isEmployer && db_available($conn)) {
    try {
        if (table_exists($conn, 'jobs')) {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM jobs WHERE company_id = :id");
            $stmt->execute([':id' => $userId]);
            $totalJobs = (int) $stmt->fetchColumn();

            if (has_column($conn, 'jobs', 'status')) {
                $stmt = $conn->prepare("SELECT COUNT(*) FROM jobs WHERE company_id = :id AND status = 1");
                $stmt->execute([':id' => $userId]);
                $activeJobs = (int) $stmt->fetchColumn();
            }
        }

        if (table_exists($conn, 'applications') && table_exists($conn, 'jobs')) {
            $stmt = $conn->prepare("
                SELECT COUNT(*) FROM applications a
                INNER JOIN jobs j ON a.job_id = j.id
                WHERE j.company_id = :id
            ");
            $stmt->execute([':id' => $userId]);
            $totalApplications = (int) $stmt->fetchColumn();

            $stmt = $conn->prepare("
                SELECT a.*, j.job_title, u.fullname AS applicant_name, u.email AS applicant_email, u.contact AS applicant_contact
                FROM applications a
                INNER JOIN jobs j ON a.job_id = j.id
                LEFT JOIN users u ON a.employee_id = u.id
                WHERE j.company_id = :id
                ORDER BY a.id DESC
                LIMIT 10
            ");
            $stmt->execute([':id' => $userId]);
            $recentApplications = $stmt->fetchAll(PDO::FETCH_OBJ);
        }
    } catch (Throwable $e) {
        // Ignore
    }
}

require '../includes/header.php';
?>

<main class="bg-slate-50">
    <section class="mx-auto max-w-screen-xl px-4 py-10">
        <div class="mb-8">
            <p class="text-sm font-semibold uppercase tracking-normal text-sky-600">Analytics</p>
            <h1 class="mt-3 text-4xl font-bold text-slate-950">Employer Insights</h1>
            <p class="mt-3 text-slate-600">Overview of your hiring activity.</p>
        </div>

        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-semibold text-slate-500">Total Jobs Posted</p>
                <p class="mt-2 text-4xl font-bold text-slate-950"><?php echo (int) $totalJobs; ?></p>
            </div>
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-6 shadow-sm">
                <p class="text-sm font-semibold text-emerald-700">Active Listings</p>
                <p class="mt-2 text-4xl font-bold text-emerald-900"><?php echo (int) $activeJobs; ?></p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-semibold text-slate-500">Total Applications</p>
                <p class="mt-2 text-4xl font-bold text-slate-950"><?php echo (int) $totalApplications; ?></p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-semibold text-slate-500">Conversion Rate</p>
                <p class="mt-2 text-4xl font-bold text-slate-950"><?php echo $totalJobs > 0 ? round(($totalApplications / $totalJobs), 1) : 0; ?></p>
                <p class="text-xs text-slate-500">apps per job</p>
            </div>
        </div>

        <?php if ($recentApplications): ?>
            <section class="mt-10">
                <h2 class="text-2xl font-bold text-slate-950">Recent Applications</h2>
                <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200 bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Applicant</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Job</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Contact</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <?php foreach ($recentApplications as $app): ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 text-sm font-medium text-slate-900"><?php echo h(field($app, 'applicant_name', '—')); ?></td>
                                    <td class="px-6 py-4 text-sm text-slate-600"><?php echo h(field($app, 'job_title', '—')); ?></td>
                                    <td class="px-6 py-4 text-sm text-slate-600"><?php echo h(field($app, 'applicant_email', '—')); ?></td>
                                    <td class="px-6 py-4">
                                        <?php $status = field($app, 'status', 'pending'); ?>
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold
                                            <?php echo $status === 'accepted' ? 'bg-emerald-50 text-emerald-700' : ($status === 'rejected' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700'); ?>">
                                            <?php echo h(ucfirst($status)); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600"><?php echo h(format_date(field($app, 'created_at', ''))); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endif; ?>
    </section>
</main>

<?php require '../includes/footer.php'; ?>