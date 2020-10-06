<?php
include('../config/db.php');

session_start();

if (!isset($_SESSION['user'])) {
  header("Location: login.php");
}

if (!isset($_SESSION['profileId'])) {
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

function updateProfile($profile,  $conn)
{

  extract($profile);

  $sql = "UPDATE profiles SET username = '$userName', favoritebooks = '$favoriteBooks', picture = '$profilePicture', bio = '$bio' WHERE userId = '$userId'";


  if (mysqli_query($conn, $sql)) {
    return true;
  } else {
    echo 'Insertion Error: ' . mysqli_error($conn) . "<br>";
  }

  return false;
}

function checkForUserName($conn, $name)
{

  $currentUser = $_SESSION['profileId'];

  $sql = "SELECT username, userId FROM profiles WHERE username = '$name'";

  $result = mysqli_query($conn, $sql);

  $userName = mysqli_fetch_assoc($result);

  mysqli_free_result($result);

  if ($userName) {
    if ($userName['username'] && $userName['userId'] !== $currentUser) {
      return true;
    }
    return false;
  }
}


function fetchExistingProfileValues($conn, $id)
{
  $sql = "SELECT * FROM profiles WHERE userId = '$id'";

  $result = mysqli_query($conn, $sql);

  $profile = mysqli_fetch_assoc($result);

  mysqli_free_result($result);

  return $profile;
}

if (isset($_GET['userId'])) {
  $profileId = $_GET['userId'];

  $temp = fetchExistingProfileValues($conn, $profileId);
  $temp['bio'] = str_replace('\\', '', $temp['bio']);

  $existingProfileValues = $temp;
}


if (isset($_POST['editprofile'])) {
  $profilePicture = getProfilePicture($_FILES['profilepicture']);

  $currentUser = $_SESSION['user'];

  $bio = $_POST['bio'];

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

    if (updateProfile($profile, $conn)) {

      header("Location: index.php");
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
    <div class="edit-profile-form">
      <form action="editprofile.php?userId=<?php echo $profileId; ?>" method="POST" enctype='multipart/form-data'>
        <h1>Edit Profile</h1>
        <div class=" input-group">
          <label>Username:</label>
          <input type="text" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : htmlspecialchars($existingProfileValues['username']); ?>" />
          <div class="form-error">
            <?php echo $errors['username']; ?>
          </div>
        </div>

        <div class="input-group">
          <label>Three favorite Books: (comma separated)</label>
          <input type="text" name="favoritebooks" value=" <?php echo isset($_POST['favoritebooks']) ? htmlspecialchars($_POST['favoritebooks']) : htmlspecialchars($existingProfileValues['favoritebooks']); ?>" />
          <div class="form-error">
            <?php echo $errors['favoritebooks']; ?>
          </div>
        </div>
        <div class="input-group">
          <label>Upload Profile Picture</label>
          <input type="file" name="profilepicture" />
          <?php if (!empty($existingProfileValues['picture'])) : ?>
            <a href="<?php echo htmlspecialchars($existingProfileValues['picture']) ?>" download>Download previous picture</a>
          <?php endif; ?>
          <div class="form-error">
            <!-- error -->
          </div>
        </div>
        <div class="input-group">
          <label>Bio:</label>
          <textarea type="text" name="bio">
          <?php echo isset($_POST['bio']) ? htmlspecialchars($_POST['bio']) : htmlspecialchars($existingProfileValues['bio']); ?>
          </textarea>
          <div class="form-error">
            <?php echo $errors['bio']; ?>
          </div>
        </div>
        <div class="button-container">
          <input type="submit" name="editprofile" value="Edit" />
        </div>
      </form>
    </div>
  </div>
  <?php include('./templates/footer.php'); ?>
</body>

</html>