<?php
require '../config/config.php';

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$projectRoot = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');
$appUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . $projectRoot;

if (isset($_SESSION['username'])) {
    header('Location: ' . $appUrl . '/index.php');
    exit();
}

$loginError = '';
$registerErrors = [];
$loginValue = '';
$registerValues = [
    'fullname' => '',
    'username' => '',
    'email' => '',
    'contact' => '',
    'type' => 'Job Seeker',
];
$activeTab = $_POST['form_type'] ?? 'login';

if (!function_exists('h')) {
    function h($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

function lr_user_columns(PDO $conn)
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_type'] ?? '') === 'login') {
    $loginValue = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($loginValue === '' || $password === '') {
        $loginError = 'Please fill in all login fields.';
    } else {
        $stmt = $conn->prepare('SELECT * FROM users WHERE username = :login OR email = :login LIMIT 1');
        $stmt->bindValue(':login', $loginValue);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['id'] = $user['id'];
            $_SESSION['username'] = $user['username'] ?? $user['name'] ?? $user['email'];
            $_SESSION['email'] = $user['email'] ?? '';
            $_SESSION['type'] = $user['type'] ?? '';
            $_SESSION['role'] = $user['role'] ?? (strtolower((string) $_SESSION['type']) === 'job seeker' ? 'employee' : strtolower((string) $_SESSION['type']));
            header('Location: ' . $appUrl . '/index.php');
            exit();
        }

        $loginError = 'Invalid username/email or password.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_type'] ?? '') === 'register') {
    $activeTab = 'register';
    $registerValues['fullname'] = trim($_POST['fullname'] ?? '');
    $registerValues['username'] = trim($_POST['username'] ?? '');
    $registerValues['email'] = trim($_POST['email'] ?? '');
    $registerValues['contact'] = trim($_POST['contact'] ?? '');
    $registerValues['type'] = ($_POST['type'] ?? '') === 'Employer' ? 'Employer' : 'Job Seeker';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($registerValues['fullname'] === '' || $registerValues['username'] === '' || $registerValues['email'] === '' || $password === '' || $confirmPassword === '') {
        $registerErrors[] = 'Please fill in all required registration fields.';
    }
    if ($registerValues['email'] !== '' && !filter_var($registerValues['email'], FILTER_VALIDATE_EMAIL)) {
        $registerErrors[] = 'Please enter a valid email address.';
    }
    if ($password !== '' && strlen($password) < 6) {
        $registerErrors[] = 'Password must be at least 6 characters.';
    }
    if ($password !== $confirmPassword) {
        $registerErrors[] = 'Password confirmation does not match.';
    }

    if (!$registerErrors) {
        $stmt = $conn->prepare('SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1');
        $stmt->execute([
            ':username' => $registerValues['username'],
            ':email' => $registerValues['email'],
        ]);

        if ($stmt->fetch()) {
            $registerErrors[] = 'Username or email is already registered.';
        }
    }

    if (!$registerErrors) {
        $columns = lr_user_columns($conn);
        $data = [];

        if (isset($columns['fullname'])) {
            $data['fullname'] = $registerValues['fullname'];
        }
        if (isset($columns['name'])) {
            $data['name'] = $registerValues['fullname'];
        }
        if (isset($columns['username'])) {
            $data['username'] = $registerValues['username'];
        }
        if (isset($columns['email'])) {
            $data['email'] = $registerValues['email'];
        }
        if (isset($columns['contact'])) {
            $data['contact'] = $registerValues['contact'];
        }
        if (isset($columns['type'])) {
            $data['type'] = $registerValues['type'];
        }
        if (isset($columns['role'])) {
            $data['role'] = $registerValues['type'] === 'Job Seeker' ? 'employee' : 'employer';
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

        $registerErrors[] = 'Failed to create your account. Please try again.';
    }
}

require '../includes/header.php';
?>

<main class="bg-slate-50">
    <section class="mx-auto min-h-[calc(100vh-5rem)] w-full max-w-screen-xl px-4 py-10">
        <div class="mx-auto max-w-4xl rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-5">
                <h1 class="text-2xl font-bold text-slate-950">Account Access</h1>
                <p class="mt-1 text-sm text-slate-500">Log in or create an account.</p>
            </div>

            <div class="grid gap-0 md:grid-cols-2">
                <section class="border-b border-slate-100 px-6 py-6 md:border-b-0 md:border-r">
                    <h2 class="text-lg font-bold text-slate-950">Log in</h2>

                    <?php if ($loginError): ?>
                        <div class="mt-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                            <?php echo h($loginError); ?>
                        </div>
                    <?php endif; ?>

                    <form action="loginRegister.php" method="POST" class="mt-5 space-y-5" novalidate>
                        <input type="hidden" name="form_type" value="login">
                        <div>
                            <label class="block text-sm font-medium text-slate-700" for="login">Username or email</label>
                            <input type="text" id="login" name="login" value="<?php echo h($loginValue); ?>" autocomplete="username" class="mt-2 block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700" for="login_password">Password</label>
                            <input type="password" id="login_password" name="password" autocomplete="current-password" class="mt-2 block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30" required>
                        </div>

                        <button type="submit" name="submit" class="inline-flex w-full items-center justify-center rounded-md bg-sky-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2">
                            Log In
                        </button>
                    </form>
                </section>

                <section class="px-6 py-6">
                    <h2 class="text-lg font-bold text-slate-950">Register</h2>

                    <?php if ($registerErrors): ?>
                        <div class="mt-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                            <ul class="list-disc space-y-1 pl-5">
                                <?php foreach ($registerErrors as $error): ?>
                                    <li><?php echo h($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="loginRegister.php" method="POST" class="mt-5 space-y-4" novalidate>
                        <input type="hidden" name="form_type" value="register">
                        <input type="text" name="fullname" value="<?php echo h($registerValues['fullname']); ?>" placeholder="Full name" autocomplete="name" class="block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30" required>
                        <input type="text" name="username" value="<?php echo h($registerValues['username']); ?>" placeholder="Username" autocomplete="username" class="block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30" required>
                        <input type="email" name="email" value="<?php echo h($registerValues['email']); ?>" placeholder="Email address" autocomplete="email" class="block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30" required>
                        <input type="text" name="contact" value="<?php echo h($registerValues['contact']); ?>" placeholder="Contact" autocomplete="tel" class="block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30">
                        <select name="type" class="block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30">
                            <option value="Job Seeker" <?php echo $registerValues['type'] === 'Job Seeker' ? 'selected' : ''; ?>>Job Seeker</option>
                            <option value="Employer" <?php echo $registerValues['type'] === 'Employer' ? 'selected' : ''; ?>>Employer</option>
                        </select>
                        <input type="password" name="password" placeholder="Password" autocomplete="new-password" minlength="6" class="block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30" required>
                        <input type="password" name="confirm_password" placeholder="Confirm password" autocomplete="new-password" minlength="6" class="block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30" required>

                        <button type="submit" name="submit" class="inline-flex w-full items-center justify-center rounded-md bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">
                            Create Account
                        </button>
                    </form>
                </section>
            </div>
        </div>
    </section>
</main>

<?php require '../includes/footer.php'; ?>
