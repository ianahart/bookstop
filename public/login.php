<?php

include('config/db.php');

session_start();



if (!isset($_SESSION['login_attempts'])) {
  $_SESSION['login_attempts'] = 0;
}



if (isset($_SESSION['user'])) {
  header("Location: index.php");
}


function matchPassword($password, $hash)
{
  if (password_verify($password, $hash)) {
    return true;
  } else {
    return false;
  }
}

$userLoggedIn = false;
$errors = [];




function flagIPAddress($conn)
{
  $ipAddress = $_SERVER['REMOTE_ADDR'];

  $sql = "INSERT INTO login_attempts (ip_address) VALUES ('$ipAddress')";

  mysqli_query($conn, $sql);
}

function deleteExpiredIPAddresses($conn)
{
  $secondsToWait = 600;
  $currentTime = time() - $secondsToWait;
  $ipAddress = $_SERVER['REMOTE_ADDR'];
  $sql = "DELETE FROM login_attempts WHERE UNIX_TIMESTAMP(created_at) < $currentTime AND ip_address = '$ipAddress'";

  if (mysqli_query($conn, $sql)) {
    if (mysqli_affected_rows($conn) > 0) {
      unset($_SESSION['login_attempts']);
    }
  }
}

deleteExpiredIPAddresses($conn);



function checkEmailExists($conn, $email)
{

  $sql = "SELECT email FROM users WHERE email = '$email'";

  $result = mysqli_query($conn, $sql);

  $existingEmail = mysqli_fetch_assoc($result);

  if (!mysqli_num_rows($result)) {
    return 'This email address does not exist';
  }
}

if (isset($_POST['submit'])) {

  $email =  $_POST['email'];
  $password = $_POST['password'];
  $sql = "SELECT * FROM users WHERE email = '$email'";

  $emailDoesNotExist = checkEmailExists($conn, $email, $sql);

  $result = mysqli_query($conn, $sql);

  $user = mysqli_fetch_assoc($result);

  mysqli_free_result($result);



  if (!$email) {
    $errors[] = 'Please provide your Email Address';
  } else if (!$password) {
    $errors[] = 'Please provide your Password';
  } else if ($emailDoesNotExist) {
    $errors[] = $emailDoesNotExist;
  }

  if ($user) {
    if (matchPassword($password, $user['password'])) {
      $userLoggedIn = true;
      $_SESSION['user'] = $user['id'];
      $_SESSION['userName'] = $user['firstName'];
      unset($_SESSION['login_attempts']);
    } else {
      $errors[] = 'Email and Password do not match';
      $_SESSION['login_attempts']++;

      if ($_SESSION['login_attempts'] > 4) {
        flagIPAddress($conn);
      }
    }
  }


  if (isset($_SESSION['user'])) {
    header('Location: index.php');
  }
}

// USE TO CLEAR LOGIN ATTEMPTS (TESTING)
// unset($_SESSION['login_attempts']);

?>


<!DOCTYPE html>
<html lang="en">

<?php include('public/templates/head.php'); ?>

<body>
  <div class="content">
    <?php include('public/templates/nav.php'); ?>

    <div class="login-form">
      <form id="login-form" action="login.php" method="POST" autocomplete="false">
        <h1>Login</h1>
        <?php if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] > 4) : ?>
          <p>Too many unsuccessful Login attempts. Please wait ten minutes and refresh the page.</p>
        <?php else : ?>
          <?php foreach ($errors as $error) : ?>
            <p id="form-error" class="form-error"><?php echo $error; ?></p>
          <?php endforeach; ?>
          <div class="input-group">
            <label>Email:</label>
            <input type="text" name="email" />
          </div>
          <div class="input-group">
            <label>Password:</label>
            <input type="password" name="password" />
          </div>
          <div class="button-container">
            <input type="submit" name="submit" value="Login" />
          </div>
          <div>
            <p>Not registered? <a href="register.php">Create an Account</a></p>
          </div>
          <div>
            <p><a class="forgot-password-link" href="forgotpassword.php">Forgot Password?</a></p>
          </div>
        <?php endif; ?>
      </form>
    </div>
  </div>
  <?php include('public/templates/footer.php'); ?>

</body>

</html>