<?php
require_once("../../config/db.php");
require_once("../models/category_sizes_model.php");
require_once("../models/attributes_model.php");
require_once("../models/colors_brands_model.php");
header("Content-type: application/json");
$size = new Size($connection);
$brand = new Brand($connection);
$color = new Color($connection);
$attribute = new AttributeType($connection);
$sub_attribute = new AttributeOption($connection);


if ($_SERVER['REQUEST_METHOD'] == 'GET') {
 
    if (isset($_GET['characteristics'])) {
        $clothing_sizes = $size->get_clothing_sizes();
        $shoes_sizes = $size->get_shoes_sizes();
        $all_brands = $brand->get_all_brands();
        $all_colors = $color->get_all_colors();
        $all_attr = $attribute->get_all_attributes();
        $all_subAttr = $sub_attribute->get_all_attribute_options();
        if ($clothing_sizes && $shoes_sizes && $all_brands && $all_attr && $all_subAttr) {
            echo json_encode([
                'status' => 'success',
                'data' => [
                    'clothing_sizes' => $clothing_sizes,
                    'shoes_sizes' => $shoes_sizes,
                    'colors' => $all_colors,
                    'brands' => $all_brands,
                    'attributes' => $all_attr,
                    'sub_attr' => $all_subAttr
                ]
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'data' => 'something went wrong getting the data (Characteristics).'
            ]);
        }
    }
    if (isset($_GET['getBrand'])) {
        $brand_id = $_GET['getBrand'];
        $brand->set_brand_id($brand_id);
        $current_brand_info = $brand->get_by_id();
        if ($current_brand_info) {
            echo json_encode([
                'status' => 'success',
                'data' => $current_brand_info
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'data' => 'something went wrong getting the data (Brand).'
            ]);
        }
    }
    if (isset($_GET['getColor'])) {
        $color_id = $_GET['getColor'];
        $color->set_color_id($color_id);
        $current_color_info = $color->get_color_by_id();
        if ($current_color_info) {
            echo json_encode([
                'status' => 'success',
                'data' => $current_color_info
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'data' => 'something went wrong getting the data (Color).'
            ]);
        }
    }
    if (isset($_GET['getAttr'])) {
        $attr_id = $_GET['getAttr'];
        $attribute->set_attribute_type_id($attr_id);
        $current_attr_info = $attribute->get_by_id();
        if ($current_attr_info) {
            echo json_encode([
                'status' => 'success',
                'data' => $current_attr_info
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'data' => 'something went wrong getting the data (Attr).'
            ]);
        }
    }
    if (isset($_GET['getSubAttr'])) {
        $subAttr_id = $_GET['getSubAttr'];
        $sub_attribute->set_attribute_option_id($subAttr_id);
        $current_subAttr_info = $sub_attribute->get_attr_option_by_id();
        if ($current_subAttr_info) {
            echo json_encode([
                'status' => 'success',
                'data' => $current_subAttr_info
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'data' => 'something went wrong getting the data (subAttr).'
            ]);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['brand_name'])) {
        if (empty($data['brand_name'])) {
            echo json_encode([
                'status' => 'error',
                'data' => 'Brand name is required.'
            ]);
            exit();
        }
        $brand->set_brand_id($data['brand_id']);
        $brand->set_brand_name($data['brand_name']);
        $brand->set_brand_description($data['brand_description']);
        if (!$data['brand_id']) {
            if ($brand->create_brand()) {
                echo json_encode([
                    'status' => 'success',
                    'data' => 'The brand was created successfully',
                    'data_form' => $data
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "data" =>  "Something went wrong creating new brand",
                    'data_form' => $data
                ]);
            }
        } else {
            if ($brand->update_brand()) {
                echo json_encode([
                    'status' => 'success',
                    'data' => 'The brand was updated successfully',
                    'data_form' => $data
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "data" =>  "Something went wrong creating updating brand."
                ]);
            }
        }
    }

    if (isset($data['color_name'])) {
        if (empty($data['color_name'])) {
            echo json_encode([
                'status' => 'error',
                'data' => 'Color name is required.'
            ]);
            exit();
        }
        $color->set_color_name($data['color_name']);
        if ($color->create_color()) {
            echo json_encode([
                "status" => "success",
                "data" =>  "Color created successfully."
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "data" =>  "Something went wrong creating new color"
            ]);
        }
    }

    if (isset($data['attr_name'])) {
        if (empty($data['attr_name'])) {
            echo json_encode([
                'status' => 'error',
                'data' => 'Attribute name is required.'
            ]);
            exit();
        }
        $attribute->set_attribute_name($data['attr_name']);
        if ($attribute->create_attribute_type()) {
            echo json_encode([
                "status" => "success",
                "data" =>  "Attribute added successfully.",
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "data" =>  "Something went wrong creating the attribute"
            ]);
        }
    }

    if (isset($data['subAttr_name'])) {
        if (empty($data['subAttr_name']) || empty($data['attribute_name'])) {
            echo json_encode([
                'status' => 'error',
                'data' => 'Sub-attribute name and attribute name are required.'
            ]);
            exit();
        }
        $sub_attribute->set_attribute_option_id($data['subAttr_id']);
        $sub_attribute->set_attribute_option_name($data['subAttr_name']);
        $sub_attribute->set_attribute_type_name($data['attribute_name']);
        if (!$data['subAttr_id']) {
            if ($sub_attribute->create_attribute_option()) {
                echo json_encode([
                    'status' => 'success',
                    'data' => 'Attribute option was created successfully',
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'data' => 'something went wrong creating the sub-attribute.',
                ]);
            }
        } else {
            if ($sub_attribute->update_attribute_option()) {
                echo json_encode([
                    'status' => 'success',
                    'data' => 'Sub-attribute option was updated successfully',
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'data' => 'something went wrong creating sub-attribute.',
                ]);
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents("php://input"), true);
    if (isset($data['brand_id'])) {
        $brand->set_brand_id($data['brand_id']);
        if ($brand->delete_brand()) {
            echo json_encode([
                'status' => 'success',
                'data' => 'The brand deleted successfully',
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'data' => 'something went wrong deleting brand.',
            ]);
        }
    }
    if (isset($data['color_id'])) {
        $color->set_color_id($data['color_id']);
        if ($color->delete_color()) {
            echo json_encode([
                'status' => 'success',
                'data' => 'The color deleted successfully',
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'data' => 'something went wrong deleting color.',
            ]);
        }
    }
    if (isset($data['attr_id'])) {
        $attribute->set_attribute_type_id($data['attr_id']);
        if ($attribute->delete_attribute_type()) {
            echo json_encode([
                'status' => 'success',
                'data' => 'The attribute deleted successfully',
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'data' => 'something went wrong deleting attribute.',
            ]);
        }
    }
    if (isset($data['subAttr_id'])) {
        $sub_attribute->set_attribute_option_id($data['subAttr_id']);
        if ($sub_attribute->delete_attribute_option()) {
            echo json_encode([
                'status' => 'success',
                'data' => 'The sub-attribute deleted successfully',
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'data' => 'something went wrong deleting sub-attribute.',
            ]);
        }
    }
}
