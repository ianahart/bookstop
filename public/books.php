<?php
session_start();
include(dirname(__DIR__) . '/config/db.php');


if (isset($_SESSION['referer'])) {
  unset($_SESSION['referer']);
}


function getAverageReviewRatings($conn, $id)
{
  $sql = "SELECT bookId, star_rating FROM reviews WHERE bookId = '$id'";

  $result = mysqli_query($conn, $sql);

  $review = mysqli_fetch_all($result, MYSQLI_ASSOC);

  mysqli_free_result($result);

  if (empty($review)) {
    return 0;
  } else {
    $averageRating = array_reduce($review, function ($acc, $curr) {
      return $acc + $curr['star_rating'];
    });
    return round($averageRating / count($review), 1);
  }
}

// $test = getAverageReviewRatings($conn, '89');
// print_r($test);





// DISPLAY BOOKS

function displayBooks($conn)
{
  $sql = 'SELECT title, author, voteCount, published, id FROM books ORDER BY voteCount DESC';

  $result = mysqli_query($conn, $sql);

  $returnedBooks = mysqli_fetch_all($result, MYSQLI_ASSOC);

  mysqli_free_result($result);

  $books = array_map(function ($book) use ($conn) {

    $avg = getAverageReviewRatings($conn, $book['id']);

    $book['avg_rating'] = $avg;

    return $book;
  }, $returnedBooks);

  return $books;
}


$books = displayBooks($conn);




function setVotedOn($conn, $id)
{
  $currentUser = $_SESSION['user'];

  $sql = "UPDATE users SET votedOn = CONCAT(votedOn,', $id') WHERE id = $currentUser";

  if (!mysqli_query($conn, $sql)) {

    echo 'Query Error: ' . mysqli_error($conn);
  }
}

function limitVote($conn, $id)
{
  $currentUser = $_SESSION['user'];

  $sql = "SELECT votedOn FROM users WHERE id=$currentUser";

  $result = mysqli_query($conn, $sql);

  $temp = mysqli_fetch_assoc($result);

  $votedOnArray = explode(',', $temp['votedOn']);

  mysqli_free_result($result);

  return array_search($id, $votedOnArray);
}

function getVoteCount($conn, $id)
{

  $sql = "SELECT voteCount FROM books WHERE id = $id";

  $result = mysqli_query($conn, $sql);

  $voteCount = mysqli_fetch_assoc($result);

  mysqli_free_result($result);
  return $voteCount;
}


function incrementVote($conn, $count, $id)
{

  setVotedOn($conn, $id);

  $count = $count + 1;

  $sql = "UPDATE books SET voteCount= '$count' WHERE id=$id";

  if (mysqli_query($conn, $sql)) {
    header('Location: books.php');
  } else {
    echo 'Query Error: ' . mysqli_error($conn);
  }
}

function decrementVote($conn, $count, $id)
{
  setVotedOn($conn, $id);

  $count = $count - 1;

  $sql = "UPDATE books SET voteCount = '$count' WHERE id=$id";

  if (mysqli_query($conn, $sql)) {

    header('Location: books.php');
  } else {
    echo 'Query Error: ' . mysqli_error($conn);
  }
}




if (isset($_POST['upvote'])) {
  $bookId = $_POST['id'];

  $book = getVoteCount($conn, $bookId);

  if (!limitVote($conn, $bookId)) {

    incrementVote($conn, $book['voteCount'], $bookId);
  }
}

if (isset($_POST['downvote'])) {
  $bookId = $_POST['id'];

  $book = getVoteCount($conn, $bookId);

  if (!limitVote($conn, $bookId)) {

    decrementVote($conn, $book['voteCount'], $bookId);
  }
}



?>


<!DOCTYPE html>
<html lang="en">
<?php include('public/templates/head.php'); ?>

<body>
  <div class="content">
    <?php include('public/templates/nav.php'); ?>
    <?php include('public/templates/filterbooks.php'); ?>
    <div class="books-container">
      <?php foreach ($books as $book) : ?>
        <div class="book">
          <?php if (isset($_SESSION['user'])) : ?>
            <div class="vote">
              <form action="books.php" method="POST">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($book['id']) ?>" />
                <button type="submit" name="upvote"> <i class="fas fa-arrow-circle-up vote-up"></i></button>
              </form>
              <form action="books.php" method="POST">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($book['id']) ?>" />
                <button type="submit" name="downvote"> <i class="fas fa-arrow-circle-down vote-down"></i></button>
              </form>
              <div class="book-meta-information">
                <p class="vote-count">Popular Votes:<span><?php echo htmlspecialchars($book['voteCount']); ?></span></p>
                <p class="rating">Review Ratings: <span><?php echo htmlspecialchars($book['avg_rating']); ?><i class="fas rating-star fa-star"></i></span></p>
              </div>
            </div>
          <?php endif; ?>
          <h3 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h3>
          <div class="book-image-container">
            <img src="img/book.png" />
          </div>
          <h5 class="book-author"><span>By</span>: <?php echo htmlspecialchars($book['author']); ?></h5>
          <h5>Published Date: <?php echo htmlspecialchars($book['published']);  ?></h5>
          <div class="line"></div>
          <div class="sub-content">
            <div class="details-wrapper">
              <a href="bookdetails.php?id=<?php echo htmlspecialchars($book['id']); ?>">Details</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php include('public/templates/footer.php'); ?>
  <script src="js/booksFilter.js"></script>
</body>

</html>