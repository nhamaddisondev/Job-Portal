<?php
require '../../config/config.php';

if (!isset($_SESSION['adminname'])) {
    header("Location: " . ADMINURL . "/admins/login-admins.php");
    exit();
}
$job_id = (int) $_GET['id'];

$pageTitle = "View Pending Job";
$breadcrumb = "Jobs";

//Fetch job details
$stmt = $conn->prepare("SELECT * FROM jobs WHERE id = :id");
$stmt->execute([':id' => $job_id]);
$job = $stmt->fetch(PDO::FETCH_OBJ);

if (!$job) {
    require "../layouts/header.php";
    echo "<div class='p-6 bg-white shadow-md rounded-lg'>Job not found.</div>";
    require "../layouts/footer.php";
    exit();
}

//Fetch job specifications application questions
$qtmt = $conn->prepare("SELECT * FROM job_specifications WHERE job_id = :job_id");
$qtmt->execute([':job_id' => $job_id]);
$specifications = $qtmt->fetchAll(PDO::FETCH_OBJ);

function h($v)
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

function qtype_label($type)
{
    switch ($type) {
        case 'text':
            return 'Text Input';
        case 'textarea':
            return 'Text Area';
        case 'radio':
            return 'Multiple Choice (Single Answer)';
        case 'checkbox':
            return 'Multiple Choice (Multiple Answers)';
        default:
            return 'Unknown Type';
    }
    return 'Unknown Type';
}

function dmy($s, $fmt = 'j M, Y')
{
    if (!$s)
        return '-';
    $ts = strtotime($s);
    return $ts ? date($fmt, $ts) : '-';
}

$siteRoot = preg_replace('#/admin/?$#', '', rtrim(ADMINURL, '/'));

require "../layouts/header.php";

?>

<div class="p-4">
    <div class="max-w-full">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <!-- Card Header -->
            <div class="bg-yellow-400 text-gray-800 font-bold p-4 flex justify-between items-center">
                <span class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd" />
                    </svg>
                    Pending Job Details
                </span>
                <span class="bg-gray-200 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded">Awaiting
                    Approval</span>
            </div>

            <!-- Card Body -->
            <div class="p-6">
                <h4 class="text-xl font-bold mb-4"><?= h($job->job_title) ?></h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left Column -->
                    <div>
                        <p class="mb-2"><strong>Region:</strong> <?= h($job->job_region ?: '—') ?></p>
                        <p class="mb-2"><strong>Type:</strong> <?= h($job->job_type ?: '—') ?></p>
                        <p class="mb-2"><strong>Work Arrangement:</strong> <?= h($job->work_arrangement ?: '—') ?></p>
                        <p class="mb-2"><strong>Vacancy:</strong> <?= (int) ($job->vacancy ?? 0) ?></p>
                        <p class="mb-2"><strong>Category:</strong> <?= h($job->job_category ?: '—') ?></p>
                        <p class="mb-2"><strong>Experience:</strong> <?= h($job->experience ?: '—') ?></p>
                        <p class="mb-2"><strong>Salary:</strong> <?= h($job->salary ?: '—') ?></p>
                        <p class="mb-2"><strong>Inclusivity Notes:</strong> <?= h($job->inclusivity_notes ?: '—') ?></p>
                        <p class="mb-2"><strong>Deadline:</strong> <?= dmy($job->application_deadline) ?></p>
                    </div>

                    <!-- Right Column -->
                    <div>
                        <p class="mb-2"><strong>Company:</strong> <?= h($job->company_name ?: '—') ?></p>
                        <p class="mb-2"><strong>Email:</strong> <?= h($job->company_email ?: '—') ?></p>
                        <p class="mb-2"><strong>Company ID:</strong> <?= (int) ($job->company_id ?? 0) ?></p>
                        <p class="mb-2"><strong>Posted At:</strong> <?= dmy($job->created_at, 'j M, Y h:i A') ?></p>
                        <div>
                            <p class="mb-2"><strong>Company Image:</strong></p>
                            <?php if (!empty($job->company_image)): ?>
                                <img src="<?= $siteRoot ?>/users/user-images/<?= h($job->company_image) ?>"
                                    alt="Company image" class="border rounded max-w-[150px]">
                            <?php else: ?>
                                <p class="text-gray-500">Company image/logo not available</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <hr class="my-6 border-gray-200" />

                <!-- Job Description -->
                <h5 class="text-lg font-bold mt-4">Job Description</h5>
                <div class="admin-rich pl-5 list-disc">
                    <?= html_entity_decode((string) ($job->job_description ?? ''), ENT_QUOTES, 'UTF-8') ?>
                </div>

                <!-- Responsibilities -->
                <h5 class="text-lg font-bold mt-4">Responsibilities</h5>
                <div class="admin-rich pl-5 list-disc">
                    <?= html_entity_decode((string) ($job->responsibilities ?? ''), ENT_QUOTES, 'UTF-8') ?>
                </div>

                <!-- Education & Experience -->
                <h5 class="text-lg font-bold mt-4">Education & Experience</h5>
                <div class="admin-rich pl-5 list-disc">
                    <?= html_entity_decode((string) ($job->education_experience ?? ''), ENT_QUOTES, 'UTF-8') ?>
                </div>

                <!-- Other Benefits -->
                <h5 class="text-lg font-bold mt-4">Other Benefits</h5>
                <div class="admin-rich pl-5 list-disc">
                    <?= html_entity_decode((string) ($job->other_benefits ?? ''), ENT_QUOTES, 'UTF-8') ?>
                </div>

                <hr class="my-6 border-gray-200" />

                <!-- Application Questions -->
                <h5 class="text-lg font-bold mt-4 flex items-center">
                    Application Questions
                    <?php if (empty($specifications)): ?>
                        <span class="bg-gray-500 text-white text-xs font-medium px-2.5 py-0.5 rounded ml-2">None</span>
                    <?php endif; ?>
                </h5>

                <?php if (!empty($specifications)): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 border">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-2/5">Question</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">Type</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/12">Required</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4">Options (if any)</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($specifications as $q): ?>
                                    <tr>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            <?= h($q->question_text) ?>
                                            <?php if ($q->source === 'predefined'): ?>
                                                <span class="bg-blue-200 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded ml-2">Standard</span>
                                            <?php else: ?>
                                                <span class="bg-indigo-200 text-indigo-800 text-xs font-medium px-2.5 py-0.5 rounded ml-2">Custom</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            <span class="bg-gray-200 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded"><?= h(qtype_label($q->qtype)) ?></span>
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            <?php if ((int) $q->is_required === 1): ?>
                                                <span class="bg-green-200 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">Yes</span>
                                            <?php else: ?>
                                                <span class="bg-gray-200 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded">No</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-2">
                                            <?php
                                            $opts = null;
                                            if (!empty($q->options)) {
                                                $opts = json_decode($q->options, true);
                                            }
                                            if (is_array($opts) && count($opts)) {
                                                echo '<ul class="list-disc pl-5">';
                                                foreach ($opts as $op) {
                                                    echo '<li>' . h($op) . '</li>';
                                                }
                                                echo '</ul>';
                                            } else {
                                                echo '<span class="text-gray-500">—</span>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <!-- Buttons -->
                <div class="mt-6 flex justify-between">
                    <a href="status-jobs.php?id=<?= (int) $job->id ?>&status=pending&r=<?= urlencode('pending-jobs.php') ?>"
                        class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        Verify & Approve
                    </a>
                    <a href="pending-jobs.php"
                        class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
                                clip-rule="evenodd" />
                        </svg>
                        Back to Pending Jobs
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .admin-rich ul {
        padding-left: 1.2rem;
    }
    .admin-rich li {
        margin-bottom: 0.25rem;
    }
</style>

<?php require "../layouts/footer.php"; ?>