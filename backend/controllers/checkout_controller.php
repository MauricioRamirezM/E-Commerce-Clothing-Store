<?php
require_once('../../config/db.php');
require_once('../models/checkout_model.php');
require_once('../models/address_model.php');
require_once('../models/users_admin_model.php');
require_once('../models/orders_model.php');
require_once('../models/payment_country_model.php');
require_once('../models/product_model.php');


header('Content-Type: application/json');

session_start();

$user = new User($connection);
$checkout = new CheckoutService($connection);
$address_model = new Address($connection);
$payment_model = new PaymentMethod($connection);
$orders_model = new UserOrder($connection);
$product = new ProductItem($connection);
$product_variation = new ProductVariation($connection);
$input = file_get_contents('php://input');
$data = json_decode($input, true);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data['available'])) {

    $cart = $data['cart'] ?? null;
    foreach ($cart as $item) {

        $product_variation->set_product_id($item['product_id']);
        $product_variation->set_size_name($item['size']);
        $available_stock = $product_variation->check_available_size();
        if ($available_stock < $item['quantity']) {
            echo json_encode([
                'status' => 'error',
                'data' => 'Product ' . $item['name'] . ' (size ' . $item['size'] . ') is out of stock. Only ' . $available_stock . ' left.',
                'available_stock' => $available_stock
            ]);
            exit;
        }else{
            echo json_encode([
            'status'=>'success',
            'data' => 'product_available'
              ]);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data['checkout'])) {

    $address = $data['address'] ?? null;
    $cart = $data['cart'] ?? null;
    $payment = $data['payment_method'] ?? null;


    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            'status' => 'error',
            'data' => 'User not logged in'
        ]);
        exit;
    }
    $user_id = $_SESSION['user_id'];


    if (!$address || !$cart || !is_array($cart) || count($cart) === 0) {
        echo json_encode([
            'status' => 'error',
            'data' => 'Missing address or cart data'
        ]);
        exit;
    }



    $address_model->set_user_id($user_id);
    $address_model->set_street($address['address_street']);
    $address_model->set_ext_number($address['address_ext_num']);
    $address_model->set_int_number($address['address_int_num']);
    $address_model->set_cp($address['address_cp']);
    $address_model->set_city($address['address_city']);
    $address_model->set_country($address['address_country']);
    $address_model->set_state($address['address_state']);
    $existing_addres = $address_model->find_existing_address();
    if ($existing_addres) {
        $address_id = $existing_addres;
    } else {
        $address_id = $address_model->create_address();
    }
    if (!$address_id) {
        echo json_encode([
            'status' => 'error',
            'data' => 'Failed to save address'
        ]);
        exit;
    }



    $method_id = $payment['method_id'] ?? 4;
    if ($method_id === 4) {
        $total_order  = $checkout->calculateTotal($cart);
        $user->set_user_id($user_id);
        $user_points = $user->get_points_user();
        if ($total_order > $user_points) {
            echo json_encode([
                'status' => 'error',
                'data' => 'Not enogth Missing points.'
            ]);
            exit;
        }

        $payment_model->set_user_id($user_id);
        $payment_model->set_payment_type_id($method_id);
        $payment_model->set_card_number($payment['payment_name']);
        if ($method_id === 4) {
            $payment_model->set_expiry_date(null);
        }
        if (!$payment_model->create_method()) {
            echo json_encode([
                'status' => 'error',
                'data' => 'Something went wrong with the payment. Try again later.'
            ]);
            exit;
        }


        $last_method_id = $connection->lastInsertId();
        $checkout->setUser($user_id);
        $checkout->setAddress($address_id);
        $checkout->setPaymentMethod($last_method_id);
        $success = $checkout->processCheckout($cart);

        if ($success) {
            if ($method_id === 4) {
                $user->subtract_points($total_order);
            }
            $user_info = $user->get_user_by_id($user_id);
            $orders_model->set_user_id($user_id);
            $user_orders = $orders_model->get_orders_by_user();
            foreach ($user_orders as &$order) {
                $order_id = $order['order_number'];
                $order['products'] = $product->get_products_by_order_id($order_id);
            }
            $addresses = $address_model->get_addresses_by_userid();
            echo json_encode([
                'status' => 'success',
                'message' => 'Order placed successfully',
                'data' => [
                    'user_info' => $user_info,
                    'orders' => $user_orders,
                    'addresses' => $addresses
                ]
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'data' => 'Order failed'
            ]);
        }
        exit;
    }
}
