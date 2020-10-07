<?php

include(dirname(__DIR__) . '/../config/db.php');





function getPicture($conn, $id)
{
  $sql = "SELECT picture FROM profiles WHERE userId = '$id'";

  $result = mysqli_query($conn, $sql);

  $picture = mysqli_fetch_assoc($result);

  mysqli_free_result($result);

  return $picture;
}

if (isset($_SESSION['profileId'])) {
  $profilePicture = getPicture($conn, $_SESSION['profileId']);
}
?>


<div class="account hidden">
  <div class="name">
    <?php if (isset($_SESSION['profileId'])) : ?>
      <img class="account-picture" src="<?php echo htmlspecialchars($profilePicture['picture']); ?>" />
    <?php endif; ?>
    <p><?php echo "Hello, " . htmlspecialchars($_SESSION['userName']); ?></p>
    <p><span></span>Online</p>
    <div class="account-line"></div>
  </div>
  <div class="settings">
    <h4>User Settings</h4>
    <div class="account-line"></div>
    <div class="create-profile-link">
      <?php if (isset($_SESSION['profileId'])) : ?>
        <a href="viewprofile.php?userId=<?php echo htmlspecialchars($_SESSION['profileId']) ?>"><i class="fas fa-address-card profile-link-icon"></i> View Profile</a>
      <?php else :  ?>
        <a href="createprofile.php">Create Profile</a>
      <?php endif; ?>
    </div>
    <?php if (isset($_SESSION['profileId'])) : ?>
      <div class="edit-profile-link">
        <a href="editprofile.php?userId=<?php echo $_SESSION['profileId']; ?>"><i class="fas fa-edit profile-link-icon"></i> Edit Profile</a>
      </div>
    <?php endif; ?>
    <div class="my-books-link">
      <a href="mybooks.php?userId=<?php echo $_SESSION['profileId']; ?>"><i class="fas fa-book-open profile-link-icon"></i> My Books</a>
    </div>
    <?php if (isset($_SESSION['profileId'])) : ?>
      <div class="my-friends-link">
        <a href="myfriends.php"><i class="fas fa-user-friends"></i>Friends</a>
      </div>
      <div class="my-messages-link">
        <a href="mymessages.php"><i class="fas fa-comment"></i>Messages</a>
      </div>
    <?php endif; ?>
  </div>
  <div class="search-settings">
    <h4>Search</h4>
    <div class="account-line"></div>
    <div class="search-user-link">
      <a href="users.php">Users</a>
    </div>
  </div>
  <div class="account-settings">
    <h4>Account</h4>
    <div class="account-line"></div>
    <div class="logout-link">
      <a href="logout.php">Logout</a>
    </div>
    <div class="reset-password-link">
      <a href="forgotpassword.php">Reset Password</a>
    </div>
    <div class="delete-account-link">
      <a class="delete-account-btn" href="#">Delete Account</a>
      <div class="delete-popup hidden">
        <?php include('deleteaccount.php'); ?>
      </div>
    </div>
  </div>