<?php require '../../config/config.php'; ?>

<?php
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . BASEURL . '/login.php');
    exit();
}
$pageTitle = "Create Admin";
$breadcrumb = "Systems";

$errors = [];
$success = false;
$adminnameVal = '';
$emailVal = '';
$passwordVal = '';

if (!function_exists('h')) {
    function h($v)
    {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $adminnameVal = trim($_POST['adminname'] ?? '');
    $emailVal = trim($_POST['email'] ?? '');
    $passwordVal = $_POST['password'] ?? '';

    if($adminnameVal === '' || $emailVal === '' || $passwordVal === ''){
        $errors[] = 'All fields are required.';
    }
    elseif(!filter_var($emailVal, FILTER_VALIDATE_EMAIL)){
        $errors[] = 'Invalid email format.';
    }
    else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$emailVal]);
        if ($stmt->fetch()) {
            $errors[] = 'Email is already in use.';
        } else {
            // Insert new admin
            $passwordHash = password_hash($passwordVal, PASSWORD_DEFAULT);
            $insertStmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')");
            if ($insertStmt->execute([$adminnameVal, $emailVal, $passwordHash])) {
                $success = true;
                // Clear form values on success
                $adminnameVal = '';
                $emailVal = '';
            } else {
                $errors[] = 'Failed to create admin. Please try again.';
            }
        }
    }
}

require '../../admin/layouts/header.php';
?>

<main class="w-full max-w-screen-xl mx-auto px-4 py-10">
    <div class="w-full max-w-3xl">
        <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-200 bg-slate-50 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-base font-semibold text-slate-900">Create Admin</h2>
                <a href="<?= ADMINURL ?>/admins/admins.php" class="inline-flex items-center justify-center rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2">
                    <svg class="mr-2 h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.56l3.22 3.22a.75.75 0 1 1-1.06 1.06l-4.5-4.5a.75.75 0 0 1 0-1.06l4.5-4.5a.75.75 0 0 1 1.06 1.06L5.56 9.25h10.69A.75.75 0 0 1 17 10Z" clip-rule="evenodd" />
                    </svg>
                    Back to Admins
                </a>
            </div>

            <div class="px-6 py-6">
                <?php if ($errors): ?>
                    <div class="mb-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        <ul class="list-disc space-y-1 pl-5">
                            <?php foreach ($errors as $err): ?>
                                <li><?= h($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="mb-5 flex gap-3 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        <svg class="mt-0.5 h-5 w-5 flex-none text-emerald-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.236 4.45-1.713-1.714a.75.75 0 1 0-1.061 1.061l2.333 2.333a.75.75 0 0 0 1.137-.089l3.754-5.159Z" clip-rule="evenodd" />
                        </svg>
                        <span>Admin created successfully.</span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="create-admins.php" class="space-y-5" novalidate>
                    <div>
                        <label for="adminEmail" class="block text-sm font-medium text-slate-700">Email</label>
                        <input
                            type="email"
                            name="email"
                            id="adminEmail"
                            class="mt-2 block w-full rounded-md border border-slate-300 px-3 py-2 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20"
                            placeholder="name@example.com"
                            value="<?= h($emailVal) ?>"
                            required
                        >
                    </div>

                    <div>
                        <label for="adminName" class="block text-sm font-medium text-slate-700">Username</label>
                        <input
                            type="text"
                            name="adminname"
                            id="adminName"
                            class="mt-2 block w-full rounded-md border border-slate-300 px-3 py-2 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20"
                            placeholder="username"
                            value="<?= h($adminnameVal) ?>"
                            required
                        >
                    </div>

                    <div>
                        <label for="adminPassword" class="block text-sm font-medium text-slate-700">Password</label>
                        <input
                            type="password"
                            name="password"
                            id="adminPassword"
                            class="mt-2 block w-full rounded-md border border-slate-300 px-3 py-2 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20"
                            placeholder="Password"
                            minlength="6"
                            required
                        >
                        <p class="mt-2 text-sm text-slate-500">Minimum 6 characters is recommended.</p>
                    </div>

                    <button type="submit" name="submit" class="inline-flex items-center justify-center rounded-md bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2">
                        <svg class="mr-2 h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.25 7.333a1 1 0 0 1-1.42.006L3.29 9.293a1 1 0 1 1 1.414-1.414l4.04 4.04 6.54-6.624a1 1 0 0 1 1.42-.006Z" clip-rule="evenodd" />
                        </svg>
                        Create
                    </button>
                </form>
            </div>
        </section>
    </div>
</main>

<?php require "../layouts/footer.php"; ?>
