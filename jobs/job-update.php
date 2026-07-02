<?php
require '../config/config.php';
require '../includes/public-helpers.php';

if (!isset($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'employer') {
    header('Location: ' . BASEURL . '/auth/login.php');
    exit();
}

$jobId = (int) ($_GET['id'] ?? 0);
$job = null;
$errors = [];
$success = false;

if ($jobId > 0 && db_available($conn) && table_exists($conn, 'jobs')) {
    try {
        $stmt = $conn->prepare("SELECT * FROM jobs WHERE id = :id AND company_id = :company_id LIMIT 1");
        $stmt->execute([':id' => $jobId, ':company_id' => (int) $_SESSION['id']]);
        $job = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        $job = null;
    }
}

if (!$job) {
    header('Location: ' . BASEURL . '/users/postedJobs.php');
    exit();
}

$categories = [];
$regions = [];
if (db_available($conn)) {
    try {
        if (table_exists($conn, 'categories')) {
            $categories = $conn->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_OBJ);
        }
        if (table_exists($conn, 'job_regions')) {
            $regions = $conn->query("SELECT id, name FROM job_regions ORDER BY name ASC")->fetchAll(PDO::FETCH_OBJ);
        }
    } catch (Throwable $e) {}
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $formData = [
        'job_title' => trim($_POST['job_title'] ?? ''),
        'job_category' => trim($_POST['job_category'] ?? ''),
        'job_region' => trim($_POST['job_region'] ?? ''),
        'job_type' => trim($_POST['job_type'] ?? ''),
        'work_arrangement' => trim($_POST['work_arrangement'] ?? ''),
        'vacancy' => trim($_POST['vacancy'] ?? ''),
        'experience' => trim($_POST['experience'] ?? ''),
        'salary' => trim($_POST['salary'] ?? ''),
        'application_deadline' => trim($_POST['application_deadline'] ?? ''),
        'job_description' => trim($_POST['job_description'] ?? ''),
        'responsibilities' => trim($_POST['responsibilities'] ?? ''),
        'education_experience' => trim($_POST['education_experience'] ?? ''),
        'other_benefits' => trim($_POST['other_benefits'] ?? ''),
        'inclusivity_notes' => trim($_POST['inclusivity_notes'] ?? ''),
        'company_name' => trim($_POST['company_name'] ?? ''),
        'company_email' => trim($_POST['company_email'] ?? ''),
    ];

    if ($formData['job_title'] === '') $errors[] = 'Job title is required.';
    if ($formData['job_description'] === '') $errors[] = 'Job description is required.';

    if (empty($errors)) {
        try {
            $columns = table_columns($conn, 'jobs');
            $fieldMap = [
                'job_title', 'job_category', 'job_region', 'job_type', 'work_arrangement',
                'vacancy', 'experience', 'salary', 'application_deadline',
                'job_description', 'responsibilities', 'education_experience',
                'other_benefits', 'inclusivity_notes', 'company_name', 'company_email',
            ];

            $data = [];
            foreach ($fieldMap as $field) {
                if (isset($columns[$field])) {
                    $data[] = "$field = :$field";
                }
            }
            if (isset($columns['updated_at'])) {
                $data[] = "updated_at = :updated_at";
                $formData['updated_at'] = date('Y-m-d H:i:s');
            }

            $formData[':id'] = $jobId;
            $sql = 'UPDATE jobs SET ' . implode(', ', $data) . ' WHERE id = :id';
            $stmt = $conn->prepare($sql);
            $stmt->execute($formData);

            $success = true;
            $job = array_merge($job, $formData);
        } catch (Throwable $e) {
            $errors[] = 'Failed to update job.';
        }
    }
} else {
    $formData = $job;
}

require '../includes/header.php';
?>

<main class="bg-slate-50">
    <section class="mx-auto max-w-screen-xl px-4 py-10">
        <div class="mb-8">
            <p class="text-sm font-semibold uppercase tracking-normal text-sky-600">Employer</p>
            <h1 class="mt-3 text-4xl font-bold text-slate-950">Edit Job</h1>
        </div>

        <?php if ($success): ?>
            <div class="mb-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">Job updated successfully.</div>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div class="mb-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc pl-5"><?php foreach ($errors as $err): ?><li><?php echo h($err); ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="job-update.php?id=<?php echo $jobId; ?>" class="space-y-6">
            <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-bold text-slate-950">Basic Information</h2>
                <div class="mt-5 grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="job_title">Job Title *</label>
                        <input type="text" id="job_title" name="job_title" value="<?php echo h($formData['job_title'] ?? ''); ?>" class="mt-2 block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="job_category">Category</label>
                        <select id="job_category" name="job_category" class="mt-2 block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30">
                            <option value="">Select category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo h($cat->name); ?>" <?php echo ($formData['job_category'] ?? '') === $cat->name ? 'selected' : ''; ?>><?php echo h($cat->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="job_region">Region</label>
                        <select id="job_region" name="job_region" class="mt-2 block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30">
                            <option value="">Select region</option>
                            <?php foreach ($regions as $reg): ?>
                                <option value="<?php echo h($reg->name); ?>" <?php echo ($formData['job_region'] ?? '') === $reg->name ? 'selected' : ''; ?>><?php echo h($reg->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="job_type">Job Type</label>
                        <select id="job_type" name="job_type" class="mt-2 block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30">
                            <option value="">Select type</option>
                            <?php foreach (['Full-time', 'Part-time', 'Contract', 'Temporary', 'Internship', 'Freelance'] as $type): ?>
                                <option value="<?php echo $type; ?>" <?php echo ($formData['job_type'] ?? '') === $type ? 'selected' : ''; ?>><?php echo $type; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="application_deadline">Deadline</label>
                        <input type="date" id="application_deadline" name="application_deadline" value="<?php echo h($formData['application_deadline'] ?? ''); ?>" class="mt-2 block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="salary">Salary</label>
                        <input type="text" id="salary" name="salary" value="<?php echo h($formData['salary'] ?? ''); ?>" class="mt-2 block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30">
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-bold text-slate-950">Description</h2>
                <div class="mt-5 space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="job_description">Job Description *</label>
                        <textarea id="job_description" name="job_description" rows="6" class="mt-2 block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30" required><?php echo h($formData['job_description'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="<?php echo $base_url; ?>/users/postedJobs.php" class="rounded-md border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
                <button type="submit" name="submit" class="rounded-md bg-sky-600 px-5 py-3 text-sm font-semibold text-white hover:bg-sky-700">Update Job</button>
            </div>
        </form>
    </section>
</main>

<?php require '../includes/footer.php'; ?>