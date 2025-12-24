<?php
require_once('../../config/db.php');
require_once('../models/users_admin_model.php');
require_once('../models/orders_model.php');
require_once('../models/address_model.php');
require_once('../models/product_model.php');
$user = new User($connection);
$admin = new Admin($connection);
$orders = new UserOrder($connection);
$address = new Address($connection);
$product = new ProductItem($connection);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';

    if ($action === 'register') {
        $initial_points = 2000;
        $first_name = trim($data['register_first_name'] ?? '');
        $last_name = trim($data['register_last_name'] ?? '');
        $phone = trim($data['register_phone'] ?? '');
        $email = trim($data['register_email'] ?? '');
        $birthday = trim($data['register_birthday'] ?? '');
        $password = $data['register_password'] ?? '';
        $confirm_password = $data['register_confirm_password'] ?? '';

        // Basic validation
        if (!$first_name || !$last_name || !$phone || !$email || !$birthday ||!$password || !$confirm_password) {
            echo json_encode([
                'status' => 'error',
                'message' => 'All fields are required.'
            ]);
            exit;
        }
        if ($password !== $confirm_password) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Passwords do not match.'
            ]);
            exit;
        }

        $user->set_first_name($first_name);
        $user->set_last_name($last_name);
        $user->set_email($email);
        $user->set_birthday($birthday);
        $user->set_phone($phone);
        $user->set_password($password);
        $user->set_points($initial_points);

        if ($user->verify_email()) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Email already registered.'
            ]);
            exit;
        }
        if(!$user->verify_age()){
            echo json_encode([
                'status' => 'error',
                'message' =>'You must be at least 18 years old to register.'
            ]);
            exit;
        }


        if ($user->create_user()) {
            session_start();
            $user_info = $user->get_user_by_email();
            $_SESSION['user_id'] = $user_info['user_id'];
            $_SESSION['user_email'] = $user_info['email'];
            $user_id  = $user_info['user_id'];
            $orders->set_user_id($user_id);
            $orders_db = $orders->get_orders_by_user();
            $orders_info = ($orders_db) ? $orders_db : 'Not orders to show.';
            if (is_array($orders_info)) {
                foreach ($orders_info as &$order) {
                    $order_id = $order['order_number'];
                    $order['products'] = $product->get_products_by_order_id($order_id);
                }
            }
            $address->set_user_id($user_id);
            $addresses_db = $address->get_addresses_by_userid();
            $address_info = ($addresses_db) ? $addresses_db : 'Not address added';

            echo json_encode([
                'status' => 'success',
                'message' => 'Login successful.',
                'data' => [
                    'orders' => $orders_info,
                    'addresses' => $address_info,
                    'user_info' => $user_info
                ]
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Registration failed.'
            ]);
        }
        exit;
    }

    if ($action === 'login') {
        $email = trim($data['login_email'] ?? '');
        $password = $data['login_password'] ?? '';

        if (!$email || !$password) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Email and password are required.'
            ]);
            exit;
        }

        $user->set_email($email);
        $user->set_password($password);

        if ($user->login_user()) {
            session_start();
            $user_info = $user->get_user_by_email();
            $_SESSION['user_id'] = $user_info['user_id'];
            $_SESSION['user_email'] = $user_info['email'];
            $user_id  = $user_info['user_id'];
            $orders->set_user_id($user_id);
            $orders_db = ($orders->get_orders_by_user());
            $orders_info = ($orders_db) ? $orders_db : 'Not orders to show.';
            if (is_array($orders_info)) {
                foreach ($orders_info as &$order) {
                    $order_id = $order['order_number'];
                    $order['products'] = $product->get_products_by_order_id($order_id);
                }
            }
            $address->set_user_id($user_id);
            $addresses_db = $address->get_addresses_by_userid();
            $address_info = ($addresses_db) ? $addresses_db : 'Not address added';

            echo json_encode([
                'status' => 'success',
                'message' => 'Login successful.',
                'data' => [
                    'orders' => $orders_info,
                    'addresses' => $address_info,
                    'user_info' => $user_info
                ]
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid email or password.'
            ]);
        }
        exit;
    }
    if($action === 'login_admin'){
        $email = $data['admin_email'];
        $password = $data['admin_password'];
        if(!$email || !$password){
             echo json_encode([
                'status' => 'error',
                'message' => 'Email and password are required.'
            ]);
            exit;
        }
        $admin->set_email($email);
        $admin->set_password($password);

         $admin_info =    $admin->login_admin();
        if($admin_info){
            session_start();
            $_SESSION['admin_id'] = $admin_info['admin_id'];
            $_SESSION['admin_email'] = $admin_info['email'];
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Login successful.'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid email or password.'
            ]);
        }
        exit;

    }
}
