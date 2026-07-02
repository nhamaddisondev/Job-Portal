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
                SELECT a.*, j.job_title, j.company_name, j.job_category, j.job_region
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
            <h1 class="mt-3 text-4xl font-bold text-slate-950">Application Status</h1>
            <p class="mt-3 text-slate-600">Check the current status of your applications.</p>
        </div>

        <?php if (!$applications): ?>
            <div class="rounded-lg border border-slate-200 bg-white p-8 text-center shadow-sm">
                <p class="text-slate-600">You haven't applied for any jobs yet.</p>
                <a href="<?php echo $base_url; ?>/findjobs.php" class="mt-4 inline-flex rounded-md bg-sky-600 px-5 py-3 text-sm font-semibold text-white hover:bg-sky-700">Browse Jobs</a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Job</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Company</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Applied</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php foreach ($applications as $app): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 text-sm font-medium text-slate-900"><?php echo h(field($app, 'job_title', 'Untitled')); ?></td>
                                <td class="px-6 py-4 text-sm text-slate-600"><?php echo h(field($app, 'company_name', '—')); ?></td>
                                <td class="px-6 py-4 text-sm text-slate-600"><?php echo h(format_date(field($app, 'created_at', field($app, 'applied_at', '')))); ?></td>
                                <td class="px-6 py-4">
                                    <?php $status = field($app, 'status', 'pending'); ?>
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