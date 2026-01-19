<?php
include('../config.php');
session_start();

header('Content-Type: application/json');

// 1. Authorization Check
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'customer') {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

$customer_id = $_SESSION['user_id'];
$product_id  = (int)($_POST['product_id'] ?? 0);
$action      = $_POST['action'] ?? '';
$quantity    = (int)($_POST['quantity'] ?? 1);

// 2. Basic Validation
if (!$product_id || !in_array($action, ['add', 'remove', 'update_qty'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// 3. Stock Check (needed for 'add' and 'update_qty')
if ($action === 'add' || $action === 'update_qty') {
    $res = $conn->query("SELECT stock FROM products WHERE id = $product_id");
    $product = $res->fetch_assoc();
    if (!$product || $product['stock'] < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Insufficient stock available!']);
        exit;
    }
}

// 4. Action Logic
$success = false;
$message = "";

switch ($action) {
    case 'add':
        // Check if item already exists in cart
        $check = $conn->prepare("SELECT id FROM cart WHERE customer_id = ? AND product_id = ?");
        $check->bind_param("ii", $customer_id, $product_id);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            // If exists, increment quantity
            $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE customer_id = ? AND product_id = ?");
            $stmt->bind_param("ii", $customer_id, $product_id);
            $message = "Cart updated (Quantity +1)";
        } else {
            // If new, insert with default quantity 1
            $stmt = $conn->prepare("INSERT INTO cart (customer_id, product_id, quantity) VALUES (?, ?, 1)");
            $stmt->bind_param("ii", $customer_id, $product_id);
            $message = "Added to cart!";
        }
        $success = $stmt->execute();
        break;

    case 'update_qty':
        // Direct update for the quantity selector on cart page
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE customer_id = ? AND product_id = ?");
        $stmt->bind_param("iii", $quantity, $customer_id, $product_id);
        $success = $stmt->execute();
        $message = $success ? "Quantity updated" : "Failed to update quantity";
        break;

    case 'remove':
        $stmt = $conn->prepare("DELETE FROM cart WHERE customer_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $customer_id, $product_id);
        $success = $stmt->execute();
        $message = $success ? "Removed from cart" : "Item not found";
        break;
}

// 5. Final Response
echo json_encode([
    'success' => $success,
    'message' => $message
]);