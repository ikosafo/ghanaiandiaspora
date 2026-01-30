<?php
// membership/admin/export.php
require '../config.php';

$stmt = $pdo->query("SELECT * FROM registrations ORDER BY submitted_at DESC");
$registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=registrations_export.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Full Name', 'Email', 'Membership ID', 'Nationality', 'Submitted At', 'Status', 'Rejection Reason']);

foreach ($registrations as $row) {
    fputcsv($output, [
        $row['id'],
        $row['full_name'],
        $row['email'],
        $row['membership_id'] ?? '',
        $row['nationality'],
        $row['submitted_at'],
        $row['status'] ?? 'pending',
        $row['rejection_reason'] ?? ''
    ]);
}

fclose($output);
exit;