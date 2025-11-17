<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // ဖိုင်နာမည်ယူ
    $stmt = $conn->prepare("SELECT attachment FROM applications WHERE id = ? AND student_id = ?");
    $stmt->bind_param("ii", $id, $student_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $data = $res->fetch_assoc();
        if (!empty($data['attachment']) && file_exists("uploads/" . $data['attachment'])) {
            unlink("uploads/" . $data['attachment']); // ဖိုင်ဖျက်
        }

        // record ဖျက်
        $del = $conn->prepare("DELETE FROM applications WHERE id = ? AND student_id = ?");
        $del->bind_param("ii", $id, $student_id);
        $del->execute();
    }
}

header("Location: application_list.php");
exit();
?>
