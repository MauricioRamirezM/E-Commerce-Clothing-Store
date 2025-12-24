<?php
require_once('../../config/db.php');
require_once('../models/category_sizes_model.php');
require_once('../models/product_model.php');
header('Content-Type: application/json');

$category = new Category($connection);
$size = new Size($connection);
$size_category = new SizeCategory($connection);

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['catego'])) {

        $all_categories = $category->get_all_categories();
        if ($all_categories) {
            echo json_encode([
                'status' => 'success',
                'data' => $all_categories
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'data' => 'Error getting the  data (categories).'
            ]);
        }
    }

    if (isset($_GET['getCatego'])) {
        $category_id = $_GET['getCatego'];
        $category->set_category_id($category_id);
        $current_catego_info = $category->get_category_by_id();
        if ($current_catego_info) {
            echo json_encode([
                'status' => 'success',
                'data' => $current_catego_info
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'data' => 'Something went wrong getting the data (Category).'
            ]);
        }
    }


    if(isset($_GET['get_by_category']) && $_GET['get_by_category'] !== ""){
        $get_by_category = $_GET['get_by_category'];
        $product = new Product($connection);
         $product->set_category($get_by_category);
         $produ_by_category = $product->get_product_by_category();
           if($produ_by_category){
            echo json_encode([     
                'status' => 'success',
                'data' => $produ_by_category
            ]);
           }else{
             echo json_encode([     
                'status' => 'error',
                'data' => 'category not found'
            ]);
           }

        
}
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (
        empty($_POST["category_name"]) ||
        empty($_POST["category_description"]) ||
        empty($_POST["category_size"])
    ) {
        echo json_encode([
            "status" => "error",
            "data" => "Category name, description, and size are required."
        ]);
        exit();
    }

    if (!empty($_POST['category_id'])) {
        $category->set_category_id($_POST["category_id"]);
        $category->set_category_name($_POST["category_name"]);
        $category->set_category_description($_POST["category_description"]);
        $category->set_size_category_id(intval($_POST["category_size"]));
        $category->set_parent_category_id(intval($_POST["category_parent"]));
        $img_path = "../../../assets/images/category/";
        if (isset($_FILES['category_image'])) {
            $image = $_FILES['category_image']['name'];
            $dateTime = time();
            $category_img = $dateTime . "_" . $image;
            $tmp_name = $_FILES['category_image']['tmp_name'];
            move_uploaded_file($tmp_name, $img_path.$category_img);
            $category->set_category_img($category_img);
        }
        if ($category->update_category($img_path)) {
            echo json_encode([
                "status" => "success",
                "data" => "Category updated successfully",
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "data" => "Something went wrong"
            ]);
        }
    } else {
        $category->set_category_name($_POST["category_name"]);
        $category->set_category_description($_POST["category_description"]);
        $category->set_size_category_id(intval($_POST["category_size"]));
        $category->set_parent_category_id(intval($_POST["category_parent"]));

        if (isset($_FILES['category_image'])) {
            $image = $_FILES['category_image']['name'];
            $dateTime = time();
            $newImageName= $dateTime . "_" . $image ;
            $tmp_name = $_FILES['category_image']['tmp_name'];
            if ($tmp_name != "") {
                move_uploaded_file($tmp_name, "../../../assets/images/category/" . $newImageName);
            }
            $category->set_category_img($newImageName);
        } else {
            $category->set_category_img("");
        }

        if ($category->create_category()) {
            echo json_encode([
                "status" => "success",
                "data" => "Category created successfully",
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "data" => "Something went wrong"
            ]);
        }
    }
}



if ($_SERVER["REQUEST_METHOD"] == "DELETE") {
    $data = json_decode(file_get_contents("php://input"), true);
    if (isset($data["catego_id"])) {
        $catego_id = $data["catego_id"];
        $category->set_category_id($catego_id);
        $delete =  $category->delete_category();
        if ($delete) {
            echo json_encode([
                "status" => "success",
                "data" => "Category deleted successfully",
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "data" => "Something went wrong deleting. Try again",
            ]);
        }
    }
}
