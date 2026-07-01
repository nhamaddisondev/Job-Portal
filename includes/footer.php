<?php
if (!isset($base_url)) {
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $projectName = basename(dirname(__DIR__));
    $pathParts = explode('/', trim($_SERVER['SCRIPT_NAME'], '/'));
    $projectIndex = array_search($projectName, $pathParts, true);
    $projectPath = $projectIndex === false ? '' : '/' . implode('/', array_slice($pathParts, 0, $projectIndex + 1));
    $base_url = $scheme . "://$_SERVER[HTTP_HOST]" . $projectPath;
}
?>

<footer class="m-4 rounded-md border border-gray-200 bg-white shadow-sm">
    <div class="w-full max-w-screen-xl mx-auto p-4 md:py-8">
        <div class="sm:flex sm:items-center sm:justify-between">
            <a href="<?php echo $base_url; ?>/index.php" class="flex items-center mb-4 sm:mb-0 space-x-3 rtl:space-x-reverse">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-sky-600 text-white text-sm font-bold">OJP</span>
                <span class="text-heading self-center text-2xl font-semibold whitespace-nowrap">Online Job Portal</span>
            </a>

            <ul class="flex flex-wrap items-center mb-6 text-sm font-medium text-gray-600 sm:mb-0">
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

        <hr class="my-6 border-gray-200 sm:mx-auto lg:my-8" />

        <div class="text-sm text-gray-600 sm:text-center space-y-1">
            <p>Connecting job seekers and employers with trusted opportunities.</p>
            <p>&copy; <?php echo date('Y'); ?> Online Job Portal. All rights reserved.</p>
        </div>
    </div>
</footer>
</body>
</html>
