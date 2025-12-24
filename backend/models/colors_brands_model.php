<?php

class Color {
    private $conn;
    private $color_id;
    private $color_name;

    public function __construct(PDO $connection) {
        $this->conn = $connection;
    }

    public function get_color_id() { return $this->color_id; }
    public function get_color_name() { return $this->color_name; }
    
    public function set_color_id(int $id) { $this->color_id = $id; }
    public function set_color_name(string $name) { $this->color_name = $name; }

      public function create_color(): bool {
        try {
            $query = "INSERT INTO color (color_name)
                             VALUES (:color_name)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':color_name', $this->color_name);
            return $stmt->execute();
        } catch (PDOException $e) {
            echo ($e->getMessage());
            return false;
        }
    }

    public function update_color(): bool {
        try {
            $query = "UPDATE color SET color_name = :color_name";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':color_name', $this->color_name);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete_color(): bool {
        try {
            $query = "DELETE FROM color WHERE color_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $this->color_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function get_all_colors(): array {
        try {
            $query = "SELECT * FROM color ORDER BY color_name ASC";
            $stmt = $this->conn->query($query);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function get_color_by_id(): array|false {
        try {
            $query = "SELECT * FROM color WHERE color_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $this->color_id);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }
    public function get_color_id_by_name(): int|false {
        try {
            $query = "SELECT color_id FROM color WHERE color_name = :name";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $this->color_name);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            return false;
        }
    }
}

class Brand {
    private $conn;
    private $brand_id;
    private $brand_name;
    private $brand_description;

    public function __construct(PDO $connection) {
        $this->conn = $connection;
    }

    public function get_brand_id(): int { return $this->brand_id; }
    public function get_brand_name(): string { return $this->brand_name; }
    public function get_brand_description(): string { return $this->brand_description; }

    public function set_brand_id($id): void { $this->brand_id = $id; }
    public function set_brand_name(string $name): void { $this->brand_name = $name; }
    public function set_brand_description(string $description): void { $this->brand_description = $description; }

    public function create_brand(): bool {
        try {
            $query = "INSERT INTO brand (brand_name, brand_description) 
                            VALUES (:name, :description)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $this->brand_name);
            $stmt->bindParam(':description', $this->brand_description);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
    public function update_brand(): bool {
        try {
            $query = "UPDATE brand SET brand_name = :name, brand_description = :description WHERE brand_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $this->brand_name);
            $stmt->bindParam(':description', $this->brand_description);
            $stmt->bindParam(':id', $this->brand_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete_brand(): bool {
        try {
            $query = "DELETE FROM brand WHERE brand_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $this->brand_id);
            return $stmt->execute();
        } catch (PDOException $e) {
             echo $e->getMessage();
            return false;
        }
    }

    public function get_all_brands(): array|false {
        try {
            $query = "SELECT * FROM brand";
            $stmt = $this->conn->query($query);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function get_by_id(): array|false {
        try {
            $query = "SELECT * FROM brand WHERE brand_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $this->brand_id);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }
    public function get_brand_id_by_name(): int|false {
        try {
            $query = "SELECT brand_id FROM brand WHERE brand_name = :name";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $this->brand_name);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            return false;
        }
    }
}



?>