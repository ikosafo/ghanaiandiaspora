<?php require '../config.php';
if (!isset($_SESSION['admin_logged_in'])) { header("Location: login.php"); exit; }

$stmt = $pdo->query("SELECT * FROM registrations WHERE status = 'pending' ORDER BY submitted_at DESC");
$pending = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard</title>
    <style>
        body { font-family: Arial; }
        table { width:100%; border-collapse:collapse; margin:20px 0; }
        th, td { border:1px solid #ddd; padding:8px; text-align:left; }
        th { background:#f2f2f2; }
        .btn { padding:6px 12px; color:white; text-decoration:none; border-radius:4px; }
        .approve { background:#28a745; }
        .reject { background:#dc3545; }
    </style>
</head>
<body>
    <h1>Pending Registrations</h1>
    <a href="logout.php">Logout</a>
    <?php if (empty($pending)): ?>
        <p>No pending registrations.</p>
    <?php else: ?>
        <table>
            <tr><th>ID</th><th>Name</th><th>Email</th><th>Nationality</th><th>Submitted</th><th>Actions</th></tr>
            <?php foreach ($pending as $row): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['nationality']); ?></td>
                <td><?php echo $row['submitted_at']; ?></td>
                <td>
                    <a href="process.php?action=approve&id=<?php echo $row['id']; ?>" class="btn approve">Approve</a>
                    <form method="post" action="process.php" style="display:inline;">
                        <input type="hidden" name="action" value="reject">
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                        <input type="text" name="reason" placeholder="Reason for rejection" required style="width:150px; margin-left:5px;">
                        <button type="submit" class="btn reject">Reject</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>
</html>