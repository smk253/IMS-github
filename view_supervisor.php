<?php
session_start();
require_once 'db.php';

// Optional login check
/*
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}
*/

// Handle search/filter
$search = "";
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

// Fetch supervisors with filter
if ($search !== "") {
    $stmt = $conn->prepare("SELECT id, name, email FROM supervisors 
                            WHERE name LIKE ? OR email LIKE ? 
                            ORDER BY id ASC");
    $like = "%".$search."%";
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT id, name, email FROM supervisors ORDER BY id ASC";
    $result = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>View Supervisors</title>
<style>
  body {
    font-family: Arial, sans-serif;
    background: #f7f9ff;
    padding: 20px;
    margin: 0;
  }
  h2 {
    color: #0047AB;
    text-align: center;
    margin-bottom: 20px;
  }
  .search-container {
    max-width: 900px;
    margin: 0 auto 20px auto;
    text-align: right;
  }
  .search-container form {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    flex-wrap: wrap;
  }
  .search-box {
    padding: 8px 12px;
    width: 250px;
    max-width: 100%;
    border: 1px solid #ccc;
    border-radius: 6px;
  }
  .search-btn {
    padding: 8px 16px;
    background-color: #0047AB;
    border: none;
    border-radius: 6px;
    color: white;
    cursor: pointer;
    font-size: 14px;
  }
  .search-btn:hover {
    background-color: #003377;
  }

  .table-responsive {
    max-width: 900px;
    margin: auto;
    overflow-x: auto;   /* ✅ Horizontal scroll */
    -webkit-overflow-scrolling: touch;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    box-shadow: 0 0 8px rgba(0,0,0,0.1);
    border-radius: 8px;
    overflow: hidden;
    min-width: 600px; /* ✅ Prevent squishing on small screens */
  }
  th, td {
    padding: 12px 15px;
    border-bottom: 1px solid #ddd;
    text-align: left;
    font-size: 14px;
    white-space: nowrap; /* ✅ Prevent text wrap */
  }
  th {
    background-color: #0047AB;
    color: white;
  }
  tr:hover {
    background-color: #f1f8ff;
  }
  .no-data {
    text-align: center;
    padding: 20px;
    color: #666;
  }
  .back-button-container {
    max-width: 900px;
    margin: 20px auto 0 auto;
    text-align: center;
  }
  .back-button {
    background-color: #0047AB;
    color: white;
    padding: 10px 24px;
    border: none;
    border-radius: 6px;
    text-decoration: none;
    font-size: 16px;
    cursor: pointer;
    display: inline-block;
  }
  .back-button:hover {
    background-color: #003377;
  }
  .action-btn {
    background: none;
    border: none;
    cursor: pointer;
    padding: 5px;
    margin: 0 4px;
    font-size: 18px;
    vertical-align: middle;
  }
  .edit-btn {
    color: #007bff;
  }
  .edit-btn:hover {
    color: #0056b3;
  }
  .delete-btn {
    color: #dc3545;
  }
  .delete-btn:hover {
    color: #a71d2a;
  }
</style>
</head>
<body>

<h2>👨‍🏫 Supervisors List</h2>

<div class="search-container">
  <form method="get" action="">
    <input type="text" name="search" class="search-box" placeholder="Search by name or email" value="<?= htmlspecialchars($search) ?>" />
    <button type="submit" class="search-btn">Search</button>
    <?php if ($search !== ""): ?>
      <a href="view_supervisor.php" class="search-btn" style="background-color:#6c757d; text-align:center; display:inline-block;">Clear</a>
    <?php endif; ?>
  </form>
</div>

<?php if ($result && $result->num_rows > 0): ?>
  <div class="table-responsive">
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Email</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td>
              <a href="edit_supervisor.php?id=<?= $row['id'] ?>" class="action-btn edit-btn" title="Edit Supervisor">&#9998;</a>
              <a href="delete_supervisor.php?id=<?= $row['id'] ?>" class="action-btn delete-btn" title="Delete Supervisor" onclick="return confirm('Are you sure you want to delete this supervisor?');">&#10060;</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
<?php else: ?>
  <p class="no-data">No supervisors found.</p>
<?php endif; ?>

<div class="back-button-container">
  <a href="admin_pannel.php" class="back-button">Back</a>
</div>

</body>
</html>
