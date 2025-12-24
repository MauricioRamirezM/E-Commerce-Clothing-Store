<?php
require_once('../../config/db.php');
require_once('../models/payment_country_model.php');
header('Content-type: application/json');
$country = new Country($connection);
$payment = new PaymentType($connection);
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['payCon'])) {
    $all_countries = $country->get_all_countries();
    $all_payments = $payment->get_all_types();
    if ($all_countries && $all_payments) {
        echo json_encode([
            'status' => 'success',
            'data' => [
                'country' => $all_countries,
                'payment' => $all_payments
            ]
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'data' => 'something went wrong getting the information (payments-countries)'
        ]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['payment_name'])) {
        if (empty($data['payment_name'])) {
            echo json_encode([
                'status' => 'error',
                'data' => 'Payment name is required.'
            ]);
            exit();
        }
        $payment->set_payment_type_name($data['payment_name']);
        if ($payment->create_type()) {
            echo json_encode([
                'status' => 'success',
                'data' => 'Payment type created succesfully'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'data' => 'something went wrong creating the payment type'
            ]);
        }
    }

    if(isset($data['country_name'])){
        if (empty($data['country_name'])) {
            echo json_encode([
                'status' => 'error',
                'data' => 'Country name is required.'
            ]);
            exit();
        }
        $country->set_country_name($data['country_name']);
        if($country->create_country()){
          echo json_encode([
                'status' => 'success',
                'data' => 'Country added succesfully'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'data' => 'something went wrong adding the country'
            ]);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents("php://input"), true);
    if (isset($data['payment_id'])) {
        $payment->set_payment_type_id($data['payment_id']);
        if ($payment->delete_type()) {
            echo json_encode([
                'status' => 'success',
                'data' => 'The payment type deleted successfully',
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'data' => 'something went wrong deleting payemnt type.',
            ]);
        }
    }
    if (isset($data['country_id'])) {
        $country->set_country_id($data['country_id']);
        if ($country->delete_country()) {
            echo json_encode([
                'status' => 'success',
                'data' => 'The country deleted successfully',
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'data' => 'something went wrong deleting color.',
            ]);
        }
    }
}

