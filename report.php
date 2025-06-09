<?php
include 'includes/header.php';

$error_message = '';
$success_message = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $bus_company_id = !empty($_POST['bus_company_id']) ? (int)$_POST['bus_company_id'] : null;
    $route_traveled = sanitize_input($_POST['route_traveled'] ?? '');
    $travel_date = sanitize_input($_POST['travel_date'] ?? '');
    $item_description = sanitize_input($_POST['item_description'] ?? '');
    $identifying_features = sanitize_input($_POST['identifying_features'] ?? '');
    
    // Validate required fields
    if (empty($full_name) || empty($email) || empty($phone) || empty($bus_company_id) || 
        empty($route_traveled) || empty($travel_date) || empty($item_description)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } else {
        // Handle ticket photo upload
        $ticket_photo_path = null;
        if (isset($_FILES['ticket_photo']) && $_FILES['ticket_photo']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
            $file_type = $_FILES['ticket_photo']['type'];
            
            if (!in_array($file_type, $allowed_types)) {
                $error_message = "Only JPG, JPEG, and PNG files are allowed for ticket photo.";
            } else {
                $upload_dir = 'uploads/tickets/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_extension = pathinfo($_FILES['ticket_photo']['name'], PATHINFO_EXTENSION);
                $ticket_photo_path = $upload_dir . uniqid('ticket_') . '.' . $file_extension;
                
                if (!move_uploaded_file($_FILES['ticket_photo']['tmp_name'], $ticket_photo_path)) {
                    $error_message = "Failed to upload ticket photo. Please try again.";
                }
            }
        }
        
        if (empty($error_message)) {
            // Insert lost item report
            $sql = "INSERT INTO lost_items (
                    full_name, email, phone, bus_company_id, route_traveled, 
                    travel_date, item_description, identifying_features, 
                    ticket_photo, status, report_date
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', CURRENT_TIMESTAMP)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssssssss', 
                $full_name, $email, $phone, $bus_company_id, $route_traveled,
                $travel_date, $item_description, $identifying_features, $ticket_photo_path
            );
            
            if ($stmt->execute()) {
                $success_message = "Your lost item has been reported successfully. We will contact you if we find any matching items.";
            } else {
                $error_message = "Failed to submit report. Please try again.";
            }
            $stmt->close();
        }
    }
}

// Get bus companies for dropdown
$bus_companies = get_bus_companies($conn);
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg">
                <div class="card-header bg-success text-white">
                    <h2 class="h4 mb-0">Report Lost Item</h2>
                </div>
                <div class="card-body">
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success">
                            <?php echo $success_message; ?>
                            <div class="mt-3">
                                <a href="search.php" class="btn btn-success">Search Found Items</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <form method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="bus_company_id" class="form-label">Bus Company</label>
                                <select class="form-select" id="bus_company_id" name="bus_company_id" required>
                                    <option value="">Select Bus Company</option>
                                    <?php foreach ($bus_companies as $company): ?>
                                        <option value="<?php echo $company['id']; ?>">
                                            <?php echo htmlspecialchars($company['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="route_traveled" class="form-label">Route Traveled</label>
                                <input type="text" class="form-control" id="route_traveled" name="route_traveled" placeholder="e.g. Harare to Bulawayo" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="travel_date" class="form-label">Travel Date</label>
                                <input type="date" class="form-control" id="travel_date" name="travel_date" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="item_description" class="form-label">Item Description</label>
                                <textarea class="form-control" id="item_description" name="item_description" rows="3" placeholder="Please provide a detailed description of your lost item" required></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="identifying_features" class="form-label">Identifying Features</label>
                                <textarea class="form-control" id="identifying_features" name="identifying_features" rows="3" placeholder="Any unique marks, labels, or contents that can help identify your item"></textarea>
                            </div>
                            
                            <div class="mb-4">
                                <label for="ticket_photo" class="form-label">Upload Ticket Photo</label>
                                <input type="file" class="form-control" id="ticket_photo" name="ticket_photo" accept="image/jpeg,image/png,image/jpg">
                                <div class="form-text">Upload a clear photo of your bus ticket to help verify your travel details.</div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Report
                                </button>
                                <a href="index.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Home
                                </a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>