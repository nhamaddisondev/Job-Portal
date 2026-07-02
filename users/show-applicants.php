<?php
require '../config/config.php';
require '../includes/public-helpers.php';

if (!isset($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'employer') {
    header('Location: ' . BASEURL . '/auth/login.php');
    exit();
}

$userId = (int) $_SESSION['id'];
$jobs = [];
$applicants = [];

if (db_available($conn) && table_exists($conn, 'jobs') && table_exists($conn, 'applications')) {
    try {
        $jobs = $conn->prepare("SELECT id, job_title FROM jobs WHERE company_id = :id ORDER BY job_title ASC");
        $jobs->execute([':id' => $userId]);
        $jobs = $jobs->fetchAll(PDO::FETCH_OBJ);

        $jobIds = array_map(fn($j) => $j->id, $jobs);
        $jobIdList = $jobIds ? implode(',', array_map('intval', $jobIds)) : '0';

        $stmt = $conn->prepare("
            SELECT a.*, j.job_title, u.fullname AS applicant_name, u.username AS applicant_username, u.email AS applicant_email, u.contact AS applicant_contact
            FROM applications a
            INNER JOIN jobs j ON a.job_id = j.id
            LEFT JOIN users u ON a.employee_id = u.id OR a.user_id = u.id
            WHERE j.company_id = :id
            ORDER BY a.id DESC
        ");
        $stmt->execute([':id' => $userId]);
        $applicants = $stmt->fetchAll(PDO::FETCH_OBJ);
    } catch (Throwable $e) {
        $applicants = [];
    }
}

require '../includes/header.php';
?>

<main class="bg-slate-50">
    <section class="mx-auto max-w-screen-xl px-4 py-10">
        <div class="mb-8">
            <p class="text-sm font-semibold uppercase tracking-normal text-sky-600">Hiring</p>
            <h1 class="mt-3 text-4xl font-bold text-slate-950">Applicants</h1>
            <p class="mt-3 text-slate-600">Review candidates who applied to your jobs.</p>
        </div>

        <?php if (!$applicants): ?>
            <div class="rounded-lg border border-slate-200 bg-white p-8 text-center shadow-sm">
                <p class="text-slate-600">No applicants yet for any of your jobs.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Applicant</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Job</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Contact</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php foreach ($applicants as $app): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 text-sm font-medium text-slate-900"><?php echo h($app->applicant_name ?: $app->applicant_username ?: '—'); ?></td>
                                <td class="px-6 py-4 text-sm text-slate-600"><?php echo h($app->job_title ?? '—'); ?></td>
                                <td class="px-6 py-4 text-sm text-slate-600"><?php echo h($app->applicant_email ?? '—'); ?></td>
                                <td class="px-6 py-4 text-sm text-slate-600"><?php echo h($app->applicant_contact ?? '—'); ?></td>
                                <td class="px-6 py-4">
                                    <?php $status = $app->status ?? 'pending'; ?>
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold
                                        <?php echo $status === 'accepted' ? 'bg-emerald-50 text-emerald-700' : ($status === 'rejected' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700'); ?>">
                                        <?php echo h(ucfirst($status)); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php require '../includes/footer.php'; ?>