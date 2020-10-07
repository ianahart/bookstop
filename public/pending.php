<?php
session_start();



if (isset($_GET)) {
  $email = $_GET['email'];
}



?>



<!DOCTYPE html>
<html lang="en">
<?php include('public/templates/head.php'); ?>

<body>
  <div class="content">
    <?php include('public/templates/nav.php'); ?>
    <div class="pending">
      <p>We sent an email to <strong><?php echo htmlspecialchars($email); ?></strong> to help you recover your account </p>
      <p>Please login into your email account and click on the link we sent you to reset your password</p>
    </div>
  </div>
  <?php include('public/templates/footer.php') ?>
</body>

</html>