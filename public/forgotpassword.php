<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


require(dirname(__DIR__) . '/vendor/autoload.php');
include(dirname(__DIR__) . '/config/db.php');


session_start();


$mail = new PHPMailer(true);

function checkExistingEmail($conn, $email)
{
  $doesEmailExist = false;

  $sql = "SELECT email FROM users WHERE email = '$email'";

  $result = mysqli_query($conn, $sql);

  $email = mysqli_fetch_assoc($result);

  if (mysqli_num_rows($result) === 1) {
    $doesEmailExist = true;
  }
  mysqli_free_result($result);



  return $doesEmailExist;
}

$errors = ['email' => ''];

function addToPasswordResets($conn, $token, $email)
{
  $sql = "INSERT INTO password_resets (token, email) VALUES('$token', '$email')";

  mysqli_query($conn, $sql);
}

function sendEmailVerification($email, $token)
{
  global $mail;



  //Server settings
  $mail->SMTPDebug = 2;
  $mail->isSMTP();
  $mail->Host       =  'smtp.aol.com';
  $mail->SMTPAuth   = true;
  $mail->Username   = 'edabitphp@aol.com';
  $mail->Password   = 'test123456789';
  $mail->SMTPSecure = 'tls1.3';
  $mail->Port       = 25;

  //Recipients
  $mail->setFrom($email);
  $mail->addAddress($email);

  // Content
  $mail->isHTML(true);
  $mail->Subject = 'Reset Password on Bookstop';
  //CHANGE LINK ADDRESS
  $mail->Body    = "Hi there, click on this <a href=\"http://https://murmuring-ridge-11006.herokuapp.com/newpassword.php?token=" . $token . "\">link</a> to reset your password on our site";

  $mail->send();

  header("Location: pending.php?email=$email");
}

if (isset($_POST['submit'])) {

  $email =  mysqli_real_escape_string($conn, $_POST['email']);

  $doesEmailExist = checkExistingEmail($conn, $email);

  if (!$email) {

    $errors['email'] = 'Please provide your Email Address';
  } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    $errors['email'] = 'Please check for properly formatted Email Address';
  } else if (!$doesEmailExist) {

    $errors['email'] = 'Email Address not does exist';
  }

  $token = bin2hex(random_bytes(50));


  if (!$errors['email']) {

    addToPasswordResets($conn, $token, $email);

    sendEmailVerification($email, $token);
  }
}




?>
<!DOCTYPE html>
<html lang="en">
<?php include('public/templates/head.php'); ?>

<body>
  <div class="content">
    <?php include('public/templates/nav.php'); ?>
    <div class="forgot-password-form">
      <form action="forgotpassword.php" method="POST" autocomplete="false">
        <h1>Reset Password</h1>
        <div class="input-group">
          <label>Email:</label>
          <input type="text" name="email" />
        </div>
        <div>
          <p class="form-error"><?php echo $errors['email']; ?></p>
        </div>
        <div class="button-container">
          <input type="submit" name="submit" value="Send" />
        </div>
      </form>
    </div>
  </div>
  <?php include('public/templates/footer.php') ?>
</body>

</html>