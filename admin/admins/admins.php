<?php require_once '../../config/config.php'; ?>

<?php
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . BASEURL . '/login.php');
    exit();
}

$pageTitle = "Admins";
$breadcrumb = "Systems";

function h($v) {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

$stmt = $conn->prepare("SELECT id, name, email, created_at FROM users WHERE role = 'admin' ORDER BY created_at DESC");
$stmt->execute();
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

require '../../admin/layouts/header.php';
?>

<main class="w-full max-w-screen-xl mx-auto px-4 py-10">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-slate-950">Admins</h1>
            <p class="mt-1 text-slate-600">Manage admin accounts.</p>
        </div>
        <a href="<?= ADMINURL ?>/admins/create-admins.php" class="rounded-md bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700">Create Admin</a>
    </div>

    <?php if (isset($_GET['created']) && $_GET['created'] === '1'): ?>
        <div class="mb-5 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">Admin created successfully.</div>
    <?php elseif (isset($_GET['deleted']) && $_GET['deleted'] === '1'): ?>
        <div class="mb-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">Admin deleted successfully.</div>
    <?php endif; ?>

    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">#</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Created</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                <?php if (!$admins): ?>
                    <tr><td colspan="4" class="px-6 py-4 text-center text-slate-500">No admins found.</td></tr>
                <?php else: ?>
                    <?php $i = 1; foreach ($admins as $admin): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 text-sm text-slate-600"><?= $i++; ?></td>
                            <td class="px-6 py-4 text-sm font-medium text-slate-900"><?= h($admin['name'] ?? '—'); ?></td>
                            <td class="px-6 py-4 text-sm text-slate-600"><?= h($admin['email'] ?? '—'); ?></td>
                            <td class="px-6 py-4 text-sm text-slate-600"><?= date('j M, Y', strtotime($admin['created_at'] ?? 'now')); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<?php require '../layouts/footer.php'; ?>