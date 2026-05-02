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
    <title>Online Job Portal - Footer</title>
</head>
<body>
    <footer class="bg-neutral-primary-soft rounded-base shadow-xs border border-default m-4">
        <div class="w-full max-w-screen-xl mx-auto p-4 md:py-8">
            <div class="sm:flex sm:items-center sm:justify-between">
                <a href="<?php echo $base_url; ?>/index.php" class="flex items-center mb-4 sm:mb-0 space-x-3 rtl:space-x-reverse">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-sky-600 text-white text-sm font-bold">OJP</span>
                    <span class="text-heading self-center text-2xl font-semibold whitespace-nowrap">Online Job Portal</span>
                </a>

                <ul class="flex flex-wrap items-center mb-6 text-sm font-medium text-body sm:mb-0">
                    <li>
                        <a href="<?php echo $base_url; ?>/index.php" class="hover:underline me-4 md:me-6">Home</a>
                    </li>
                    <li>
                        <a href="<?php echo $base_url; ?>/findjobs.php" class="hover:underline me-4 md:me-6">Find Jobs</a>
                    </li>
                    <li>
                        <a href="<?php echo $base_url; ?>/gerneral/companies.php" class="hover:underline me-4 md:me-6">Companies</a>
                    </li>
                    <li>
                        <a href="<?php echo $base_url; ?>/gerneral/workers.php" class="hover:underline me-4 md:me-6">Job Seekers</a>
                    </li>
                    <li>
                        <a href="<?php echo $base_url; ?>/about.php" class="hover:underline me-4 md:me-6">About</a>
                    </li>
                    <li>
                        <a href="<?php echo $base_url; ?>/contact.php" class="hover:underline">Contact</a>
                    </li>
                </ul>
            </div>

            <hr class="my-6 border-default sm:mx-auto lg:my-8" />

            <div class="text-sm text-body sm:text-center space-y-1">
                <p>Connecting job seekers and employers with trusted opportunities.</p>
                <p>&copy; <?php echo date('Y'); ?> Online Job Portal. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
