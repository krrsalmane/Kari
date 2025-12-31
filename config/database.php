<?php
class Database {
    
    private static string $host = "localhost";
    private static string $dbname = "kari";
    private static string $username = "root";
    private static string $password = "root@123";

    private static ?PDO $pdo = null;

    

    public static function connect():PDO {
        if(self::$pdo===null){
            try{
                self::$pdo=new PDO(
                    "mysql:host=".self::$host . ";dbname=" .self::$dbname . ";chartset=utf8",self::$username ,self::$password
                );
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE , PDO::ERRMODE_EXCEPTION);
            }catch(PDOException $e){
                die("error connecting to db :" .$e-> getMessage());
            }
        }return self::$pdo;
    }
}
?>
