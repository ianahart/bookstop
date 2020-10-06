<?php


// require "../vendor/autoload.php";

// include(dirname(__DIR__) . '/vendor/autoload.php');

// use Dotenv\Dotenv;





// $dotenv = Dotenv::createImmutable(__DIR__);
// $dotenv->load();

$url = parse_url(getenv("CLEARDB_DATABASE_URL"));

$server = $url["host"];
$username = $url["user"];
$password = $url["pass"];
$db = substr($url["path"], 1);


$conn = mysqli_connect($server, $username, $password, $db);


if (!$conn) {
  echo 'Connection Error: ' . mysqli_connect_error($conn);
} else {
}
