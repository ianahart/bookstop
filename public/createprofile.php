<?php
include('../config/db.php');

session_start();

if (!isset($_SESSION['user'])) {
  header("Location: login.php");
}

if (isset($_SESSION['profileId'])) {
  header("Location: index.php");
}

$errors = ['username' => '', 'favoritebooks' => '', 'bio' => ''];


function validateFavoriteBooks($books)
{
  $originalStr = substr($books, 0);
  $booksArray = explode(', ', $books);



  $testStr = implode(', ', $booksArray);



  return $testStr === $originalStr && count($booksArray) === 3;
}

function getProfilePicture($pic)
{
  $name = $pic['name'];

  $target_dir = "upload/";

  $target_file = $target_dir . basename($name);

  $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

  $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

  if (in_array($imageFileType, $allowedExtensions) && $pic['size'] < 2087152) {

    $image_base64 = base64_encode(file_get_contents($pic['tmp_name']));

    $image = 'data:image/' . $imageFileType . ';base64,' . $image_base64;

    return $image;
  }
}

function createProfile($profile,  $conn)
{
  $temp = "'" . implode("','", array_values($profile)) . "'";

  $sql = "INSERT INTO profiles (username, favoritebooks, picture, bio, userId ) VALUES ($temp)";

  if (mysqli_query($conn, $sql)) {
    return true;
  } else {
    echo 'Insertion Error: ' . mysqli_error($conn) . "<br>";
  }

  return false;
}

function checkForUserName($conn, $name)
{
  $sql = "SELECT username FROM profiles WHERE username = '$name'";

  $result = mysqli_query($conn, $sql);

  $userName = mysqli_fetch_assoc($result);

  mysqli_free_result($result);

  if ($userName) {
    if ($userName['username']) {
      return true;
    }
    return false;
  }
}


if (isset($_POST['submit'])) {
  $profilePicture = getProfilePicture($_FILES['profilepicture']);

  $currentUser = $_SESSION['user'];

  $bioTemp = $_POST['bio'];

  $bio = str_replace("'", "\\'", $bioTemp);


  $profile = [
    'userName' => $_POST['username'],
    'favoriteBooks' => $_POST['favoritebooks'],
    'profilePicture' => $profilePicture,
    'bio' => $bio,
    'userId' => $currentUser,
  ];
  //NAME
  if (empty($profile['userName'])) {

    $errors['username'] = 'Please provide a username';
  } else if (strlen($profile['userName']) < 2) {

    $errors['username'] = 'Username must be a minimum of two characters';
  } else if (checkForUserName($conn, $profile['userName'])) {

    $errors['username'] = 'Username is already taken';
  }
  // FAVORITE BOOKS
  if (empty($profile['favoriteBooks'])) {

    $errors['favoritebooks'] = 'Please provide three books';
  } else if (!validateFavoriteBooks($profile['favoriteBooks'])) {

    $errors['favoritebooks'] = 'Please use a comma separated list and at least three books';
  }

  // BIO
  if (empty($bio)) {

    $errors['bio'] = 'Please provide a short bio about yourself';
  } else if (strlen($bio) > 1000) {

    $errors['bio'] = 'Bio must be shorter than 1000 characters';
  }

  // IF NO ERRORS
  if (!count(array_filter($errors))) {

    $profile = array_map(function ($prop) use ($conn) {

      return mysqli_escape_string($conn, $prop);
    }, $profile);

    if (createProfile($profile, $conn)) {
      $_SESSION['profileId'] = $profile['userId'];

      if (isset($_SESSION['profileId'])) {
        header("Location: index.php");
      }
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
    <div class="create-profile-form">
      <form action="createprofile.php" method="POST" enctype='multipart/form-data'>
        <h1>Create Profile</h1>
        <div class=" input-group">
          <label>Username:</label>
          <input type="text" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" />
          <div class="form-error">
            <?php echo $errors['username']; ?>
          </div>
        </div>

        <div class="input-group">
          <label>Three favorite Books: (comma separated)</label>
          <input type="text" name="favoritebooks" value=" <?php echo isset($_POST['favoritebooks']) ? htmlspecialchars($_POST['favoritebooks']) : ''; ?>" />
          <div class="form-error">
            <?php echo $errors['favoritebooks']; ?>
          </div>
        </div>
        <div class="input-group">
          <label>Upload Profile Picture</label>
          <input type="file" name="profilepicture" />
          <div class="form-error">
            <!-- error -->
          </div>
        </div>
        <div class="input-group">
          <label>Bio:</label>
          <textarea type="text" name="bio">
          <?php echo isset($_POST['bio']) ? htmlspecialchars($_POST['bio']) : ''; ?>
          </textarea>
          <div class="form-error">
            <?php echo $errors['bio']; ?>
          </div>
        </div>
        <div class="button-container">
          <input type="submit" name="submit" value="Create" />
        </div>
      </form>
    </div>
  </div>
  <?php include('./templates/footer.php'); ?>
</body>

</html>