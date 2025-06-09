<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="hero" style="background-image: 'url('assets/images/hero-background.jpg');">
    <div class="container hero-content text-center">
        <h1 class="display-4 fw-bold mb-4">Zimbabwe Bus Luggage Recovery System</h1>
        <p class="lead mb-5">Lost your luggage while traveling by bus in Zimbabwe? We're here to help you recover your belongings.</p>
        <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
            <a href="report.php" class="btn btn-warning btn-lg">Report Lost Luggage</a>
            <a href="search.php" class="btn btn-outline-light btn-lg">Search Lost Items</a>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="py-5">
    <div class="container">

        <h2 class="text-center mb-5">Our Services</h2>
        
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100 text-center">
                    <div class="card-body p-4">
                        <div class="card-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h3 class="card-title h5 mb-3">Report Lost Items</h3>
                        <p class="card-text mb-4">Fill out our detailed form to report your lost luggage. Provide as much information as possible to help us locate your items.</p>
                        <a href="report.php" class="btn btn-sm btn-outline-success">Report Now <i class="fas fa-arrow-right ms-1"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card h-100 text-center">
                    <div class="card-body p-4">
                        <div class="card-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3 class="card-title h5 mb-3">Search Database</h3>
                        <p class="card-text mb-4">Search our database of found items to see if your luggage has been recovered and is waiting for you to claim.</p>
                        <a href="search.php" class="btn btn-sm btn-outline-success">Search Now <i class="fas fa-arrow-right ms-1"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card h-100 text-center">
                    <div class="card-body p-4">
                        <div class="card-icon">
                            <i class="fas fa-bus"></i>
                        </div>
                        <h3 class="card-title h5 mb-3">Bus Company Network</h3>
                        <p class="card-text mb-4">We work with all major bus companies across Zimbabwe to ensure a comprehensive lost luggage recovery system.</p>
                        <a href="about.php" class="btn btn-sm btn-outline-success">Learn More <i class="fas fa-arrow-right ms-1"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="py-5 bg-success text-white">
    <div class="container">
        <h2 class="text-center mb-5">Our Impact</h2>
        
        <div class="row">
            <div class="col-6 col-md-3 mb-4">
                <div class="stat-item">
                    <div class="stat-number">1,200+</div>
                    <div class="stat-label">Items Recovered</div>
                </div>
            </div>
            
            <div class="col-6 col-md-3 mb-4">
                <div class="stat-item">
                    <div class="stat-number">35+</div>
                    <div class="stat-label">Bus Companies</div>
                </div>
            </div>
            
            <div class="col-6 col-md-3 mb-4">
                <div class="stat-item">
                    <div class="stat-number">95%</div>
                    <div class="stat-label">Success Rate</div>
                </div>
            </div>
            
            <div class="col-6 col-md-3 mb-4">
                <div class="stat-item">
                    <div class="stat-number">24/7</div>
                    <div class="stat-label">Support</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Recent Recoveries -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Recent Recoveries</h2>
        
        <div class="row">
            <?php
            $recent_items = get_recent_found_items($conn, 3);
            
            if (empty($recent_items)) {
                echo '<div class="col-12 text-center"><p>No recent recoveries to display.</p></div>';
            } else {
                foreach ($recent_items as $item) {
                    echo '
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">' . htmlspecialchars($item['description']) . '</h5>
                                <p class="card-text">
                                    <strong>Found on:</strong> ' . format_date($item['date_found']) . '<br>
                                    <strong>Bus Company:</strong> ' . htmlspecialchars($item['bus_company']) . '<br>
                                    <strong>Route:</strong> ' . htmlspecialchars($item['route']) . '<br>
                                    <strong>Location:</strong> ' . htmlspecialchars($item['location']) . '
                                </p>
                                <div class="mt-3">
                                    ' . get_status_badge($item['status']) . '
                                </div>
                            </div>
                        </div>
                    </div>';
                }
            }
            ?>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5 text-center">
    <div class="container">
        <h2 class="mb-3">Need Help Recovering Your Luggage?</h2>
        <p class="lead mb-4">Our team is ready to assist you in recovering your lost items. Contact us today or visit our office in Harare.</p>
        <a href="contact.php" class="btn btn-success btn-lg">
            <i class="fas fa-phone me-2"></i> Contact Us
        </a>
    </div>
</section>

<?php include 'includes/footer.php'; ?>