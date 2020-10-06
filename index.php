<?php

$request = $_SERVER['REQUEST_URI'];

switch ($request) {
  case '/':
    include __DIR__ . '/public/index.php';
    break;

  case '/index.php':
    include __DIR__ . '/public/index.php';
    break;
  case '/login.php':
    include __DIR__ . '/public/login.php';
    break;
}
