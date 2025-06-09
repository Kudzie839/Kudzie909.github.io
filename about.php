<?php include 'includes/header.php'; ?>

<!-- About Page -->
<div class="container py-5">
    <h1 class="text-center mb-5">About Zimbabwe Bus Luggage Recovery</h1>
    
    <!-- Mission Section -->
    <div class="row align-items-center mb-5">
        <div class="col-lg-6 mb-4 mb-lg-0">
            <h2 class="mb-3">Our Mission</h2>
            <p>Zimbabwe Bus Luggage Recovery was established in 2020 with a simple mission: to help passengers recover their lost luggage when traveling by bus across Zimbabwe.</p>
            <p>We understand how stressful and inconvenient it can be to lose your belongings while traveling. Our centralized system connects all major bus companies in Zimbabwe, creating a comprehensive network that maximizes the chances of recovering lost items.</p>
            <p>Our dedicated team works tirelessly to reunite passengers with their lost luggage, providing peace of mind and excellent customer service throughout the process.</p>
        </div>
        <div class="col-lg-6">
            <div class="rounded overflow-hidden shadow-lg">
                <img src="logo.jpg" alt="Zimbabwe Bus Terminal" class="img-fluid w-100">
            </div>
        </div>
    </div>
    
    <!-- How It Works Section -->
    <div class="mb-5">
        <h2 class="text-center mb-4">How It Works</h2>
        
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100 text-center">
                    <div class="card-body p-4">
                        <div class="rounded-circle bg-light-green d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 80px; height: 80px;">
                            <span class="h3 fw-bold text-success mb-0">1</span>
                        </div>
                        <h3 class="h5 mb-3">Report Lost Item</h3>
                        <p class="text-muted">Fill out our detailed form with information about your lost luggage and the bus journey during which it was lost.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card h-100 text-center">
                    <div class="card-body p-4">
                        <div class="rounded-circle bg-light-green d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 80px; height: 80px;">
                            <span class="h3 fw-bold text-success mb-0">2</span>
                        </div>
                        <h3 class="h5 mb-3">We Search For You</h3>
                        <p class="text-muted">Our team coordinates with bus companies and terminals across Zimbabwe to locate your lost items.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card h-100 text-center">
                    <div class="card-body p-4">
                        <div class="rounded-circle bg-light-green d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 80px; height: 80px;">
                            <span class="h3 fw-bold text-success mb-0">3</span>
                        </div>
                        <h3 class="h5 mb-3">Recover Your Luggage</h3>
                        <p class="text-muted">Once found, we'll notify you and arrange for you to collect your luggage or have it delivered to you.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Partner Bus Companies -->
    <div class="mb-5">
        <h2 class="text-center mb-4">Our Partner Bus Companies</h2>
        
        <div class="row row-cols-2 row-cols-md-4 g-3">
            <?php
            $bus_companies = get_bus_companies($conn);
            
            foreach ($bus_companies as $company) {
                echo '
                <div class="col">
                    <div class="card h-100 border-success border-opacity-25">
                        <div class="card-body d-flex align-items-center justify-content-center">
                            <span class="fw-medium">' . htmlspecialchars($company['name']) . '</span>
                        </div>
                    </div>
                </div>';
            }
            ?>
        </div>
    </div>
    
    <!-- Benefits Section -->
    <div class="row align-items-center mb-5">
        <div class="col-lg-6 order-lg-2 mb-4 mb-lg-0">
            <h2 class="mb-3">Why Choose Our Service</h2>
            
            <div class="mb-3">
                <div class="d-flex align-items-start mb-2">
                    <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                    <p class="mb-0">Nationwide network covering all major bus routes</p>
                </div>
                
                <div class="d-flex align-items-start mb-2">
                    <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                    <p class="mb-0">Partnerships with all major bus companies</p>
                </div>
                
                <div class="d-flex align-items-start mb-2">
                    <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                    <p class="mb-0">Centralized database of lost and found items</p>
                </div>
                
                <div class="d-flex align-items-start mb-2">
                    <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                    <p class="mb-0">Quick and efficient recovery process</p>
                </div>
                
                <div class="d-flex align-items-start mb-2">
                    <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                    <p class="mb-0">Dedicated customer support team</p>
                </div>
                
                <div class="d-flex align-items-start mb-2">
                    <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                    <p class="mb-0">Secure item verification process</p>
                </div>
            </div>
        </div>
        <div class="col-lg-6 order-lg-1">
            <div class="rounded overflow-hidden shadow-lg">
                <img src="logo1.jpg" alt="Zimbabwe Bus Service" class="img-fluid w-100">
            </div>
        </div>
    </div>
    
    <!-- Call to Action -->
    <div class="bg-success text-white p-5 rounded-3 text-center">
        <h2 class="mb-3">Lost Your Luggage?</h2>
        <p class="lead mb-4">Don't worry! Our team is ready to help you recover your lost items. Report your lost luggage now or contact our support team for assistance.</p>
        <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
            <a href="report.php" class="btn btn-warning btn-lg">Report Lost Luggage</a>
            <a href="contact.php" class="btn btn-outline-light btn-lg">Contact Support</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>