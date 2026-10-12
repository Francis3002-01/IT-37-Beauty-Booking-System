<?php
//Send the stored photo to an img element; no HTML or JSON is needed.
$id = $_GET['id'] ?? '';
if (!is_string($id) || !ctype_digit($id) || $id < 1 || $id > 2147483647) {
    http_response_code(400);
    exit;
}

try {
    require_once __DIR__ . '/config/database.php';
    $statement = $conn->prepare('SELECT imageData, imageMime FROM staff WHERE staffID = ?');
    $statement->bind_param('i', $id);
    $statement->execute();
    $image = $statement->get_result()->fetch_assoc();
    $statement->close();
    $conn->close();

    if (!$image || $image['imageData'] === null
        || !in_array($image['imageMime'], ['image/jpeg', 'image/png', 'image/webp'], true)) {
        http_response_code(404);
        exit;
    }
    header('Content-Type: ' . $image['imageMime']);
    header('Content-Length: ' . strlen($image['imageData']));
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: no-store'); //show the new photo after editing
    echo $image['imageData'];
} catch (mysqli_sql_exception $error) {
    error_log('Staff image failed: ' . $error->getMessage());
    http_response_code(500);
}
