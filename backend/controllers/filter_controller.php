<?php
require_once('../../config/db.php');
require_once('../models/product_model.php');
require_once('../models/colors_brands_model.php');
require_once('../models/category_sizes_model.php');
require_once('../models/attributes_model.php');
$attr_option_mod = new AttributeOption($connection);
$attr_type_mod = new AttributeType($connection);
$size_mod = new Size($connection);
$category_mod = new Category($connection);

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $all_attribute_types = $attr_type_mod->get_all_attributes();
    $types = [];
    if ($all_attribute_types) {
        $types = array_map(fn($attr) => $attr['attribute_name'], $all_attribute_types);
    }

    $clothing_sizes = $size_mod->get_clothing_sizes();
    $shoes_sizes = $size_mod->get_shoes_sizes();
    $all_attr_options = !empty($types) ? $attr_option_mod->get_all_by_types_2($types) : [];
    $categories_names = $category_mod->get_categories_names();

    $data = [
        "categories" => $categories_names,
        "clothing_sizes" => $clothing_sizes,
        "shoes_sizes" => $shoes_sizes,
        "attrs" => $all_attr_options
    ];

    if ($data) {
        echo json_encode([
            'status' => 'success',
            'data' => $data
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'No attributes found'
        ]);
    }
   

}




if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_mod = new Product($connection);
    $brand_mod = new Brand($connection);
    $color_mod = new Color($connection);

    $data = json_decode(file_get_contents("php://input"), true);

    $brand = ($data['brand']) ?? "";
    $color = ($data['color']) ?? "";
    $size = ($data['size']) ?? "";
    $material = ($data['material']) ?? "";
    $style = ($data['style']) ?? "";

    $brand_mod->set_brand_name($brand);
    $color_mod->set_color_name($color);
    $size_mod->set_size_name($size);
    $attr_option_mod->set_attribute_option_name($material);
    $material_id = $attr_option_mod->get_attr_option_id_by_name();
    $attr_option_mod->set_attribute_option_name($style);    
    $style_id = $attr_option_mod->get_attr_option_id_by_name();
    $brand_id = $brand_mod->get_brand_id_by_name();
    $color_id = $color_mod->get_color_id_by_name();
    $size_id = $size_mod->get_size_id_by_name();

    $filters = [
        'brand' => $brand_id,
        'color' => $color_id,
        'size' => $size_id,
        'material' => $material_id,
        'style' => $style_id
    ];

    $apply_filters = $product_mod->get_products_with_filters($filters);
    if ($apply_filters && count($apply_filters) > 0) {
        echo json_encode([
            'status' => 'success',
            'data' => $apply_filters
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'data' => 'No products found.'
        ]);
    }
}