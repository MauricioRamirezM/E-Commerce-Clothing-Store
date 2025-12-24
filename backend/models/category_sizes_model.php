<?php

class Category
{
    private PDO $conn;
    private int $category_id;
    private string $category_name;
    private string $category_description;
    private string $category_img;
    private int $size_category_id;
    private int $parent_category_id;

    public function __construct(PDO $connection)
    {
        $this->conn = $connection;
    }

    public function get_category_id(): int
    {
        return $this->category_id;
    }
    public function get_category_name(): string
    {
        return $this->category_name;
    }
    public function get_category_description(): string
    {
        return $this->category_description;
    }
    public function get_category_img(): string
    {
        return $this->category_img;
    }
    public function get_size_category_id(): int
    {
        return $this->size_category_id;
    }
    public function get_parent_category_id(): int
    {
        return $this->parent_category_id;
    }

    public function set_category_id(int $id): void
    {
        $this->category_id = $id;
    }
    public function set_category_name(string $name): void
    {
        $this->category_name = $name;
    }
    public function set_category_description(string $description): void
    {
        $this->category_description = $description;
    }
    public function set_category_img(string $img): void
    {
        $this->category_img = $img;
    }
    public function set_size_category_id(int $id): void
    {
        $this->size_category_id = $id;
    }
    public function set_parent_category_id(int $id): void
    {
        $this->parent_category_id = $id;
    }

    public function create_category()
    {
        try {
            $query = "INSERT INTO category (category_name, category_description, category_img, size_category_id, parent_category_id)
                VALUES (:name, :description, :img, :size_cat_id, :parent_id)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $this->category_name);
            $stmt->bindParam(':description', $this->category_description);
            $stmt->bindParam(':img', $this->category_img);
            $stmt->bindParam(':size_cat_id', $this->size_category_id);
            $stmt->bindParam(':parent_id', $this->parent_category_id);
            return $stmt->execute();
        } catch (PDOException $error) {
            return $error->getMessage();
        }
    }

    public function update_category($imgPath)
    {
        try {
            // 1. Update basic fields
            $query = "UPDATE category SET 
                    category_name = :name,
                    category_description = :description, 
                    size_category_id = :size_id,
                    parent_category_id = :parent_id
                  WHERE category_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $this->category_id);
            $stmt->bindParam(':name', $this->category_name);
            $stmt->bindParam(':description', $this->category_description);
            $stmt->bindParam(':size_id', $this->size_category_id);
            $stmt->bindParam(':parent_id', $this->parent_category_id);
            $stmt->execute();

            if (!empty($this->category_img)) {
                $query = "SELECT category_img FROM category WHERE category_id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':id', $this->category_id);
                $stmt->execute();
                $image_info = $stmt->fetch();

                if (isset($image_info['category_img']) && $image_info['category_img'] && file_exists($imgPath . $image_info['category_img'])) {
                    unlink($imgPath . $image_info['category_img']);
                }

                $query = "UPDATE category SET category_img = :image WHERE category_id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':image', $this->category_img);
                $stmt->bindParam(':id', $this->category_id);
                if (!$stmt->execute()) return false;
            }
            return true;
        } catch (PDOException $error) {
            return $error->getMessage();
        }
    }

    public function delete_category(): bool
    {
        try {
            $query = "DELETE FROM category WHERE category_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $this->category_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }



    public function get_all_categories(): array|false
    {
        try {
            $query = "SELECT 
                c.category_id,
                c.category_name,
                c.category_description,
                sc.category_name as size
            FROM category c
            LEFT JOIN size_category sc ON sc.category_id = c.size_category_id
            WHERE c.category_id > 3";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return false;
        }
    }
    public function get_categories_names(): array|false
    {
        try {
            $query = "SELECT category_name FROM category;";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return false;
        }
    }


    public function get_subcategories(int $parent_id): array|false
    {
        try {
            $query = "SELECT * FROM category WHERE parent_category_id = :parent_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':parent_id', $this->parent_category_id,);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function get_category_by_id(): array|false
    {
        try {
            $query = "SELECT * FROM category WHERE category_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $this->category_id);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }
}

class Size
{
    private PDO $conn;
    private int $size_id;
    private int $size_category_id;
    private string $size_name;
    private int $sort_order;

    public function __construct(PDO $connection)
    {
        $this->conn = $connection;
    }

    public function get_size_id(): int
    {
        return $this->size_id;
    }
    public function set_size_id(int $id): void
    {
        $this->size_id = $id;
    }

    public function get_size_category_id(): int
    {
        return $this->size_category_id;
    }
    public function set_size_category_id(int $id): void
    {
        $this->size_category_id = $id;
    }

    public function get_size_name(): string
    {
        return $this->size_name;
    }
    public function set_size_name(string $name): void
    {
        $this->size_name = $name;
    }

    public function get_sort_order(): int
    {
        return $this->sort_order;
    }
    public function set_sort_order(int $order): void
    {
        $this->sort_order = $order;
    }

    public function create_size(): bool
    {
        try {
            $query = "INSERT INTO size (size_category_id, size_name, sort_order)
                      VALUES (:cat_id, :name, :sort_order)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':cat_id', $this->size_category_id);
            $stmt->bindParam(':name', $this->size_name);
            $stmt->bindParam(':sort_order', $this->sort_order);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete_size(): bool
    {
        try {
            $query = "DELETE FROM size WHERE size_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $this->size_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function get_size_by_Id(): array|false
    {
        try {
            $sql = "SELECT * FROM size WHERE size_id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $this->size_id);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function get_size_id_by_name(): int|false
    {
        try {
            $sql = "SELECT size_id FROM size WHERE size_name = :name";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':name', $this->size_name);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function get_size_by_categoryId(int $category_id): array
    {
        try {
            $sql =  "SELECT * FROM size WHERE size_category_id = :cat_id ORDER BY sort_order ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':cat_id', $category_id);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    public function get_clothing_sizes(): array
    {
        try {
            $sql = "SELECT 
                s.size_name AS size,
                sc.category_name AS size_category
                FROM size s
                JOIN size_category sc ON s.size_category_id = sc.category_id
                WHERE s.size_category_id = 1
            ORDER BY sort_order ASC";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    public function get_shoes_sizes(): array
    {
        try {
            $sql = "SELECT 
                s.size_name AS size,
                sc.category_name AS size_category
                FROM size s
                JOIN size_category sc ON s.size_category_id = sc.category_id
                WHERE s.size_category_id = 2
            ORDER BY sort_order ASC";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}

class SizeCategory
{
    private $conn;
    private $category_id;
    private $category_name;

    public function __construct(PDO $connection)
    {
        $this->conn = $connection;
    }

    public function get_category_id(): int
    {
        return $this->category_id;
    }
    public function set_category_id(int $id): void
    {
        $this->category_id = $id;
    }

    public function get_category_name(): string
    {
        return $this->category_name;
    }
    public function set_category_name(string $name): void
    {
        $this->category_name = $name;
    }

    public function create_sizeCategory(): bool
    {
        try {
            $sql = "INSERT INTO size_category (category_name) VALUES (:name)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':name', $this->category_name);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete_sizeCategory(): bool
    {
        try {
            $sql = "DELETE FROM size_category WHERE category_id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $this->category_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function get_sizeCategory_ById(): array|false
    {
        try {
            $sql = "SELECT * FROM size_category WHERE category_id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $this->category_id);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function get_all_sizeCategory(): array
    {
        try {
            $sql = "SELECT * FROM size_category ORDER BY category_name ASC";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}
