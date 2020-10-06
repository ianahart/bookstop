<?php

$request = $_SERVER['REQUEST_URI'];

switch ($request) {
  case '/':
    include __DIR__ . '/public/index.php';
    break;
}
