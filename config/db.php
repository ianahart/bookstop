<?php



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
