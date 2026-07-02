<?php
require '../config/config.php';
require '../includes/public-helpers.php';

if (!isset($_SESSION['id'])) {
    header('Location: ' . BASEURL . '/auth/login.php');
    exit();
}

$userId = (int) $_SESSION['id'];
$notifications = [];

if (db_available($conn) && table_exists($conn, 'notifications')) {
    try {
        $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = :id ORDER BY created_at DESC LIMIT 50");
        $stmt->execute([':id' => $userId]);
        $notifications = $stmt->fetchAll(PDO::FETCH_OBJ);

        // Mark all as read
        $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :id AND is_read = 0")->execute([':id' => $userId]);
    } catch (Throwable $e) {
        $notifications = [];
    }
}

require '../includes/header.php';
?>

<main class="bg-slate-50">
    <section class="mx-auto max-w-screen-xl px-4 py-10">
        <div class="mb-8">
            <p class="text-sm font-semibold uppercase tracking-normal text-sky-600">Notifications</p>
            <h1 class="mt-3 text-4xl font-bold text-slate-950">Notification Center</h1>
        </div>

        <?php if (!$notifications): ?>
            <div class="rounded-lg border border-slate-200 bg-white p-8 text-center shadow-sm">
                <p class="text-slate-600">No notifications yet.</p>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($notifications as $note): ?>
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm <?php echo (int) ($note->is_read ?? 0) === 0 ? 'border-l-4 border-l-sky-500' : ''; ?>">
                        <p class="text-slate-900"><?php echo h($note->message ?? 'Notification'); ?></p>
                        <p class="mt-2 text-xs text-slate-500"><?php echo h(format_date($note->created_at ?? '', 'M j, Y g:i A')); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php require '../includes/footer.php'; ?>