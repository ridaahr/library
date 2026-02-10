<?php
class CoreDB
{
    public static function connect()
    {
        $host = "127.0.0.1";
        $user = "root";
        $pass = "Sandia4you";
        $db = "library";
        $port = "3307";
        $conn = new mysqli($host, $user, $pass, $db, $port);
        if ($conn->connect_error) {
            die("Error de conexión: " . $conn->connect_error);
        }
        $conn->set_charset("utf8");
        return $conn;
    }
}
