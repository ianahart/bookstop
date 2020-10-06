<?php
session_start();



include('../config/db.php');







function applyProfileView($conn, $userId)
{
  $data = null;

  $sql = "SELECT userId FROM profiles WHERE userId = '$userId'";

  $result = mysqli_query($conn, $sql);

  $profile = mysqli_fetch_assoc($result);

  if (mysqli_num_rows($result) === 1) {
    $data = $profile['userId'];
  }
  mysqli_free_result($result);

  return $data;
}

if (isset($_SESSION['user'])) {
  $profileId = applyProfileView($conn, $_SESSION['user']);
  $_SESSION['profileId'] = $profileId;
}



?>

<!DOCTYPE html>
<html lang="en">

<?php include('public/templates/head.php'); ?>


<body>
  <div class="content">
    <?php include('public/templates/nav.php'); ?>
    <section class="jumbotron">
      <div class="home-header">
        <h1>The Premier Stop For Book Information</h1>
        <h3>Helping others discover books since 2020</h3>
      </div>

      <div class="images-container">
        <img src="public/img/home-1.jpg" alt="books" />
        <img src="public/img/home-2.jpg" alt="books" />
      </div>
    </section>
    <section class="extra-info">
      <h3>Bookstop Cares</h3>
      <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Ipsum explicabo sit odit eveniet numquam quia alias molestiae dolorum fugit quidem nemo minima sed culpa ex est reprehenderit excepturi cum, exercitationem incidunt tenetur aut aperiam. Voluptatem quos dolor, doloremque ipsum blanditiis, ducimus, quasi asperiores vitae odio exercitationem sunt delectus laborum nihil?</p>
    </section>
    <section class="social">
      <div>
        <i class="fab fa-facebook fa-3x"></i>
        <i class="fab fa-twitter-square fa-3x"></i>
        <i class="fab fa-instagram-square fa-3x"></i>
        <i class="fab fa-google-plus-square fa-3x"></i>
      </div>
    </section>
  </div>

  <?php include('public/templates/footer.php'); ?>
</body>

</html>