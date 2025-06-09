<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

$success_message = '';
$error_message = '';

// Handle status update
if (isset($_GET['action']) && $_GET['action'] === 'update_status' && isset($_GET['id']) && isset($_GET['status'])) {
    $id = (int)$_GET['id'];
    $status = sanitize_input($_GET['status']);
    
    $valid_statuses = ['Pending', 'Found', 'Not Found', 'Claimed'];
    
    if (in_array($status, $valid_statuses)) {
        $sql = "UPDATE lost_items SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $id);
        
        if ($stmt->execute()) {
            $success_message = "Status updated successfully.";
        } else {
            $error_message = "Error updating status: " . $stmt->error;
        }
        
        $stmt->close();
    }
}

// Handle delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Begin transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Get the report data before deleting
        $stmt = $conn->prepare("SELECT * FROM lost_items WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($report = $result->fetch_assoc()) {
            // Insert into deleted_reports
            $stmt = $conn->prepare("INSERT INTO deleted_reports (original_id, report_date, full_name, email, phone, bus_company_id, route_traveled, travel_date, item_description, identifying_features, ticket_photo, status, deleted_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            // Store the values in variables to ensure proper binding
            $original_id = $report['id'];
            $report_date = $report['report_date'];
            $full_name = $report['full_name'];
            $email = $report['email'];
            $phone = $report['phone'];
            $bus_company_id = $report['bus_company_id'];
            $route_traveled = $report['route_traveled'];
            $travel_date = $report['travel_date'];
            $item_description = $report['item_description'];
            $identifying_features = $report['identifying_features'];
            $ticket_photo = $report['ticket_photo'];
            $status = $report['status'];
            $admin_id = $_SESSION['admin_id'];
            
            // Debug output
            error_log("Binding parameters for deleted_reports insert:");
            error_log("original_id: " . $original_id);
            error_log("report_date: " . $report_date);
            error_log("full_name: " . $full_name);
            error_log("email: " . $email);
            error_log("phone: " . $phone);
            error_log("bus_company_id: " . $bus_company_id);
            error_log("route_traveled: " . $route_traveled);
            error_log("travel_date: " . $travel_date);
            error_log("item_description: " . $item_description);
            error_log("identifying_features: " . $identifying_features);
            error_log("ticket_photo: " . ($ticket_photo ?? 'NULL'));
            error_log("status: " . $status);
            error_log("admin_id: " . $admin_id);
            
            // Bind parameters with correct types
            if (!$stmt->bind_param("issssissssssi", 
                $original_id,
                $report_date,
                $full_name,
                $email,
                $phone,
                $bus_company_id,
                $route_traveled,
                $travel_date,
                $item_description,
                $identifying_features,
                $ticket_photo,
                $status,
                $admin_id
            )) {
                throw new Exception("Binding parameters failed: " . $stmt->error);
            }
            
            // Execute the statement
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            // Delete from lost_items
            $stmt = $conn->prepare("DELETE FROM lost_items WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            
            mysqli_commit($conn);
            $success_message = "Report moved to deletion history.";
        } else {
            throw new Exception("Report not found");
        }
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error_message = "Error deleting report: " . $e->getMessage();
    }
}

// Get search parameters
$search_term = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';
$bus_company_id = isset($_GET['bus_company']) ? (int)$_GET['bus_company'] : null;

// Prepare SQL query
$sql = "SELECT l.*, l.route_traveled as route, l.travel_date as date_lost, b.name as bus_company 
        FROM lost_items l 
        JOIN bus_companies b ON l.bus_company_id = b.id 
        WHERE 1=1";

$params = array();
$types = "";

if (!empty($search_term)) {
    $search_term = "%$search_term%";
    $sql .= " AND (l.full_name LIKE ? OR l.item_description LIKE ? OR l.email LIKE ?)";
    $types .= "sss";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

if (!empty($status_filter)) {
    $sql .= " AND l.status = ?";
    $types .= "s";
    $params[] = $status_filter;
}

if (!empty($bus_company_id)) {
    $sql .= " AND l.bus_company_id = ?";
    $types .= "i";
    $params[] = $bus_company_id;
}

$sql .= " ORDER BY l.report_date DESC";

// Execute query
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$reports = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get bus companies for filter
$bus_companies = get_bus_companies($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost Reports - Admin Dashboard</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
    .modal {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        z-index: 1050;
        width: 90%;
        max-width: 800px;
        max-height: 90vh;
        overflow-y: auto;
    }

    .modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1040;
    }

    .modal-title {
        font-size: 1.25rem;
        font-weight: 500;
        margin-bottom: 20px;
        padding-right: 30px;
    }

    .modal-close {
        position: absolute;
        top: 20px;
        right: 20px;
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        padding: 0;
        margin: 0;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0.5;
    }

    .modal-close:hover {
        opacity: 1;
    }

    .modal-body {
        margin-bottom: 20px;
    }

    .close-btn {
        background: #6c757d;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 4px;
        cursor: pointer;
    }

    .close-btn:hover {
        background: #5a6268;
    }

    h6 {
        font-weight: 600;
        margin-bottom: 10px;
    }

    .badge.not-found {
        background-color: #dc3545;
        color: white;
    }

    .badge.found {
        background-color: #28a745;
        color: white;
    }

    .badge.pending {
        background-color: #ffc107;
        color: black;
    }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="../index.php">
                <img src="../logo.jpg" alt="Logo" width="40" height="40" class="rounded-circle me-2">
                <span>Admin Dashboard</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="reports.php">Lost Reports</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="found_items.php">Found Items</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="deleted_reports.php">Deleted Reports</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">View Website</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Lost Luggage Reports</h1>
            <a href="dashboard.php" class="btn btn-outline-success">
                <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
            </a>
        </div>
        
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- Search and Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="get" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" placeholder="Search by name, email, or description" value="<?php echo htmlspecialchars($search_term); ?>">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="Pending" <?php echo $status_filter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="Found" <?php echo $status_filter === 'Found' ? 'selected' : ''; ?>>Found</option>
                            <option value="Not Found" <?php echo $status_filter === 'Not Found' ? 'selected' : ''; ?>>Not Found</option>
                            <option value="Claimed" <?php echo $status_filter === 'Claimed' ? 'selected' : ''; ?>>Claimed</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="bus_company" class="form-label">Bus Company</label>
                        <select class="form-select" id="bus_company" name="bus_company">
                            <option value="">All Companies</option>
                            <?php foreach ($bus_companies as $company): ?>
                                <option value="<?php echo $company['id']; ?>" <?php echo $bus_company_id == $company['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($company['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-search me-2"></i> Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Reports Table -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Date</th>
                                <th>Name</th>
                                <th>Contact</th>
                                <th>Bus Company</th>
                                <th>Item Description</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($reports)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">No reports found matching your criteria.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($reports as $report): ?>
                                    <tr>
                                        <td><?php echo $report['id']; ?></td>
                                        <td><?php echo format_date($report['report_date']); ?></td>
                                        <td><?php echo htmlspecialchars($report['full_name']); ?></td>
                                        <td>
                                            <small>
                                                <i class="fas fa-envelope me-1"></i> <?php echo htmlspecialchars($report['email']); ?><br>
                                                <i class="fas fa-phone me-1"></i> <?php echo htmlspecialchars($report['phone']); ?>
                                            </small>
                                        </td>
                                        <td><?php echo htmlspecialchars($report['bus_company']); ?></td>
                                        <td class="text-truncate" style="max-width: 200px;"><?php echo htmlspecialchars($report['item_description']); ?></td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm <?php echo get_status_class($report['status']); ?> dropdown-toggle" type="button" id="statusDropdown<?php echo $report['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <?php echo $report['status']; ?>
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="statusDropdown<?php echo $report['id']; ?>">
                                                    <li><a class="dropdown-item" href="reports.php?action=update_status&id=<?php echo $report['id']; ?>&status=Pending">Pending</a></li>
                                                    <li><a class="dropdown-item" href="reports.php?action=update_status&id=<?php echo $report['id']; ?>&status=Found">Found</a></li>
                                                    <li><a class="dropdown-item" href="reports.php?action=update_status&id=<?php echo $report['id']; ?>&status=Not Found">Not Found</a></li>
                                                    <li><a class="dropdown-item" href="reports.php?action=update_status&id=<?php echo $report['id']; ?>&status=Claimed">Claimed</a></li>
                                                </ul>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#reportModal<?php echo $report['id']; ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?php echo $report['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                            
                                            <!-- Report Details Modal -->
                                            <div class="modal" id="reportModal<?php echo $report['id']; ?>" style="display: none;">
                                                <button type="button" class="modal-close" onclick="closeModal(<?php echo $report['id']; ?>)">×</button>
                                                <h5 class="modal-title">Report Details #<?php echo $report['id']; ?></h5>
                                                
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <h6>Reporter Information</h6>
                                                            <p class="mb-0">
                                                                <strong>Name:</strong> <?php echo htmlspecialchars($report['full_name']); ?><br>
                                                                <strong>Email:</strong> <?php echo htmlspecialchars($report['email']); ?><br>
                                                                <strong>Phone:</strong> <?php echo htmlspecialchars($report['phone']); ?><br>
                                                                <strong>Report Date:</strong> <?php echo format_date($report['report_date']); ?>
                                                            </p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6>Item Information</h6>
                                                            <p class="mb-0">
                                                                <strong>Bus Company:</strong> <?php echo htmlspecialchars($report['bus_company']); ?><br>
                                                                <strong>Route:</strong> <?php echo !empty($report['route']) ? htmlspecialchars($report['route']) : $report['route_traveled']; ?><br>
                                                                <strong>Date Lost:</strong> <?php echo !empty($report['date_lost']) ? format_date($report['date_lost']) : format_date($report['travel_date']); ?><br>
                                                                <strong>Status:</strong> <span class="badge <?php echo strtolower(str_replace(' ', '-', $report['status'])); ?>"><?php echo htmlspecialchars($report['status']); ?></span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="row mt-4">
                                                        <div class="col-12">
                                                            <h6>Item Description</h6>
                                                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($report['item_description'])); ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="text-end">
                                                    <button type="button" class="close-btn" onclick="closeModal(<?php echo $report['id']; ?>)">Close</button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> Zimbabwe Bus Luggage Recovery. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">Admin Dashboard</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize all modals
        var modals = document.querySelectorAll('.modal');
        modals.forEach(function(modal) {
            var myModal = new bootstrap.Modal(modal);
            
            // Fix for close button
            modal.querySelector('.btn-close').addEventListener('click', function() {
                myModal.hide();
            });
            
            modal.querySelector('.btn-secondary').addEventListener('click', function() {
                myModal.hide();
            });
            
            // Fix for backdrop click
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    myModal.hide();
                }
            });
        });
    });

    function confirmDelete(id) {
        if (confirm('Are you sure you want to move this report to deletion history? You can restore it later from the Deleted Reports page.')) {
            window.location.href = `reports.php?action=delete&id=${id}`;
        }
    }

    function get_status_class(status) {
        switch (status) {
            case 'Pending':
                return 'btn-warning';
            case 'Found':
                return 'btn-success';
            case 'Not Found':
                return 'btn-danger';
            case 'Claimed':
                return 'btn-info';
            default:
                return 'btn-secondary';
        }
    }

    // Function to show modal
    function showModal(id) {
        document.getElementById('reportModal' + id).style.display = 'block';
        document.body.insertAdjacentHTML('beforeend', '<div class="modal-backdrop"></div>');
        document.body.style.overflow = 'hidden';
    }

    // Function to close modal
    function closeModal(id) {
        document.getElementById('reportModal' + id).style.display = 'none';
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.remove();
        }
        document.body.style.overflow = '';
    }

    // Update the eye icon button to use the new function
    document.querySelectorAll('[data-bs-toggle="modal"]').forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const modalId = button.getAttribute('data-bs-target').replace('#reportModal', '');
            showModal(modalId);
        });
    });

    // Close modal when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal-backdrop')) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                const modalId = modal.id.replace('reportModal', '');
                closeModal(modalId);
            });
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (modal.style.display === 'block') {
                    const modalId = modal.id.replace('reportModal', '');
                    closeModal(modalId);
                }
            });
        }
    });
    </script>
</body>
</html>