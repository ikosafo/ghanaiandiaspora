<?php
//session_start();
require '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$stmt = $pdo->query("
    SELECT * FROM registrations 
    ORDER BY submitted_at DESC
");
$registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pagination
$perPage = 10;
$total = count($registrations);
$pages = ceil($total / $perPage);
$currentPage = max(1, isset($_GET['page']) ? (int)$_GET['page'] : 1);
$offset = ($currentPage - 1) * $perPage;
$paginated = array_slice($registrations, $offset, $perPage);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Admin Dashboard | Ghana Diaspora</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Feather Icons -->
    <script src="https://unpkg.com/feather-icons"></script>

    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --bg: #f8fafc;
            --card: #ffffff;
            --text: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --shadow-sm: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
            --shadow-md: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
        }

        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            padding: 32px 20px;
        }

        .container { max-width: 1400px; margin: 0 auto; }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }

        .header h1 { font-size: 1.875rem; font-weight: 700; }

        .btn-logout {
            padding: 10px 20px;
            background: var(--danger);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
        }

        .btn-logout:hover { background: #dc2626; }

        .controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .search-input {
            padding: 12px 16px;
            width: 360px;
            max-width: 100%;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 1rem;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(37,99,235,0.15);
        }

        .table-container {
            background: var(--card);
            border-radius: 12px;
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }

        table { width: 100%; border-collapse: collapse; }

        th, td {
            padding: 16px 20px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        th {
            background: #f1f5f9;
            font-weight: 600;
            color: var(--text-muted);
            font-size: 0.8125rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        tr:hover { background: #f8fafc; }

        .status-badge {
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 0.8125rem;
            font-weight: 600;
        }

        .status-pending  { background: #fef3c7; color: #92400e; }
        .status-approved { background: #d1fae5; color: #065f46; }
        .status-rejected { background: #fee2e2; color: #991b1b; }

        .actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border: none;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            color: white;
        }

        .btn-view   { background: #6b7280; }
        .btn-approve { background: var(--success); }
        .btn-reject  { background: var(--danger); }

        .btn-view:hover   { background: #4b5563; }
        .btn-approve:hover { background: #059669; }
        .btn-reject:hover  { background: #dc2626; }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-top: 32px;
        }

        .page-link {
            padding: 10px 16px;
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
        }

        .page-link:hover { background: #f1f5f9; color: var(--primary); border-color: var(--primary); }
        .page-link.active { background: var(--primary); color: white; border-color: var(--primary); }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 20px;
        }

        .modal-content {
            background: var(--card);
            border-radius: 12px;
            max-width: 600px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: var(--shadow-md);
            animation: fadeIn 0.3s;
        }

        .modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 { font-size: 1.25rem; font-weight: 600; }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-muted);
        }

        .modal-body {
            padding: 24px;
        }

        .detail-row {
            display: flex;
            margin-bottom: 16px;
            gap: 16px;
        }

        .detail-label {
            font-weight: 600;
            color: var(--text-muted);
            min-width: 180px;
        }

        .detail-value {
            flex: 1;
        }

        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        @media (max-width: 768px) {
            .controls { flex-direction: column; }
            .search-input { width: 100%; }
            .table-container { overflow-x: auto; }
            .detail-row { flex-direction: column; gap: 4px; }
            .detail-label { min-width: auto; }
        }

        .reject-form {
            transition: opacity 0.2s ease;
        }

        .reject-form input:focus {
            outline: none;
            border-color: var(--danger);
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <img src="./assets/images/logo.png" alt="Ghana Diaspora Logo" style="height: 50px; margin-bottom: 16px;">
        <h1>Registrations Dashboard</h1>
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>

    <div class="controls">
        <input type="text" id="searchInput" class="search-input" placeholder="Search by name, email or nationality...">
    </div>

    <div class="table-container">
        <table id="registrationsTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Nationality</th>
                    <th>Submitted</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($paginated)): ?>
                    <tr><td colspan="6" style="text-align:center; padding:40px; color:var(--text-muted);">No registrations found.</td></tr>
                <?php else: ?>
                    <?php foreach ($paginated as $row): ?>
                    <tr data-search="<?php echo strtolower($row['full_name'].' '.$row['email'].' '.$row['nationality']); ?>" 
                        data-fullname="<?php echo htmlspecialchars($row['full_name']); ?>"
                        data-email="<?php echo htmlspecialchars($row['email']); ?>"
                        data-gender="<?php echo htmlspecialchars($row['gender'] ?? '—'); ?>"
                        data-dob="<?php echo htmlspecialchars($row['date_of_birth'] ?? '—'); ?>"
                        data-whatsapp="<?php echo htmlspecialchars($row['whatsapp_number'] ?? '—'); ?>"
                        data-passport="<?php echo htmlspecialchars($row['ghana_passport_number'] ?? '—'); ?>"
                        data-nationality="<?php echo htmlspecialchars($row['nationality'] ?? '—'); ?>"
                        data-address-diaspora="<?php echo htmlspecialchars($row['current_address_diaspora'] ?? '—'); ?>"
                        data-address-ghana="<?php echo htmlspecialchars($row['current_address_ghana'] ?? '—'); ?>"
                        data-emergency-name="<?php echo htmlspecialchars($row['emergency_contact_person'] ?? '—'); ?>"
                        data-emergency-phone="<?php echo htmlspecialchars($row['emergency_phone_number'] ?? '—'); ?>"
                        data-submitted="<?php echo date('d M Y H:i', strtotime($row['submitted_at'])); ?>"
                        data-status="<?php echo strtolower($row['status'] ?? 'pending'); ?>">
                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['nationality']); ?></td>
                        <td><?php echo date('d M Y H:i', strtotime($row['submitted_at'])); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($row['status'] ?? 'pending'); ?>">
                                <?php echo ucfirst($row['status'] ?? 'Pending'); ?>
                            </span>
                        </td>
                        <td class="actions">
                            <button class="btn-action btn-view" 
                                    onclick="showDetails(this)" 
                                    data-id="<?php echo $row['id']; ?>">
                                <i data-feather="eye"></i> View
                            </button>

                            <?php if (strtolower($row['status'] ?? 'pending') !== 'approved'): ?>
                                <a href="process.php?action=approve&id=<?php echo $row['id']; ?>" 
                                class="btn-action btn-approve">
                                    <i data-feather="check"></i> Approve
                                </a>
                            <?php endif; ?>

                            <?php if (strtolower($row['status'] ?? 'pending') !== 'rejected'): ?>
                                <!-- Reject trigger button + hidden form -->
                                <div class="reject-wrapper" style="display: inline-flex; align-items: center; gap: 12px;">
                                    <button type="button" class="btn-action btn-reject show-reject-form"
                                            onclick="toggleRejectForm(this)">
                                        <i data-feather="x"></i> Reject
                                    </button>

                                    <form method="post" action="process.php" class="reject-form" style="display: none; align-items: center; gap: 8px;">
                                        <input type="hidden" name="action" value="reject">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <input type="text" name="reason" placeholder="Reason for rejection" required 
                                            style="padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; font-size: 0.875rem; width: 220px;">
                                        <button type="submit" class="btn-action btn-reject">
                                            <i data-feather="send"></i> Confirm
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pages > 1): ?>
    <div class="pagination">
        <?php if ($currentPage > 1): ?>
            <a href="?page=<?php echo $currentPage - 1; ?>" class="page-link">Previous</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $pages; $i++): ?>
            <a href="?page=<?php echo $i; ?>" class="page-link <?php echo $i === $currentPage ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>

        <?php if ($currentPage < $pages): ?>
            <a href="?page=<?php echo $currentPage + 1; ?>" class="page-link">Next</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Modal -->
<div id="detailModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Registration Details</h2>
            <button class="modal-close" onclick="closeModal()">×</button>
        </div>
        <div class="modal-body" id="modalBody">
            <!-- Details will be filled here by JS -->
        </div>
    </div>
</div>

<script>
    feather.replace();

    // Search filter
    document.getElementById('searchInput').addEventListener('input', function(e) {
        const filter = e.target.value.toLowerCase();
        document.querySelectorAll('#registrationsTable tbody tr').forEach(row => {
            const searchable = row.getAttribute('data-search') || '';
            row.style.display = searchable.includes(filter) ? '' : 'none';
        });
    });

    function toggleRejectForm(button) {
        const wrapper = button.closest('.reject-wrapper');
        const form = wrapper.querySelector('.reject-form');
        const triggerBtn = wrapper.querySelector('.show-reject-form');

        if (form.style.display === 'none' || form.style.display === '') {
            form.style.display = 'inline-flex';
            triggerBtn.style.display = 'none'; // hide the original Reject button
            form.querySelector('input[name="reason"]').focus();
        }
    }

    // Show details in modal
    function showDetails(btn) {
        const row = btn.closest('tr');
        const modalBody = document.getElementById('modalBody');

        const fields = [
            { label: 'Full Name',          value: row.dataset.fullname },
            { label: 'Email',              value: row.dataset.email },
            { label: 'Gender',             value: row.dataset.gender },
            { label: 'Date of Birth',      value: row.dataset.dob },
            { label: 'WhatsApp Number',    value: row.dataset.whatsapp },
            { label: 'Ghana Passport No.', value: row.dataset.passport },
            { label: 'Nationality',        value: row.dataset.nationality },
            { label: 'Address in Diaspora',value: row.dataset.addressDiaspora.replace(/\n/g, '<br>') },
            { label: 'Address in Ghana',   value: row.dataset.addressGhana.replace(/\n/g, '<br>') },
            { label: 'Emergency Contact',  value: row.dataset.emergencyName },
            { label: 'Emergency Phone',    value: row.dataset.emergencyPhone },
            { label: 'Submitted',          value: row.dataset.submitted },
            { label: 'Current Status',     value: `<span class="status-badge status-${row.dataset.status}">${row.dataset.status.charAt(0).toUpperCase() + row.dataset.status.slice(1)}</span>` }
        ];

        let html = '';
        fields.forEach(f => {
            html += `
                <div class="detail-row">
                    <div class="detail-label">${f.label}:</div>
                    <div class="detail-value">${f.value || '—'}</div>
                </div>
            `;
        });

        modalBody.innerHTML = html;
        document.getElementById('detailModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('detailModal').style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('detailModal');
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    }
</script>

</body>
</html>