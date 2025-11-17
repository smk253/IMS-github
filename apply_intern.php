<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: ../../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Apply for Internship</title>
<style>
:root {
    --primary:#0077b6;
    --primary-hover:#005f8c;
    --accent:#2a9df4;
    --accent-hover:#1b82d1;
    --bg-gradient: linear-gradient(135deg, #74ebd5 0%, #9face6 100%);
    --white-bg: #ffffffdd;
}

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

body {
    background: var(--bg-gradient);
}

.container {
    max-width: 600px;
    margin: 60px auto;
    background: var(--white-bg);
    padding: 30px 25px;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0,119,182,0.3);
}

h2 {
    text-align: center;
    color: var(--primary);
    margin-bottom: 25px;
    font-size: 1.8rem;
}

form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

input[type="text"],
input[type="date"],
input[type="file"],
textarea {
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 1rem;
    width: 100%;
}

label {
    font-weight: 600;
    color: var(--primary);
    margin-top: 5px;
}

input[type="submit"] {
    background-color: var(--accent);
    color: white;
    padding: 12px;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

input[type="submit"]:hover {
    background-color: var(--accent-hover);
    transform: translateY(-2px);
}

.back-link {
    display: block;
    margin: 25px auto 0;
    padding: 10px 20px;
    background-color: var(--primary);
    color: white;
    border-radius: 8px;
    text-decoration: none;
    font-weight: bold;
    text-align: center;
    max-width: 220px;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

.back-link:hover {
    background-color: var(--primary-hover);
    transform: translateY(-2px);
}

/* Responsive two-column layout for start/end date */
@media (min-width: 768px) {
    .date-row {
        display: flex;
        gap: 15px;
    }
    .date-row label {
        width: 100%;
        margin-top: 0;
    }
    .date-row input {
        width: 100%;
    }
}
</style>
</head>
<body>
<div class="container">
    <h2>📄 Apply for Internship</h2>
    
    <form action="submit_application.php" method="POST" enctype="multipart/form-data">
        <input type="text" name="company_name" placeholder="🏢 Company Name" required>
        <input type="text" name="position" placeholder="💼 Position Title(Optional)" >
        <input type="text" name="location" placeholder="📍 Location" required>
        
        <div class="date-row">
            <div>
                <label for="start_date">📅 Start Date</label>
                <input type="date" name="start_date" required>
            </div>
            <div>
                <label for="end_date">📅 End Date</label>
                <input type="date" name="end_date" required>
            </div>
        </div>

        <textarea name="description" rows="4" placeholder="📝 Description (Optional)"></textarea>
        <label for="attachment">📎 Upload CV or File (Optional)</label>
        <input type="file" name="attachment" accept=".pdf,.doc,.docx">

        <input type="submit" value="🚀 Submit Application">
    </form>

    <a href="student_dashboard.php" class="back-link">🔙 Back to Dashboard</a>
</div>
</body>
</html>
