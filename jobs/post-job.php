<?php
require '../config/config.php';
require '../includes/public-helpers.php';

if (!isset($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'employer') {
    header('Location: ' . BASEURL . '/auth/login.php');
    exit();
}

$errors = [];
$success = false;

$formData = [
    'job_title' => '',
    'job_category' => '',
    'job_region' => '',
    'job_type' => '',
    'work_arrangement' => '',
    'vacancy' => '',
    'experience' => '',
    'salary' => '',
    'application_deadline' => '',
    'job_description' => '',
    'responsibilities' => '',
    'education_experience' => '',
    'other_benefits' => '',
    'inclusivity_notes' => '',
    'company_name' => '',
    'company_email' => '',
    'company_image' => '',
];

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
    } catch (Throwable $e) {
        // Ignore
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $formData['job_title'] = trim($_POST['job_title'] ?? '');
    $formData['job_category'] = trim($_POST['job_category'] ?? '');
    $formData['job_region'] = trim($_POST['job_region'] ?? '');
    $formData['job_type'] = trim($_POST['job_type'] ?? '');
    $formData['work_arrangement'] = trim($_POST['work_arrangement'] ?? '');
    $formData['vacancy'] = trim($_POST['vacancy'] ?? '');
    $formData['experience'] = trim($_POST['experience'] ?? '');
    $formData['salary'] = trim($_POST['salary'] ?? '');
    $formData['application_deadline'] = trim($_POST['application_deadline'] ?? '');
    $formData['job_description'] = trim($_POST['job_description'] ?? '');
    $formData['responsibilities'] = trim($_POST['responsibilities'] ?? '');
    $formData['education_experience'] = trim($_POST['education_experience'] ?? '');
    $formData['other_benefits'] = trim($_POST['other_benefits'] ?? '');
    $formData['inclusivity_notes'] = trim($_POST['inclusivity_notes'] ?? '');
    $formData['company_name'] = trim($_POST['company_name'] ?? '');
    $formData['company_email'] = trim($_POST['company_email'] ?? '');

    if ($formData['job_title'] === '') $errors[] = 'Job title is required.';
    if ($formData['job_category'] === '') $errors[] = 'Category is required.';
    if ($formData['job_region'] === '') $errors[] = 'Region is required.';
    if ($formData['job_type'] === '') $errors[] = 'Job type is required.';
    if ($formData['application_deadline'] === '') $errors[] = 'Application deadline is required.';
    if ($formData['job_description'] === '') $errors[] = 'Job description is required.';
    if ($formData['company_name'] === '') $errors[] = 'Company name is required.';

    // Handle file upload
    $imageName = '';
    if (isset($_FILES['company_image']) && $_FILES['company_image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['company_image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $imageName = uniqid('company_') . '.' . $ext;
            $uploadDir = __DIR__ . '/../users/user-images/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            move_uploaded_file($_FILES['company_image']['tmp_name'], $uploadDir . $imageName);
        } else {
            $errors[] = 'Invalid image format. Allowed: jpg, jpeg, png, gif, webp.';
        }
    }

    if (empty($errors)) {
        try {
            $columns = table_columns($conn, 'jobs');
            $data = [];

            $fieldMap = [
                'job_title', 'job_category', 'job_region', 'job_type', 'work_arrangement',
                'vacancy', 'experience', 'salary', 'application_deadline',
                'job_description', 'responsibilities', 'education_experience',
                'other_benefits', 'inclusivity_notes', 'company_name', 'company_email',
            ];

            foreach ($fieldMap as $field) {
                if (isset($columns[$field])) {
                    $data[$field] = $formData[$field];
                }
            }

            if (isset($columns['company_image']) && $imageName) {
                $data['company_image'] = $imageName;
            }
            if (isset($columns['company_id'])) {
                $data['company_id'] = (int) $_SESSION['id'];
            }
            if (isset($columns['status'])) {
                $data['status'] = 0; // Pending approval
            }
            if (isset($columns['created_at'])) {
                $data['created_at'] = date('Y-m-d H:i:s');
            }
            if (isset($columns['updated_at'])) {
                $data['updated_at'] = date('Y-m-d H:i:s');
            }

            $fieldNames = array_keys($data);
            $placeholders = array_map(fn($f) => ':' . $f, $fieldNames);
            $sql = 'INSERT INTO jobs (' . implode(', ', $fieldNames) . ') VALUES (' . implode(', ', $placeholders) . ')';
            $stmt = $conn->prepare($sql);
            $stmt->execute($data);

            $success = true;
            $formData = array_map(fn() => '', $formData);
        } catch (Throwable $e) {
            $errors[] = 'Failed to post job. Please try again.';
        }
    }
}

require '../includes/header.php';
?>

<main class="bg-slate-50">
    <section class="mx-auto max-w-screen-xl px-4 py-10">
        <div class="mb-8">
            <p class="text-sm font-semibold uppercase tracking-normal text-sky-600">Employer</p>
            <h1 class="mt-3 text-4xl font-bold text-slate-950">Post a New Job</h1>
            <p class="mt-3 text-slate-600">Fill in the details below. Your job will be reviewed by an admin before being published.</p>
        </div>

        <?php if ($success): ?>
            <div class="mb-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                Job posted successfully! It will be visible once approved by an admin.
            </div>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div class="mb-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc pl-5">
                    <?php foreach ($errors as $err): ?>
                        <li><?php echo h($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="post-job.php" enctype="multipart/form-data" class="space-y-6">
            <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-bold text-slate-950">Basic Information</h2>
                <div class="mt-5 grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="job_title">Job Title *</label>
                        <input type="text" id="job_title" name="job_title" value="<?php echo h($formData['job_title']); ?>" class="mt-2 block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="job_category">Category *</label>
                        <select id="job_category" name="job_category" class="mt-2 block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30" required>
                            <option value="">Select category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo h($cat->name); ?>" <?php echo $formData['job_category'] === $cat->name ? 'selected' : ''; ?>><?php echo h($cat->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="job_region">Region *</label>
                        <select id="job_region" name="job_region" class="mt-2 block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30" required>
                            <option value="">Select region</option>
                            <?php foreach ($regions as $reg): ?>
                                <option value="<?php echo h($reg->name); ?>" <?php echo $formData['job_region'] === $reg->name ? 'selected' : ''; ?>><?php echo h($reg->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="job_type">Job Type *</label>
                        <select id="job_type" name="job_type" class="mt-2 block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30" required>
                            <option value="">Select type</option>
                            <option value="Full-time" <?php echo $formData['job_type'] === 'Full-time' ? 'selected' : ''; ?>>Full-time</option>
                            <option value="Part-time" <?php echo $formData['job_type'] === 'Part-time' ? 'selected' : ''; ?>>Part-time</option>
                            <option value="Contract" <?php echo $formData['job_type'] === 'Contract' ? 'selected' : ''; ?>>Contract</option>
                            <option value="Temporary" <?php echo $formData['job_type'] === 'Temporary' ? 'selected' : ''; ?>>Temporary</option>
                            <option value="Internship" <?php echo $formData['job_type'] === 'Internship' ? 'selected' : ''; ?>>Internship</option>
                            <option value="Freelance" <?php echo $formData['job_type'] === 'Freelance' ? 'selected' : ''; ?>>Freelance</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="work_arrangement">Work Arrangement</label>
                        <select id="work_arrangement" name="work_arrangement" class="mt-2 block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30">
                            <option value="">Select arrangement</option>
                            <option value="On-site" <?php echo $formData['work_arrangement'] === 'On-site' ? 'selected' : ''; ?>>On-site</option>
                            <option value="Remote" <?php echo $formData['work_arrangement'] === 'Remote' ? 'selected' : ''; ?>>Remote</option>
                            <option value="Hybrid" <?php echo $formData['work_arrangement'] === 'Hybrid' ? 'selected' : ''; ?>>Hybrid</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="vacancy">Vacancy</label>
                        <input type="number" id="vacancy" name="vacancy" value="<?php echo h($formData['vacancy']); ?>" min="1" class="mt-2 block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="experience">Experience Required</label>
                        <input type="text" id="experience" name="experience" value="<?php echo h($formData['experience']); ?>" placeholder="e.g., 2-3 years" class="mt-2 block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="salary">Salary</label>
                        <input type="text" id="salary" name="salary" value="<?php echo h($formData['salary']); ?>" placeholder="e.g., $50,000 - $70,000" class="mt-2 block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="application_deadline">Application Deadline *</label>
                        <input type="date" id="application_deadline" name="application_deadline" value="<?php echo h($formData['application_deadline']); ?>" class="mt-2 block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30" required>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-bold text-slate-950">Detailed Information</h2>
                <div class="mt-5 space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="job_description">Job Description *</label>
                        <textarea id="job_description" name="job_description" rows="6" class="mt-2 block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30" required><?php echo h($formData['job_description']); ?></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="responsibilities">Responsibilities</label>
                        <textarea id="responsibilities" name="responsibilities" rows="4" class="mt-2 block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30"><?php echo h($formData['responsibilities']); ?></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="education_experience">Education & Experience</label>
                        <textarea id="education_experience" name="education_experience" rows="4" class="mt-2 block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30"><?php echo h($formData['education_experience']); ?></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="other_benefits">Other Benefits</label>
                        <textarea id="other_benefits" name="other_benefits" rows="4" class="mt-2 block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30"><?php echo h($formData['other_benefits']); ?></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="inclusivity_notes">Inclusivity Notes</label>
                        <textarea id="inclusivity_notes" name="inclusivity_notes" rows="3" class="mt-2 block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30"><?php echo h($formData['inclusivity_notes']); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-bold text-slate-950">Company Information</h2>
                <div class="mt-5 grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="company_name">Company Name *</label>
                        <input type="text" id="company_name" name="company_name" value="<?php echo h($formData['company_name']); ?>" class="mt-2 block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="company_email">Company Email</label>
                        <input type="email" id="company_email" name="company_email" value="<?php echo h($formData['company_email']); ?>" class="mt-2 block w-full rounded-md border border-slate-300 px-4 py-3 text-slate-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="company_image">Company Logo / Image</label>
                        <input type="file" id="company_image" name="company_image" accept="image/*" class="mt-2 block w-full text-slate-500 file:mr-4 file:rounded-md file:border-0 file:bg-sky-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-sky-700 hover:file:bg-sky-100">
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="<?php echo $base_url; ?>/users/employer_dashboard.php" class="rounded-md border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
                <button type="submit" name="submit" class="rounded-md bg-sky-600 px-5 py-3 text-sm font-semibold text-white hover:bg-sky-700">Post Job</button>
            </div>
        </form>
    </section>
</main>

<?php require '../includes/footer.php'; ?>