<?php
require '../config/config.php';
require '../includes/public-helpers.php';

if (!isset($_SESSION['id'])) {
    header('Location: ' . BASEURL . '/auth/login.php');
    exit();
}

$userId = (int) $_SESSION['id'];
$errors = [];
$success = false;

$user = [];
if (db_available($conn) && table_exists($conn, 'users')) {
    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        $errors[] = 'Unable to load profile.';
    }
}

if (!$user) {
    $user = [
        'fullname' => '',
        'name' => '',
        'username' => '',
        'email' => '',
        'contact' => '',
        'type' => '',
        'role' => '',
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $fullname = trim($_POST['fullname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contact = trim($_POST['contact'] ?? '');

    if ($fullname === '') $errors[] = 'Full name is required.';
    if ($username === '') $errors[] = 'Username is required.';
    if ($email === '') $errors[] = 'Email is required.';
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format.';

    if (empty($errors) && db_available($conn) && table_exists($conn, 'users')) {
        try {
            $columns = table_columns($conn, 'users');
            $data = [];

            if (isset($columns['fullname'])) $data['fullname'] = $fullname;
            if (isset($columns['name'])) $data['name'] = $fullname;
            if (isset($columns['username'])) $data['username'] = $username;
            if (isset($columns['email'])) $data['email'] = $email;
            if (isset($columns['contact'])) $data['contact'] = $contact;

            $sets = [];
            foreach ($data as $field => $value) {
                $sets[] = "$field = :$field";
            }

            if ($sets) {
                $data[':id'] = $userId;
                $sql = 'UPDATE users SET ' . implode(', ', $sets) . ' WHERE id = :id';
                $stmt = $conn->prepare($sql);
                $stmt->execute($data);
                $success = true;

                // Update session
                $_SESSION['username'] = $username;
            }
        } catch (Throwable $e) {
            $errors[] = 'Failed to update profile.';
        }
    }
}

require '../includes/header.php';
?>

<main class="bg-slate-50">
    <section class="mx-auto max-w-screen-xl px-4 py-10">
        <div class="mb-8">
            <p class="text-sm font-semibold uppercase tracking-normal text-sky-600">Profile</p>
            <h1 class="mt-3 text-4xl font-bold text-slate-950">Edit Profile</h1>
            <p class="mt-3 text-slate-600">Update your personal information.</p>
        </div>

        <?php if ($success): ?>
            <div class="mb-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">Profile updated successfully.</div>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div class="mb-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc pl-5">
                    <?php foreach ($errors as $err): ?><li><?php echo h($err); ?></li><?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="update-profile.php" class="max-w-2xl space-y-5">
            <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="fullname">Full Name</label>
                        <input type="text" id="fullname" name="fullname" value="<?php echo h($user['fullname'] ?: $user['name'] ?: ''); ?>" class="mt-2 block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="username">Username</label>
                        <input type="text" id="username" name="username" value="<?php echo h($user['username'] ?? ''); ?>" class="mt-2 block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo h($user['email'] ?? ''); ?>" class="mt-2 block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="contact">Contact</label>
                        <input type="text" id="contact" name="contact" value="<?php echo h($user['contact'] ?? ''); ?>" class="mt-2 block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30">
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="<?php echo $base_url; ?>/users/employer_dashboard.php" class="rounded-md border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
                <button type="submit" name="submit" class="rounded-md bg-sky-600 px-5 py-3 text-sm font-semibold text-white hover:bg-sky-700">Save Changes</button>
            </div>
        </form>

        <div class="mt-8">
            <a href="<?php echo $base_url; ?>/users/change_password.php" class="text-sm font-semibold text-sky-600 hover:text-sky-700">Change password &rarr;</a>
        </div>
    </section>
</main>

<?php require '../includes/footer.php'; ?>