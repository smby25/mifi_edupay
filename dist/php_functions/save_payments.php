<?php
include "../../conn.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $batch_name = $_POST['batch_name'];
    $target_grade = $_POST['target_grade'];
    $payment_types = $_POST['payment_type'];
    $amounts = $_POST['amount'];

    $stmt = $conn->prepare("INSERT INTO payments (batch_name, target_grade, payment_type, amount) VALUES (?, ?, ?, ?)");

    for ($i = 0; $i < count($payment_types); $i++) {
        $type = htmlspecialchars(trim($payment_types[$i]));
        $amt = floatval($amounts[$i]);

        $stmt->bind_param("sssd", $batch_name, $target_grade, $type, $amt);
        $stmt->execute();
    }

    $stmt->close();
    $conn->close();

    header("Location: ../student_fees_sidebar.php?success=1");
    exit();
}
?>
