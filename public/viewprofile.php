<?php
include(dirname(__DIR__) . '/config/db.php');

session_start();

if (!isset($_SESSION['user'])) {
  header("Location: login.php");
}

if (isset($_GET['userId'])) {
  $userId = $_GET['userId'];
}

if (!isset($_SESSION['profileId']) && $_SESSION['user'] === $userId) {
  header("Location: index.php");
}



function fetchExtraDetails($conn, $id)
{

  $sql = "SELECT votedOn, created_at FROM users WHERE id = '$id'";

  $result = mysqli_query($conn, $sql);

  $details = mysqli_fetch_assoc($result);

  mysqli_free_result($result);

  $details['votedOn'] = count(explode(',', $details['votedOn'])) - 1;

  // construct joined date
  $year = date('Y', strtotime($details['created_at']));

  $month = date('M', strtotime($details['created_at']));

  $date = $month . ", " . $year;

  $details['created_at'] = $date;

  return $details;
}

function fetchUserProfile($conn, $id)
{
  $sql = "SELECT * FROM profiles WHERE userId = '$id'";

  $result = mysqli_query($conn, $sql);

  $profileResult = mysqli_fetch_assoc($result);

  mysqli_free_result($result);

  $extraDetails = fetchExtraDetails($conn, $id);

  $profileResult['bio'] = str_replace("\\", "", $profileResult['bio']);

  $profile = array_merge($profileResult, $extraDetails);

  return $profile;
}

$userProfile = fetchUserProfile($conn, $userId);



?>


<!DOCTYPE html>
<html lang="en">
<?php include('public/templates/head.php'); ?>

<body>
  <div class="content">
    <?php include('public/templates/nav.php'); ?>
    <div class="view-profile-container">
      <div class="view-profile">
        <div class="profile-username">
          <h2><?php echo htmlspecialchars($userProfile['username']); ?></h2>
        </div>
        <div class="left-column">
          <div>
            <img src="<?php echo !empty($userProfile['picture']) ? $userProfile['picture'] : 'img/no-profile-pic.png';   ?>" alt="<?php echo htmlspecialchars($userProfile['username']); ?>" />
          </div>
          <div>
            <p><i class="fas fa-user"></i> Member since: <span><?php echo htmlspecialchars($userProfile['created_at']); ?></span></p>
          </div>
          <div>
            <p><i class="fas fa-vote-yea"></i> Votes casted: <span><?php echo htmlspecialchars($userProfile['votedOn']); ?></span></p>
          </div>
          <div>
            <p><i class="fas fa-book"></i> Favorite Books: <span><?php echo htmlspecialchars($userProfile['favoritebooks']); ?></span></p>
          </div>
        </div>
        <div class="right-column">
          <p class="profile-bio"><i class="fas fa-address-card"></i> <?php echo htmlspecialchars($userProfile['bio']); ?></p>
        </div>
      </div>
    </div>
  </div>
  <?php include('public/templates/footer.php'); ?>
</body>

</html>