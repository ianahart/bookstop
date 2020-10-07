<?php
session_start();
include(dirname(__DIR__) . '/config/db.php');

$userId = null;
if (isset($_SESSION['user'])) {
  $userId = $_SESSION['user'];
}



function getSubmittedBy($userId)
{
  global $conn;

  $sql = "SELECT username FROM profiles WHERE userId=$userId";

  $result = mysqli_query($conn, $sql);

  $userName = mysqli_fetch_assoc($result);

  mysqli_free_result($result);

  mysqli_close($conn);

  return $userName;
}

function getCurrentUserVotes($bookId, $user, $conn)
{
  $sql = "SELECT votedOn, id FROM users";

  $result = mysqli_query($conn, $sql);

  $votes = mysqli_fetch_all($result, MYSQLI_ASSOC);

  mysqli_free_result($result);


  $votes = filterAndFormatElements($votes, $bookId);

  return $votes;
}

function filterAndFormatElements($elements, $id)
{
  $mainArr = array_map(function ($arr) {

    $votes['votedOn'] = explode(',', $arr['votedOn']);

    $votes['id'] = $arr['id'];

    return $votes;
  }, $elements);


  $filteredEls = [];

  foreach ($mainArr as $subArr) {
    if ($key = array_search($id, $subArr['votedOn'])) {

      unset($subArr['votedOn'][$key]);
    }
    $temp = implode(',', $subArr['votedOn']);

    $newArr = ['votedOn' => $temp, 'id' => $subArr['id']];

    array_push($filteredEls, $newArr);
  }

  return $filteredEls;
}

function removeVoteFromDB($votes, $conn)
{
  foreach ($votes as $vote) {
    $votedOn = $vote['votedOn'];

    $userId = $vote['id'];

    $sql = "UPDATE users SET votedOn = '$votedOn' WHERE id = $userId";

    if (mysqli_query($conn, $sql)) {
      // echo 'Record updated successfully';
    } else {
      echo 'Insertion Error: ' . mysqli_error($conn);
    }
  }
}

function removeBookReviews($conn, $id)
{
  $sql = "DELETE FROM reviews WHERE bookId = '$id'";

  mysqli_query($conn, $sql);
}

if (isset($_POST['submit'])) {
  $idToRemove = mysqli_real_escape_string($conn, $_POST['id-to-remove']);

  $votes = getCurrentUserVotes($idToRemove, $userId, $conn);

  removeVoteFromDB($votes, $conn);

  removeBookReviews($conn, $idToRemove);

  $sql = "DELETE FROM books WHERE id = $idToRemove";

  if (mysqli_query($conn, $sql)) {
    header('Location: mybooks.php?userId=' . $userId);
  } else {
    echo 'Query Error: ' . mysqli_error($conn);
  }
}


if (isset($_GET['id'])) {


  $id = mysqli_real_escape_string($conn, $_GET['id']);

  $sql = "SELECT * FROM books WHERE id = $id";

  $result = mysqli_query($conn, $sql);

  $book = mysqli_fetch_assoc($result);

  mysqli_free_result($result);


  $id = $book['userId'];

  $user = getSubmittedBy($id);
}



function generateGenreList($list)
{
  $html = '';
  $arr = explode(',', $list);

  foreach ($arr as $el) {
    $html .= "<p class=\"genre\"><i class=\"fas fa-map-pin genre-icon\"></i>$el</p>";
  }

  return $html;
}
if ($book) {
  $renderedList = generateGenreList($book['genres']);
}

function showRemoveBtnForOwner($user, $book)
{
  if ($user) {
    if ($user === $book) {
      return true;
    } else {
      return false;
    }
  }
}

$isOwnerOfBook = null;

if (isset($_SESSION['user'])) {

  global $isOwnerOfBook;

  $isOwnerOfBook = showRemoveBtnForOwner($_SESSION['user'], $book['userId']);
}

if (isset($_GET['rating'])) {
  $URL = "bookdetails.php?id=" . $book['id'];
  header("Location: " . $URL);
}


?>




<!DOCTYPE html>
<html lang="en">

<?php include('public/templates/head.php'); ?>

<body>
  <div class="content">

    <?php include('public/templates/nav.php'); ?>
    <?php if ($book) : ?>
      <div class="detail-container">
        <div class="modal hide-modal">
          <div class="modal-content">
            <p>Are you sure?</p>
            <div class="modal-button-container">
              <form action="bookdetails.php" method="POST" class="delete-form">
                <div>
                  <input type="hidden" name="id-to-remove" value="<?php echo $book['id'] ?>" />
                  <input class="yes" type="submit" name="submit" value="Yes" />
                </div>
              </form>
              <button class="no">Cancel</button>
            </div>
          </div>
        </div>
        <div class="detail-heading">
          <h3><?php echo htmlspecialchars($book['title']); ?></h3>
          <h5><span class="details-submitted">Book Submitted By:</span> <span><?php echo htmlspecialchars($user['username']); ?></span></h5>

        </div>
        <img src="<?php echo empty($book['image']) ? 'public/img/book.png' : htmlspecialchars($book['image']); ?>" alt="book" />
        <h4>Written By: <span><?php echo htmlspecialchars($book['author']) ?></span></h4>
        <div class="genre-container">
          <p>Genres:</p>

          <?php echo $renderedList; ?>
        </div>
        <p>Published Date: <span><?php echo htmlspecialchars($book['published']); ?></span></?>
          <p>Page Count: <span><?php echo htmlspecialchars($book['pages']); ?></span></p>
          <div class="blurb-divider"></div>
          <div>
            <p class="blurb"><?php echo htmlspecialchars($book['blurb']); ?></p>
          </div>
          <?php if ($isOwnerOfBook && isset($_SESSION['referer'])) : ?>
            <button class="remove-button">Remove</button>
            <a href="editbook.php?id=<?php echo htmlspecialchars($book['id']); ?>" class="edit">Edit</a>
          <?php endif; ?>
          <?php if (!$isOwnerOfBook && isset($_SESSION['profileId'])) : ?>
            <a href="createreview.php?bookId=<?php echo htmlspecialchars($book['id']); ?>" class="review-link" href="#"><i class="fas fa-pen"></i> Write review</a>
          <?php endif; ?>
      </div>

    <?php else : ?>
      <h2 class="book-error">This book does not exist</h2>
    <?php endif; ?>
    <?php include('public/templates/reviews.php'); ?>
  </div>

  <?php include('public/templates/footer.php'); ?>
  <script src="public/js/modal.js"></script>
  <script src="public/js/starRating.js"></script>
</body>

</html>