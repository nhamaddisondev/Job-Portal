<?php
require '../config/config.php';
require '../includes/public-helpers.php';

if (!isset($_SESSION['id'])) {
    header('Location: ' . BASEURL . '/auth/login.php');
    exit();
}

$userId = (int) $_SESSION['id'];
$availability = [];

if (db_available($conn) && table_exists($conn, 'availability')) {
    try {
        $stmt = $conn->prepare("SELECT * FROM availability WHERE user_id = :id ORDER BY day_of_week ASC, start_time ASC");
        $stmt->execute([':id' => $userId]);
        $availability = $stmt->fetchAll(PDO::FETCH_OBJ);
    } catch (Throwable $e) {
        $availability = [];
    }
}

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

require '../includes/header.php';
?>

<main class="bg-slate-50">
    <section class="mx-auto max-w-screen-xl px-4 py-10">
        <div class="mb-8">
            <p class="text-sm font-semibold uppercase tracking-normal text-sky-600">Availability</p>
            <h1 class="mt-3 text-4xl font-bold text-slate-950">My Availability</h1>
            <p class="mt-3 text-slate-600">Manage your weekly availability schedule.</p>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <form method="POST" action="update_availability.php" class="space-y-4">
                <?php foreach ($days as $index => $day): ?>
                    <div class="flex flex-wrap items-center gap-4 rounded-md border border-slate-100 p-4">
                        <div class="w-24 font-semibold text-slate-700"><?php echo h($day); ?></div>
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" name="days[<?php echo $index; ?>][enabled]" value="1"
                                <?php
                                $found = false;
                                foreach ($availability as $a) {
                                    if ((int) $a->day_of_week === $index) { $found = true; break; }
                                }
                                echo $found ? 'checked' : '';
                                ?>
                                class="h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                            Available
                        </label>
                        <input type="hidden" name="days[<?php echo $index; ?>][day]" value="<?php echo $index; ?>">
                        <div class="flex items-center gap-2">
                            <input type="time" name="days[<?php echo $index; ?>][start_time]" value="<?php
                                foreach ($availability as $a) {
                                    if ((int) $a->day_of_week === $index) { echo h($a->start_time); break; }
                                }
                            ?>" class="rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30">
                            <span class="text-slate-500">to</span>
                            <input type="time" name="days[<?php echo $index; ?>][end_time]" value="<?php
                                foreach ($availability as $a) {
                                    if ((int) $a->day_of_week === $index) { echo h($a->end_time); break; }
                                }
                            ?>" class="rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/30">
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="flex justify-end pt-4">
                    <button type="submit" name="submit" class="rounded-md bg-sky-600 px-5 py-3 text-sm font-semibold text-white hover:bg-sky-700">Save Availability</button>
                </div>
            </form>
        </div>
    </section>
</main>

<?php require '../includes/footer.php'; ?>