<?php
require_once('../../config/db.php');
require_once('../models/product_model.php');
require_once('../models/attributes_model.php');
header('Content-Type: application/json');

$product = new product($connection);
$imgObj = new ProductImage($connection);
$item = new ProductItem($connection);
$variation = new ProductVariation($connection);
$item = new ProductItem($connection);
$productAttribute = new ProductAttribute($connection);
if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    if (isset($_GET['get_by_gender'])) {
        $gender = ($_GET['get_by_gender']) ?? "";
        $attribute_option = new AttributeOption($connection);
        $attribute_option->set_attribute_option_name($gender);
        $gender_id = $attribute_option->get_attribute_option_id();

        $products_by_gender = $product->get_product_by_gender($gender_id);
        if ($products_by_gender) {
            echo json_encode([
                'status' => 'success',
                'data' => $products_by_gender
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'data' => 'There are not product available.'
            ]);
        };
    }
     if(isset($_GET['get_by_category'])){
        $category= $_GET['get_by_category'] ?? "";
        $product->set_category($category);
        $product_by_category = $product->get_product_by_category();
        if($product_by_category){

            echo json_encode([  
                'status' => 'success',
                'data' => $product_by_category
            ]);
        }else{
             echo json_encode([  
                'status' => 'error',
                'data' => 'errod getting category '
            ]);
        }
    }


    if (isset($_GET['product'])) {
        $data = $product->get_all_products();

        echo json_encode([
            'status' => 'success',
            'data' => $data
        ]);
    }

    if (isset($_GET['getProduct'])) {
        $product_id = ($_GET['getProduct']) ?? "";
        $product->set_product_id($product_id);
        $current_product_info = $product->get_product_by_id();
        if ($current_product_info) {
            echo json_encode([
                'status' => 'success',
                'data' => $current_product_info
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'data' => 'Sometehing went wrong getting the data (product).'
            ]);
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    if (
        empty($_POST["product_name"]) ||
        empty($_POST["product_brand"]) ||
        empty($_POST["product_category"]) ||
        empty($_POST["product_price"]) ||
        !is_numeric($_POST["product_price"])
    ) {
        echo json_encode([
            "status" => "error",
            "data" => "All required fields must be filled and price must be valid."
        ]);
        exit();
    }

    $product_id = $_POST['product_id'] ?? null;
    $product_code = $_POST['product_code'] ?? '';
    $product_name = $_POST['product_name'] ?? '';
    $product_description = $_POST['product_description'] ?? '';
    $product_brand = $_POST['product_brand'] ?? '';
    $product_category = $_POST['product_category'] ?? '';
    $product_color = $_POST['product_color'] ?? '';
    $size_type = $_POST['size_type'] ?? '';
    $product_size = $_POST['product_size'] ?? '';
    $product_stock = $_POST['product_stock'] ?? '';
    $product_attribute = $_POST['product_type_attribute'] ?? '';
    $product_subAttribute = $_POST['product_subtype_attribute'] ?? '';
    $product_price = floatval($_POST['product_price'] ?? '');
    $product_sale_price = floatval($_POST['product_sale_price'] ?? '');

    $img_path = "../../../assets/images/product/";
    $product_img = null;
    if (isset($_FILES['product_image']) && $_FILES['product_image']['tmp_name'] != "") {
        $image = $_FILES['product_image']['name'];
        $dateTime = time();
        $product_img = $dateTime . "_" . $image;
        move_uploaded_file($_FILES['product_image']['tmp_name'], $img_path . $product_img);
    }
    $product->set_name($product_name);
    $product->set_brand($product_brand);
    $product->set_category($product_category);
    $product->set_description($product_description);

    if (isset($_POST['product_id']) && $_POST['product_id']) {
        $product->set_product_id($_POST['product_id']);
        $result = $product->update_full_product(
            $product_color,
            $product_price,
            $product_sale_price,
            $product_code,
            $product_size,
            $product_stock,
            $product_subAttribute,
            $product_img
        );
        if ($result) {
            echo json_encode([
                "status" => "success",
                "data" =>  "Product updated successfully"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "data" =>  "Something went wrong updateing the product"
            ]);
        }
    } else {
        $result = $product->create_full_product(
            $product_color,
            $product_price,
            $product_sale_price,
            $product_code,
            $product_size,
            $product_stock,
            $product_subAttribute,
            $product_img
        );
        if ($result) {
            $last_id = $connection->lastInsertId(); 
            $product_code = 'PDR' . $last_id;
            $item->set_product_id($last_id);
            $item->update_product_code($product_code);
            echo json_encode([
                "status" => "success",
                "data" =>  "Product created successfully"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "data" =>  "Something went wrong creating the product"
            ]);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents("php://input"), true);
    if ((isset($data['product_id']))) {
        $product_id = $data['product_id'] ?? "";
        $product->set_product_id($product_id);

        if ($product->delete_product()) {
            echo json_encode([
                "status" => "success",
                "data" => "Product and all related data deleted successfully"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "data" => "Failed to delete product",
                'resp' => $product_id
            ]);
        }
    }
}
