<?php
define("HOST", "localhost");
define("USER", "root");
define("PASSWORD", "");
define("DATABASE", "e_commerce");
define("CHARSET", "utf8mb4" );

class Connection {
    public static ?PDO $instance = null;
    public static function connectDB (){
        if(!isset(self::$instance)){
            $dns = "mysql:host=" . HOST . ";dbname=" . DATABASE . ";charset=" . CHARSET;
                 try{
                    $options = [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ];
                    self::$instance = new PDO($dns, USER, PASSWORD, $options);
                    // echo "the connection was sucessfully";
                } 
                catch (PDOException $error){
                    exit($error->getMessage());
                } 

        }
        
            return self::$instance;

    }

};

?>