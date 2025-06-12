<?php
include "../../conn.php";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'])) {
    $student_id = intval($_POST['student_id']);
    $stmt = $conn->prepare("UPDATE students SET status = 'inactive' WHERE student_id = ?");
    $stmt->bind_param("i", $student_id);
    if ($stmt->execute()) {
        echo "success";
    } else {
        http_response_code(500);
        echo "error";
    }
    $stmt->close();
    $conn->close();
} else {
    http_response_code(400);
    echo "Invalid request";
}