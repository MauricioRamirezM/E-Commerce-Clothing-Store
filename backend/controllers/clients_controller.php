<?php
require_once('../models/users_admin_model.php');
require_once('../models/orders_model.php');
require_once('../models/address_model.php');
require_once('../models/product_model.php');
require_once('../../config/db.php');
header('Content-Type: application/json');
$admin = new Admin($connection);
$client = new User($connection);


if ($_SERVER['REQUEST_METHOD'] == "GET") {
    if (isset($_GET['client'])) {

        $list_client = $client->get_all_users();
        if ($list_client) {
            echo json_encode([
                'status' => 'success',
                'data' => $list_client
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'data' => 'Something went wrong getting the data. (clients)'
            ]);
        }
    }


    if (isset($_GET['getClient'])) {
        $orders = new UserOrder($connection);
        $address = new Address($connection);
        $product = new ProductItem($connection);
        $client_id = $_GET['getClient'];
        $client->set_user_id($client_id);
        $current_client_info = $client->get_user_by_id();
        $orders->set_user_id($client_id);
        $current_client_orders = $orders->get_orders_by_user();
        foreach ($current_client_orders as &$order) {
            $order_id = $order['order_number'];
            $order['products'] = $product->get_products_by_order_id($order_id);
        }
            $address->set_user_id($client_id);
        $current_client_addresses = $address->get_addresses_by_userid();
        if ($current_client_info ) {
            echo json_encode([
                'status' => 'success',
                'data' => [
                    'clientInfo' => $current_client_info,
                    'clientOrders' => $current_client_orders,
                    'clientAddresses' => $current_client_addresses
                ]
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'data' => 'something went wrong getting the data (Client)'
            ]);
        }
    }
}



if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (
        empty($data["first_name"]) ||
        empty($data["last_name"]) ||
        empty($data["email"]) ||
        empty($data["birthday"]) ||
        empty($data["password"]) ||
        !filter_var($data["email"], FILTER_VALIDATE_EMAIL)
    ) {
        echo json_encode([
            "status" => "error",
            "data" => "All fields are required and email must be valid."
        ]);
        exit();
    }

    $client->set_first_name($data["first_name"]) ?? "";
    $client->set_last_name($data["last_name"]) ?? "";
    $client->set_email($data["email"]) ?? "";
    $client->set_email($data["email"]) ?? "";
    $client->set_phone($data["phone"] ?? "");
    $client->set_birthday($data["birthday"] ?? "");
    $client->set_password(password_hash($data["password"], PASSWORD_DEFAULT));
    $birthday_age = $data["birthday"] ;
    if ($client->verify_email()) {
        echo json_encode([
            "status" => "error",
            "data" => "The email is already taken. Try again"
        ]);
        exit;
    } 
    if(!$client->verify_age()){
        echo json_encode([
                'status' => 'error',
                'data' =>'The user must be at least 18 years old to register.',
                'age' => $birthday_age
            ]);
            exit;
    }

       if( $client->create_user()){   
           echo json_encode([
               "status" => "success",
               "data" => "Client registered successfully"
            ]);
        }else{
              echo json_encode([
               "status" => "error",
               "data" => "something went wrong. Try again"
            ]);
        }
}


if ($_SERVER['REQUEST_METHOD'] == "PUT") {
    $data = json_decode(file_get_contents("php://input"), true);
    if (isset($data['id'])) {
        $client->set_user_id($data["id"] ?? "");
        $client->set_first_name($data["first_name"] ?? "");
        $client->set_last_name($data["last_name"] ?? "");
        $client->set_email($data["email"] ?? "");
        $client->set_birthday($data["birthday"] ?? "");
        $client->set_phone($data["phone"] ?? "");

        if (!empty($data["password"])) {
            $hashed_password = password_hash($data["password"], PASSWORD_DEFAULT);
            $client->set_password($hashed_password);
        }

        if ($client->update_user()) {
            echo json_encode([
                "status" => "success",
                "data" => "Client updated successfully"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "data" => "Something went wrong. Try again",
                "info" => $data
            ]);
        }
    } else {
        echo json_encode([
            "status" => "error",
            "data" => "Client not found"
        ]);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "DELETE") {
    $data = json_decode(file_get_contents("php://input"), true);
    if (isset($data["client_id"])) {
        $client_id = $data["client_id"];
        $client->set_user_id($client_id);
        $delete = $client->delete_user();
        if ($delete) {
            echo json_encode([
                "status" => "success",
                "data" => "Client deleted successfully",
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "data" => "Something went wrong deleting.",
            ]);
        }
    }
}
