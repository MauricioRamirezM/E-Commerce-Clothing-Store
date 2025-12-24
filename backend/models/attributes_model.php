<?php

class AttributeType
{
    private $conn;
    private $attribute_type_id;
    private $attribute_name;

    public function __construct(PDO $connection)
    {
        $this->conn = $connection;
    }

    public function get_attribute_type_id()
    {
        return $this->attribute_type_id;
    }
    public function get_attribute_name()
    {
        return $this->attribute_name;
    }

    public function set_attribute_type_id( $id)
    {
        $this->attribute_type_id = $id;
    }
    public function set_attribute_name(string $name)
    {
        $this->attribute_name = $name;
    }

    public function create_attribute_type(): bool
    {
        try {
            $query = "INSERT INTO attribute_type (attribute_name) VALUES (:attribute_name)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':attribute_name', $this->attribute_name);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete_attribute_type(): bool
    {
        try {
            $query = "DELETE FROM attribute_type WHERE attribute_type_id = :attribute_type_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':attribute_type_id', $this->attribute_type_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function get_all_attributes(): array|false
    {
        try {
            $query = "SELECT attribute_name FROM attribute_type";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function get_by_id(): array|false
    {
        try {
            $query = "SELECT * FROM attribute_type WHERE attribute_type_id = :attribute_type_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':attribute_type_id', $this->attribute_type_id);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }
      public function get_attr_id_by_name(): int | false
    {
        try {
            $sql = "SELECT  attribute_type_id,
                FROM attribute_type  WHERE attribute_name = :name";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':name', $this->attribute_name);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            return false;
        }
    }
}

class AttributeOption
{
    private  $conn;
    private  $attribute_option_id;
    private  $attribute_option_name;
    private  $attribute_type_name;

    public function __construct(PDO $connection)
    {
        $this->conn = $connection;
    }

    public function get_attribute_option_id(): int {
       try {
            $sql = "SELECT attribute_option_id FROM attribute_option 
                WHERE attribute_option_name = :name";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':name', $this->attribute_option_name);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function get_attribute_option_name(): string
    {
        return $this->attribute_option_name;
    }

    public function get_attribute_type_name(): int
    {
        return $this->attribute_type_name;
    }

    public function set_attribute_option_id($id): void
    {
        $this->attribute_option_id = $id;
    }

    public function set_attribute_option_name(string $name): void
    {
        $this->attribute_option_name = trim($name);
    }

    public function set_attribute_type_name($name): void
    {
        $this->attribute_type_name = $name;
    }

    public function create_attribute_option(): bool
    {
        try {
           $sql = "INSERT INTO attribute_option (attribute_option_name, attribute_type_id) 
                VALUES (:name, (SELECT attribute_type_id FROM attribute_type WHERE attribute_name = :type_name))";
        $stmt = $this->conn->prepare($sql);
         $stmt->bindParam(':name', $this->attribute_option_name);
        $stmt->bindParam(':type_name', $this->attribute_type_name);
        return $stmt->execute();
        } catch (PDOException $e) {
              echo $e->getMessage(); 
            return false;
        }
        
    }

    public function update_attribute_option(): bool{
        try {
            $sql = "UPDATE attribute_option 
                SET attribute_option_name = :name, attribute_type_id = 
                (SELECT attribute_type_id FROM attribute_type WHERE attribute_name = :attribute_name)
                WHERE attribute_option_id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':name', $this->attribute_option_name);
        $stmt->bindParam(':attribute_name', $this->attribute_type_name);
        $stmt->bindParam(':id', $this->attribute_option_id);
        return $stmt->execute();
        }catch (PDOException $e) {
             echo $e->getMessage(); 
            return false;
        }
    }
    public function get_attr_option_by_id(): array | false
    {
        try {
            $sql = "SELECT 
            ao.attribute_option_id,
            ao.attribute_option_name,
            att.attribute_name
                FROM attribute_option ao
                JOIN attribute_type att ON ao.attribute_type_id = att.attribute_type_id
                WHERE attribute_option_id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $this->attribute_option_id);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }
    public function get_attr_option_id_by_name(): int | false
    {
        try {
            $sql = "SELECT  attribute_option_id
                FROM attribute_option WHERE attribute_option_name = :name";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':name', $this->attribute_option_name);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete_attribute_option(): bool
    {
        if (!$this->attribute_option_id) return false;

        try {
            $sql = "DELETE FROM attribute_option WHERE attribute_option_id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $this->attribute_option_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function get_all_attribute_options(): array|false
    {
        try {
            $sql = "SELECT 
            ao.attribute_option_id as id, 
            ao.attribute_option_name as name, 
            att.attribute_name  as attribute
                FROM attribute_option ao
                JOIN attribute_type att ON ao.attribute_type_id = att.attribute_type_id
                ORDER BY att.attribute_name, ao.attribute_option_name";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function get_all_by_type($type): array|false
    {
        try {
            $sql = "SELECT attribute_option_id, attribute_option_name 
                FROM attribute_option 
                WHERE attribute_type_id = 
                (SELECT attribute_type_id FROM attribute_type WHERE attribute_name = :type_name )
                ORDER BY attribute_option_name";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':type_name', $type);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function get_all_by_types_2(array $types): array|false
{
    try {
        $placeholders = implode(',', array_fill(0, count($types), '?'));

        $sql = "SELECT 
                    ao.attribute_option_name AS subAttr_name, 
                    att.attribute_name AS attr_name
                FROM attribute_option  ao
                INNER JOIN attribute_type att ON ao.attribute_type_id = att.attribute_type_id 
                WHERE att.attribute_name IN ($placeholders)
                ORDER BY att.attribute_name, ao.attribute_option_name";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($types);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return false;
    }
}
}

