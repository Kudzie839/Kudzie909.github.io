<?php
include 'includes/header.php';

$search_term = '';
$bus_company_id = '';
$date_from = '';
$search_results = null;

// Get bus companies for dropdown
$bus_companies = get_bus_companies($conn);

// Process search form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $search_term = sanitize_input($_POST['searchTerm'] ?? '');
    $bus_company_id = !empty($_POST['busCompany']) ? (int)$_POST['busCompany'] : null;
    $date_from = sanitize_input($_POST['dateFrom'] ?? '');
    
    // Prepare SQL query
    $sql = "SELECT f.*, b.name as bus_company 
            FROM found_items f 
            JOIN bus_companies b ON f.bus_company_id = b.id 
            WHERE f.status = 'Found'";
    
    $params = array();
    $types = "";
    
    if (!empty($search_term)) {
        $search_term = "%$search_term%";
        $sql .= " AND f.description LIKE ?";
        $types .= "s";
        $params[] = $search_term;
    }
    
    if (!empty($bus_company_id)) {
        $sql .= " AND f.bus_company_id = ?";
        $types .= "i";
        $params[] = $bus_company_id;
    }
    
    if (!empty($date_from)) {
        $sql .= " AND DATE(f.date_found) >= ?";
        $types .= "s";
        $params[] = $date_from;
    }
    
    $sql .= " ORDER BY f.date_found DESC";
    
    // Execute query
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $search_results = array();
    while ($row = $result->fetch_assoc()) {
        $search_results[] = $row;
    }
    
    $stmt->close();
}
?>

<!-- Search Lost Items Page -->
<div class="bg-image py-5" style="background-image: url('assets/images/search-background.jpg');">
    <div class="container position-relative">
        <h1 class="text-center mb-2">Search Found Items</h1>
        <p class="text-center text-muted mb-5">Search our database of found items to locate your lost luggage.</p>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="card shadow-lg">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label for="searchTerm" class="form-label">Item Description</label>
                            <input type="text" class="form-control" id="searchTerm" name="searchTerm" placeholder="Enter item description (e.g. Yellow suitcase with black stripes)" value="<?php echo isset($_POST['searchTerm']) ? htmlspecialchars($_POST['searchTerm']) : ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="busCompany" class="form-label">Bus Company</label>
                            <select class="form-select" id="busCompany" name="busCompany">
                                <option value="">All Bus Companies</option>
                                <?php foreach ($bus_companies as $company): ?>
                                    <option value="<?php echo $company['id']; ?>" <?php echo (isset($_POST['busCompany']) && $_POST['busCompany'] == $company['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($company['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="dateFrom" class="form-label">Found After Date</label>
                            <input type="date" class="form-control" id="dateFrom" name="dateFrom" value="<?php echo isset($_POST['dateFrom']) ? htmlspecialchars($_POST['dateFrom']) : ''; ?>">
                        </div>
                        
                        <input type="hidden" name="status" value="Found">
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-success px-4">
                                <i class="fas fa-search me-2"></i> Search Items
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Loading Spinner (hidden by default) -->
<div id="loadingSpinner" class="text-center my-5 d-none">
    <div class="spinner-border text-success" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <p class="mt-2">Searching for items...</p>
</div>

<!-- Search Results -->
<div class="container py-5">
    <?php if (isset($search_results)): ?>
        <h2 class="mb-4">Search Results</h2>
        
        <?php if (empty($search_results)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                No items matching your search criteria were found. Please try different search terms or contact our support team for assistance.
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($search_results as $item): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 border-success border-opacity-25">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($item['description']); ?></h5>
                                <div class="mb-3">
                                    <span class="badge bg-success">Found</span>
                                </div>
                                <p class="card-text">
                                    <strong><i class="fas fa-bus me-2"></i>Bus Company:</strong><br>
                                    <?php echo htmlspecialchars($item['bus_company']); ?>
                                </p>
                                <p class="card-text">
                                    <strong><i class="fas fa-route me-2"></i>Route:</strong><br>
                                    <?php echo htmlspecialchars($item['route']); ?>
                                </p>
                                <p class="card-text">
                                    <strong><i class="fas fa-calendar me-2"></i>Date Found:</strong><br>
                                    <?php echo format_date($item['date_found']); ?>
                                </p>
                                <p class="card-text">
                                    <strong><i class="fas fa-map-marker-alt me-2"></i>Current Location:</strong><br>
                                    <?php echo htmlspecialchars($item['location']); ?>
                                </p>
                                <div class="mt-3">
                                    <a href="claim.php?id=<?php echo $item['id']; ?>" class="btn btn-success w-100">
                                        <i class="fas fa-hand-holding me-2"></i>Claim This Item
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>