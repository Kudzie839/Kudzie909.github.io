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

// Handle add/edit form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = sanitize_input($_POST['description']);
    $bus_company_id = (int)$_POST['bus_company_id'];
    $route = sanitize_input($_POST['route']);
    $location = sanitize_input($_POST['location']);
    $status = sanitize_input($_POST['status']);
    $claimed_by = isset($_POST['claimed_by']) ? sanitize_input($_POST['claimed_by']) : null;
    
    // Validate required fields
    if (empty($description) || empty($bus_company_id) || empty($route) || empty($location) || empty($status)) {
        $error_message = "All required fields must be filled out.";
    } else {
        if (isset($_POST['id'])) {
            // Update existing item
            $id = (int)$_POST['id'];
            
            $sql = "UPDATE found_items SET description = ?, bus_company_id = ?, route = ?, location = ?, status = ?, claimed_by = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sissssi", $description, $bus_company_id, $route, $location, $status, $claimed_by, $id);
            
            if ($stmt->execute()) {
                $success_message = "Item updated successfully.";
            } else {
                $error_message = "Error updating item: " . $stmt->error;
            }
        } else {
            // Add new item
            $sql = "INSERT INTO found_items (description, bus_company_id, route, location, status, claimed_by) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sissss", $description, $bus_company_id, $route, $location, $status, $claimed_by);
            
            if ($stmt->execute()) {
                $success_message = "Item added successfully.";
            } else {
                $error_message = "Error adding item: " . $stmt->error;
            }
        }
        
        $stmt->close();
    }
}

// Handle status update
if (isset($_GET['action']) && $_GET['action'] === 'update_status' && isset($_GET['id']) && isset($_GET['status'])) {
    $id = (int)$_GET['id'];
    $status = sanitize_input($_GET['status']);
    
    $valid_statuses = ['Found', 'Claimed'];
    
    if (in_array($status, $valid_statuses)) {
        $claimed_by = null;
        if ($status === 'Claimed' && isset($_GET['claimed_by'])) {
            $claimed_by = sanitize_input($_GET['claimed_by']);
        }
        
        $sql = "UPDATE found_items SET status = ?, claimed_by = ?, claimed_date = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $status, $claimed_by, $id);
        
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
    
    $sql = "DELETE FROM found_items WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $success_message = "Item deleted successfully.";
    } else {
        $error_message = "Error deleting item: " . $stmt->error;
    }
    
    $stmt->close();
}

// Get item for editing
$edit_item = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    $sql = "SELECT * FROM found_items WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $edit_item = $result->fetch_assoc();
    }
    
    $stmt->close();
}

// Get search parameters
$search_term = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';
$bus_company_id = isset($_GET['bus_company']) ? (int)$_GET['bus_company'] : null;

// Prepare SQL query
$sql = "SELECT f.*, b.name as bus_company 
        FROM found_items f 
        JOIN bus_companies b ON f.bus_company_id = b.id 
        WHERE 1=1";

$params = array();
$types = "";

if (!empty($search_term)) {
    $search_term = "%$search_term%";
    $sql .= " AND (f.description LIKE ? OR f.location LIKE ? OR f.claimed_by LIKE ?)";
    $types .= "sss";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

if (!empty($status_filter)) {
    $sql .= " AND f.status = ?";
    $types .= "s";
    $params[] = $status_filter;
}

if (!empty($bus_company_id)) {
    $sql .= " AND f.bus_company_id = ?";
    $types .= "i";
    $params[] = $bus_company_id;
}

$sql .= " ORDER BY f.date_found DESC";

// Execute query
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$items = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get bus companies for dropdown
$bus_companies = get_bus_companies($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Found Items - Admin Dashboard</title>
    
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
                        <a class="nav-link" href="reports.php">Lost Reports</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="found_items.php">Found Items</a>
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
            <h1>Found Items Management</h1>
            <div>
                <?php if (isset($_GET['action']) && ($_GET['action'] === 'add' || $_GET['action'] === 'edit')): ?>
                    <a href="found_items.php" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-times me-2"></i> Cancel
                    </a>
                <?php else: ?>
                    <a href="found_items.php?action=add" class="btn btn-success me-2">
                        <i class="fas fa-plus me-2"></i> Add New Item
                    </a>
                <?php endif; ?>
                <a href="dashboard.php" class="btn btn-outline-success">
                    <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
                </a>
            </div>
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

        <?php if (isset($_GET['action']) && ($_GET['action'] === 'add' || $_GET['action'] === 'edit')): ?>
            <!-- Add/Edit Form -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><?php echo isset($_GET['action']) && $_GET['action'] === 'edit' ? 'Edit' : 'Add New'; ?> Found Item</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                        <?php if (isset($_GET['action']) && $_GET['action'] === 'edit'): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_item['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" required><?php echo isset($edit_item) ? htmlspecialchars($edit_item['description']) : ''; ?></textarea>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="bus_company_id" class="form-label">Bus Company</label>
                                <select class="form-select" id="bus_company_id" name="bus_company_id" required>
                                    <option value="">Select Bus Company</option>
                                    <?php foreach ($bus_companies as $company): ?>
                                        <option value="<?php echo $company['id']; ?>" <?php echo isset($edit_item) && $edit_item['bus_company_id'] == $company['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($company['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="route" class="form-label">Route</label>
                                <input type="text" class="form-control" id="route" name="route" value="<?php echo isset($edit_item) ? htmlspecialchars($edit_item['route']) : ''; ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="location" class="form-label">Location Found</label>
                                <input type="text" class="form-control" id="location" name="location" value="<?php echo isset($edit_item) ? htmlspecialchars($edit_item['location']) : ''; ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="Found" <?php echo isset($edit_item) && $edit_item['status'] === 'Found' ? 'selected' : ''; ?>>Found</option>
                                    <option value="Claimed" <?php echo isset($edit_item) && $edit_item['status'] === 'Claimed' ? 'selected' : ''; ?>>Claimed</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="claimed_by" class="form-label">Claimed By</label>
                                <input type="text" class="form-control" id="claimed_by" name="claimed_by" 
                                       value="<?php echo (isset($edit_item['claimed_by']) && $edit_item['claimed_by'] !== null && $edit_item['claimed_by'] !== '') ? htmlspecialchars($edit_item['claimed_by']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i> Save Item
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <!-- Search Form -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="get" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="row g-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="search" placeholder="Search items..." value="<?php echo htmlspecialchars($search_term); ?>">
                        </div>
                        
                        <div class="col-md-3">
                            <select class="form-select" name="status">
                                <option value="">All Statuses</option>
                                <option value="Found" <?php echo $status_filter === 'Found' ? 'selected' : ''; ?>>Found</option>
                                <option value="Claimed" <?php echo $status_filter === 'Claimed' ? 'selected' : ''; ?>>Claimed</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <select class="form-select" name="bus_company">
                                <option value="">All Bus Companies</option>
                                <?php foreach ($bus_companies as $company): ?>
                                    <option value="<?php echo $company['id']; ?>" <?php echo $bus_company_id === $company['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($company['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-search me-2"></i> Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Items Table -->
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Date Found</th>
                                    <th>Description</th>
                                    <th>Bus Company</th>
                                    <th>Route</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Claimed By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($items)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-3">No items found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($items as $item): ?>
                                        <tr>
                                            <td><?php echo $item['id']; ?></td>
                                            <td><?php echo format_date($item['date_found']); ?></td>
                                            <td class="text-truncate" style="max-width: 200px;"><?php echo htmlspecialchars($item['description']); ?></td>
                                            <td><?php echo htmlspecialchars($item['bus_company']); ?></td>
                                            <td><?php echo htmlspecialchars($item['route']); ?></td>
                                            <td><?php echo htmlspecialchars($item['location']); ?></td>
                                            <td><?php echo get_status_badge($item['status']); ?></td>
                                            <td><?php echo (isset($item['claimed_by']) && $item['claimed_by'] !== null && $item['claimed_by'] !== '') ? htmlspecialchars($item['claimed_by']) : '-'; ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="found_items.php?action=edit&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?php echo $item['id']; ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
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
        <?php endif; ?>
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
    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this item?')) {
            window.location.href = `found_items.php?action=delete&id=${id}`;
        }
    }
    </script>
</body>
</html>