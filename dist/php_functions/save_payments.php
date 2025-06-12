<?php
include "../../conn.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $batch_name = $_POST['batch_name'];
    $target_grade = $_POST['target_grade'];
    $payment_types = $_POST['payment_type'];
    $amounts = $_POST['amount'];
    $payment_id = isset($_POST['payment_id']) ? intval($_POST['payment_id']) : 0;

    if ($payment_id > 0) {
        // Update the payment batch (only the first payment_type/amount for simplicity)
        $type = htmlspecialchars(trim($payment_types[0]));
        $amt = floatval($amounts[0]);
        $stmt = $conn->prepare("UPDATE payments SET batch_name=?, target_grade=?, payment_type=?, amount=? WHERE id=?");
        $stmt->bind_param("sssdi", $batch_name, $target_grade, $type, $amt, $payment_id);
        $stmt->execute();
        $stmt->close();

        // Update all related student_payments for this payment_id
        $stmt2 = $conn->prepare("UPDATE student_payments SET amount_paid=? WHERE payment_id=?");
        $stmt2->bind_param("di", $amt, $payment_id);
        $stmt2->execute();
        $stmt2->close();

        $conn->close();
        header("Location: ../student_fees_sidebar.php?success=1");
        exit();
    } else {
        // Insert new payment batch
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
}
?>