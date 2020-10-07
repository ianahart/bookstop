<?php

session_start();

include(dirname(__DIR__) . '/config/db.php');

if (!isset($_SESSION['user'])) {
  header("Location: login.php");
}

if (isset($_GET['userId'])) {

  $userId = $_GET['userId'];
}

if (!isset($_SESSION['referer'])) {
  $_SESSION['referer'] = basename($_SERVER['PHP_SELF']);
}


function getOwnerBooks($conn, $id)
{

  $sql = "SELECT title, id,  author, image, created_at FROM books WHERE userId='$id' ORDER BY created_at DESC ";

  $result = mysqli_query($conn, $sql);

  $books = mysqli_fetch_all($result, MYSQLI_ASSOC);

  mysqli_free_result($result);

  mysqli_close($conn);

  return $books;
}

$ownerBooks = getOwnerBooks($conn, $userId);

function formatTime($date)
{
  $month = date('M', strtotime($date));

  $year = date('Y', strtotime($date));

  $formatted = $month . ", " . $year;

  return $formatted;
}

$numOfBooks = count($ownerBooks);


?>

<!DOCTYPE html>
<html lang="en">

<?php include('public/templates/head.php'); ?>

<body>
  <div class="content">
    <?php include('public/templates/nav.php'); ?>
    <div>
      <h3 class="mybook-heading">You have submitted <span class="num-of-books"><?php echo $numOfBooks; ?></span> books</h3>
    </div>
    <div class="mybooks-container">
      <?php foreach ($ownerBooks as $book) : ?>
        <div class="mybook">
          <div class="mybook-left-column">
            <h3><?php echo htmlspecialchars($book['title']); ?></h3>
            <div class="mybook-img-container">
              <a href="bookdetails.php?id=<?php echo htmlspecialchars($book['id']); ?>"><img src="<?php echo !empty($book['image']) ? htmlspecialchars($book['image']) : 'public/img/book.png' ?>" alt="<?php echo htmlspecialchars($book['title']); ?>" /></a>
            </div>
          </div>
          <div class="mybook-right-column">
            <h3>Written by: <span><?php echo htmlspecialchars($book['author']); ?></span></h3>
            <p>Submitted: <span><?php echo htmlspecialchars(formatTime($book['created_at'])); ?></span> </p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php include('public/templates/footer.php'); ?>
</body>

</html>