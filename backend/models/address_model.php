<?php


class Address
{
    private $conn;
    private $address_id;
    private $user_id;
    private $ext_number;
    private $int_number = null;
    private $city;
    private $state;
    private $street;
    private $cp;
    private $country;

    public function __construct(PDO $connection)
    {
        $this->conn = $connection;
    }

    public function set_address_id($id)
    {
        $this->address_id = $id;
    }
    public function set_user_id($id)
    {
        $this->user_id = $id;
    }
    public function set_ext_number($ext_number)
    {
        $this->ext_number = $ext_number;
    }

    public function set_int_number($int_number)
    {
        $this->int_number = $int_number;
    }
    public function set_city(string $city)
    {
        $this->city = $city;
    }
    public function set_state(string $state)
    {
        $this->state = $state;
    }
    public function set_street(string $street)
    {
        $this->street = $street;
    }
    public function set_cp(string $cp)
    {
        $this->cp = $cp;
    }
    public function set_country(string $country)
    {
        $this->country = $country;
    }



    public function get_address_id()
    {
        return $this->address_id;
    }
    public function get_user_id()
    {
        return $this->user_id;
    }
    public function get_ext_number()
    {
        return $this->ext_number;
    }
    public function get_int_number()
    {
        return $this->int_number;
    }
    public function get_city()
    {
        return $this->city;
    }
    public function get_state()
    {
        return $this->state;
    }
    public function get_street()
    {
        return $this->street;
    }
    public function get_cp()
    {
        return $this->cp;
    }
    public function get_country()
    {
        return $this->country;
    }



    public function create_address(): int|false
    {
        try {
            $this->conn->beginTransaction();
            $query = "INSERT INTO address (ext_number, int_number, city, state, street, cp, country_id)
                  VALUES (:ext_number, :int_number, :city, :state, :street, :cp, 
                  (SELECT country_id FROM country WHERE country_name = :country))";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":ext_number", $this->ext_number);
            $stmt->bindParam(":int_number", $this->int_number);
            $stmt->bindParam(":city", $this->city);
            $stmt->bindParam(":state", $this->state);
            $stmt->bindParam(":street", $this->street);
            $stmt->bindParam(":cp", $this->cp);
            $stmt->bindParam(":country", $this->country);
            if (!$stmt->execute()) {
                $this->conn->rollBack();
                echo 'insert address table breaks';
                return false;
            }
            $address_id = $this->conn->lastInsertId();
            $user_address_query = "INSERT INTO user_address (user_id, address_id)
                               VALUES (:user_id, :address_id)";
            $stmt = $this->conn->prepare($user_address_query);
            $stmt->bindParam(":user_id", $this->user_id);
            $stmt->bindParam(":address_id", $address_id);
            if (!$stmt->execute()) {
                $this->conn->rollBack();
                echo 'insert user_address table breaks';
                return false;
            }
            $this->conn->commit();
            return $address_id; // <-- Return the new address_id here
        } catch (PDOException $e) {
            $this->conn->rollBack();
            echo ($e->getMessage() . 'is the geral error');
            return false;
        }
    }


    public function get_addresses_by_userid(): array|false
    {
        try {
            $query = " SELECT 
                a.address_id,
                a.ext_number,
                a.int_number,
                a.city,
                a.street,
                a.state,
                a.cp,
                c.country_name
            FROM user_address ua
            JOIN user u ON ua.user_id = u.user_id
            JOIN address a ON a.address_id = ua.address_id AND u.user_id = ua.user_id
            JOIN country c ON a.country_id = c.country_id
            WHERE u.user_id = :user_id;";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $this->user_id);
            $stmt->execute();
            $addresses = $stmt->fetchAll();
            return $addresses;
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }


    public function update_address(): bool
    {
        try {


            $query = "UPDATE address SET ext_number= :ext_number, int_number= :int_number, city= :city,
                             street= :street, state = :state, cp = :cp, country = 
                             (SELECT country_name FROM country WHERE country_id = :country_id)
                             WHERE address_id = :address_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":address_id", $this->address_id);
            $stmt->bindParam(":ext_number", $this->ext_number);
            $stmt->bindParam(":int_number", $this->int_number);
            $stmt->bindParam(":city", $this->city);
            $stmt->bindParam(":street", $this->street);
            $stmt->bindParam(":state", $this->state);
            $stmt->bindParam(":cp", $this->cp);
            $stmt->bindParam(":country_id", $this->country);
            return $stmt->execute();
        } catch (PDOException $error) {
            return false;
        };
    }

    public function delete_address(): bool
    {
        try {
            $query = "DELETE FROM address where address_id = :address_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':address_id', $this->address_id);
            return $stmt->execute();
        } catch (PDOException $error) {
            return false;
        }
    }
    public function find_existing_address(): int|false
    {
        try {
            $query = "SELECT a.address_id
                  FROM address a
                  JOIN user_address ua ON ua.address_id = a.address_id
                  WHERE ua.user_id = :user_id
                    AND a.ext_number = :ext_number
                    AND a.int_number = :int_number
                    AND a.city = :city
                    AND a.state = :state
                    AND a.street = :street
                    AND a.cp = :cp
                    AND a.country_id = (SELECT country_id FROM country WHERE country_name = :country)
                  LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $this->user_id);
            $stmt->bindParam(":ext_number", $this->ext_number);
            $stmt->bindParam(":int_number", $this->int_number);
            $stmt->bindParam(":city", $this->city);
            $stmt->bindParam(":state", $this->state);
            $stmt->bindParam(":street", $this->street);
            $stmt->bindParam(":cp", $this->cp);
            $stmt->bindParam(":country", $this->country);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result ? $result['address_id'] : false;
        } catch (PDOException $e) {
            return false;
        }
    }
}
