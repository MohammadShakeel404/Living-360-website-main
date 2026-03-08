<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

$id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid service id']);
    exit;
}

$service = getServiceById($id);
if (!$service) {
    echo json_encode(['success' => false, 'message' => 'Service not found']);
    exit;
}

// Normalize fields and optional image if present
$response = [
    'success' => true,
    'data' => [
        'id' => (int)$service['id'],
        'title' => $service['title'],
        'description' => $service['description'],
        'icon' => isset($service['icon']) ? $service['icon'] : '',
        'image' => isset($service['image']) ? $service['image'] : null,
    ]
];

echo json_encode($response);
