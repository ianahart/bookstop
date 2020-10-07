<?php
session_start();
include(dirname(__DIR__) . '/config/db.php');


if (isset($_SESSION['user'])) {
  header("Location: index.php");
}


function hashPassword($password)
{

  return password_hash($password, PASSWORD_BCRYPT);
}

function validatePassword($password)
{
  $number = false;
  $uppercase = false;

  foreach (str_split($password) as $char) {
    if ($char === strtoupper($char) && !is_numeric($char)) {
      global $uppercase;
      $uppercase = true;
    }
    if (is_numeric($char)) {
      global $number;
      $number = true;
    }
  }
  return $number && $uppercase ? true : false;
}

// Check DB for existing email
function CheckForExistingEmail($emailAddress)
{
  $emailExists = false;
  global $conn;

  $sql = "SELECT email FROM users WHERE email = '$emailAddress'";



  $result = mysqli_query($conn, $sql);

  $fetchedEmail = mysqli_fetch_assoc($result);

  mysqli_free_result($result);

  if ($fetchedEmail) {
    if (count($fetchedEmail) === 1) {
      $emailExists = true;
    }

    return $emailExists;
  }
}



$errors = ['firstName' => '', 'lastName' => '', 'email' => '', 'password' => ''];



if (isset($_POST['submit'])) {
  $firstName = $_POST['first_name'];
  $lastName = $_POST['last_name'];
  $email = $_POST['email'];
  $password = $_POST['password'];

  // FIRST NAME
  if (!strlen($firstName)) {
    $errors['firstName'] = 'Please provide a firstName';
  } else if (strlen($firstName) < 2) {
    $errors['firstName'] = 'Name should be minimum two characters';
  }

  // FIRST NAME
  if (!strlen($lastName)) {
    $errors['lastName'] = 'Please provide a lastName';
  } else if (strlen($lastName) < 2) {
    $errors['lastName'] = 'Name should be minimum two characters';
  }

  // EMAIL
  if (!strlen($email)) {
    $errors['email'] = 'Please provide an email address';
  } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Please provide a valid email address';
  } else if (CheckForExistingEmail($email)) {
    $errors['email'] = 'Email Address already exists';
  }

  // PASSWORD
  if (!strlen($password)) {
    $errors['password'] = 'Please provide a password';
  } else if (strlen($password) < 6) {
    $errors['password'] = 'Password should be minimum six characters';
  } else if (!validatePassword($password)) {
    $errors['password'] = 'Password must include one uppercase letter and a digit';
  }


  function checkErrors($errors)
  {
    return array_filter($errors, function ($error) {
      return $error !== '';
    });
  }

  if (!checkErrors($errors)) {
    // header("Location: login.php");
    $firstName = mysqli_escape_string($conn, $firstName);
    $lastName = mysqli_escape_string($conn, $lastName);
    $email = mysqli_escape_string($conn, $email);
    $password = mysqli_escape_string($conn, $password);
    $hashedPassword = hashPassword($password);

    $sql = "INSERT INTO users (firstName, lastName, email, password) VALUES ('$firstName','$lastName', '$email', '$hashedPassword')";

    if (mysqli_query($conn, $sql)) {
      header('Location: login.php');
    } else {
      echo 'Query Error: ' . mysqli_error($conn);
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<?php include('public/templates/head.php'); ?>

<body>
  <div class="content">
    <?php include('public/templates/nav.php'); ?>
    <div class="register-form">
      <form action="register.php" method="POST">
        <h1>Create Account</h1>


        <div class="input-group">
          <label>First Name:</label>
          <input type="text" name="first_name" value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : '';  ?>" />
          <div class="form-error">
            <?php echo $errors['firstName']; ?>
          </div>
        </div>
        <div class="input-group">
          <label>Last Name:</label>
          <input type="text" name="last_name" value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : '';  ?>" />
          <div class="form-error">
            <?php echo $errors['lastName']; ?>
          </div>
        </div>
        <div class="input-group">
          <label>Email:</label>
          <input type="text" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" />
          <div class="form-error">
            <?php echo $errors['email']; ?>
          </div>
        </div>
        <div class="input-group">
          <label>Password:</label>
          <input type="password" name="password" value="<?php echo isset($_POST['password']) ? htmlspecialchars($_POST['password']) : ''; ?>" />
          <div class="form-error">
            <?php echo $errors['password']; ?>
          </div>
        </div>
        <div class="button-container">
          <input type="submit" name="submit" value="Create" />
        </div>
      </form>
    </div>
  </div>
  <?php include('public/templates/footer.php'); ?>
</body>

</html>