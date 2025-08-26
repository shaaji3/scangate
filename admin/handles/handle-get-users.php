<?php
require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../repositories/UserRepository.php';

header('Content-Type: application/json');

// --- Auth Check ---
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION['user_role'] !== 'super_admin') {
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

try {
    $userRepo = new UserRepository($pdo);

    // DataTables sends its parameters in the request
    // The new repository method is designed to handle this structure
    $data = $userRepo->getUsersForDataTable($_REQUEST);

    echo json_encode($data);

} catch (Exception $e) {
    error_log("Get Users Error: " . $e->getMessage());
    // Return a DataTables-compatible error message
    echo json_encode([
        "draw"            => intval($_REQUEST['draw'] ?? 0),
        "recordsTotal"    => 0,
        "recordsFiltered" => 0,
        "data"            => [],
        "error"           => "An internal server error occurred."
    ]);
}
