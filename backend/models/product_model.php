<?php
class Product
{
    private $conn;
    private $product_id;
    private $name;
    private $brand;
    private $category;
    private $description;

    public function __construct(PDO $connection)
    {
        $this->conn = $connection;
    }

    public function get_product_id()
    {
        return $this->product_id;
    }
    public function get_name()
    {
        return $this->name;
    }
    public function get_brand()
    {
        return $this->brand;
    }
    public function get_category()
    {
        return $this->category;
    }
    public function get_description()
    {
        return $this->description;
    }

    public function set_product_id($id)
    {
        $this->product_id = $id;
    }
    public function set_name(string $name)
    {
        $this->name = $name;
    }
    public function set_brand(string $brand)
    {
        $this->brand = $brand;
    }
    public function set_category(string $category)
    {
        $this->category = $category;
    }
    public function set_description(string $description)
    {
        $this->description = $description;
    }


    public function create_product(): bool
    {
        try {
            $query = "INSERT INTO product (product_name, brand_id, category_id, product_description)
                        VALUES (:product_name, (SELECT brand_id FROM brand WHERE brand_name = :brand_name),
                        (SELECT category_id FROM category WHERE category_name = :category_name) , :product_description)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':product_name', $this->name);
            $stmt->bindParam(':brand_name', $this->brand);
            $stmt->bindParam(':category_name', $this->category);
            $stmt->bindParam(':product_description', $this->description);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }


    public function update_product(): bool
    {
        try {
            $query = "UPDATE product SET product_name = :product_name, 
                            brand_id = (SELECT brand_id FROM brand WHERE brand_name = :brand_name),
                             category_id = (SELECT category_id FROM category WHERE category_name = :category_name), 
                             product_description = :product_description, updated_at = CURRENT_TIMESTAMP WHERE product_id = :product_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':product_name', $this->name);
            $stmt->bindParam(':brand_name', $this->brand);
            $stmt->bindParam(':category_name', $this->category);
            $stmt->bindParam(':product_description', $this->description);
            $stmt->bindParam(':product_id', $this->product_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            echo $e->getMessage() . 'update product';
            return false;
        }
    }

    public function delete_product(): bool
    {
        try {
            $query = "DELETE FROM  product  WHERE product_id =  :product_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':product_id', $this->product_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            echo $e->getMessage() . 'delete product';
            return false;
        }
    }
    public function create_full_product($color_id, $original_price, $sale_price, $product_code, $size_id, $quantity_stock, $attribute_option_id, $image): bool
    {
        try {
            $this->conn->beginTransaction();

            if (!$this->create_product()) {
                $this->conn->rollBack();
                echo 'prodU CREATE breaks';
                return false;
            }
            $product_id = $this->conn->lastInsertId();

            $item = new ProductItem($this->conn);
            $item->set_product_id($product_id);
            $item->set_color_id($color_id);
            $item->set_original_price($original_price);
            $item->set_sale_price($sale_price);
            $item->set_product_code($product_code);
            if (!$item->create_productItem()) {
                $this->conn->rollBack();
                echo 'produ_item breaks';
                return false;
            }
            $product_item_id = $this->conn->lastInsertId();

            if ($image) {
                $imgObj = new ProductImage($this->conn);
                $imgObj->set_product_item_id($product_item_id);
                $imgObj->set_img_url($image);
                if (!$imgObj->add_image()) {
                    $this->conn->rollBack();
                    return false;
                }
            }

            $variation = new ProductVariation($this->conn);
            $variation->set_product_item_id($product_item_id);
            $variation->set_size_id($size_id);
            $variation->set_quantity_stock($quantity_stock);
            if (!$variation->create_variation()) {
                $this->conn->rollBack();
                return false;
            }

            $productAttribute = new ProductAttribute($this->conn);
            $productAttribute->set_product_id($product_id);
            $productAttribute->set_attribute_option_id($attribute_option_id);
            if (!$productAttribute->create_product_attr()) {
                $this->conn->rollBack();
                return false;
            }

            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log($e->getMessage());
            return false;
        }
    }
    public function update_full_product(
        $color_id,
        $original_price,
        $sale_price,
        $product_code,
        $size_id,
        $quantity_stock,
        $attribute_option_id,
        $image
    ): bool {
        try {
            $this->conn->beginTransaction();

            if (!$this->update_product()) {
                $this->conn->rollBack();
                return false;
            }

            $item = new ProductItem($this->conn);
            $item->set_product_id($this->product_id);
            $product_items = $item->get_product_items_by_product_id();
            $product_item_id = $product_items[0]['product_item_id'] ?? null;

            if ($product_item_id) {
                $item->set_product_item_id($product_item_id);
                $item->set_color_id($color_id);
                $item->set_original_price($original_price);
                $item->set_sale_price($sale_price);
                $item->set_product_code($product_code);
                if (!$item->update_productItem()) {
                    $this->conn->rollBack();
                    return false;
                }
            } else {
                $item->set_color_id($color_id);
                $item->set_original_price($original_price);
                $item->set_sale_price($sale_price);
                $item->set_product_code($product_code);
                if (!$item->create_productItem()) {

                    $this->conn->rollBack();
                    return false;
                }
                $product_item_id = $this->conn->lastInsertId();
            }

            if ($image && $product_item_id) {
                $imgObj = new ProductImage($this->conn);
                $imgObj->set_product_item_id($product_item_id);
                $imgObj->delete_image();
                $imgObj->set_img_url($image);
                if (!$imgObj->add_image()) {
                    $this->conn->rollBack();
                    return false;
                }
            }

            $variation = new ProductVariation($this->conn);
            $variation->set_product_item_id($product_item_id);
            $variation->set_product_id($this->product_id);
            $variation->delete_product_variation(); 
            $variation->set_size_id($size_id);
            $variation->set_quantity_stock($quantity_stock);
            if (!$variation->create_variation()) {
                $this->conn->rollBack();
               
                return false;
            }

            $productAttribute = new ProductAttribute($this->conn);
            $productAttribute->set_product_id($this->product_id);
            $productAttribute->delete_product_attr();
            $productAttribute->set_attribute_option_id($attribute_option_id);
            if (!$productAttribute->create_product_attr()) {
                $this->conn->rollBack();
                echo 'produ_+attr breaks';
                return false;
            }

            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log($e->getMessage());
            return false;
        }
    }


    public function get_all_products(): array|false
    { 
        try {
            $query = " SELECT 
    p.product_id as id,
    p.product_description as description,
    p.product_name AS name,
    b.brand_name AS brand,
    c.category_name AS category,
    pi.product_code AS code,
    pi.original_price AS price,
    pi.sale_price AS salePrice,
    co.color_name AS color,
    GROUP_CONCAT( DISTINCT att.attribute_name) AS attributes,
    GROUP_CONCAT( DISTINCT ao.attribute_option_name) AS subAttributes,
    img.img_filename AS image,
    s.size_name AS size,
    pv.quantity_stock AS stock,
    sc.category_name as sizeCategory
FROM product p
JOIN brand b ON p.brand_id = b.brand_id
JOIN category c ON p.category_id = c.category_id
JOIN product_item pi ON p.product_id = pi.product_id
JOIN color co ON pi.color_id = co.color_id AND pi.product_id = p.product_id
LEFT JOIN product_attribute pa ON pa.product_id = p.product_id
LEFT JOIN attribute_option ao ON pa.attribute_option_id = ao.attribute_option_id
LEFT JOIN attribute_type att ON ao.attribute_type_id = att.attribute_type_id
JOIN product_variation pv ON pv.product_item_id = pi.product_item_id
JOIN size s ON s.size_id = pv.size_id
JOIN product_img img ON pi.product_item_id = img.product_item_id
JOIN size_category sc ON sc.category_id = s.size_category_id
GROUP BY pi.product_code
ORDER BY pi.product_code";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $products = $stmt->fetchAll();
            return $products;
        } catch (PDOException $error) {
            return false;
        }
    }

    public function get_product_by_name(): array|false
    {
        try {
            $query = "SELECT 
        p.product_name AS Product,
        b.brand_name AS Brand,
        pi.product_code AS Code,
        pi.original_price AS Price,
        co.color_name AS Color,
        img.img_filename AS Photo
        FROM product  p 
        JOIN brand b ON p.brand_id = b.brand_id
        JOIN product_item pi ON p.product_id = pi.product_id
        JOIN color co ON pi.color_id = co.color_id  and pi.product_id = p.product_id
        JOIN product_img img ON pi.product_item_id = img.product_item_id
        WHERE b.brand_id = :brand_id
        ORDER BY p.product_name, pi.product_code";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":brand_id", $this->brand);
            $stmt->execute();
            $products = $stmt->fetchAll();
            return $products;
        } catch (PDOException $error) {
            return false;
        }
    }

    public function get_product_by_id()
    {
        try {
            $query = "SELECT 
            p.product_id as id,
            p.product_name AS name,
            p.product_description AS description,
            b.brand_name AS brand,
            c.category_name AS category,
            pi.product_code AS code,
            pi.original_price AS price,
            pi.sale_price AS sale_price,
            co.color_name AS color,
            att.attribute_name AS attribute,
            ao.attribute_option_name AS subAttribute,
            img.img_filename AS image,
            s.size_name AS size,
            pv.quantity_stock AS stock,
            sc.category_name as sizeCategory
            FROM product  p 
            JOIN brand b ON p.brand_id = b.brand_id
            JOIN category c ON p.category_id = c.category_id
            JOIN product_item pi ON p.product_id = pi.product_id
            JOIN color co ON pi.color_id = co.color_id  and pi.product_id = p.product_id
            LEFT JOIN product_attribute pa ON pa.product_id = p.product_id
            JOIN product_variation pv ON pv.product_item_id = pi.product_item_id 
            JOIN size s ON s.size_id = pv.size_id
            LEFT JOIN attribute_option ao ON pa.attribute_option_id = ao.attribute_option_id 
            LEFT JOIN attribute_type att ON ao.attribute_type_id = att.attribute_type_id
            JOIN product_img img ON pi.product_item_id = img.product_item_id
            JOIN size_category sc ON sc.category_id = s.size_category_id
            WHERE p.product_id = :product_id
            ORDER BY p.product_name, pi.product_code
            LIMIT 1 ";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":product_id", $this->product_id);
            $stmt->execute();
            $product = $stmt->fetch();
            return $product;
        } catch (PDOException $error) {
            return $error->getMessage();
        }
    }

    public function get_product_by_category(): array | string
    {
        try {
            $query = " SELECT 
    p.product_id as id,
    p.product_description as description,
    p.product_name AS name,
    b.brand_name AS brand,
    c.category_name AS category,
    pi.product_code AS code,
    pi.original_price AS price,
    pi.sale_price AS salePrice,
    co.color_name AS color,
    GROUP_CONCAT( DISTINCT att.attribute_name) AS attributes,
    GROUP_CONCAT( DISTINCT ao.attribute_option_name) AS subAttributes,
    img.img_filename AS image,
    s.size_name AS size,
    pv.quantity_stock AS stock,
    sc.category_name as sizeCategory
FROM product p
JOIN brand b ON p.brand_id = b.brand_id
JOIN category c ON p.category_id = c.category_id
JOIN product_item pi ON p.product_id = pi.product_id
JOIN color co ON pi.color_id = co.color_id AND pi.product_id = p.product_id
LEFT JOIN product_attribute pa ON pa.product_id = p.product_id
LEFT JOIN attribute_option ao ON pa.attribute_option_id = ao.attribute_option_id
LEFT JOIN attribute_type att ON ao.attribute_type_id = att.attribute_type_id
JOIN product_variation pv ON pv.product_item_id = pi.product_item_id
JOIN size s ON s.size_id = pv.size_id
JOIN product_img img ON pi.product_item_id = img.product_item_id
JOIN size_category sc ON sc.category_id = s.size_category_id
WHERE c.category_name = :category_name
GROUP BY pi.product_code
ORDER BY pi.product_code";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":category_name", $this->category);
            $stmt->execute();
            $products = $stmt->fetchAll();
            return $products;
        } catch (PDOException $error) {
            return $error->getMessage();
        }
    }
    public function get_product_by_brand()
    {
        try {
            $query = "SELECT 
        p.product_name AS Product,
        p.product_description AS description,
        b.brand_name AS Brand,
        pi.original_price AS Price,
        co.color_name AS Color,
        img.img_filename AS Photo
        FROM product  p 
        JOIN brand b ON p.brand_id = b.brand_id
        JOIN product_item pi ON p.product_id = pi.product_id
        JOIN color co ON pi.color_id = co.color_id  and pi.product_id = p.product_id
        JOIN product_img img ON pi.product_item_id = img.product_item_id
        WHERE b.brand_name = :brand_name
        ORDER BY p.product_name, pi.product_code";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":brand_name", $this->brand);
            $stmt->execute();
            $products = $stmt->fetchAll();
            return $products;
        } catch (PDOException $error) {
            return false;
        }
    }

    public function assign_attributes(array $attribute_option_ids): bool
    {
        try {
            $this->conn->beginTransaction();

            $query = "INSERT INTO product_attribute (product_id, attribute_option_id) 
                  VALUES (:product_id, :attribute_option_id)";
            $stmt = $this->conn->prepare($query);

            foreach ($attribute_option_ids as $option_id) {
                $stmt->bindParam(":product_id", $this->product_id);
                $stmt->bindParam(":attribute_option_id", $option_id);
                if (!$stmt->execute()) {
                    $this->conn->rollBack();
                    return false;
                }
            }

            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function get_attributes(int $product_id): array|false
    {
        try {
            $query = " SELECT at.attribute_name, ao.attribute_option_name
                FROM product_attribute pa
                JOIN attribute_option ao ON pa.attribute_option_id = ao.attribute_option_id
                JOIN attribute_type at ON ao.attribute_type_id = at.attribute_type_id
                WHERE pa.product_id = :product_id
            ";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":product_id", $product_id);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function get_product_by_gender(int $gender)
    {
        try {
            $query = " SELECT 
                p.product_id as id,
                p.product_description as description,
                p.product_name AS name,
                b.brand_name AS brand,
                c.category_name AS category,
                pi.product_code AS code,
                pi.original_price AS price,
                pi.sale_price AS salePrice,
                co.color_name AS color,
                att.attribute_name AS attibute,
                ao.attribute_option_name AS subAttribute,
                img.img_filename AS image,
                s.size_name AS size,
                pv.quantity_stock AS stock,
                sc.category_name as sizeCategory
            FROM product  p 
            JOIN brand b ON p.brand_id = b.brand_id
            JOIN category c ON p.category_id = c.category_id
            LEFT JOIN product_item pi ON p.product_id = pi.product_id
            LEFT JOIN color co ON pi.color_id = co.color_id  AND pi.product_id = p.product_id
            LEFT JOIN product_attribute pa ON pa.product_id = p.product_id
            LEFT JOIN product_variation pv ON pv.product_item_id = pi.product_item_id 
            LEFT JOIN size s ON s.size_id = pv.size_id
            LEFT JOIN attribute_option ao ON pa.attribute_option_id = ao.attribute_option_id 
            LEFT JOIN attribute_type att ON ao.attribute_type_id = att.attribute_type_id
            LEFT JOIN product_img img ON pi.product_item_id = img.product_item_id
            LEFT JOIN size_category sc ON sc.category_id = s.size_category_id
            WHERE ao.attribute_option_id = :attribute_option_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":attribute_option_id", $gender);
            $stmt->execute();
            $products = $stmt->fetchAll();
            return $products;
        } catch (PDOException $error) {
            return false;
        }
    }
    public function get_products_with_filters(array $filters): array
{
    try {
        $sql = "SELECT 
            p.product_id as id,
            p.product_description as description,
            p.product_name AS name,
            b.brand_name AS brand,
            c.category_name AS category,
            pi.product_code AS code,
            pi.original_price AS price,
            pi.sale_price AS salePrice,
            co.color_name AS color,
            GROUP_CONCAT(DISTINCT att.attribute_name) AS attributes,
            GROUP_CONCAT(DISTINCT ao.attribute_option_name) AS subAttributes,
            img.img_filename AS image,
            s.size_name AS size,
            pv.quantity_stock AS stock,
            sc.category_name as sizeCategory
        FROM product p
        JOIN brand b ON p.brand_id = b.brand_id
        JOIN category c ON p.category_id = c.category_id
        JOIN product_item pi ON p.product_id = pi.product_id
        JOIN color co ON pi.color_id = co.color_id AND pi.product_id = p.product_id
        JOIN product_variation pv ON pv.product_item_id = pi.product_item_id
        JOIN size s ON s.size_id = pv.size_id
        JOIN product_img img ON pi.product_item_id = img.product_item_id
        JOIN size_category sc ON sc.category_id = s.size_category_id
        ";

        $where = [];
        $params = [];

        if (!empty($filters['material'])) {
            $sql .= " INNER JOIN product_attribute pa_mat ON pa_mat.product_id = p.product_id
                      INNER JOIN attribute_option ao_mat ON pa_mat.attribute_option_id = ao_mat.attribute_option_id
                      INNER JOIN attribute_type att_mat ON ao_mat.attribute_type_id = att_mat.attribute_type_id AND att_mat.attribute_name = 'Material' ";
            $where[] = "ao_mat.attribute_option_id = ?";
            $params[] = $filters['material'];
        }

        if (!empty($filters['style'])) {
            $sql .= " INNER JOIN product_attribute pa_style ON pa_style.product_id = p.product_id
                      INNER JOIN attribute_option ao_style ON pa_style.attribute_option_id = ao_style.attribute_option_id
                      INNER JOIN attribute_type att_style ON ao_style.attribute_type_id = att_style.attribute_type_id AND att_style.attribute_name = 'Style' ";
            $where[] = "ao_style.attribute_option_id = ?";
            $params[] = $filters['style'];
        }

        $sql .= " LEFT JOIN product_attribute pa ON pa.product_id = p.product_id
                  LEFT JOIN attribute_option ao ON pa.attribute_option_id = ao.attribute_option_id
                  LEFT JOIN attribute_type att ON ao.attribute_type_id = att.attribute_type_id ";

        if (!empty($filters['brand'])) {
            $where[] = "b.brand_id = ?";
            $params[] = $filters['brand'];
        }
        if (!empty($filters['color'])) {
            $where[] = "co.color_id = ?";
            $params[] = $filters['color'];
        }
        if (!empty($filters['size'])) {
            $where[] = "s.size_id = ?";
            $params[] = $filters['size'];
        }

        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " GROUP BY pi.product_code ORDER BY pi.product_code";
        

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

    public function check_promotion(): array
    {
        try {
            $sql = "SELECT pr.*, pc.category_id 
                    FROM promotion pr
                    JOIN promo_category pc ON pr.promo_id = pc.promo_id
                    WHERE pc.category_id = ? 
                    AND NOW() BETWEEN pr.start_date AND pr.end_date";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':category_id', $this->category);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            return [];
        }
    }
}





class ProductItem
{
    private $conn;
    private $product_item_id;
    private $product_id;
    private $color_id;
    private $original_price;
    private $sale_price;
    private $product_code;

    public function __construct(PDO $connection)
    {
        $this->conn = $connection;
    }

    

    public function get_product_item_id(): int
    {
        return $this->product_item_id;
    }
    public function get_product_id(): int
    {
        return $this->product_id;
    }
    public function get_color_id(): int
    {
        return $this->color_id;
    }
    public function get_original_price(): float
    {
        return $this->original_price;
    }
    public function get_sale_price(): float
    {
        return $this->sale_price;
    }
    public function get_product_code(): string
    {
        return $this->product_code;
    }

    public function set_product_item_id($id): void
    {
        $this->product_item_id = $id;
    }
    public function set_product_id($id): void
    {
        $this->product_id = $id;
    }
    public function set_color_id($id): void
    {
        $this->color_id = $id;
    }
    public function set_original_price($price): void
    {
        $this->original_price = $price;
    }
    public function set_sale_price($price): void
    {
        $this->sale_price = $price;
    }
    public function set_product_code($code): void
    {
        $this->product_code = $code;
    }

    public function create_productItem(): bool
    {
        try {
            $query = "INSERT INTO product_item (product_id, color_id, original_price, sale_price, product_code)
                      VALUES (:product_id, (SELECT color_id from color WHERE color_name = :color_name), :original_price, :sale_price, :product_code)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":product_id", $this->product_id);
            $stmt->bindParam(":color_name", $this->color_id);
            $stmt->bindParam(":original_price", $this->original_price);
            $stmt->bindParam(":sale_price", $this->sale_price);
            $stmt->bindParam(":product_code", $this->product_code);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function update_productItem(): bool
    {
        try {
            $query = "UPDATE product_item SET color_id = (SELECT color_id from color WHERE color_name = :color_name), original_price = :original_price,
                      sale_price = :sale_price, product_code = :code WHERE product_item_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $this->product_item_id);
            $stmt->bindParam(":color_name", $this->color_id);
            $stmt->bindParam(":original_price", $this->original_price);
            $stmt->bindParam(":sale_price", $this->sale_price);
            $stmt->bindParam(":code", $this->product_code);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete_productItem(): bool
    {
        try {
            $query = "DELETE FROM product_item WHERE product_id =  :product_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":product_id", $this->product_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage()); 
            return false;
        }
    }

    public function get_productItem_by_id(int $id): array|false
    {
        try {
            $query = "SELECT * FROM product_item WHERE product_item_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }
    public function get_product_items_by_product_id(): array
    {
        try {
            $query = "SELECT * FROM product_item WHERE product_id = :product_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':product_id', $this->product_id);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function get_available_colors_for_productId(): array
    {
        try {
            $sql = "SELECT DISTINCT c.color_id, c.color_name 
                    FROM color c
                    JOIN product_item pi ON c.color_id = pi.color_id
                    WHERE pi.product_id = :product_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':product_id', $this->product_id);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    public function get_products_by_order_id($order_id): array|false
    {
        try {
            $query = "SELECT 
                oi.quantity,
                oi.price,
                pi.product_code,
                pi.original_price,
                pi.sale_price,
                p.product_name,
                p.product_description,
                b.brand_name,
                c.category_name,
                co.color_name,
                img.img_filename
            FROM order_item oi
            JOIN product_item pi ON oi.product_item_id = pi.product_item_id
            JOIN product p ON pi.product_id = p.product_id
            JOIN brand b ON p.brand_id = b.brand_id
            JOIN category c ON p.category_id = c.category_id
            JOIN color co ON pi.color_id = co.color_id
            LEFT JOIN product_img img ON pi.product_item_id = img.product_item_id
            WHERE oi.user_order_id = :order_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':order_id', $order_id);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function update_product_code(string $new_code): bool
    {
        try {
            $query = "SELECT product_item_id FROM product_item WHERE product_id = :product_id LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':product_id', $this->product_id);
            $stmt->execute();
            $row = $stmt->fetch();
            if (!$row) {
                return false;
            }
            $product_item_id = $row['product_item_id'];

            $update = "UPDATE product_item SET product_code = :code WHERE product_item_id = :id";
            $stmt2 = $this->conn->prepare($update);
            $stmt2->bindParam(':code', $new_code);
            $stmt2->bindParam(':id', $product_item_id);
            return $stmt2->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}

class ProductImage
{
    private $conn;
    private $img_id;
    private $product_id;
    private $product_item_id;
    private $img_filename;

    public function __construct(PDO $connection)
    {
        $this->conn = $connection;
    }

    public function get_product_img_id()
    {
        return $this->img_id;
    }
    public function get_product_id()
    {
        return $this->product_id;
    }
    public function get_product_item_id()
    {
        return $this->product_item_id;
    }
    public function get_img_url()
    {
        return $this->img_filename;
    }

    public function set_product_img_id($id)
    {
        $this->img_id = $id;
    }
    public function set_product_id($id)
    {
        $this->product_id = $id;
    }
    public function set_product_item_id($id)
    {
        $this->product_item_id = $id;
    }
    public function set_img_url(string $url)
    {
        $this->img_filename = $url;
    }

    public function get_images_by_product_item()
    {
        try {
            $query = "SELECT img_filename FROM product_img 
            WHERE product_item_id = :product_item_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':product_item_id', $this->product_item_id);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function add_image(): bool
    {
        try {
            $query = "INSERT INTO product_img (product_item_id, img_filename) VALUES (:product_item_id, :img_filename)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':product_item_id', $this->product_item_id);
            $stmt->bindParam(':img_filename', $this->img_filename);
            return $stmt->execute();
        } catch (PDOException $e) {
           
            return false;
        }
    }

    public function delete_image(): bool
    {
        try {
            $query = "DELETE FROM product_img WHERE product_item_id = :product_item_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':product_item_id', $this->product_item_id);
            return $stmt->execute();
        } catch (PDOException $e) {
           
            return false;
        }
    }
}


class ProductVariation
{
    private $conn;
    private $product_id;
    private $variation_id;
    private $product_item_id;
    private $size_id;
    private $size_name;
    private $quantity_stock;

    public function __construct(PDO $connection)
    {
        $this->conn = $connection;
    }

    public function get_variation_id()
    {
        return $this->variation_id;
    }
    public function get_product_id()
    {
        return $this->product_id;
    }
    public function get_product_item_id()
    {
        return $this->product_item_id;
    }
    public function get_size_id()
    {
        return $this->size_id;
    }
    public function get_size_name()
    {
        return $this->size_name;
    }
    public function get_quantity_stock()
    {
        return $this->quantity_stock;
    }

    public function set_variation_id($id)
    {
        $this->variation_id = $id;
    }
    public function set_product_id($id)
    {
        $this->product_id = $id;
    }
    public function set_product_item_id($id)
    {
        $this->product_item_id = $id;
    }
    public function set_size_id($id)
    {
        $this->size_id = $id;
    }
    public function set_size_name($name)
    {
        $this->size_name = $name;
    }
    public function set_quantity_stock($quantity)
    {
        $this->quantity_stock = $quantity;
    }

    public function create_variation(): bool
    {
        try {
            $query = "INSERT INTO product_variation (product_item_id, size_id, quantity_stock)
                             VALUES (:product_item, (SELECT size_id from size WHERE size_name = :size_item), :stock)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':product_item', $this->product_item_id);
            $stmt->bindParam(':size_item', $this->size_id);
            $stmt->bindParam(':stock', $this->quantity_stock);
            return $stmt->execute();
        } catch (PDOException $e) {
           
            return false;
        }
    }

    public function update_stock(): bool
    {
        try {
            $query = " UPDATE product_variation
                SET quantity_stock = :stock WHERE variation_id = :variation ";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':stock', $this->quantity_stock);
            $stmt->bindParam(':variation',  $this->variation_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete_product_variation()
    {
        try {
            $query  = "DELETE FROM product_variation WHERE product_item_id
             IN (SELECT product_item_id FROM product_item WHERE product_id = :product_id)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':product_id', $this->product_id);
            return $stmt->execute();
        } catch (PDOException $e) {
          
            return false;
        }
    }

    public function get_lower_stock(): array|false
    {
        try {
            $query = " SELECT * FROM product_variation WHERE product_item_id = :id
                ORDER BY quantity_stock ASC LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $this->product_item_id);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }
    public function check_available_size(): int|false
    {
        try {
            $query = "SELECT quantity_stock FROM product_variation WHERE product_item_id =
             (SELECT product_item_id FROM product_item 
             WHERE product_id = :product_id) AND size_id = (SELECT size_id FROM size where size_name = :name)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':product_id', $this->product_id);
            $stmt->bindParam(':name', $this->size_name);
            $stmt->execute();
            $result = $stmt->fetchColumn();
            if ($result !== false && $result !== null) {
                return (int)$result;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            return false;
        }
    }

    public function get_available_sizes(): array
    {
        try {
            $query = "SELECT s.size_id, s.size_name, v.quantity_stock
                FROM product_variation v
                JOIN size s ON v.size_id = s.size_id
                WHERE v.product_item_id = :item_id AND v.quantity_stock > 0
                ORDER BY s.sort_order ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':item_id', $this->product_item_id);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}
class ProductAttribute
{
    private PDO $conn;
    private $product_id;
    private $attribute_option_id;

    public function __construct(PDO $connection)
    {
        $this->conn = $connection;
    }

    public function get_product_id(): int
    {
        return $this->product_id;
    }

    public function get_attribute_option_id(): int
    {
        return $this->attribute_option_id;
    }

    public function set_product_id($id): void
    {
        $this->product_id = $id;
    }

    public function set_attribute_option_id($id): void
    {
        $this->attribute_option_id = $id;
    }

    public function create_product_attr(): bool
    {
        try {
            $sql = "INSERT INTO product_attribute (product_id, attribute_option_id) 
                    VALUES (:product_id, (SELECT attribute_option_id from attribute_option WHERE attribute_option_name = :attribute_option_id ))";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':product_id', $this->product_id);
            $stmt->bindParam(':attribute_option_id', $this->attribute_option_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            echo $e->getMessage() . 'create_product_attr';
            return false;
        }
    }
    public function delete_product_attr(): bool
    {
        try {
            $stmt = $this->conn->prepare("DELETE FROM product_attribute WHERE product_id = :product_id");
            $stmt->bindParam(':product_id', $this->product_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            
            return false;
        }
    }

    public function get_attr_of_Aproduct(int $product_id): array|false
    {
        try {
            $sql = "SELECT ao.attribute_option_id, ao.attribute_option_name, at.attribute_type_name
                    FROM product_attribute pa
                    JOIN attribute_option ao ON pa.attribute_option_id = ao.attribute_option_id
                    JOIN attribute_type at ON ao.attribute_type_id = at.attribute_type_id
                    WHERE pa.product_id = :product_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':product_id', $product_id);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function filter_products_by_attributes(array $attribute_option_ids): array|false
    {
        if (empty($attribute_option_ids)) return false;

        try {
            $placeholders = implode(',', array_fill(0, count($attribute_option_ids), '?'));
            $sql = "SELECT DISTINCT pa.product_id
                    FROM product_attribute pa
                    WHERE pa.attribute_option_id IN ($placeholders)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($attribute_option_ids);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return false;
        }
    }
}
