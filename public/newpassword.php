<?php
include(dirname(__DIR__) . '/config/db.php');




session_start();



$errors = ['password' => '', 'password2' => '', 'match' => ''];

if (isset($_GET['token'])) {
  $_SESSION['token'] = $_GET['token'];
}

function errorsPresent($errors)
{
  return array_filter($errors, function ($error) {
    return $error !== '';
  });
}

function validatePasswords($pass)
{
  $lower = 0;
  $upper = 0;
  $number = 0;

  foreach (str_split($pass) as $char) {
    if ($char === strtolower($char) && !is_numeric($char)) {
      $lower++;
    }
    if ($char === strtoupper($char) && !is_numeric($char)) {
      $upper++;
    }
    if (is_numeric($char)) {
      $number++;
    }
  }

  if ($upper > 0 && $lower > 0 && $number > 0) {
    return true;
  }

  return false;
}

// DATABASE WORK
function getAssociatedEmail($conn, $token)
{
  $sql = "SELECT email FROM password_resets WHERE token = '$token' ";

  $result = mysqli_query($conn, $sql);

  $row = mysqli_fetch_assoc($result);

  mysqli_free_result($result);

  return $row['email'];
}


function updatePassword($conn, $currentPassword, $newPassword, $email)
{
  $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

  $sql = "UPDATE users SET password = '$hashedPassword' WHERE email = '$email'";

  mysqli_query($conn, $sql);
}


function createNewPassword($conn, $email, $newPassword)
{
  $sql = "SELECT password FROM users WHERE email = '$email'";

  $result = mysqli_query($conn, $sql);

  $row = mysqli_fetch_assoc($result);

  mysqli_free_result($result);

  updatePassword($conn, $row['password'], $newPassword, $email);
}

if (isset($_POST['submit'])) {

  $password = $_POST['password'];
  $password2 = $_POST['password2'];

  // PASSWORD
  if (empty($password)) {

    $errors['password'] = 'Please enter a password';
  } else if (strlen($password) < 6) {

    $errors['password'] = 'Password must be minimum of six characters';
  } else if (!validatePasswords($password)) {

    $errors['password'] = 'Password must have one uppercase, one number, and one lowercase character';
  }
  // PASSWORD 2
  if (empty($password2)) {

    $errors['password2'] = 'Please enter a password';
  } else if (strlen($password2) < 6) {

    $errors['password2'] = 'Password must be minimum of six characters';
  } else if (!validatePasswords($password2)) {

    $errors['password2'] = 'Password must have one uppercase, one number, and one lowercase character';
  }

  // CHECK IF PASSWORDS ARE THE SAME
  if ($password !== $password2) {
    $errors['match'] = 'Passwords do not match';
  }

  // IF NO ERRORS GO ON AND QUERY PASSWORD RESET TABLE
  // FOR A ROW THAT HAS MATCHING TOKEN FROM TOKEN IN SESSION
  // VARIABLE. GET EMAIL QUERY USER TABLE FOR
  // EMAIL AND UPDATE PASSWORD FOR THAT EMAIL
  if (!errorsPresent($errors)) {
    global $conn;

    $token = $_SESSION['token'];

    $matchedEmail = getAssociatedEmail($conn, $token);

    createNewPassword($conn, $matchedEmail, $password);

    header("Location: login.php");
  }
}


?>





<!DOCTYPE html>
<html lang="en">
<?php include('public/templates/head.php'); ?>

<body>
  <div class="content">
    <?php include('public/templates/nav.php'); ?>
    <div class="new-password-form">
      <form action="newpassword.php" method="POST" autocomplete="false">
        <h1>New Password</h1>
        <div>
          <p class="form-error"><?php echo $errors['match']; ?></p>
        </div>
        <div class="input-group">
          <label>Password:</label>
          <input type="password" name="password" />
          <div>
            <p class="form-error"><?php echo $errors['password']; ?></p>
          </div>
        </div>
        <div class="input-group">
          <label>Confirm Password:</label>
          <input type="password" name="password2" />
        </div>
        <div>
          <p class="form-error"><?php echo $errors['password2']; ?></p>
        </div>
        <div class="button-container">
          <input type="submit" name="submit" value="Reset" />
        </div>
      </form>
    </div>
  </div>
  <?php include('public/templates/footer.php') ?>
</body>

</html>