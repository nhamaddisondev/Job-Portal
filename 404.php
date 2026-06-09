<?php

http_response_code(404);
require 'config/config.php';
require 'includes/header.php';
?>

<main class="flex min-h-[calc(100vh-5rem)] items-center justify-center px-4 py-16">
    <div class="text-center">
        <h1 class="text-7xl font-bold tracking-normal text-gray-900 md:text-8xl">404</h1>
        <p class="mt-4 text-2xl text-gray-900">
            <span class="text-red-600">Opps!</span> Page not found.
        </p>
        <p class="mt-3 text-lg text-gray-600">
            The page you're looking for doesn't exist.
        </p>
        <a href="<?php echo $base_url; ?>/index.php" class="mt-8 inline-flex items-center justify-center rounded-md bg-sky-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2">
            Go Home
        </a>
    </div>
    <?php require 'includes/footer.php'; ?>
</main>
</body>
</html>
