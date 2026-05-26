<?php require '../../config/config.php'; ?>

<?php
$suppressPageHead = true;

if (isset($_SESSION['adminname'])) {
    header('Location: ' . ADMINURL . '');
    exit();
}

$error = null;
$email = '';

if (!function_exists('h')) {
    function h($v)
    {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Email and password are required.';
    } else {
        $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ? AND role = 'admin'");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['id'] = $admin['id'];
            $_SESSION['adminname'] = $admin['name'];
            $_SESSION['email'] = $admin['email'];
            $_SESSION['role'] = 'admin';
            header('Location: ' . ADMINURL . '');
            exit();
        }

        $error = 'Invalid email or password.';
    }
}

require '../../admin/layouts/header.php';
?>

<main class="flex min-h-screen items-center justify-center bg-slate-50 px-4 py-12">
    <section class="w-full max-w-[420px]">
        <div class="overflow-hidden rounded-2xl bg-white shadow-[0_12px_36px_rgba(15,23,42,0.08)]">
            <div class="px-6 py-8 sm:px-10 sm:py-10">
                <div class="mb-6 text-center">
                    <a href="<?php echo $base_url; ?>" class="mb-3 inline-flex items-center justify-center">
                        <img class="h-10 w-10 object-contain" src="<?php echo ADMINURL; ?>/images/logo.png" alt="Logo">
                    </a>
                    <h1 class="text-[1.35rem] font-extrabold text-slate-950">Admin Login</h1>
                    <p class="mt-1 text-sm text-slate-500">Sign in to manage the dashboard</p>
                </div>

                <?php if ($error): ?>
                    <div class="mb-5 flex gap-3 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                        <svg class="mt-0.5 h-5 w-5 flex-none text-red-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l7.16 12.4c.673 1.167-.17 2.625-1.516 2.625H2.84c-1.347 0-2.19-1.458-1.516-2.625l7.16-12.4ZM10 6a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 6Zm0 8a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <span class="font-semibold">Error:</span>
                            <?php echo h($error); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login-admins.php" class="space-y-4" novalidate>
                    <div>
                        <label for="email" class="sr-only">Email</label>
                        <div class="flex rounded-md shadow-sm">
                            <span class="inline-flex w-12 items-center justify-center rounded-l-md border border-r-0 border-slate-200 bg-slate-50 text-slate-400">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="M3 4a2 2 0 0 0-2 2v.161l9 4.5 9-4.5V6a2 2 0 0 0-2-2H3Z" />
                                    <path d="m19 8.397-8.33 4.165a1.5 1.5 0 0 1-1.34 0L1 8.397V14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8.397Z" />
                                </svg>
                            </span>
                            <input
                                type="email"
                                name="email"
                                id="email"
                                class="block min-w-0 flex-1 rounded-r-md border border-slate-200 px-3 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-sky-400 focus:ring-2 focus:ring-sky-500/20"
                                placeholder="Email address"
                                autocomplete="username"
                                value="<?php echo h($email); ?>"
                                required
                            >
                        </div>
                    </div>

                    <div>
                        <label for="password" class="sr-only">Password</label>
                        <div class="flex rounded-md shadow-sm">
                            <span class="inline-flex w-12 items-center justify-center rounded-l-md border border-r-0 border-slate-200 bg-slate-50 text-slate-400">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 0 0-4.5 4.5V8H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 7V5.5a3 3 0 1 0-6 0V8h6Z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="block min-w-0 flex-1 border border-slate-200 px-3 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-sky-400 focus:ring-2 focus:ring-sky-500/20"
                                placeholder="Password"
                                autocomplete="current-password"
                                required
                                minlength="6"
                            >
                            <button type="button" class="inline-flex w-12 items-center justify-center rounded-r-md border border-l-0 border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-500/30" id="togglePwd" aria-label="Show password" aria-pressed="false">
                                <svg class="eye-icon h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="M10 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" />
                                    <path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 0 1 0-1.18A10.004 10.004 0 0 1 10 3c4.257 0 7.893 2.66 9.336 6.41.147.381.147.799 0 1.18A10.004 10.004 0 0 1 10 17C5.743 17 2.107 14.34.664 10.59ZM14 10a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                        <p id="capsHint" class="mt-2 hidden text-sm font-medium text-amber-600">
                            Caps Lock is on
                        </p>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <label class="inline-flex items-center gap-2 text-sm text-slate-400">
                            <input class="h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500 disabled:cursor-not-allowed disabled:opacity-60" type="checkbox" id="remember" disabled>
                            Remember me
                        </label>
                        <a href="#" class="text-sm text-slate-400 transition hover:text-sky-600">Forgot password?</a>
                    </div>

                    <button type="submit" name="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-sky-600 px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 disabled:cursor-wait disabled:opacity-75" id="submitBtn">
                        Sign in
                    </button>
                </form>
            </div>
        </div>
    </section>
</main>

<script>
(function(){
  var pwd = document.getElementById('password');
  var tgl = document.getElementById('togglePwd');
  var caps = document.getElementById('capsHint');
  var btn = document.getElementById('submitBtn');
  var form = document.querySelector('form[action="login-admins.php"]');

  if (tgl && pwd){
    tgl.addEventListener('click', function(){
      var show = pwd.type === 'password';
      pwd.type = show ? 'text' : 'password';
      this.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
      this.setAttribute('aria-pressed', show ? 'true' : 'false');
    });
    pwd.addEventListener('keyup', function(e){
      if (!caps) return;
      var on = e.getModifierState && e.getModifierState('CapsLock');
      caps.classList.toggle('hidden', !on);
    });
  }
  if (form && btn){
    form.addEventListener('submit', function(){
      btn.disabled = true;
      btn.innerHTML = '<span class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span> Signing in...';
    });
  }
})();
</script>

<?php require "../layouts/footer.php"; ?>
