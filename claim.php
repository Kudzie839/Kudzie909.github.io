<?php
include 'includes/header.php';

// Check if item ID is provided
if (!isset($_GET['id'])) {
    header('Location: search.php');
    exit;
}

$item_id = (int)$_GET['id'];

// Get item details
$sql = "SELECT f.*, b.name as bus_company 
        FROM found_items f 
        JOIN bus_companies b ON f.bus_company_id = b.id 
        WHERE f.id = ? AND f.status = 'Found'";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $item_id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();
$stmt->close();

// Redirect if item not found or already claimed
if (!$item) {
    header('Location: search.php');
    exit;
}

$error_message = '';
$success_message = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $contact_number = sanitize_input($_POST['contact_number'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $address = sanitize_input($_POST['address'] ?? '');
    
    // Validate required fields
    if (empty($full_name) || empty($contact_number) || empty($email) || empty($address)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } else {
        // Handle photo upload
        $photo_path = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
            $file_type = $_FILES['photo']['type'];
            
            if (!in_array($file_type, $allowed_types)) {
                $error_message = "Only JPG, JPEG, and PNG files are allowed for photo.";
            } else {
                $upload_dir = 'uploads/claims/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                $photo_path = $upload_dir . uniqid('claim_photo_') . '.' . $file_extension;
                
                if (!move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path)) {
                    $error_message = "Failed to upload photo. Please try again.";
                }
            }
        } else {
            $error_message = "Please upload your photo.";
        }
        
        if (empty($error_message)) {
            // Update found item status and add claim details
            $sql = "UPDATE found_items SET 
                    status = 'Claimed',
                    claimed_by = ?,
                    claimed_date = CURRENT_TIMESTAMP,
                    claimer_contact = ?,
                    claimer_email = ?,
                    claimer_address = ?,
                    claimer_photo = ?
                    WHERE id = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssssi', $full_name, $contact_number, $email, $address, $photo_path, $item_id);
            
            if ($stmt->execute()) {
                $success_message = "Claim submitted successfully. Our team will contact you for verification.";
            } else {
                $error_message = "Failed to submit claim. Please try again.";
            }
            $stmt->close();
        }
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg">
                <div class="card-header bg-success text-white">
                    <h2 class="h4 mb-0">Claim Item</h2>
                </div>
                <div class="card-body">
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success">
                            <?php echo $success_message; ?>
                            <div class="mt-3">
                                <a href="search.php" class="btn btn-success">Back to Search</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="mb-4">
                            <h3 class="h5">Item Details:</h3>
                            <p class="mb-1"><strong>Description:</strong> <?php echo htmlspecialchars($item['description']); ?></p>
                            <p class="mb-1"><strong>Bus Company:</strong> <?php echo htmlspecialchars($item['bus_company']); ?></p>
                            <p class="mb-1"><strong>Found Location:</strong> <?php echo htmlspecialchars($item['location']); ?></p>
                            <p class="mb-0"><strong>Date Found:</strong> <?php echo format_date($item['date_found']); ?></p>
                        </div>
                        
                        <form method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="contact_number" class="form-label">Contact Number</label>
                                <input type="tel" class="form-control" id="contact_number" name="contact_number" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label">House Address</label>
                                <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                            </div>
                            
                            <div class="mb-4">
                                <label for="photo" class="form-label">Your Photo</label>
                                <input type="file" class="form-control" id="photo" name="photo" accept="image/jpeg,image/png,image/jpg" required>
                                <div class="form-text">Please upload a clear photo of yourself for verification purposes.</div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check-circle me-2"></i>Submit Claim
                                </button>
                                <a href="search.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Search
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