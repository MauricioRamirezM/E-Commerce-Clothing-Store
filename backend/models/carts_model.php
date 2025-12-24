<?php 

class Cart {
    private $conn;
    private $cart_id;
    private $user_id;

    public function __construct(PDO $connection) {
        $this->conn = $connection;
    }

    public function get_cart_id() { return $this->cart_id; }
    public function get_user_id() { return $this->user_id; }
    
    public function set_cart_id(int $id) { $this->cart_id = $id; }
    public function set_user_id(int $id) { $this->user_id = $id; }

    public function get_cart_by_user(): array|false {
        try {
            $sql = "SELECT * FROM cart WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':user_id', $this->user_id);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function create_cart_for_user(): bool {
        try {
            $sql = "INSERT INTO cart (user_id) VALUES (:user_id)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':user_id', $this->user_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function clear_cart(): bool {
        try {
            $sql = "DELETE FROM cart_item WHERE cart_id = :cart_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':cart_id', $this->cart_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

}

class CartItem {
    private $conn;
    private $cart_item_id;
    private $cart_id;
    private $product_item_id;
    private $quantity;

    public function __construct(PDO $connection) {
        $this->conn = $connection;
    }

    public function get_cart_item_id() { return $this->cart_item_id; }
    public function get_cart_id() { return $this->cart_id; }
    public function get_product_item_id() { return $this->product_item_id; }
    public function get_quantity() { return $this->quantity; }
    
    public function set_cart_item_id(int $id) { $this->cart_item_id = $id; }
    public function set_cart_id(int $id) { $this->cart_id = $id; }
    public function set_product_item_id(int $id) { $this->product_item_id = $id; }
    public function set_quantity(int $quantity) { $this->quantity = $quantity; }

    public function get_cart_items(int $cart_id): array|false {
        try {
            $sql = "SELECT * FROM cart_item WHERE cart_id = :cart_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':cart_id', $cart_id);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function add_to_cart(): bool {
        try {
            $sql = "INSERT INTO cart_item (cart_id, product_item_id, quantity)
                    VALUES (:cart_id, :product_item_id, :quantity)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':cart_id', $this->cart_id);
            $stmt->bindParam(':product_item_id', $this->product_item_id);
            $stmt->bindParam(':quantity', $this->quantity);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function update_cart_quantity(): bool {
        try {
            $sql = "UPDATE cart_item 
                    SET quantity = :quantity 
                    WHERE cart_id = :cart_id AND product_item_id = :product_item_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':quantity', $this->quantity);
            $stmt->bindParam(':cart_id', $this->cart_id);
            $stmt->bindParam(':product_item_id', $this->product_item_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function remove_from_cart(): bool {
        try {
            $sql = "DELETE FROM cart_item 
                    WHERE cart_id = :cart_id AND product_item_id = :product_item_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':cart_id', $this->cart_id);
            $stmt->bindParam(':product_item_id', $this->product_item_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function get_cart_total(): float|false {
        try {
            $sql = "SELECT SUM(ci.quantity * pi.price) AS total
                    FROM cart_item ci
                    JOIN product_item pi ON ci.product_item_id = pi.product_item_id
                    WHERE ci.cart_id = :cart_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':cart_id', $this->cart_id);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result ? (float)$result['total'] : 0;
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>