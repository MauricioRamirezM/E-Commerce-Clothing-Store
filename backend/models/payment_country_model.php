<?php

class PaymentType {
    private $conn;
    private $payment_type_id;
    private $payment_type_name;

    public function __construct(PDO $connection) {
        $this->conn = $connection;
    }

    public function get_payment_type_id() { return $this->payment_type_id; }
    public function get_payment_type_name() { return $this->payment_type_name; }

    public function set_payment_type_id( $id) { $this->payment_type_id = $id; }
    public function set_payment_type_name( $name) { $this->payment_type_name = $name; }

    public function create_type(): bool {
        try {
            $sql = "INSERT INTO payment_type (payment_type_name) 
                    VALUES (:name)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':name', $this->payment_type_name);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete_type(): bool {
        try {
            $sql = "DELETE FROM payment_type 
                    WHERE payment_type_id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $this->payment_type_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function get_type_by_id(int $id): array|false {
        try {
            $sql = "SELECT * FROM payment_type 
                    WHERE payment_type_id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function get_all_types(): array|false {
        try {
            $sql = "SELECT * FROM payment_type";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return false;
        }
    }
}


class PaymentMethod {
    private $conn;
    private $method_id;
    private $user_id;
    private $payment_type_id;
    private $card_number;
    private $expiry_date;
    private $is_default;

    public function __construct(PDO $connection) {
        $this->conn = $connection;
    }

    public function get_method_id() { return $this->method_id; }
    public function get_user_id() { return $this->user_id; }
    public function get_payment_type_id() { return $this->payment_type_id; }
    public function get_card_number() { return $this->card_number; }
    public function get_expiry_date() { return $this->expiry_date; }
    public function get_is_default() { return $this->is_default; }

    public function set_method_id(int $id) { $this->method_id = $id; }
    public function set_user_id(int $id) { $this->user_id = $id; }
    public function set_payment_type_id(int $id) { $this->payment_type_id = $id; }
    public function set_card_number(string $number) { $this->card_number = $number; }
    public function set_expiry_date( $date) { $this->expiry_date = $date; }
    public function set_is_default(bool $default) { $this->is_default = $default; }

    public function create_method(): bool {
        try {
            $sql = "INSERT INTO payment_method 
                    (user_id, payment_type_id, card_number, expiry_date, is_default) 
                    VALUES (:user_id, :type_id, :card, :expiry, :default)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':user_id', $this->user_id);
            $stmt->bindParam(':type_id', $this->payment_type_id);
            $stmt->bindParam(':card', $this->card_number);
            $stmt->bindParam(':expiry', $this->expiry_date);
            $stmt->bindParam(':default', $this->is_default);
            return $stmt->execute();
        } catch (PDOException $e) {
            echo $e->getMessage() . 'the paym,ent method breaks';
            return false;
        }
    }

    public function update_method(): bool {
        if (!$this->method_id) return false;
        try {
            $sql = "UPDATE payment_method 
                    SET payment_type_id = :type_id, card_number = :card, 
                        expiry_date = :expiry, is_default = :default 
                    WHERE method_id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':type_id', $this->payment_type_id);
            $stmt->bindParam(':card', $this->card_number);
            $stmt->bindParam(':expiry', $this->expiry_date);
            $stmt->bindParam(':default', $this->is_default);
            $stmt->bindParam(':id', $this->method_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete_method(): bool {
        try {
            $sql = "DELETE FROM payment_method 
                    WHERE method_id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $this->method_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function get_method_by_user_id(): array|false {
        try {
            $sql = "SELECT * FROM payment_method 
                    WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':user_id', $this->user_id);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function get_default(): array|false {
        try {
            $sql = "SELECT * FROM payment_method 
                    WHERE user_id = :user_id AND is_default = 1 LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':user_id', $this->user_id);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function set_default(): bool {
        try {
            $this->conn->beginTransaction();

            $sql = "UPDATE payment_method 
                    SET is_default = 0 
                    WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':user_id', $this->user_id);
            $stmt->execute();

            $sql2 = "UPDATE payment_method 
                     SET is_default = 1 
                     WHERE method_id = :id AND user_id = :user_id";
            $stmt2 = $this->conn->prepare($sql2);
            $stmt2->bindParam(':id', $this->method_id);
            $stmt2->bindParam(':user_id', $this->user_id);
            $success = $stmt2->execute();

            $this->conn->commit();
            return $success;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return false;
        }
    }
}

Class Country {
    private $conn;
    private $country_id;
    private $country_name;

    public function __construct(PDO $connection) {
        $this->conn = $connection;
    }

    public function get_country_id() { return $this->country_id; }
    public function get_country_name() { return $this->country_name; }

    public function set_country_id( $id) { $this->country_id = $id; }
    public function set_country_name($name) { $this->country_name = $name; }

    public function create_country():bool{
        try {
            $sql = "INSERT INTO country (country_name) 
                    VALUES (:name)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':name', $this->country_name);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
    public function delete_country(){
        try {
            $sql = "DELETE FROM country 
                    WHERE country_id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $this->country_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
    
     public function get_country_by_id(int $id): array|false {
        try {
            $sql = "SELECT * FROM country 
                    WHERE country_id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

     public function get_all_countries(): array|false {
        try {
            $sql = "SELECT * FROM country";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>