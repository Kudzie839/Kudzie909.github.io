<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

// Get statistics
$total_lost_items_query = "SELECT COUNT(*) as count FROM lost_items";
$total_found_items_query = "SELECT COUNT(*) as count FROM found_items";
$total_claimed_items_query = "SELECT COUNT(*) as count FROM found_items WHERE status = 'Claimed'";
$total_pending_items_query = "SELECT COUNT(*) as count FROM lost_items WHERE status = 'Pending'";

$total_lost_items = $conn->query($total_lost_items_query)->fetch_assoc()['count'];
$total_found_items = $conn->query($total_found_items_query)->fetch_assoc()['count'];
$total_claimed_items = $conn->query($total_claimed_items_query)->fetch_assoc()['count'];
$total_pending_items = $conn->query($total_pending_items_query)->fetch_assoc()['count'];

// Get recent lost items
$recent_lost_items = get_recent_lost_items($conn, 5);

// Get recent found items
$recent_found_items = get_recent_found_items($conn, 5);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Zimbabwe Bus Luggage Recovery</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="../index.php">
                <img src="../assets/images/logo.jpg" alt="Logo" width="40" height="40" class="rounded-circle me-2">
                <span>Admin Dashboard</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">Lost Reports</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="found_items.php">Found Items</a>
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
            <h1>Admin Dashboard</h1>
            <p class="mb-0">Welcome, <strong><?php echo htmlspecialchars($_SESSION['admin_fullname']); ?></strong></p>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row mb-5">
            <div class="col-md-3 mb-4">
                <div class="card dashboard-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Total Lost Items</h6>
                                <h2 class="mb-0"><?php echo $total_lost_items; ?></h2>
                            </div>
                            <div class="dashboard-icon">
                                <i class="fas fa-suitcase"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="card dashboard-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Total Found Items</h6>
                                <h2 class="mb-0"><?php echo $total_found_items; ?></h2>
                            </div>
                            <div class="dashboard-icon">
                                <i class="fas fa-search"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="card dashboard-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Claimed Items</h6>
                                <h2 class="mb-0"><?php echo $total_claimed_items; ?></h2>
                            </div>
                            <div class="dashboard-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="card dashboard-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Pending Reports</h6>
                                <h2 class="mb-0"><?php echo $total_pending_items; ?></h2>
                            </div>
                            <div class="dashboard-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3 mb-md-0">
                                <a href="reports.php" class="btn btn-outline-success w-100 py-3">
                                    <i class="fas fa-list me-2"></i> View All Reports
                                </a>
                            </div>
                            <div class="col-md-3 mb-3 mb-md-0">
                                <a href="found_items.php" class="btn btn-outline-success w-100 py-3">
                                    <i class="fas fa-search me-2"></i> Manage Found Items
                                </a>
                            </div>
                            <div class="col-md-3 mb-3 mb-md-0">
                                <a href="found_items.php?action=add" class="btn btn-outline-success w-100 py-3">
                                    <i class="fas fa-plus me-2"></i> Add Found Item
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="../index.php" class="btn btn-outline-success w-100 py-3">
                                    <i class="fas fa-globe me-2"></i> View Website
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Lost Reports</h5>
                        <a href="reports.php" class="btn btn-sm btn-outline-success">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Date</th>
                                        <th>Name</th>
                                        <th>Item</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recent_lost_items)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-3">No recent reports found.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recent_lost_items as $item): ?>
                                            <tr>
                                                <td><?php echo $item['id']; ?></td>
                                                <td><?php echo format_date($item['report_date']); ?></td>
                                                <td><?php echo htmlspecialchars($item['full_name']); ?></td>
                                                <td class="text-truncate" style="max-width: 150px;"><?php echo htmlspecialchars($item['item_description']); ?></td>
                                                <td><?php echo get_status_badge($item['status']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Found Items</h5>
                        <a href="found_items.php" class="btn btn-sm btn-outline-success">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Date Found</th>
                                        <th>Description</th>
                                        <th>Location</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recent_found_items)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-3">No recent found items.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recent_found_items as $item): ?>
                                            <tr>
                                                <td><?php echo $item['id']; ?></td>
                                                <td><?php echo format_date($item['date_found']); ?></td>
                                                <td class="text-truncate" style="max-width: 150px;"><?php echo htmlspecialchars($item['description']); ?></td>
                                                <td><?php echo htmlspecialchars($item['location']); ?></td>
                                                <td><?php echo get_status_badge($item['status']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
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
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Custom JS -->
    <script src="../assets/js/script.js"></script>
</body>
</html>