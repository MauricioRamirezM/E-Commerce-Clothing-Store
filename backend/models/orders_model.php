<?php
class UserOrder {
    private $conn;
    private $user_order_id;
    private $user_id;
    private $order_date;
    private $payment_method_id;
    private $address_id;
    private $order_status_id;
    private $order_total;

    public function __construct(PDO $connection) {
        $this->conn = $connection;
    }

    public function get_user_order_id() { return $this->user_order_id; }
    public function get_user_id() { return $this->user_id; }
    public function get_order_date() { return $this->order_date; }
    public function get_payment_method_id() { return $this->payment_method_id; }
    public function get_address_id() { return $this->address_id; }
    public function get_order_status_id() { return $this->order_status_id; }
    public function get_order_total() { return $this->order_total; }
    
    public function set_user_order_id(int $id) { $this->user_order_id = $id; }
    public function set_user_id(int $id) { $this->user_id = $id; }
    public function set_order_date(string $date) { $this->order_date = $date; }
    public function set_payment_method_id(int $id) { $this->payment_method_id = $id; }
    public function set_address_id(int $id) { $this->address_id = $id; }
    public function set_order_status_id(int $id) { $this->order_status_id = $id; }
    public function set_order_total(float $total) { $this->order_total = $total; }

    public function create_order(): bool {
        try {
            $sql = "INSERT INTO user_order 
                (user_id, order_date, payment_method_id, address_id, order_status_id, order_total) 
                VALUES (:user_id, :order_date, :payment_method_id, :address_id, :order_status_id, :order_total)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':user_id', $this->user_id);
            $stmt->bindParam(':order_date', $this->order_date);
            $stmt->bindParam(':payment_method_id', $this->payment_method_id);
            $stmt->bindParam(':address_id', $this->address_id);
            $stmt->bindParam(':order_status_id', $this->order_status_id);
            $stmt->bindParam(':order_total', $this->order_total);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function update_order_status(): bool {
        try {
            $sql = "UPDATE user_order 
                SET order_status_id = :status_id 
                WHERE user_order_id = :order_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':status_id', $this->order_status_id);
            $stmt->bindParam(':order_id', $this->user_order_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function cancel_order(): bool {
        try {
            $sql = "UPDATE user_order 
                SET order_status_id = (SELECT order_status_id FROM order_status WHERE status_name = 'Canceled' LIMIT 1) 
                WHERE user_order_id = :order_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':order_id', $this->user_order_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function get_order_by_id(): array|false {
        try {
            $sql = "SELECT * FROM user_order 
                WHERE user_order_id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $this->user_order_id);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function get_orders_by_user(): array|false {
        try {
            $sql = "SELECT
    uo.user_order_id AS order_number,
    uo.order_date AS date,
    pt.payment_type_name AS payment_method,
    CONCAT(ad.street, ' ', ad.ext_number, IFNULL(CONCAT(' Int ', ad.int_number), ''), ', ', ad.city, ', ', ad.state, ', ', ad.cp, ', ', c.country_name) AS address,
    os.status_name AS status,
    uo.order_total AS total
FROM user_order uo
JOIN payment_method pm ON uo.payment_method_id = pm.method_id
JOIN payment_type pt ON pm.payment_type_id = pt.payment_type_id
JOIN address ad ON uo.address_id = ad.address_id
JOIN country c ON ad.country_id = c.country_id
JOIN order_status os ON uo.order_status_id = os.status_id
WHERE uo.user_id = :user_id
ORDER BY uo.order_date DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':user_id', $this->user_id);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function get_order_total_calculated(): float|false {
        try {
            $sql = "SELECT SUM(oi.quantity * pi.price) as total
                    FROM order_item oi
                    JOIN product_item pi ON oi.product_item_id = pi.product_item_id
                    WHERE oi.user_order_id = :order_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':order_id', $this->user_order_id);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result ? floatval($result['total']) : false;
        } catch (PDOException $e) {
            return false;
        }
    }
}

class OrderStatus {
    private $conn;
    private $status_id;
    private $status_name;

    public function __construct(PDO $connection) {
        $this->conn = $connection;
    }

    public function get_status_id() { return $this->status_id; }
    public function get_status_name() { return $this->status_name; }
    
    public function set_status_id(int $id) { $this->status_id = $id; }
    public function set_status_name(string $name) { $this->status_name = $name; }

    public function add_status(): bool {
        try {
            $sql = "INSERT INTO order_status (status_name) VALUES (:name)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':name', $this->status_name);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function update_status(): bool {
        try {
            $sql = "UPDATE order_status SET status_name = :name WHERE status_id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':name', $this->status_name);
            $stmt->bindParam(':id', $this->status_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete_status(): bool {
        try {
            $sql = "DELETE FROM order_status WHERE status_id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $this->status_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function get_all_statuses(): array {
        try {
            $sql = "SELECT * FROM order_status ORDER BY status_id ASC";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
}

class OrderItem {
    private $conn;
    private $order_item_id;
    private $product_item_id;
    private $user_order_id;
    private $price;
    private $quantity;

    public function __construct(PDO $connection) {
        $this->conn = $connection;
    }

    public function get_order_item_id() { return $this->order_item_id; }
    public function get_product_item_id() { return $this->product_item_id; }
    public function get_user_order_id() { return $this->user_order_id; }
    public function get_price() { return $this->price; }
    public function get_quantity() { return $this->quantity; }
    
    public function set_order_item_id(int $id) { $this->order_item_id = $id; }
    public function set_product_item_id(int $id) { $this->product_item_id = $id; }
    public function set_user_order_id(int $id) { $this->user_order_id = $id; }
    public function set_price(float $price) { $this->price = $price; }
    public function set_quantity(int $quantity) { $this->quantity = $quantity; }

    public function add_order_item(): bool {
        try {
            $sql = "INSERT INTO order_item 
                (product_item_id, user_order_id, price, quantity) 
                VALUES (:product_item_id, :user_order_id, :price, :quantity)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':product_item_id', $this->product_item_id);
            $stmt->bindParam(':user_order_id', $this->user_order_id);
            $stmt->bindParam(':price', $this->price);
            $stmt->bindParam(':quantity', $this->quantity);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function update_quantity(): bool {
        try {
            $sql = "UPDATE order_item 
                SET quantity = :quantity 
                WHERE order_item_id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':quantity', $this->quantity);
            $stmt->bindParam(':id', $this->order_item_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function remove_order_item(): bool {
        try {
            $sql = "DELETE FROM order_item 
                WHERE order_item_id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $this->order_item_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function get_items_by_order(): array|false {
        try {
            $sql = "SELECT oi.*, pi.name AS product_name
                    FROM order_item oi
                    JOIN product_item pi ON oi.product_item_id = pi.product_item_id
                    WHERE oi.user_order_id = :order_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':order_id', $this->user_order_id);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return false;
        }
    }
}


?>