<?php
require '../config/config.php';
require '../includes/public-helpers.php';

if (!isset($_SESSION['id'])) {
    header('Location: ' . BASEURL . '/auth/login.php');
    exit();
}

$userId = (int) $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    if (db_available($conn) && table_exists($conn, 'availability')) {
        try {
            // Delete existing availability for this user
            $conn->prepare("DELETE FROM availability WHERE user_id = :id")->execute([':id' => $userId]);

            $days = $_POST['days'] ?? [];
            $columns = table_columns($conn, 'availability');

            foreach ($days as $dayData) {
                if (!isset($dayData['enabled']) || $dayData['enabled'] !== '1') continue;

                $dayOfWeek = (int) ($dayData['day'] ?? 0);
                $startTime = $dayData['start_time'] ?? '09:00';
                $endTime = $dayData['end_time'] ?? '17:00';

                $data = [
                    'user_id' => $userId,
                    'day_of_week' => $dayOfWeek,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                ];

                if (isset($columns['created_at'])) {
                    $data['created_at'] = date('Y-m-d H:i:s');
                }

                $fields = array_keys($data);
                $placeholders = array_map(fn($f) => ':' . $f, $fields);
                $sql = 'INSERT INTO availability (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $placeholders) . ')';
                $stmt = $conn->prepare($sql);
                $stmt->execute($data);
            }
        } catch (Throwable $e) {
            // Ignore
        }
    }
}

header('Location: ' . BASEURL . '/users/my_availability.php');
exit();