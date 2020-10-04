<?php 

class Rest {
    private $conn;

    function __construct($host, $name, $username, $password)
    {
        try{
            $this->conn = new PDO("mysql:host=" . $host . ";dbname=" . $name, $username, $password);
            $this->conn->exec("set names utf8");
        }catch(PDOException $exception){
            echo "Connection error: " . $exception->getMessage();
        }
    }

    function uuid() : string {
        $data = openssl_random_pseudo_bytes(16);
        assert(strlen($data) == 16);
    
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
    
        return vsprintf('%s%s', str_split(bin2hex($data), 4));
    }
    
    function getOrders() : string {
        $query = $this->conn->prepare("SELECT * FROM orders");
        $query->execute();
        $order = $query->fetchAll(PDO::FETCH_ASSOC);
        
        return str_replace(['[', ']'], "", json_encode($order));
    }
 
    function checkKey($code, $isAdmin = false) : bool {
        if($isAdmin) {
            $query = $this->conn->prepare("SELECT id FROM api_keys WHERE code = :code AND admin = '1'");
        } else {
            $query = $this->conn->prepare("SELECT id FROM api_keys WHERE code = :code");
        }
        $query->bindParam(":code", $code);
        $query->execute();

        return $query->rowCount() == 1;
    }
    
    function deleteOrder($order_id) : bool {
        $query = $this->conn->prepare("DELETE FROM orders WHERE order_id = :id");
        $query->bindParam(":id", $order_id);
        $query->execute();

        $query = $this->conn->prepare("DELETE FROM items WHERE order_id = :id");
        $query->bindParam(":id", $order_id);

        return $query->execute();
    }
    

    function doesOrderExist($order_id) : bool {
        $query = $this->conn->prepare("SELECT id FROM orders WHERE order_id = :ord");
        $query->bindParam(":ord", $order_id);
        $query->execute();

        return $query->rowCount() == 1;
    }

    function updateStatus($order_id, $status) : bool {
        $query = $this->conn->prepare("UPDATE orders SET status=:stat WHERE order_id = :ord");
        $query->bindParam(":stat", $status);
        $query->bindParam(":ord", $order_id);

        return $query->execute();
    }

    function createOrder($order) : string {
        $order_id = $this->uuid();
        $query = $this->conn->prepare("INSERT INTO orders(order_id, address, phone_number, first_name, last_name) VALUES(:order, :add, :number, :first, :last)");
        $query->bindParam(":order", $order_id);
        $query->bindParam(":add", $order->address);
        $query->bindParam(":number", $order->phone_number);
        $query->bindParam(":first", $order->first_name);
        $query->bindParam(":last", $order->last_name); 
        $query->execute();

        foreach ($order->items as $item) {
            $query = $this->conn->prepare("INSERT INTO items(order_id, item_id, quantity) VALUES(:order, :item, :quan)");
            $query->bindParam(":order", $order_id);
            $query->bindParam(":item", $item->item_id);
            $query->bindParam(":quan", $item->quantity);
            $query->execute();
        }

        return $order_id;
    }

    function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    function respond($code, $body) {
        http_response_code($code);
        header('Content-Type: application/json');
        die (json_encode($body));
    }

    function error($code, $message) {
        $this->respond($code, ["status" => "error", "message"=> $message]);
    }

}