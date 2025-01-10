<?php

namespace App\Models;

use \PDO;

require_once "../../../config-inc.php";


class Db
{
    public function connect()
    {
        $conn_str = "mysql:host=" . SQL_SERVER . ";dbname=" . SQL_DATABASE;
        $conn = new PDO($conn_str, SQL_USERNAME, SQL_PASSWORD);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $conn;
    }
}