<?php
include 'includes/header.php';

$success_message = '';
$error_message = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form data
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $subject = sanitize_input($_POST['subject']);
    $message = sanitize_input($_POST['message']);
    
    // Validate required fields
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error_message = "All fields are required.";
    } else {
        // Insert into database
        $sql = "INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $name, $email, $subject, $message);
        
        if ($stmt->execute()) {
            $success_message = "Your message has been sent successfully. We will get back to you soon.";
            // Reset form data
            $name = $email = $subject = $message = '';
        } else {
            $error_message = "Error: " . $stmt->error;
        }
        
        $stmt->close();
    }
}
?>

<!-- Contact Page -->
<div class="container py-5">
    <h1 class="text-center mb-2">Contact Us</h1>
    <p class="text-center text-muted mb-5">Have questions or need assistance with lost luggage? Our team is here to help. Reach out to us using any of the methods below.</p>
    
    <div class="row mb-5">
        <div class="col-md-4 mb-4">
            <div class="card h-100 text-center">
                <div class="card-body p-4">
                    <div class="rounded-circle bg-light-green d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 60px; height: 60px;">
                        <i class="fas fa-phone text-success"></i>
                    </div>
                    <h3 class="h5 mb-2">Phone</h3>
                    <p class="text-muted mb-2">Call our support team</p>
                    <a href="tel:+263771234567" class="text-success fw-medium">+263 77 123 4567</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100 text-center">
                <div class="card-body p-4">
                    <div class="rounded-circle bg-light-green d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 60px; height: 60px;">
                        <i class="fas fa-envelope text-success"></i>
                    </div>
                    <h3 class="h5 mb-2">Email</h3>
                    <p class="text-muted mb-2">Send us an email</p>
                    <a href="mailto:info@zbluggage.co.zw" class="text-success fw-medium">info@zbluggage.co.zw</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100 text-center">
                <div class="card-body p-4">
                    <div class="rounded-circle bg-light-green d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 60px; height: 60px;">
                        <i class="fas fa-map-marker-alt text-success"></i>
                    </div>
                    <h3 class="h5 mb-2">Office</h3>
                    <p class="text-muted mb-2">Visit our main office</p>
                    <address class="mb-0 text-success fw-medium">
                        123 Samora Machel Avenue<br>
                        Harare, Zimbabwe
                    </address>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-6 mb-5 mb-lg-0">
            <h2 class="mb-4">Send Us a Message</h2>
            
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
            
            <form id="contactForm" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="mb-3">
                    <label for="name" class="form-label">Your Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>">
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                </div>
                
                <div class="mb-3">
                    <label for="subject" class="form-label">Subject</label>
                    <input type="text" class="form-control" id="subject" name="subject" value="<?php echo isset($subject) ? htmlspecialchars($subject) : ''; ?>">
                </div>
                
                <div class="mb-4">
                    <label for="message" class="form-label">Message</label>
                    <textarea class="form-control" id="message" name="message" rows="5"><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-success btn-lg w-100">Send Message</button>
            </form>
        </div>
        
        <div class="col-lg-6">
            <h2 class="mb-4">Office Locations</h2>
            
            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="h5 mb-3">Harare (Main Office)</h3>
                    <address class="mb-3 text-muted">
                        123 Samora Machel Avenue<br>
                        Harare, Zimbabwe
                    </address>
                    <p class="mb-1"><strong>Phone:</strong> +263 77 123 4567</p>
                    <p class="mb-1"><strong>Email:</strong> harare@zbluggage.co.zw</p>
                    <p class="mb-0"><strong>Hours:</strong> Mon-Fri: 8am-5pm, Sat: 9am-1pm</p>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="h5 mb-3">Bulawayo Office</h3>
                    <address class="mb-3 text-muted">
                        456 Joshua Nkomo Street<br>
                        Bulawayo, Zimbabwe
                    </address>
                    <p class="mb-1"><strong>Phone:</strong> +263 77 765 4321</p>
                    <p class="mb-1"><strong>Email:</strong> bulawayo@zbluggage.co.zw</p>
                    <p class="mb-0"><strong>Hours:</strong> Mon-Fri: 8am-5pm, Sat: 9am-1pm</p>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <h3 class="h5 mb-3">Victoria Falls Office</h3>
                    <address class="mb-3 text-muted">
                        789 Livingstone Way<br>
                        Victoria Falls, Zimbabwe
                    </address>
                    <p class="mb-1"><strong>Phone:</strong> +263 77 987 6543</p>
                    <p class="mb-1"><strong>Email:</strong> vicfalls@zbluggage.co.zw</p>
                    <p class="mb-0"><strong>Hours:</strong> Mon-Fri: 8am-5pm, Sat: 9am-1pm</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>