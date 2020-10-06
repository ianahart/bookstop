<?php

function getReviewSubmittedBy($conn, $userId)
{
  $sql = "SELECT username FROM profiles WHERE userId = '$userId'";

  $result = mysqli_query($conn, $sql);

  $userName = mysqli_fetch_assoc($result);

  mysqli_free_result($result);

  return $userName;
}

function getBookReviews($conn, $bookId)
{
  $sql = "SELECT * FROM reviews WHERE bookId = '$bookId' ORDER BY created_at DESC";

  $result = mysqli_query($conn, $sql);

  $reviews = mysqli_fetch_all($result, MYSQLI_ASSOC);

  mysqli_free_result($result);

  $formattedReviews = array_map(function ($review) use ($conn) {

    $userName = getReviewSubmittedBy($conn, $review['userId']);
    if ($userName) {
      $review['username'] = $userName['username'];
    }


    return $review;
  }, $reviews);

  return $formattedReviews;

  mysqli_close($conn);
}

$reviews = getBookReviews($conn, $book['id']);

function formatCreatedAt($time)
{
  $day = date('jS', strtotime($time));

  $month = date('M', strtotime($time));

  $year = date('Y', strtotime($time));

  return $month . ", " . $day . " " . $year;
}

function deleteReview($conn, $id)
{

  $sql = "DELETE FROM reviews WHERE id = $id";

  mysqli_query($conn, $sql);
}


if (isset($_POST['deletereview'])) {

  $idToDelete = $_POST['reviewtodelete'];

  deleteReview($conn, $idToDelete);

  $URL = "bookdetails.php?id=" . $book['id'];

  if (headers_sent()) {

    echo ("<script>location.href='$URL'</script>");
  } else {

    header("Location: " . $URL);
  }
}


function addStarRating($conn, $starRating, $userId, $bookId)
{
  $sql = "UPDATE reviews SET star_rating = '$starRating'  WHERE bookId = '$bookId' AND userId = '$userId'";

  if (!mysqli_query($conn, $sql)) {
    echo 'Insertion error: ' . mysqli_error($conn);
  }
}



if (isset($_GET['rating']) && isset($_GET['userId'])) {
  $starRating = $_GET['rating'];
  $starUserId = $_GET['userId'];

  addStarRating($conn, $starRating, $starUserId, $book['id']);
}




?>




<div class="reviews-container">
  <h3 class="reviews-heading">Reviews (<span><?php echo count($reviews); ?></span>)</h3>
  <?php if (count($reviews) !== 0) : ?>
    <div class="reviews-inner-content">
      <?php foreach ($reviews as $review) : ?>
        <div class="single-review">
          <div class="star-rating-default" data-review="<?php echo $review['star_rating']; ?>" data-owner="<?php echo $review['userId']; ?>" data-currentUser="<?php echo $_SESSION['profileId']; ?>" ">
            <span value=" 0" class="fa fa-star star default-star star-unchecked " data-userId="<?php echo $review['userId']; ?>" data-bookId="<?php echo $book['id']; ?>"></span>
            <span value="1" class="fa fa-star star default-star star-unchecked" data-userId="<?php echo $review['userId']; ?>" data-bookId="<?php echo $book['id']; ?>"></span>
            <span value="2" class="fa fa-star star default-star star-unchecked" data-userId="<?php echo $review['userId']; ?>" data-bookId="<?php echo $book['id']; ?>"></span>
            <span value="3" class="fa fa-star star default-star star-unchecked" data-userId="<?php echo $review['userId']; ?>" data-bookId="<?php echo $book['id']; ?>"></span>
            <span value="4" class="fa fa-star star default-star star-unchecked" data-userId="<?php echo $review['userId']; ?>" data-bookId="<?php echo $book['id']; ?>"></span>
          </div>
          <?php if (isset($_SESSION['profileId'])) : ?>
            <?php if ($_SESSION['profileId'] === $review['userId']) : ?>
              <form action="bookdetails.php?id=<?php echo htmlspecialchars($review['bookId']); ?>" method="POST">
                <input type="hidden" name="reviewtodelete" value="<?php echo htmlspecialchars($review['id']); ?>" />
                <input type="submit" name="deletereview" value="Delete" />
              </form>
            <?php endif; ?>
          <?php endif; ?>
          <h3 class="review-author"><a href="viewprofile.php?userId=<?php echo htmlspecialchars($review['userId']); ?>"><?php echo htmlspecialchars($review['username']); ?></a> said:</h3>
          <p class="date-posted"><?php echo formatCreatedAt($reviews[0]['created_at']); ?></p>
          <p>"<?php echo htmlspecialchars($review['reviewText']); ?>"</p>
          <hr>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>