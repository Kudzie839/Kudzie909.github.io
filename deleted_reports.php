<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

// Handle report restoration
if (isset($_POST['restore']) && isset($_POST['report_id'])) {
    $report_id = intval($_POST['report_id']);
    
    // Begin transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Get the deleted report
        $stmt = $conn->prepare("SELECT * FROM deleted_reports WHERE id = ?");
        $stmt->bind_param("i", $report_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($report = $result->fetch_assoc()) {
            // Insert back into lost_items table
            $stmt = $conn->prepare("INSERT INTO lost_items (id, report_date, full_name, email, phone, bus_company_id, route_traveled, travel_date, item_description, identifying_features, ticket_photo, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssissssss", 
                $report['original_id'],
                $report['report_date'],
                $report['full_name'],
                $report['email'],
                $report['phone'],
                $report['bus_company_id'],
                $report['route_traveled'],
                $report['travel_date'],
                $report['item_description'],
                $report['identifying_features'],
                $report['ticket_photo'],
                $report['status']
            );
            $stmt->execute();
            
            // Delete from deleted_reports
            $stmt = $conn->prepare("DELETE FROM deleted_reports WHERE id = ?");
            $stmt->bind_param("i", $report_id);
            $stmt->execute();
            
            mysqli_commit($conn);
            $_SESSION['success_msg'] = "Report has been successfully restored.";
        } else {
            throw new Exception("Report not found");
        }
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['error_msg'] = "Error restoring report: " . $e->getMessage();
    }
    
    header('Location: deleted_reports.php');
    exit();
}

// Get all deleted reports
$query = "SELECT dr.*, bc.name as bus_company_name, u.full_name as deleted_by_name 
          FROM deleted_reports dr 
          LEFT JOIN bus_companies bc ON dr.bus_company_id = bc.id 
          LEFT JOIN users u ON dr.deleted_by = u.id 
          ORDER BY dr.deleted_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deleted Reports - Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1.5rem;
        }
        .status-badge {
            font-size: 0.875rem;
            padding: 0.35em 0.65em;
        }
        .status-Pending { background-color: #ffc107; color: #000; }
        .status-Found { background-color: #198754; color: #fff; }
        .status-Not-Found { background-color: #dc3545; color: #fff; }
        .status-Claimed { background-color: #0dcaf0; color: #000; }
        .btn-action {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            margin: 0 0.125rem;
        }
        .details-row {
            border-bottom: 1px solid #dee2e6;
            padding: 0.75rem 0;
        }
        .details-row:last-child {
            border-bottom: none;
        }
        .details-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.25rem;
        }
        .modal-backdrop {
            opacity: 0.5;
        }
        .modal-content {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .table th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }
        .table td {
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Deleted Reports</h2>
            <a href="reports.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i>Back to Reports
            </a>
        </div>
        
        <?php if (isset($_SESSION['success_msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['success_msg'];
                unset($_SESSION['success_msg']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_msg'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['error_msg'];
                unset($_SESSION['error_msg']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Report Date</th>
                                <th>Full Name</th>
                                <th>Bus Company</th>
                                <th>Route</th>
                                <th>Status</th>
                                <th>Deleted By</th>
                                <th>Deleted At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($result) == 0): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4">No deleted reports found.</td>
                                </tr>
                            <?php else: ?>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['original_id']); ?></td>
                                        <td><?php echo date('Y-m-d', strtotime($row['report_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['bus_company_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['route_traveled']); ?></td>
                                        <td>
                                            <span class="badge status-<?php echo str_replace(' ', '-', $row['status']); ?>">
                                                <?php echo $row['status']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['deleted_by_name']); ?></td>
                                        <td><?php echo date('Y-m-d H:i', strtotime($row['deleted_at'])); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-info btn-action" data-bs-toggle="modal" data-bs-target="#detailsModal<?php echo $row['id']; ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <form action="" method="POST" class="d-inline">
                                                <input type="hidden" name="report_id" value="<?php echo $row['id']; ?>">
                                                <button type="submit" name="restore" class="btn btn-success btn-action" onclick="return confirm('Are you sure you want to restore this report?')">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    
                                    <!-- Details Modal -->
                                    <div class="modal fade" id="detailsModal<?php echo $row['id']; ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Report Details #<?php echo $row['original_id']; ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row details-row">
                                                        <div class="col-md-6">
                                                            <div class="details-label">Full Name</div>
                                                            <div><?php echo htmlspecialchars($row['full_name']); ?></div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="details-label">Contact Information</div>
                                                            <div>
                                                                <i class="fas fa-envelope me-1"></i> <?php echo htmlspecialchars($row['email']); ?><br>
                                                                <i class="fas fa-phone me-1"></i> <?php echo htmlspecialchars($row['phone']); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row details-row">
                                                        <div class="col-md-6">
                                                            <div class="details-label">Bus Company</div>
                                                            <div><?php echo htmlspecialchars($row['bus_company_name']); ?></div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="details-label">Route</div>
                                                            <div><?php echo htmlspecialchars($row['route_traveled']); ?></div>
                                                        </div>
                                                    </div>
                                                    <div class="row details-row">
                                                        <div class="col-md-6">
                                                            <div class="details-label">Travel Date</div>
                                                            <div><?php echo date('Y-m-d', strtotime($row['travel_date'])); ?></div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="details-label">Status</div>
                                                            <div>
                                                                <span class="badge status-<?php echo str_replace(' ', '-', $row['status']); ?>">
                                                                    <?php echo $row['status']; ?>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row details-row">
                                                        <div class="col-12">
                                                            <div class="details-label">Item Description</div>
                                                            <div><?php echo nl2br(htmlspecialchars($row['item_description'])); ?></div>
                                                        </div>
                                                    </div>
                                                    <div class="row details-row">
                                                        <div class="col-12">
                                                            <div class="details-label">Identifying Features</div>
                                                            <div><?php echo nl2br(htmlspecialchars($row['identifying_features'])); ?></div>
                                                        </div>
                                                    </div>
                                                    <?php if ($row['ticket_photo']): ?>
                                                        <div class="row details-row">
                                                            <div class="col-12">
                                                                <div class="details-label">Ticket Photo</div>
                                                                <div>
                                                                    <a href="../<?php echo htmlspecialchars($row['ticket_photo']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                        <i class="fas fa-image me-1"></i> View Ticket
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <form action="" method="POST" class="d-inline">
                                                        <input type="hidden" name="report_id" value="<?php echo $row['id']; ?>">
                                                        <button type="submit" name="restore" class="btn btn-success">
                                                            <i class="fas fa-undo me-1"></i> Restore Report
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize all modals
        var modals = document.querySelectorAll('.modal');
        modals.forEach(function(modal) {
            var myModal = new bootstrap.Modal(modal);
        });

        // Auto-hide alerts after 5 seconds
        var alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
    });

    // Function to confirm restore action
    function confirmRestore(id) {
        return confirm('Are you sure you want to restore this report? It will be moved back to active reports.');
    }
    </script>
</body>
</html> 