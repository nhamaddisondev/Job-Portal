<?php require '../../config/config.php'; ?>

<?php
if (!isset($_SESSION['adminname'])) {
    header("Location: " . ADMINURL . "/admins/login-admins.php");
    exit();
}

$pageTitle = "Create Job Region";
$breadcrumb = "Systems";
$errors = [];
$nameVal = "";
$codeVal = "";
$statusVal = "1";

function h($v)
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

if (isset($_POST['submit'])) {
    $nameVal = trim($_POST['name'] ?? '');
    $codeVal = trim($_POST['code'] ?? '');
    $statusVal = $_POST['status'] ?? '1';

    if ($nameVal === '') {
        $errors[] = 'Job region name is required.';
    } else {
        $stmt = $conn->prepare("SELECT id FROM job_regions WHERE name = ?");
        $stmt->execute([$nameVal]);
        if ($stmt->fetch()) {
            $errors[] = 'Job region name already exists.';
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO job_regions (name, code, status) VALUES (?, ?, ?)");
        if ($stmt->execute([$nameVal, strtoupper($codeVal), (int) $statusVal])) {
            header('Location: ' . ADMINURL . '/job-regions/show-jobregions.php?created=1');
            exit();
        } else {
            $errors[] = 'Failed to create job region. Please try again.';
        }
    }
}

require '../../admin/layouts/header.php';
?>
<div class="max-w-4xl mx-auto p-4">
    <div class="bg-white shadow-md rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Add New Job Region</h2>
            <a href="<?= ADMINURL ?>/job-regions/show-jobregions.php"
                class="px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Job Regions
            </a>
        </div>

        <?php if ($errors): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md mb-6" role="alert">
                <ul class="list-disc pl-5 m-0">
                    <?php foreach ($errors as $err): ?>
                        <li><?= h($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="create-jobregions.php" novalidate>
            <div class="mb-4">
                <label for="regName" class="block text-sm font-medium text-gray-700 mb-1">Region Name</label>
                <input type="text" name="name" id="regName"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="e.g., New South Wales" value="<?= h($nameVal) ?>" required>
            </div>

            <div class="mb-4">
                <label for="regCode" class="block text-sm font-medium text-gray-700 mb-1">Region Code</label>
                <input type="text" name="code" id="regCode"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="e.g., NSW" value="<?= h($codeVal) ?>" required>
                <p class="text-sm text-gray-500 mt-1">Will be saved in uppercase.</p>
            </div>

            <div class="mb-6">
                <label for="regStatus" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" id="regStatus"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="1" <?= $statusVal === '1' ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= $statusVal === '0' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>

            <button type="submit" name="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Add
            </button>
        </form>
    </div>
</div>

<?php require '../../admin/layouts/footer.php'; ?>