<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Function to sanitize input data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Function to redirect if not logged in
function require_login() {
    if (!is_logged_in()) {
        header("Location: index.php");
        exit;
    }
}

// Function to get all bus companies
function get_bus_companies($conn) {
    $sql = "SELECT * FROM bus_companies ORDER BY name";
    $result = $conn->query($sql);
    
    $companies = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $companies[] = $row;
        }
    }
    
    return $companies;
}

// Function to get bus company name by ID
function get_bus_company_name($conn, $id) {
    $sql = "SELECT name FROM bus_companies WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['name'];
    }
    
    return "Unknown";
}

// Function to get status class
function get_status_class($status) {
    switch ($status) {
        case 'Pending':
            return 'bg-warning text-dark';
        case 'Found':
            return 'bg-success';
        case 'Not Found':
            return 'bg-danger';
        case 'Claimed':
            return 'bg-info';
        default:
            return 'bg-secondary';
    }
}

// Function to get status badge HTML
function get_status_badge($status) {
    $class = get_status_class($status);
    return '<span class="badge ' . $class . '">' . htmlspecialchars($status) . '</span>';
}

// Function to format date
function format_date($date) {
    return date("d/m/Y", strtotime($date));
}

// Function to get recent lost items
function get_recent_lost_items($conn, $limit = 5) {
    $sql = "SELECT l.*, b.name as bus_company 
            FROM lost_items l 
            JOIN bus_companies b ON l.bus_company_id = b.id 
            ORDER BY l.report_date DESC 
            LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $items = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
    }
    
    return $items;
}

// Function to get recent found items
function get_recent_found_items($conn, $limit = 5) {
    $sql = "SELECT f.*, b.name as bus_company 
            FROM found_items f 
            JOIN bus_companies b ON f.bus_company_id = b.id 
            ORDER BY f.date_found DESC 
            LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $items = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
    }
    
    return $items;
}

// Function to search lost items
function search_lost_items($conn, $search_term = '', $bus_company_id = null, $date_from = null) {
    $sql = "SELECT l.*, b.name as bus_company 
            FROM lost_items l 
            JOIN bus_companies b ON l.bus_company_id = b.id 
            WHERE 1=1";
    
    $params = array();
    $types = "";
    
    if (!empty($search_term)) {
        $search_term = "%$search_term%";
        $sql .= " AND (l.item_description LIKE ? OR l.full_name LIKE ?)";
        $types .= "ss";
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    if (!empty($bus_company_id)) {
        $sql .= " AND l.bus_company_id = ?";
        $types .= "i";
        $params[] = $bus_company_id;
    }
    
    if (!empty($date_from)) {
        $sql .= " AND l.travel_date >= ?";
        $types .= "s";
        $params[] = $date_from;
    }
    
    $sql .= " ORDER BY l.report_date DESC";
    
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $items = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
    }
    
    return $items;
}

// Function to search found items
function search_found_items($conn, $search_term = '', $bus_company_id = null, $status = null) {
    $sql = "SELECT f.*, b.name as bus_company 
            FROM found_items f 
            JOIN bus_companies b ON f.bus_company_id = b.id 
            WHERE 1=1";
    
    $params = array();
    $types = "";
    
    if (!empty($search_term)) {
        $search_term = "%$search_term%";
        $sql .= " AND (f.description LIKE ? OR f.location LIKE ?)";
        $types .= "ss";
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    if (!empty($bus_company_id)) {
        $sql .= " AND f.bus_company_id = ?";
        $types .= "i";
        $params[] = $bus_company_id;
    }
    
    if (!empty($status)) {
        $sql .= " AND f.status = ?";
        $types .= "s";
        $params[] = $status;
    }
    
    $sql .= " ORDER BY f.date_found DESC";
    
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $items = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
    }
    
    return $items;
}
?>