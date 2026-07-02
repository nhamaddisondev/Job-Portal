<?php
require '../config/config.php';
require '../includes/public-helpers.php';

$profileId = (int) ($_GET['id'] ?? 0);
$profile = null;

if ($profileId > 0 && db_available($conn) && table_exists($conn, 'users')) {
    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $profileId]);
        $profile = $stmt->fetch(PDO::FETCH_OBJ);
    } catch (Throwable $e) {
        $profile = null;
    }
}

require '../includes/header.php';
?>

<main class="bg-slate-50">
    <section class="mx-auto max-w-screen-xl px-4 py-10">
        <?php if (!$profile): ?>
            <div class="text-center py-16">
                <h1 class="text-3xl font-bold text-slate-950">Profile not found</h1>
                <a href="<?php echo $base_url; ?>/gerneral/workers.php" class="mt-6 inline-flex rounded-md bg-sky-600 px-5 py-3 text-sm font-semibold text-white hover:bg-sky-700">Back to Job Seekers</a>
            </div>
        <?php else: ?>
            <div class="max-w-2xl mx-auto">
                <div class="rounded-lg border border-slate-200 bg-white p-8 shadow-sm">
                    <div class="flex items-center gap-6">
                        <div class="flex h-20 w-20 items-center justify-center rounded-full bg-slate-900 text-3xl font-bold text-white">
                            <?php echo h(strtoupper(substr((string) ($profile->fullname ?: $profile->name ?: $profile->username ?: 'U'), 0, 1))); ?>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-slate-950"><?php echo h($profile->fullname ?: $profile->name ?: $profile->username ?: 'User'); ?></h1>
                            <p class="text-slate-600">@<?php echo h($profile->username ?? 'profile'); ?></p>
                        </div>
                    </div>

                    <hr class="my-6 border-slate-200">

                    <div class="space-y-4">
                        <div>
                            <span class="text-sm font-semibold text-slate-500">Email</span>
                            <p class="text-slate-900"><?php echo h($profile->email ?? 'Not listed'); ?></p>
                        </div>
                        <div>
                            <span class="text-sm font-semibold text-slate-500">Contact</span>
                            <p class="text-slate-900"><?php echo h($profile->contact ?? 'Not listed'); ?></p>
                        </div>
                        <div>
                            <span class="text-sm font-semibold text-slate-500">Account Type</span>
                            <p class="text-slate-900"><?php echo h($profile->type ?? $profile->role ?? 'User'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php require '../includes/footer.php'; ?>