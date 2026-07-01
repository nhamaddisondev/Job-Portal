<?php
$scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$projectName = basename(dirname(__DIR__));
$pathParts = explode('/', trim($_SERVER['SCRIPT_NAME'], '/'));
$projectIndex = array_search($projectName, $pathParts, true);
$projectPath = $projectIndex === false ? '' : '/' . implode('/', array_slice($pathParts, 0, $projectIndex + 1));
$base_url = $scheme . "://$_SERVER[HTTP_HOST]" . $projectPath;
$isLoggedIn = isset($_SESSION['username']);
$displayName = $_SESSION['username'] ?? '';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>
    <title>Online Job Portal</title>
</head>
<body class="bg-gray-50 text-gray-900">
    <header class="bg-white border-b border-gray-200 shadow-sm">
        <nav class="w-full max-w-screen-xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between gap-4">
                <a href="<?php echo $base_url; ?>/index.php" class="flex items-center space-x-3">
                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-sky-600 text-white text-sm font-bold">OJP</span>
                    <span class="text-2xl font-semibold whitespace-nowrap">Online Job Portal</span>
                </a>

                <button type="button" class="inline-flex items-center justify-center rounded-md p-2 text-gray-600 hover:bg-gray-100 hover:text-gray-900 md:hidden" onclick="document.getElementById('main-menu').classList.toggle('hidden')" aria-controls="main-menu" aria-label="Toggle navigation">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                <div id="main-menu" class="hidden absolute left-4 right-4 top-20 z-20 rounded-md border border-gray-200 bg-white p-4 shadow-lg md:static md:block md:border-0 md:bg-transparent md:p-0 md:shadow-none">
                    <div class="flex flex-col gap-4 text-sm font-medium text-gray-700 md:flex-row md:items-center md:gap-6">
                        <a href="<?php echo $base_url; ?>/index.php" class="hover:text-sky-600">Home</a>
                        <a href="<?php echo $base_url; ?>/findjobs.php" class="hover:text-sky-600">Find Jobs</a>
                        <a href="<?php echo $base_url; ?>/gerneral/companies.php" class="hover:text-sky-600">Companies</a>
                        <a href="<?php echo $base_url; ?>/gerneral/workers.php" class="hover:text-sky-600">Job Seekers</a>
                        <a href="<?php echo $base_url; ?>/about.php" class="hover:text-sky-600">About</a>
                        <a href="<?php echo $base_url; ?>/contact.php" class="hover:text-sky-600">Contact</a>
                        <div class="flex flex-col gap-3 border-t border-gray-100 pt-4 md:flex-row md:border-t-0 md:pt-0">
                            <?php if ($isLoggedIn): ?>
                                <span class="px-1 py-2 text-gray-500">Hi, <?php echo htmlspecialchars((string) $displayName, ENT_QUOTES, 'UTF-8'); ?></span>
                                <a href="<?php echo $base_url; ?>/auth/logout.php" class="rounded-md border border-sky-600 px-4 py-2 text-center text-sky-600 hover:bg-sky-50">Logout</a>
                            <?php else: ?>
                                <a href="<?php echo $base_url; ?>/auth/login.php" class="rounded-md border border-sky-600 px-4 py-2 text-center text-sky-600 hover:bg-sky-50">Login</a>
                                <a href="<?php echo $base_url; ?>/auth/register.php" class="rounded-md bg-sky-600 px-4 py-2 text-center text-white hover:bg-sky-700">Register</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </header>
