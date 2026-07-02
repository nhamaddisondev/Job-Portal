<?php
require '../config/config.php';

if (!isset($_SESSION['id'])) {
    header('Location: ' . BASEURL . '/auth/login.php');
    exit();
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($current === '' || $new === '' || $confirm === '') {
        $errors[] = 'All fields are required.';
    } elseif ($new !== $confirm) {
        $errors[] = 'New password and confirmation do not match.';
    } elseif (strlen($new) < 6) {
        $errors[] = 'New password must be at least 6 characters.';
    } else {
        try {
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => (int) $_SESSION['id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($current, $user['password'])) {
                $errors[] = 'Current password is incorrect.';
            } else {
                $hash = password_hash($new, PASSWORD_DEFAULT);
                $update = $conn->prepare("UPDATE users SET password = :password WHERE id = :id");
                $update->execute([':password' => $hash, ':id' => (int) $_SESSION['id']]);
                $success = true;
            }
        } catch (Throwable $e) {
            $errors[] = 'Unable to change password. Please try again.';
        }
    }
}

require '../includes/header.php';
?>

<main class="bg-slate-50">
    <section class="mx-auto max-w-screen-xl px-4 py-10">
        <div class="mb-8">
            <p class="text-sm font-semibold uppercase tracking-normal text-sky-600">Security</p>
            <h1 class="mt-3 text-4xl font-bold text-slate-950">Change Password</h1>
        </div>

        <?php if ($success): ?>
            <div class="mb-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">Password changed successfully.</div>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div class="mb-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc pl-5"><?php foreach ($errors as $err): ?><li><?php echo h($err); ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="change_password.php" class="max-w-md space-y-5">
            <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <div>
                    <label class="block text-sm font-medium text-slate-700" for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" class="mt-2 block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30" required>
                </div>
                <div class="mt-5">
                    <label class="block text-sm font-medium text-slate-700" for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" minlength="6" class="mt-2 block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30" required>
                </div>
                <div class="mt-5">
                    <label class="block text-sm font-medium text-slate-700" for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" minlength="6" class="mt-2 block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30" required>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="<?php echo $base_url; ?>/users/employer_dashboard.php" class="rounded-md border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
                <button type="submit" name="submit" class="rounded-md bg-sky-600 px-5 py-3 text-sm font-semibold text-white hover:bg-sky-700">Change Password</button>
            </div>
        </form>
    </section>
</main>

<?php require '../includes/footer.php'; ?>