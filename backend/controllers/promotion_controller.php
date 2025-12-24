<?php
require_once('../../config/db.php');
require_once('../models/promotion_model.php');
header('Content-type: application/json');
$promotion = new Promotion($connection);

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['promo'])) {
        $all_promos = $promotion->get_all_promotions();
        if ($all_promos) {
            echo json_encode([
                'status' => 'success',
                'data' => $all_promos
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'data' => 'Somethinbg went wrong getting the data (Promotions).'
            ]);
        }
    }

    if (isset($_GET['getPromo'])) {
        $promo_id = $_GET['getPromo'];
        $promotion->set_promo_id($promo_id);
        $curren_promo_info = $promotion->get_promotion_by_id();
        if ($curren_promo_info) {
            echo json_encode([
                'status' => 'success',
                'data' => $curren_promo_info
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'data' => 'Something went wrong getting the data (Promotions).'
            ]);
        }
    }
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['promo_name'], $_POST['promo_porcent'], $_POST['promo_start_date'], $_POST['promo_end_date'], $_POST['promo_description']) &&
    !isset($_POST['promo_id'])
) {
    $promotion->set_promo_name($_POST['promo_name']);
    $promotion->set_discount_porcent((float)$_POST['promo_porcent']);
    $promotion->set_start_date($_POST['promo_start_date']);
    $promotion->set_end_date($_POST['promo_end_date']);
    $promotion->set_promo_description($_POST['promo_description']);
    $result = $promotion->create_promo();
    if ($result) {
        echo json_encode(['status' => 'success', 'data' => 'Promotion created successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'data' => 'Failed to create promotion.']);
    }
    exit;
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['promo_id'], $_POST['promo_name'], $_POST['promo_porcent'], $_POST['promo_start_date'], $_POST['promo_end_date'], $_POST['promo_description'])
) {
    $promotion->set_promo_id($_POST['promo_id']);
    $promotion->set_promo_name($_POST['promo_name']);
    $promotion->set_discount_porcent((float)$_POST['promo_porcent']);
    $promotion->set_start_date($_POST['promo_start_date']);
    $promotion->set_end_date($_POST['promo_end_date']);
    $promotion->set_promo_description($_POST['promo_description']);
    $result = $promotion->update_promo();
    if ($result) {
        echo json_encode(['status' => 'success', 'data' => 'Promotion updated successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'data' => 'Failed to update promotion.']);
    }
    exit;
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['delete_promo_id'])
) {
    $promotion->set_promo_id($_POST['promo_id']);
    $result = $promotion->delete_promo();
    if ($result) {
        echo json_encode(['status' => 'success', 'data' => 'Promotion deleted successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'data' => 'Failed to delete promotion.']);
    }
    exit;
}
