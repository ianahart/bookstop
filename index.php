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
  case '/books.php':
    include __DIR__ . '/public/books.php';
    break;
  case '/about.php':
    include __DIR__ . '/public/about.php';
    break;
  case '/register.php':
    include __DIR__ . '/public/register.php';
    break;
  case '/logout.php':
    include __DIR__ . '/public/logout.php';
    break;
  case '/createprofile.php':
    include __DIR__ . '/public/createprofile.php';
    break;
  case '/newpassword.php':
    include __DIR__ . '/public/newpassword.php';
    break;
  case '/forgotpassword.php':
    include __DIR__ . '/public/forgotpassword.php';
    break;
    // FIX THIS
  case '/pending.php?':
    include __DIR__ . '/public/pending.php';
    break;
  case strpos($_GET, 'viewprofile.php'):
    include __DIR__ . '/public/viewprofile.php';
}
