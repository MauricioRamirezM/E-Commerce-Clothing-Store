<?php

class Promotion {
    private $conn;
    private $promo_id;
    private $promo_name;
    private $promo_description;
    private $discount_porcent;
    private $start_date;
    private $end_date;

    public function __construct(PDO $connection) {
        $this->conn = $connection;
    }

    public function get_promo_id() { return $this->promo_id; }
    public function get_promo_name() { return $this->promo_name; }
    public function get_promo_description() { return $this->promo_description; }
    public function get_discount_porcent() { return $this->discount_porcent; }
    public function get_start_date() { return $this->start_date; }
    public function get_end_date() { return $this->end_date; }

    public function set_promo_id($id) { $this->promo_id = $id; }
    public function set_promo_name(string $name) { $this->promo_name = $name; }
    public function set_promo_description(string $description) { $this->promo_description = $description; }
    public function set_discount_porcent(float $percent) { $this->discount_porcent = $percent; }
    public function set_start_date(string $date) { $this->start_date = $date; }
    public function set_end_date(string $date) { $this->end_date = $date; }

    public function create_promo(): bool {
        try {
            $sql = "INSERT INTO promotion (promo_name, promo_description, discount_porcent, start_date, end_date) 
                    VALUES (:name, :description, :discount, :start, :end)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':name', $this->promo_name);
            $stmt->bindParam(':description', $this->promo_description);
            $stmt->bindParam(':discount', $this->discount_porcent);
            $stmt->bindParam(':start', $this->start_date);
            $stmt->bindParam(':end', $this->end_date);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function update_promo(): bool {
        if (!$this->promo_id) return false;
        try {
            $sql = "UPDATE promotion 
                    SET promo_name = :name, promo_description = :description, discount_porcent = :discount,
                        start_date = :start, end_date = :end
                    WHERE promo_id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':name', $this->promo_name);
            $stmt->bindParam(':description', $this->promo_description);
            $stmt->bindParam(':discount', $this->discount_porcent);
            $stmt->bindParam(':start', $this->start_date);
            $stmt->bindParam(':end', $this->end_date);
            $stmt->bindParam(':id', $this->promo_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete_promo(): bool {
        if (!$this->promo_id) return false;
        try {
            $sql = "DELETE FROM promotion WHERE promo_id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $this->promo_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function get_all_promotions(){
        try {
             $sql = "SELECT * FROM promotion";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            return false;
        }
    }

    public function get_promotion_by_id(): array|false {
        try {
            $sql = "SELECT * FROM promotion WHERE promo_id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $this->promo_id);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function calculate_porcent(float $price): float {
        return round($price - ($price * $this->discount_porcent / 100), 2);
    }

    public function apply_promotion(float $price): float {
        $today = date('Y-m-d');
        if ($today >= $this->start_date && $today <= $this->end_date) {
            return $this->calculate_porcent($price);
        }
        return $price;
    }
}

class PromoCategory {
    private $conn;
    private $category_id;
    private $promo_id;

    public function __construct(PDO $connection) {
        $this->conn = $connection;
    }

    public function get_category_id() { return $this->category_id; }
    public function get_promo_id() { return $this->promo_id; }

    public function set_category_id(int $id) { $this->category_id = $id; }
    public function set_promo_id(int $id) { $this->promo_id = $id; }

    public function create_promo_category(): bool {
        try {
            $sql = "INSERT INTO promo_category (category_id, promo_id) 
                    VALUES (:category_id, :promo_id)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':category_id', $this->category_id);
            $stmt->bindParam(':promo_id', $this->promo_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function update_promo_category(): bool {
        try {
            $sql = "UPDATE promo_category 
                    SET promo_id = :promo_id 
                    WHERE category_id = :category_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':promo_id', $this->promo_id);
            $stmt->bindParam(':category_id', $this->category_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete_promo_category(): bool {
        try {
            $sql = "DELETE FROM promo_category 
                    WHERE category_id = :category_id AND promo_id = :promo_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':category_id', $this->category_id);
            $stmt->bindParam(':promo_id', $this->promo_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function get_promo_by_category(int $category_id): array|false {
        try {
            $sql = "SELECT p.* FROM promotion p
                    JOIN promo_category pc ON pc.promo_id = p.promo_id
                    WHERE pc.category_id = :category_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':category_id', $category_id);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function get_categories_by_promo(int $promo_id): array|false {
        try {
            $sql = "SELECT c.* FROM category c
                    JOIN promo_category pc ON pc.category_id = c.category_id
                    WHERE pc.promo_id = :promo_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':promo_id', $promo_id);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return false;
        }
    }
}

?>