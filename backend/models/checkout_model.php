<?php

class CheckoutService
{
    private PDO $conn;
    private int $user_id;
    private int $address_id;
    private int $payment_method_id;

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    public function setUser(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    public function setAddress(int $address_id): void
    {
        $this->address_id = $address_id;
    }

    public function setPaymentMethod(int $payment_method_id): void
    {
        $this->payment_method_id = $payment_method_id;
    }

    public function processCheckout($shoppingcart): bool
    {
        try {
            $this->conn->beginTransaction();

            if (empty($shoppingcart)) {
                throw new Exception("Cart is empty.");
            }

            $total = $this->calculateTotal($shoppingcart);

            $orderQuery = "INSERT INTO user_order (user_id, order_date, payment_method_id, address_id, order_status_id, order_total)
                           VALUES (:user_id, NOW(), :payment_method_id, :address_id, 1, :total)";
            $stmt = $this->conn->prepare($orderQuery);
            $stmt->bindParam(":user_id", $this->user_id);
            $stmt->bindParam(":payment_method_id", $this->payment_method_id);
            $stmt->bindParam(":address_id", $this->address_id);
            $stmt->bindParam(":total", $total);
            $stmt->execute();
            $order_id = $this->conn->lastInsertId();
            foreach ($shoppingcart as $item) {
                $itemIdQuery = "SELECT product_item_id FROM product_item WHERE product_id = :product_id";
                $itemIdStmt = $this->conn->prepare($itemIdQuery);
                $itemIdStmt->bindParam(":product_id", $item['product_id']);
                $itemIdStmt->execute();
                $product_item_ids = $itemIdStmt->fetchAll(PDO::FETCH_COLUMN);

                foreach ($product_item_ids as $product_item_id) {
                    $itemQuery = "INSERT INTO order_item (product_item_id, user_order_id, price, quantity)
                      VALUES (:item_id, :order_id, :price, :qty)";
                    $stmt = $this->conn->prepare($itemQuery);
                    $stmt->bindParam(":item_id", $product_item_id);
                    $stmt->bindParam(":order_id", $order_id);
                    $clean_price = floatval(str_replace(['€', ' '], '', $item['price']));
                    $stmt->bindParam(":price",$clean_price);
                    $stmt->bindParam(":qty", $item['quantity']);
                    $stmt->execute();

                    $updateStockQuery = "UPDATE product_variation SET quantity_stock = quantity_stock - :qty WHERE product_item_id = :item_id";
                    $updateStockStmt = $this->conn->prepare($updateStockQuery);
                    $updateStockStmt->bindParam(":qty", $item['quantity']);
                    $updateStockStmt->bindParam(":item_id", $product_item_id);
                    $updateStockStmt->execute();
                }
            }


            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            echo $e->getMessage() . 'proces cheak out breaks';
            return false;
        }
    }

    public function validateCartItems(): array
    {
        $query = "SELECT ci.product_item_id, ci.quantity, pv.quantity_stock
                  FROM cart_item ci
                  JOIN cart c ON ci.cart_id = c.cart_id
                  JOIN product_variation pv ON ci.product_item_id = pv.product_item_id
                  WHERE c.user_id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getCartSummary(): array|false
    {
        $query = "SELECT ci.product_item_id, ci.quantity, pi.sale_price, pi.original_price, p.product_name, c.color_name
                  FROM cart_item ci
                  JOIN cart c ON ci.cart_id = c.cart_id
                  JOIN product_item pi ON ci.product_item_id = pi.product_item_id
                  JOIN product p ON pi.product_id = p.product_id
                  JOIN color c ON pi.color_id = c.color_id
                  WHERE c.user_id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function calculateTotal(array $cart_items): float
    {
        $total = 0.0;
        foreach ($cart_items as $item) {
            $clean_price = floatval(str_replace('€', '', $item['price']));
            $total += $clean_price * $item['quantity'];
        }
        return $total;
    }

    public function applyPromoCode(string $code): float|false
    {
        return false;
    }

    public function clearCart(): bool
    {
        $query = "DELETE FROM cart_item WHERE cart_id = (SELECT cart_id FROM cart WHERE user_id = :user_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
