<?php

class Admin
{
    private PDO $conn;
    private int $admin_id;
    private string $first_name;
    private string $last_name;
    private string $email;
    private string $phone;
    private string $password;
    private string $updated_at;

    public function __construct(PDO $connection)
    {
        $this->conn = $connection;
    }

    public function get_admin_id(): int
    {
        return $this->admin_id;
    }
    public function get_first_name(): string
    {
        return $this->first_name;
    }
    public function get_last_name(): string
    {
        return $this->last_name;
    }
    public function get_email(): string
    {
        return $this->email;
    }
    public function get_phone(): string
    {
        return $this->phone;
    }
    public function get_password(): string
    {
        return $this->password;
    }
    public function get_updated_at(): string
    {
        return $this->updated_at;
    }

    public function set_admin_id(int $id): void
    {
        $this->admin_id = $id;
    }
    public function set_first_name(string $name): void
    {
        $this->first_name = $name;
    }
    public function set_last_name(string $name): void
    {
        $this->last_name = $name;
    }
    public function set_email(string $email): void
    {
        $this->email = $email;
    }
    public function set_phone(string $phone): void
    {
        $this->phone = $phone;
    }

    public function set_password(string $password): void
    {
        $this->password = $password;
    }

    public function create_admin(): bool
    {
        try {
            $query = "INSERT INTO admin (first_name, last_name, email, phone, password, updated_at)
                      VALUES (:first_name, :last_name, :email, :phone, :password, NOW())";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':first_name', $this->first_name);
            $stmt->bindParam(':last_name', $this->last_name);
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':phone', $this->phone);
            $stmt->bindParam(':password', $this->password);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function update_admin(): bool
    {
        try {
            $query = "UPDATE admin SET 
                    first_name = :first_name, 
                    last_name = :last_name, 
                    email = :email, 
                    phone = :phone";
            if (!empty($this->password)) {
                $query .= ", password = :password";
            }
            $query .= ", updated_at = NOW() WHERE admin_id = :admin_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':first_name', $this->first_name);
            $stmt->bindParam(':last_name', $this->last_name);
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':phone', $this->phone);
            if (!empty($this->password)) {
                $stmt->bindParam(':password', $this->password);
            }
            $stmt->bindParam(':admin_id', $this->admin_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function login_admin(): false | array     {
        try {
            $query = "SELECT * FROM admin WHERE email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $this->email);
            $stmt->execute();
            $admin = $stmt->fetch();

            if ($admin && password_verify($this->password, $admin['password'])) {
                $this->admin_id = $admin['admin_id'];
                $this->first_name = $admin['first_name'];
                $this->last_name = $admin['last_name'];
                $this->email = $admin['email'];
                $this->phone = $admin['phone'];
                $this->password = $admin['password'];
                $this->updated_at = $admin['updated_at'];
                return $admin;
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function verify_email()
    {
        try {
            $query = "SELECT COUNT(*) FROM admin WHERE email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $this->email);
            $stmt->execute();
            $count = $stmt->fetchColumn();
            return $count > 0;
        } catch (PDOException $e) {
            return false;
        }
    }


    public function get_all_admins(): array
    {
        try {
            $query = "SELECT 
                admin_id as id,
                first_name,
                last_name,
                email, 
                phone
                FROM admin 
                ORDER BY admin_id ASC";
            $stmt = $this->conn->query($query);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function get_admin_by_id(): array|false
    {
        try {
            $query = "SELECT * FROM admin WHERE admin_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $this->admin_id);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete_admin(): bool
    {
        try {
            $query = "DELETE FROM admin WHERE admin_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $this->admin_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
    public function logout_admin(): bool
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        session_destroy();
        return true;
    }
}







class User
{
    private $conn;
    private $user_id;
    private $first_name;
    private $last_name;
    private $email;
    private $phone;
    private $password;
    private $points;
    private $birthday;


    public function __construct(PDO $connection)
    {
        $this->conn = $connection;
    }

    public function set_user_id($user_id)
    {
        $this->user_id = $user_id;
    }
    public function set_first_name(string $first_name)
    {
        $this->first_name = $first_name;
    }
    public function set_last_name(string $last_name)
    {
        $this->last_name = $last_name;
    }
    public function set_email(string $email)
    {
        $this->email = $email;
    }
    public function set_phone(string $phone)
    {
        $this->phone = $phone;
    }
    public function set_password(string $password)
    {
        $this->password = $password;
    }
    public function set_points(int $points)
    {
        $this->points = $points;
    }
    public function set_birthday(string $birthday)
    {
        $this->birthday = $birthday;
    }


    public function get_user_id()
    {
        return $this->user_id;
    }
    public function get_first_name(): string
    {
        return $this->first_name;
    }
    public function get_last_name(): string
    {
        return $this->last_name;
    }
    public function get_email(): string
    {
        return $this->email;
    }
    public function get_phone(): string
    {
        return $this->phone;
    }


    public function get_points_user(): int
    {

        try {
            $conn = $this->conn;
            $query = "SELECT points FROM user WHERE user_id = :id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(":id", $this->user_id);
            if ($stmt->execute()) {
                return $stmt->fetchColumn();
            } else {
                return false;
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function subtract_points(int $amount): bool
    {
        try {
            $query = "UPDATE user SET points = points - :amount WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':amount', $amount);
            $stmt->bindParam(':user_id', $this->user_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }



    public function login_user(): bool
    {
        try {
            $query = "SELECT email, password FROM user WHERE email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":email", $this->email);
            $stmt->execute();
            $info = $stmt->fetch();
            if (!$info) return false;
            $found_email = $info['email'];
            $found_password = $info['password'];
            if (empty($found_email) || empty($found_password)) {
                return false;
            }
            if (password_verify($this->password, $found_password)) {
                $_SESSION['user_email'] = $this->email;
                return true;
            }
        } catch (PDOException $error) {
            return false;
        }

        return false;
    }

    public function logout_user(): bool
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        session_destroy();
        return true;
    }

    public function get_all_users()
    {
        try {
            $query = "SELECT 
                user_id as id, first_name,
                last_name,  email, 
                birthday_date,   phone
                FROM user 
                ORDER BY user_id ASC";
            $stmt = $this->conn->query($query);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    public function get_user_by_email(): array | false
    {
        try {
            $conn = $this->conn;
            $query = "SELECT 
                        user_id, first_name, 
                        last_name, email, birthday_date, phone, 
                        created_at, updated_at, points
                            FROM user WHERE email = :email";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(":email", $this->email);
            if ($stmt->execute()) {
                return $stmt->fetch();
            } else {
                return false;
            }
        } catch (PDOException $error) {
            return false;
        }
    }
    public function get_user_by_id()
    {
        try {
            $query = "SELECT 
                        user_id, first_name, 
                        last_name, email, birthday_date, phone, 
                        created_at, updated_at, points
                            FROM user
                            WHERE user_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $this->user_id);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function create_user(): bool
    {
        try {
            $hashed_password = password_hash($this->password, PASSWORD_DEFAULT);
            $query = "INSERT INTO user (first_name, last_name, email, birthday_date, phone, password, points) 
                  VALUES (:first_name, :last_name, :email, :birthday, :phone, :password, :points)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":first_name", $this->first_name);
            $stmt->bindParam(":last_name", $this->last_name);
            $stmt->bindParam(":email", $this->email);
            $stmt->bindParam(":birthday", $this->birthday);
            $stmt->bindParam(":phone", $this->phone);
            $stmt->bindParam(":password", $hashed_password);
            $stmt->bindParam(":points", $this->points);
            return $stmt->execute();
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }





    public function update_user()
    {
        try {
            $query = "UPDATE user SET first_name = :first_name, last_name = :last_name,
             email = :email, birthday_date = :birthday, phone=:phone";
            if (!empty($this->password)) {
                $query .= ", password = :password";
            }
            $query .= " WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $this->user_id);
            $stmt->bindParam(":first_name", $this->first_name);
            $stmt->bindParam(":last_name", $this->last_name);
            $stmt->bindParam(":email", $this->email);
            $stmt->bindParam(":birthday", $this->birthday);
            $stmt->bindParam(":phone", $this->phone);
            if (!empty($this->password)) {
                $stmt->bindParam(":password", $this->password);
            }
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete_user(): bool
    {
        try {
            $query = "DELETE FROM user WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $this->user_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function verify_email(): bool
    {
        try {
            $query = "SELECT email FROM user WHERE email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":email", $this->email);
            $stmt->execute();
            $count = $stmt->fetchColumn();
            return $count > 0;
        } catch (PDOException $e) {
            return false;
        }
    }
     
    public function verify_age(){
        try {
            if(!$this->birthday) return false;
            $birthday_date = new DateTime($this->birthday);
            $today = new DateTime();
            $age = $today->diff($birthday_date)->y;
            return $age >= 18;
        } catch (PDOException $e) {
        }
    }
};
