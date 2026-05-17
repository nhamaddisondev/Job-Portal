<?php
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

$project_folder = explode('/', $_SERVER['REQUEST_URI'])[1];

$base_url = $base_url . '/' . $project_folder;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>
    <title>Online Job Portal - Admin</title>
</head>
<body class="bg-gray-50 text-gray-900">
    <header class="bg-slate-950 text-white shadow-sm">
        <nav class="w-full max-w-screen-xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between gap-4">
                <a href="<?php echo $base_url; ?>/admin/index.php" class="flex items-center space-x-3">
                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-sky-600 text-white text-sm font-bold">OJP</span>
                    <span class="text-2xl font-semibold whitespace-nowrap">Admin Panel</span>
                </a>

                <button type="button" class="inline-flex items-center justify-center rounded-md p-2 text-slate-200 hover:bg-slate-800 hover:text-white md:hidden" onclick="document.getElementById('admin-menu').classList.toggle('hidden')" aria-controls="admin-menu" aria-label="Toggle navigation">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                <div id="admin-menu" class="hidden absolute left-4 right-4 top-20 z-20 rounded-md border border-slate-700 bg-slate-950 p-4 shadow-lg md:static md:block md:border-0 md:bg-transparent md:p-0 md:shadow-none">
                    <div class="flex flex-col gap-4 text-sm font-medium text-slate-200 md:flex-row md:items-center md:gap-6">
                        <a href="<?php echo $base_url; ?>/admin/index.php" class="hover:text-sky-300">Dashboard</a>
                        <a href="<?php echo $base_url; ?>/admin/jobs-admins/show-jobs.php" class="hover:text-sky-300">Jobs</a>
                        <a href="<?php echo $base_url; ?>/admin/jobs-admins/pending-jobs.php" class="hover:text-sky-300">Pending Jobs</a>
                        <a href="<?php echo $base_url; ?>/admin/categories-admins/show-categories.php" class="hover:text-sky-300">Categories</a>
                        <a href="<?php echo $base_url; ?>/admin/job-regions/show-jobregions.php" class="hover:text-sky-300">Regions</a>
                        <a href="<?php echo $base_url; ?>/admin/users/show-employers.php" class="hover:text-sky-300">Employers</a>
                        <a href="<?php echo $base_url; ?>/admin/users/show-jobseekers.php" class="hover:text-sky-300">Job Seekers</a>
                        <a href="<?php echo $base_url; ?>/admin/admins/admins.php" class="hover:text-sky-300">Admins</a>
                        <a href="<?php echo $base_url; ?>/admin/admins/logout-admins.php" class="rounded-md bg-red-600 px-4 py-2 text-center text-white hover:bg-red-700">Logout</a>
                    </div>
                </div>
            </div>
        </nav>
    </header>
