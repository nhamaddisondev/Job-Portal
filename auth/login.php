<?php
require '../config/config.php';

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$projectRoot = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');
$appUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . $projectRoot;

if (isset($_SESSION['username'])) {
    header('Location: ' . $appUrl . '/index.php');
    exit();
}

$error = '';
$success = isset($_GET['registered']) ? 'Account created successfully. Please log in.' : '';
$login = '';

if (!function_exists('h')) {
    function h($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($login === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $conn->prepare("
            SELECT *
            FROM users
            WHERE username = :login OR email = :login
            LIMIT 1
        ");
        $stmt->bindValue(':login', $login);
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

        $error = 'Invalid username/email or password.';
    }
}

require '../includes/header.php';
?>

<main class="bg-slate-50">
    <section class="relative overflow-hidden border-b border-slate-200 bg-white">
        <div class="mx-auto grid min-h-[calc(100vh-5rem)] w-full max-w-screen-xl gap-10 px-4 py-10 md:grid-cols-[1fr_440px] md:items-center md:py-14">
            <div class="max-w-2xl">
                <p class="text-sm font-semibold uppercase tracking-normal text-sky-600">Online Job Portal</p>
                <h1 class="mt-3 text-4xl font-bold tracking-normal text-slate-950 md:text-5xl">Welcome back</h1>
                <p class="mt-4 text-lg leading-8 text-slate-600">
                    Sign in to continue managing your applications, saved jobs, and hiring activity from one simple workspace.
                </p>

                <div class="mt-8 grid gap-4 sm:grid-cols-3">
                    <div class="rounded-md border border-slate-200 bg-slate-50 p-4">
                        <p class="text-2xl font-bold text-slate-950">Fast</p>
                        <p class="mt-1 text-sm text-slate-600">Access your dashboard quickly.</p>
                    </div>
                    <div class="rounded-md border border-slate-200 bg-slate-50 p-4">
                        <p class="text-2xl font-bold text-slate-950">Focused</p>
                        <p class="mt-1 text-sm text-slate-600">Track jobs and applicants.</p>
                    </div>
                    <div class="rounded-md border border-slate-200 bg-slate-50 p-4">
                        <p class="text-2xl font-bold text-slate-950">Secure</p>
                        <p class="mt-1 text-sm text-slate-600">Your account stays protected.</p>
                    </div>
                </div>
            </div>

            <div class="w-full">
                <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-5">
                        <h2 class="text-xl font-bold text-slate-950">Log in</h2>
                        <p class="mt-1 text-sm text-slate-500">Use your username or email address.</p>
                    </div>

                    <div class="px-6 py-6">
                        <?php if ($success): ?>
                            <div class="mb-5 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" role="status">
                                <?php echo h($success); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="mb-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                                <?php echo h($error); ?>
                            </div>
                        <?php endif; ?>

                        <form action="login.php" method="POST" class="space-y-5" novalidate>
                            <div>
                                <label class="block text-sm font-medium text-slate-700" for="login">Username or email</label>
                                <input
                                    type="text"
                                    id="login"
                                    name="login"
                                    value="<?php echo h($login); ?>"
                                    autocomplete="username"
                                    class="mt-2 block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30"
                                    placeholder="Enter username or email"
                                    required
                                >
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700" for="password">Password</label>
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    autocomplete="current-password"
                                    class="mt-2 block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30"
                                    placeholder="Enter password"
                                    required
                                >
                            </div>

                            <button
                                type="submit"
                                name="submit"
                                class="inline-flex w-full items-center justify-center rounded-md bg-sky-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2"
                            >
                                Log In
                            </button>
                        </form>

                        <p class="mt-6 text-center text-sm text-slate-600">
                            New to Online Job Portal?
                            <a href="<?php echo $base_url; ?>/auth/register.php" class="font-semibold text-sky-600 hover:text-sky-700">Create an account</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
<?php require '../includes/footer.php'; ?>
