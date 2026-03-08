<?php
require_once '../includes/functions.php';

// Set header to return JSON
header('Content-Type: application/json');

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $project_type = isset($_POST['project_type']) ? sanitizeInput($_POST['project_type']) : '';
    $space_size = isset($_POST['space_size']) ? sanitizeInput($_POST['space_size']) : '';
    $services = isset($_POST['services']) ? $_POST['services'] : [];
    
    // Validate required fields
    if (empty($project_type) || empty($space_size) || empty($services)) {
        echo json_encode([
            'success' => false,
            'message' => 'Please fill in all required fields.'
        ]);
        exit;
    }
    
    // Base costs per square foot by project type
    $baseCosts = [
        'residential' => 75,
        'commercial' => 95,
        'hospitality' => 120,
        'other' => 85
    ];
    
    // Service multipliers
    $serviceMultipliers = [
        'space-planning' => 1.0,
        'concept-design' => 1.2,
        '3d-visualization' => 1.3,
        'material-selection' => 1.1,
        'furniture-design' => 1.4,
        'lighting-design' => 1.15,
        'project-management' => 1.25,
        'custom-furniture' => 1.5
    ];
    
    // Convert space size to numeric value
    $spaceSizeMap = [
        'less-1000' => 750,
        '1000-2000' => 1500,
        '2000-3000' => 2500,
        '3000-5000' => 4000,
        'more-5000' => 6000
    ];
    
    // Calculate base cost
    $baseCost = $baseCosts[$project_type] * $spaceSizeMap[$space_size];
    
    // Apply service multipliers
    $totalMultiplier = 1.0;
    foreach ($services as $service) {
        if (isset($serviceMultipliers[$service])) {
            $totalMultiplier += ($serviceMultipliers[$service] - 1.0);
        }
    }
    
    // Calculate total estimated cost
    $estimatedCost = $baseCost * $totalMultiplier;
    
    // Calculate range (±15%)
    $minCost = $estimatedCost * 0.85;
    $maxCost = $estimatedCost * 1.15;
    
    // Format the response
    echo json_encode([
        'success' => true,
        'min_cost' => number_format($minCost, 2),
        'max_cost' => number_format($maxCost, 2),
        'average_cost' => number_format($estimatedCost, 2),
        'message' => "Based on your selections, the estimated cost for your {$project_type} project is between $" . number_format($minCost, 2) . " and $" . number_format($maxCost, 2) . ". This is a rough estimate and the actual cost may vary based on specific requirements and materials chosen."
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
}
?>