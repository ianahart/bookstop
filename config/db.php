<?php


require "../vendor/autoload.php";

use Dotenv\Dotenv;





$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();




$conn = mysqli_connect('us-cdbr-east-02.cleardb.com', 'b2cf0765c84f57', '81e92f78', 'heroku_3f34e06ba4944be');


if (!$conn) {
  echo 'Connection Error: ' . mysqli_connect_error($conn);
} else {
}
