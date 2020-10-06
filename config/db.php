<?php


// require "../vendor/autoload.php";

include(dirname(__DIR__) . '/vendor/autoload.php');

use Dotenv\Dotenv;





$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();




$conn = mysqli_connect($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD'], $_ENV['DB']);


if (!$conn) {
  echo 'Connection Error: ' . mysqli_connect_error($conn);
} else {
}
