<?php require "../../config/config.php"; ?>


<?php
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . BASEURL . '/login.php');
    exit();
}

$pageTitle = "Change Password";
$breadcrumb = "Systems";

$success = $error = null;
$forceLogout = false;

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New password and confirmation do not match.";
    } else {
        // Fetch current password hash
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($current_password, $user['password'])) {
            $error = "Current password is incorrect.";
        } else {
            // Update to new password
            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($update_stmt->execute([$new_hash, $_SESSION['id']])) {
                $success = "Password changed successfully. Please log in again.";
                $forceLogout = true;
            } else {
                $error = "Failed to update password. Please try again.";
            }
        }
    }
}
require '../../admin/layouts/header.php';
?>

<main class="w-full max-w-screen-xl mx-auto px-4 py-10">
    <div class="flex justify-center">
        <div class="w-full max-w-2xl">
            <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
                    <h2 class="text-base font-semibold text-slate-900">Change Admin Password</h2>
                </div>

                <div class="px-6 py-6">
                    <?php if ($error): ?>
                        <div class="mb-5 flex gap-3 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                            <svg class="mt-0.5 h-5 w-5 flex-none text-red-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.19-1.458-1.516-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
                            </svg>
                            <span><?= htmlspecialchars($error); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="mb-5 flex gap-3 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                            <svg class="mt-0.5 h-5 w-5 flex-none text-emerald-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.236 4.45-1.713-1.714a.75.75 0 1 0-1.061 1.061l2.333 2.333a.75.75 0 0 0 1.137-.089l3.754-5.159Z" clip-rule="evenodd" />
                            </svg>
                            <div>
                                <p><?= htmlspecialchars($success); ?></p>
                                <p class="mt-1 text-xs text-emerald-700">For security reasons, please sign in again to continue.</p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" autocomplete="off" class="<?= $forceLogout ? 'hidden' : 'space-y-5'; ?>">
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-slate-700">Current Password</label>
                            <input
                                type="password"
                                id="current_password"
                                name="current_password"
                                class="mt-2 block w-full rounded-md border border-slate-300 px-3 py-2 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20"
                                required
                            >
                        </div>

                        <div>
                            <label for="new_password" class="block text-sm font-medium text-slate-700">New Password</label>
                            <input
                                type="password"
                                id="new_password"
                                name="new_password"
                                class="mt-2 block w-full rounded-md border border-slate-300 px-3 py-2 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20"
                                required
                            >
                        </div>

                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-slate-700">Confirm New Password</label>
                            <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                class="mt-2 block w-full rounded-md border border-slate-300 px-3 py-2 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20"
                                required
                            >
                        </div>

                        <button type="submit" class="inline-flex items-center justify-center rounded-md bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2">
                            <svg class="mr-2 h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 0 0-4.5 4.5V8H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 7V5.5a3 3 0 1 0-6 0V8h6Z" clip-rule="evenodd" />
                            </svg>
                            Update Password
                        </button>
                    </form>
                </div>
            </section>
        </div>
    </div>
</main>

<?php if ($forceLogout): ?>
    <script>
        setTimeout(function () {
            window.location.href = "<?= ADMINURL; ?>/admins/logout-admins.php";
        }, 1000);
    </script>
<?php endif; ?>

<?php require "../layouts/footer.php"; ?>
