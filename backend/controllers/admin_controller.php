<?php

require_once('../models/users_admin_model.php');
require_once('../../config/db.php');
header('Content-Type: application/json');
$admin = new Admin($connection);


if ($_SERVER['REQUEST_METHOD'] == "GET") {
    if (isset($_GET['admin'])) {
        $list_admins = $admin->get_all_admins();
        if ($list_admins) {
            echo json_encode([
                'status' => 'success',
                'data' => $list_admins
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'data' => 'Something went wrong getting the data. (admins)'
            ]);
        }
    }

    if (isset($_GET['getAdmin'])) {
        $admin_id = $_GET['getAdmin'];
        $admin->set_admin_id($admin_id);
        $current_admin_info = $admin->get_admin_by_id();
        if ($current_admin_info) {
            echo json_encode([
                'status' => 'success',
                'data' => $current_admin_info
            ]);
        } else {
            echo json_encode([
                'status' => 'success',
                'data' => 'Administrator not found.'
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
        empty($data["password"]) ||
        !filter_var($data["email"], FILTER_VALIDATE_EMAIL)
    ) {
        echo json_encode([
            "status" => "error",
            "data" => "All fields are required and email must be valid."
        ]);
        exit();
    }

    $admin->set_first_name($data["first_name"]);
    $admin->set_last_name($data["last_name"]);
    $admin->set_email($data["email"]);
    $admin->set_phone($data["phone"] ?? "");
    $password = $data["password"];
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $admin->set_password($hashed_password);
    if(!$admin->verify_email()){
        $admin->create_admin();
        echo json_encode([
            "status" => "success",
            "data" => "Admin registered successfully",
            "forminfo" => $data
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "data" => "The email already has an account",
        ]);
    }
}

if ($_SERVER['REQUEST_METHOD'] == "PUT") {
    $data = json_decode(file_get_contents("php://input"), true);
    if (isset($data['id'])) {
        if (
            empty($data["first_name"]) ||
            empty($data["last_name"]) ||
            empty($data["email"]) ||
            !filter_var($data["email"], FILTER_VALIDATE_EMAIL)
        ) {
            echo json_encode([
                "status" => "error",
                "data" =>  "All fields are required and email must be valid."
            ]);
            exit();
        }

        $admin->set_admin_id($data["id"]);
        $admin->set_first_name($data["first_name"]);
        $admin->set_last_name($data["last_name"]);
        $admin->set_email($data["email"]);
        $admin->set_phone($data["phone"] ?? "");

        if (!empty($data["password"])) {
            $hashed_password = password_hash($data["password"], PASSWORD_DEFAULT);
            $admin->set_password($hashed_password);
        }

        if ($admin->update_admin()) {
            echo json_encode([
                "status" => "success",
                "data" => "Admin updated successfully"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "data" => "Something went wrong. Try again"
            ]);
        }
    } else {
        echo json_encode([
            "status" => "error",
            "data" => "Admin not found"
        ]);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "DELETE") {
    $data = json_decode(file_get_contents("php://input"), true);
    if (isset($data["admin_id"])) {
        $admin_id = $data["admin_id"];
        $admin->set_admin_id($admin_id);
        $delete =  $admin->delete_admin();
        if ($delete) {
            echo json_encode([
                "status" => "success",
                "data" => "Admin deleted successfully",
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "data" => "Something went wrong deleting. Try again",
            ]);
        }
    }
}
