
<?php
session_start();
require_once 'db.php'; // DB connection include

// Check login
if (!isset($_SESSION['student_id'])) {
    header("Location: ../../login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Receive form data
$company_name = $_POST['company_name'];
$position = $_POST['position'];
$location = $_POST['location'];
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];
$description = $_POST['description'] ?? null;

$attachment_path = null;

// Handle file upload if exists
if (!empty($_FILES['attachment']['name'])) {
    $upload_dir = "uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $allowed_exts = ['pdf', 'doc', 'docx'];
    $file_ext = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));

    if (in_array($file_ext, $allowed_exts)) {
        $filename = time() . '_' . basename($_FILES['attachment']['name']);
        $target_path = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target_path)) {
            $attachment_path = $filename;
        } else {
            die("❌ Failed to upload file.");
        }
    } else {
        die("❌ Invalid file type. Only PDF, DOC, DOCX allowed.");
    }
}

// Insert into database
$stmt = $conn->prepare("INSERT INTO applications (student_id, company_name, position, location, start_date, end_date, description, attachment) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssssss", $student_id, $company_name, $position, $location, $start_date, $end_date, $description, $attachment_path);

if ($stmt->execute()) {
    echo "<script>alert('✅ Application submitted successfully!'); window.location.href='application_list.php';</script>";
} else {
    echo "❌ Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>

