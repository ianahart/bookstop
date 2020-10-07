<?php

?>

<div>
  <nav id="nav">
    <div>
      <h1>Bookstop</h1>
    </div>
    <div id="nav-wrapper" class="show-navigation">
      <ul class="navigation">
        <li><a class="link" href="index.php">Home</a></li>
        <?php if (!isset($_SESSION['user'])) : ?>
          <li>
            <a class="link" href="login.php">Login</a>
          </li>
        <?php endif; ?>
        <li><a class="link" href="books.php">Books</a></li>
        <?php if (isset($_SESSION['user'])) : ?>
          <li> <a class="link" href="addbook.php">Add Book</a></li>
        <?php endif; ?>
        <li><a class="link" href="about.php">About</a></li>
        <?php if (isset($_SESSION['user'])) : ?>
          <li class="user">
            <i class="account-btn fas fa-user-circle fa-2x"></i>
          </li>
        <?php endif; ?>
      </ul>

    </div>
</div>
<div class="hamburger">
  <div class="line"></div>
  <div class="line"></div>
</div>
</nav>
</div>