<?php
session_start();
if(!isset($_SESSION['admin_email'])){
    if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'){
        header('Content-Type: application/json');
        echo json_encode(['redirect' => '/e-commerce/frontend/html/admin/login.html']);
        exit;
    } else {
        header('Location: ../../frontend/html/admin/login.html');
        exit;
    }
}


if ($_SERVER[ 'REQUEST_METHOD'] === 'GET' && isset($_GET['page'])) {
    $page = $_GET['page'];

    switch ($page) {
        case 'admins':
            header('Location: ../views/admins/list.html');
            break;
        case 'clients':
            header('Location: ../views/clients/list.html');
            break;
        case 'products':
            header('Location: ../views/products/list.html');
            break;
        case 'categories':
            header('Location: ../views/categories/list.html');
            break;
        case 'characteristics':
            header('Location: ../views/characteristics/size_brand_color_attr.html');
            break;
        case 'promotions':
            header('Location: ../views/promotions/list_update.html');
            break;
        case 'payments':
            header('Location: ../views/payments_countries/payments.html');
            break;
        case 'profile':
            header('Location: ../views/clients/profile.html');
            break;
        case 'home':
            header('Location: ../views/home.html');
            break;
    }
}elseif (isset($_GET['update'])) {
    $page = $_GET['update'];

    switch ($page) {
        case 'update_client':
            header('Location: ../views/clients/update.html');
            break;
        case 'update_admin':
            header('Location: ../views/admins/update.html');
            break;
        case 'update_product':
            header('Location: ../views/products/update.html');
            break;
        case 'update_category':
            header('Location: ../views/categories/update.html');
            break;
    }
}elseif (isset($_GET['create'])) {
    $page = $_GET['create'];
    switch ($page) {
        case 'create_client':
            header('Location: ../views/clients/create.html');
            break;
        case 'create_admin':
            header('Location: ../views/admins/create.html');
            break;
        case 'create_product':
            header('Location: ../views/products/create.html');
            break;
        case 'create_category':
            header('Location: ../views/categories/create.html');
            break;
    }
}else{
    header('Location:../views/home.html');
    exit;
}
