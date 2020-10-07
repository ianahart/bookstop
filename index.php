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
  case "/viewprofile.php?userId=" . $_GET['userId']:
    include __DIR__ . '/public/viewprofile.php';
    break;
  case "/bookdetails.php?id=" . $_GET['id']:
    include __DIR__ . '/public/bookdetails.php';
    break;
  case "/bookdetails.php?id=" . $_GET['id']  . "&rating=" . $_GET['rating'] . "&userId=" . $_GET['userId']:
    include __DIR__ . '/public/bookdetails.php';
  case "/createreview.php?bookId=" . $_GET['bookId']:
    include __DIR__ . '/public/createreview.php';
    break;
  case "/addbook.php":
    include __DIR__ . '/public/addbook.php';
    break;
  case "/mybooks.php?userId=" . $_GET['userId']:
    include __DIR__ . '/public/mybooks.php';
    break;
}
