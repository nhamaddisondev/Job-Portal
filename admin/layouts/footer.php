<?php
if (!isset($base_url)) {
    $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    $requestPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    $parts = explode('/', $requestPath);
    $adminIndex = array_search('admin', $parts);
    $projectPath = $adminIndex !== false ? implode('/', array_slice($parts, 0, $adminIndex)) : $parts[0];
    $base_url = $base_url . '/' . $projectPath;
}
?>

    <?php if (empty($suppressPageHead)): ?>
    <footer class="mt-12 border-t border-slate-800 bg-slate-950 text-slate-300">
        <div class="w-full max-w-screen-xl mx-auto px-4 py-8">
            <div class="flex flex-col gap-6 md:flex-row md:items-start md:justify-between">
                <div class="max-w-md">
                    <a href="<?php echo $base_url; ?>/admin/index.php" class="inline-flex items-center space-x-3">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-sky-600 text-sm font-bold text-white">OJP</span>
                        <span class="text-xl font-semibold text-white">Admin Panel</span>
                    </a>
                    <p class="mt-3 text-sm leading-6 text-slate-400">
                        Manage jobs, categories, regions, employers, job seekers, and admin accounts from one place.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-8 text-sm sm:grid-cols-3">
                    <div>
                        <h2 class="mb-3 font-semibold uppercase tracking-wide text-white">Manage</h2>
                        <ul class="space-y-2">
                            <li><a href="<?php echo $base_url; ?>/admin/jobs-admins/show-jobs.php" class="hover:text-sky-300">Jobs</a></li>
                            <li><a href="<?php echo $base_url; ?>/admin/jobs-admins/pending-jobs.php" class="hover:text-sky-300">Pending Jobs</a></li>
                            <li><a href="<?php echo $base_url; ?>/admin/categories-admins/show-categories.php" class="hover:text-sky-300">Categories</a></li>
                        </ul>
                    </div>

                    <div>
                        <h2 class="mb-3 font-semibold uppercase tracking-wide text-white">Users</h2>
                        <ul class="space-y-2">
                            <li><a href="<?php echo $base_url; ?>/admin/users/show-employers.php" class="hover:text-sky-300">Employers</a></li>
                            <li><a href="<?php echo $base_url; ?>/admin/users/show-jobseekers.php" class="hover:text-sky-300">Job Seekers</a></li>
                            <li><a href="<?php echo $base_url; ?>/admin/admins/admins.php" class="hover:text-sky-300">Admins</a></li>
                        </ul>
                    </div>

                    <div>
                        <h2 class="mb-3 font-semibold uppercase tracking-wide text-white">System</h2>
                        <ul class="space-y-2">
                            <li><a href="<?php echo $base_url; ?>/admin/index.php" class="hover:text-sky-300">Dashboard</a></li>
                            <li><a href="<?php echo $base_url; ?>/admin/job-regions/show-jobregions.php" class="hover:text-sky-300">Regions</a></li>
                            <li><a href="<?php echo $base_url; ?>/admin/admins/logout-admins.php" class="hover:text-red-300">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="mt-8 border-t border-slate-800 pt-6 text-sm text-slate-400 md:flex md:items-center md:justify-between">
                <p>&copy; <?php echo date('Y'); ?> Online Job Portal. All rights reserved.</p>
                <p class="mt-2 md:mt-0">Built for efficient hiring management.</p>
            </div>
        </div>
    </footer>
    <?php endif; ?>
</body>
</html>
