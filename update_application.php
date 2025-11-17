<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = intval($_POST['id']);
    $company_name = $_POST['company_name'];
    $position = $_POST['position'];
    $location = $_POST['location'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $description = $_POST['description'];

    // ယခင်ဖိုင်နာမည်ယူ
    $stmt = $conn->prepare("SELECT attachment FROM applications WHERE id = ? AND student_id = ?");
    $stmt->bind_param("ii", $id, $student_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        die("Application not found.");
    }
    $oldData = $res->fetch_assoc();
    $attachment = $oldData['attachment'];

    // Attachment upload
    if (!empty($_FILES['attachment']['name'])) {
        $targetDir = "uploads/";
        $fileName = time() . "_" . basename($_FILES['attachment']['name']);
        $targetFilePath = $targetDir . $fileName;
        move_uploaded_file($_FILES["attachment"]["tmp_name"], $targetFilePath);
        $attachment = $fileName;
    }

    // Update query
    $sql = "UPDATE applications SET company_name=?, position=?, location=?, start_date=?, end_date=?, description=?, attachment=? WHERE id=? AND student_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssii", $company_name, $position, $location, $start_date, $end_date, $description, $attachment, $id, $student_id);

    if ($stmt->execute()) {
        header("Location: application_list.php");
        exit();
    } else {
        echo "Error updating record.";
    }
}
?>
