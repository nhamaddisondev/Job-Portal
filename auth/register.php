<?php
require '../config/config.php';

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$projectRoot = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');
$appUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . $projectRoot;

if (isset($_SESSION['username'])) {
    header('Location: ' . $appUrl . '/index.php');
    exit();
}

$errors = [];
$values = [
    'fullname' => '',
    'username' => '',
    'email' => '',
    'contact' => '',
    'type' => 'Job Seeker',
];

if (!function_exists('h')) {
    function h($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

function user_columns(PDO $conn)
{
    static $columns = null;

    if ($columns !== null) {
        return $columns;
    }

    $columns = [];
    $stmt = $conn->query('DESCRIBE users');
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $column) {
        $columns[$column['Field']] = $column;
    }

    return $columns;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $values['fullname'] = trim($_POST['fullname'] ?? '');
    $values['username'] = trim($_POST['username'] ?? '');
    $values['email'] = trim($_POST['email'] ?? '');
    $values['contact'] = trim($_POST['contact'] ?? '');
    $values['type'] = ($_POST['type'] ?? '') === 'Employer' ? 'Employer' : 'Job Seeker';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($values['fullname'] === '' || $values['username'] === '' || $values['email'] === '' || $password === '' || $confirmPassword === '') {
        $errors[] = 'Please fill in all required fields.';
    }

    if ($values['email'] !== '' && !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if ($password !== '' && strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Password confirmation does not match.';
    }

    if (!$errors) {
        $stmt = $conn->prepare('SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1');
        $stmt->execute([
            ':username' => $values['username'],
            ':email' => $values['email'],
        ]);

        if ($stmt->fetch()) {
            $errors[] = 'Username or email is already registered.';
        }
    }

    if (!$errors) {
        $columns = user_columns($conn);
        $data = [];

        if (isset($columns['fullname'])) {
            $data['fullname'] = $values['fullname'];
        }
        if (isset($columns['name'])) {
            $data['name'] = $values['fullname'];
        }
        if (isset($columns['username'])) {
            $data['username'] = $values['username'];
        }
        if (isset($columns['email'])) {
            $data['email'] = $values['email'];
        }
        if (isset($columns['contact'])) {
            $data['contact'] = $values['contact'];
        }
        if (isset($columns['type'])) {
            $data['type'] = $values['type'];
        }
        if (isset($columns['role'])) {
            $data['role'] = $values['type'] === 'Job Seeker' ? 'employee' : 'employer';
        }
        if (isset($columns['password'])) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $fieldNames = array_keys($data);
        $placeholders = array_map(fn($field) => ':' . $field, $fieldNames);
        $sql = 'INSERT INTO users (' . implode(', ', $fieldNames) . ') VALUES (' . implode(', ', $placeholders) . ')';
        $stmt = $conn->prepare($sql);

        if ($stmt->execute($data)) {
            header('Location: ' . $appUrl . '/auth/login.php?registered=1');
            exit();
        }

        $errors[] = 'Failed to create your account. Please try again.';
    }
}

require '../includes/header.php';
?>

<main class="bg-slate-50">
    <section class="mx-auto grid min-h-[calc(100vh-5rem)] w-full max-w-screen-xl gap-10 px-4 py-10 lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
        <div class="max-w-xl">
            <p class="text-sm font-semibold uppercase tracking-normal text-sky-600">Join Online Job Portal</p>
            <h1 class="mt-3 text-4xl font-bold tracking-normal text-slate-950 md:text-5xl">Create your account</h1>
            <p class="mt-4 text-lg leading-8 text-slate-600">
                Build a profile as a job seeker or employer and start using the portal from one account.
            </p>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-5">
                <h2 class="text-xl font-bold text-slate-950">Register</h2>
                <p class="mt-1 text-sm text-slate-500">Fields marked with * are required.</p>
            </div>

            <div class="px-6 py-6">
                <?php if ($errors): ?>
                    <div class="mb-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                        <ul class="list-disc space-y-1 pl-5">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo h($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="register.php" method="POST" class="space-y-5" novalidate>
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700" for="fullname">Full name *</label>
                            <input type="text" id="fullname" name="fullname" value="<?php echo h($values['fullname']); ?>" autocomplete="name" class="mt-2 block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700" for="username">Username *</label>
                            <input type="text" id="username" name="username" value="<?php echo h($values['username']); ?>" autocomplete="username" class="mt-2 block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30" required>
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700" for="email">Email *</label>
                            <input type="email" id="email" name="email" value="<?php echo h($values['email']); ?>" autocomplete="email" class="mt-2 block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700" for="contact">Contact</label>
                            <input type="text" id="contact" name="contact" value="<?php echo h($values['contact']); ?>" autocomplete="tel" class="mt-2 block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="type">Account type *</label>
                        <select id="type" name="type" class="mt-2 block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30">
                            <option value="Job Seeker" <?php echo $values['type'] === 'Job Seeker' ? 'selected' : ''; ?>>Job Seeker</option>
                            <option value="Employer" <?php echo $values['type'] === 'Employer' ? 'selected' : ''; ?>>Employer</option>
                        </select>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700" for="password">Password *</label>
                            <input type="password" id="password" name="password" autocomplete="new-password" minlength="6" class="mt-2 block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700" for="confirm_password">Confirm password *</label>
                            <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password" minlength="6" class="mt-2 block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30" required>
                        </div>
                    </div>

                    <button type="submit" name="submit" class="inline-flex w-full items-center justify-center rounded-md bg-sky-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2">
                        Create Account
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-slate-600">
                    Already have an account?
                    <a href="<?php echo $base_url; ?>/auth/login.php" class="font-semibold text-sky-600 hover:text-sky-700">Log in</a>
                </p>
            </div>
        </div>
    </section>
</main>

<?php require '../includes/footer.php'; ?>
