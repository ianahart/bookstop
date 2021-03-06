<?php
session_start();

if (!isset($_SESSION['user'])) {
  header("Location: login.php");
}
include('../config/db.php');

$errors = ['title' => '', 'author' => '', 'pages' => '', 'published' => '', 'blurb' => '', 'genres' => ''];
$letters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I ', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'];
$bookId = null;
$currentBook = null;



// HELPER FUNCTIONS
function checkForIntegers($name)
{
  $numberCount = 0;
  foreach (str_split($name) as $char) {
    if (is_numeric($char)) {
      $numberCount++;
    }
  }

  return $numberCount > 0;
}

function checkForOnlyIntegers($str, $alphabet)
{
  $letterIncluded = false;
  foreach (str_split($str) as $char) {
    if (in_array($char, $alphabet)) {
      global $letterIncluded;
      $letterIncluded = true;
    }
  }
  return $letterIncluded;
}

function validateList($str)
{
  $originalStr = substr($str, 0);
  $temp = explode(', ', $str);



  $testStr = implode(', ', $temp);

  $passedRegex = preg_match(
    '/^[a-zA-Z, ]*$/',
    $str
  );

  return $originalStr === $testStr && $passedRegex;
}



// GET BOOK FROM DB TO FILL FORM VALUES

if (isset($_GET['id'])) {
  global $bookId;
  $bookId = htmlspecialchars($_GET['id']);
  $sql = "SELECT * FROM books WHERE id = $bookId";

  $result = mysqli_query($conn, $sql);

  $currentBook = mysqli_fetch_assoc($result);

  mysqli_free_result($result);
}


if (isset($_POST['submit'])) {
  $title = $_POST['title'];
  $author = $_POST['author'];
  $pages = $_POST['pages'];
  $published = $_POST['published'];
  $blurb = $_POST['blurb'];
  $genres = $_POST['genres'];


  // TITLE
  if (!$title) {
    $errors['title'] = 'You must provide a Book Title';
  } else if (strlen($title) > 50) {
    $errors['title'] = 'Book Title cannot be more than forty characters';
  }

  // AUTHOR
  if (!$author) {
    $errors['author'] = 'You must provide an Author';
  } else if (strlen($author) > 30) {
    $errors['author'] = 'Author cannot be more than thirty characters';
  } else if (checkForIntegers($author)) {
    $errors['author'] = 'Poorly formatted Author name';
  }

  // PAGES
  if (!$pages) {
    $errors['pages'] = 'You must provide a Page count';
  } else if (strlen($pages) > 10) {
    $errors['pages'] = 'Page count cannot be more than ten characters';
  } else if (checkForOnlyIntegers($pages, $letters)) {
    $errors['pages'] = 'Page count cannot include letters';
  }

  // PUBLISHED
  if (!$published) {
    $errors['published'] = 'You must provide a Published Date';
  } else if (strlen($published) !== 4) {
    $errors['published'] = 'Published Date must be four digits';
  } else if (checkForOnlyIntegers($published, $letters)) {
    $errors['published'] = 'Published Date cannot include letters';
  } else if ($published > date('Y')) {
    $errors['published'] = 'Published Date cannot be greater than current year';
  }

  // DESCRIPTION
  if (strlen($blurb) === 0) {
    $errors['blurb'] = 'You must provide a short Description';
  } else if (strlen($blurb) > 500) {
    $errors['blurb'] = 'Description must be shorter than 500 characters';
  }

  // GENRES
  if (!$genres) {
    $errors['genres'] = 'You must provide at least one genre';
  } else if (checkForIntegers($genres)) {
    $errors['genres'] = 'Digits are not allowed for genre types';
  } else if (!validateList($genres)) {
    $errors['genres'] = 'Poorly formatted list';
  }

  $errorCount = array_filter($errors, function ($error) {
    return $error !== '';
  });


  if (!$errorCount) {
    $title = mysqli_real_escape_string($conn, $title);
    $author = mysqli_real_escape_string($conn, $author);
    $pages = mysqli_real_escape_string($conn, $pages);
    $published = mysqli_real_escape_string($conn, $published);
    $genres = mysqli_real_escape_string($conn, $genres);
    $blurb = mysqli_real_escape_string($conn, $blurb);
    $userId = $_SESSION['user'];


    $sql = "UPDATE books SET title= '$title', author = '$author', pages = '$pages', published = '$published', genres = '$genres', blurb = '$blurb' WHERE id=$bookId";

    $currentUser = $_SESSION['user'];

    if (mysqli_query($conn, $sql)) {
      if (isset($_SESSION['referer'])) {
        header('Location: mybooks.php?userId=' . $currentUser);
      } else {
        header('Location: books.php');
      }
    } else {
      echo 'Insertion Error: ' . mysqli_error($conn);
    }
  }
}

?>
<!DOCTYPE html>
<html lang="en">

<?php include('./templates/head.php'); ?>

<body>
  <div class="content">
    <?php include('./templates/nav.php'); ?>

    <div class="edit-form">
      <form action="editbook.php?id=<?php echo $_GET['id']; ?>" method="POST">
        <h1>Edit Book</h1>
        <div class="input-group">
          <label>Book Title:</label>
          <input type="text" name="title" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : $currentBook['title'];  ?>" />
          <div class="form-error">
            <?php echo $errors['title'] ?>
          </div>
        </div>
        <div class="input-group">
          <label>Author:</label>
          <input type="text" name="author" value="<?php echo isset($_POST['author']) ? htmlspecialchars($_POST['author']) : $currentBook['author'];  ?>" />
          <div class="form-error">
            <?php echo $errors['author']; ?>
          </div>
        </div>
        <div class="input-group">
          <label>Pages:</label>
          <input type="text" name="pages" value="<?php echo isset($_POST['pages']) ? htmlspecialchars($_POST['pages']) : $currentBook['pages'];  ?>" />
          <div class="form-error">
            <?php echo $errors['pages'] ?>
          </div>
        </div>
        <div class="input-group">
          <label>Published Date:</label>
          <input type="text" name="published" value="<?php echo isset($_POST['published']) ? htmlspecialchars($_POST['published']) : $currentBook['published'];  ?>" />
          <div class="form-error">
            <?php echo $errors['published'] ?>
          </div>
        </div>
        <div class="input-group">
          <label>Genres:(comma separated following a space)</label>
          <input type="text" name="genres" value="<?php echo isset($_POST['genres']) ? htmlspecialchars($_POST['genres']) : $currentBook['genres'];  ?>" />
          <div class="form-error">
            <?php echo $errors['genres']; ?>
          </div>
        </div>
        <div class="input-group">
          <label>Description:</label>
          <textarea type="text" name="blurb">
            <?php echo isset($_POST['blurb']) ? htmlspecialchars($_POST['blurb']) : $currentBook['blurb'];  ?>
          </textarea>
          <div class="form-error">
            <?php echo $errors['blurb']; ?>
          </div>
        </div>
        <div class="button-container">
          <input type="submit" name="submit" value="Edit" />
        </div>
      </form>
    </div>
  </div>
  <?php include('./templates/footer.php'); ?>
</body>

</html>