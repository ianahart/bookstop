<?php
include(dirname(__DIR__) . '/config/db.php');


session_start();

if (!isset($_SESSION['user'])) {
  header("Location: login.php");
}

if (isset($_SESSION['user']) && !isset($_SESSION['profileId'])) {
  header("Location: index.php");
}

if (isset($_GET['bookId'])) {
  $bookId = $_GET['bookId'];
}

function getBookTitle($conn, $id)
{
  $sql = "SELECT title FROM books WHERE id =  '$id'";

  $result = mysqli_query($conn, $sql);

  $book = mysqli_fetch_assoc($result);

  mysqli_free_result($result);

  return $book;
}

function addReview($conn, $bookId, $userId, $text)
{

  $sql = "INSERT INTO reviews (userId, bookId, reviewText, star_rating) VALUES('$userId', '$bookId', '$text', '0')";

  if (mysqli_query($conn, $sql)) {
    echo 'hi';
  } else {
    echo 'Insertion Error: ' . mysqli_error($conn);
  }
}

function checkUserExistingReview($conn, $bookId, $userId)
{
  $existingReviewFound = false;

  $sql = "SELECT bookId FROM reviews WHERE userId = $userId";

  $result = mysqli_query($conn, $sql);

  $reviews = mysqli_fetch_all($result, MYSQLI_ASSOC);

  mysqli_free_result($result);

  foreach ($reviews as $review) {
    if ($review['bookId'] === $bookId) {

      $existingReviewFound = true;
    }
  }

  return $existingReviewFound;
}

$bookTitle = getBookTitle($conn, $bookId);

$userExistingReview = checkUserExistingReview($conn, $bookId, $_SESSION['profileId']);


$errors = ['review' => ''];

if (isset($_POST['submit'])) {
  $reviewText = $_POST['review'];
  $reviewText = mysqli_escape_string($conn, $reviewText);
  if (empty($reviewText)) {

    $errors['review'] = 'Review cannot be empty';
  } else if (strlen($reviewText) < 20) {

    $errors['review'] = 'Review must be a minimum of twenty characters';
  } else if (strlen($reviewText) > 500) {

    $errors['review'] = 'Review must not exceed 500 characters';
  }

  $errorCount = array_filter($errors, function ($error) {
    return $error !== '';
  });

  if (count($errorCount) === 0) {

    addReview($conn, $bookId, $_SESSION['profileId'], $reviewText);

    header("Location: bookdetails.php?id=" . $bookId);
  }
}
?>


<!DOCTYPE html>
<html lang="en">

<?php include('public/templates/head.php'); ?>

<body>
  <div class="content">
    <?php include('public/templates/nav.php'); ?>
    <div class="create-review-form">

      <form action="createreview.php?bookId=<?php echo $bookId; ?>" method="POST">
        <h1>Write Review</h1>
        <?php if ($userExistingReview) : ?>
          <h4>You have already reviewed <span><?php echo htmlspecialchars($bookTitle['title']); ?></span></h4>
        <?php else : ?>
          <h4>Reviewing <span><?php echo htmlspecialchars($bookTitle['title']); ?></span></h4>

        <?php endif; ?>
        <div class="group">
          <label>Enter Review:</label>
          <textarea type="text" name="review"></textarea>
          <div class="form-error">
            <?php echo $errors['review']; ?>
          </div>
        </div>
        <div class="button-container">
          <input type="submit" name="submit" <?php echo $userExistingReview ? 'disabled' : '' ?> value="Submit" />
        </div>
      </form>
    </div>
  </div>
  <?php include('public/templates/footer.php'); ?>
</body>

</html>