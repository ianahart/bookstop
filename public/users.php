<?php
include(dirname(__DIR__) . '/config/db.php');

session_start();

if (!isset($_SESSION['user'])) {
  header("Location: login.php");
}


function fetchUser($conn, $value)
{
  $sql = "SELECT username, picture, pending_requests, userId FROM profiles WHERE username LIKE '%$value%'";

  $result = mysqli_query($conn, $sql);

  $foundUsers = mysqli_fetch_all($result, MYSQLI_ASSOC);

  mysqli_free_result($result);

  return $foundUsers;
}


function getPendingRequestsInvitee($conn, $invitee)
{

  $sql = "SELECT pending_requests FROM profiles WHERE userId = '$invitee'";

  $result = mysqli_query($conn, $sql);

  $pendingRequests = mysqli_fetch_assoc($result);

  mysqli_free_result($result);

  return $pendingRequests;
}


function sendRequestInvitation($conn, $inviter, $invitee, $inviteeRequests)
{
  $pendingArrayInvitee = null;
  $pendingStrInvitee = null;

  if (empty($inviteeRequests['pending_requests'])) {
    $pendingArrayInvitee = [];

    $pendingArrayInvitee[] = $inviter;

    $pendingStrInvitee = serialize($pendingArrayInvitee);
  } else {
    $pendingArrayInvitee = unserialize($inviteeRequests['pending_requests']);

    $pendingArrayInvitee[] = $inviter;

    $pendingStrInvitee = serialize($pendingArrayInvitee);
  }


  $sql = "UPDATE profiles SET pending_requests = '$pendingStrInvitee' WHERE userId = '$invitee'";

  mysqli_query($conn, $sql);
}

function getCurrentUser($conn, $currentUser)
{
  $sql = "SELECT pending_requests, friends FROM profiles WHERE userId = '$currentUser'";

  $result = mysqli_query($conn, $sql);

  $user = mysqli_fetch_assoc($result);

  mysqli_free_result($result);

  $user = array_map(function ($column) {

    return unserialize($column);
  }, $user);


  return $user;
}

function fetchUserList($conn)
{
  $sql = "SELECT userId, picture, pending_requests, username FROM profiles";

  $result = mysqli_query($conn, $sql);

  $userList = mysqli_fetch_all($result, MYSQLI_ASSOC);

  mysqli_free_result($result);

  return $userList;
}


if (isset($_POST['search'])) {
  unset($_POST['userlist']);
  $searchValue = $_POST['user'];

  $searchedUsers = fetchUser($conn, $searchValue);

  $resultsLength = count($searchedUsers);

  $currentUser = getCurrentUser($conn, $_SESSION['profileId']);
}

if (isset($_POST['friendrequest'])) {

  $requester = $_POST['requestmaker'];
  $receiver = $_POST['requestedid'];

  $pendingRequestsInvitee = getPendingRequestsInvitee($conn, $receiver);
  sendRequestInvitation($conn, $requester, $receiver, $pendingRequestsInvitee);
}

if (isset($_POST['userlist'])) {
  unset($_POST['searchUsers']);
  $userList = fetchUserList($conn);

  $currentUser = getCurrentUser($conn, $_SESSION['profileId']);
}

?>

<!DOCTYPE html>
<html lang="en">

<?php include('public/templates/head.php'); ?>

<body>
  <div class="content">
    <?php include('public/templates/nav.php'); ?>
    <div class="search-container">
      <form class="user-list-form" action="users.php" method="POST">
        <button class="user-list-btn" type="submit" name="userlist"><i class="fas fa-users"></i>User List</button>
      </form>
      <form class="user-search" action="users.php" method="POST">
        <div class="form-input-container">
          <h3>User Search:</h3>
          <input type="text" name="user" placeholder="Enter a username..." />
          <input type="submit" name="search" value="Search" />
        </div>
      </form>
      <?php if (isset($userList)) : ?>
        <div class="user-list-container">
          <?php foreach ($userList as $user) : ?>
            <div class="user-list-user">
              <div class="user-list-user-column">
                <img src="<?php echo !empty($user['picture']) ? $user['picture'] : 'img/no-profile-pic.png'; ?>" alt="<?php echo htmlspecialchars($user['username']); ?>" />
                <?php if (!empty($currentUser['friends']) && in_array($user['userId'], $currentUser['friends'])) : ?>
                  <a href="viewprofile.php?userId=<?php echo htmlspecialchars($user['userId']); ?>">
                    <h5><?php echo htmlspecialchars($user['username']); ?></h5>
                  </a>
                <?php else : ?>
                  <h5><?php echo htmlspecialchars($user['username']); ?></h5>
                <?php endif; ?>
              </div>
              <?php if (in_array($_SESSION['profileId'], empty(unserialize($user['pending_requests'])) ? [] : unserialize($user['pending_requests']))) : ?>
                <button class="user-status-btn">Pending...</button>
              <?php elseif (!empty($currentUser['friends']) && in_array($user['userId'], $currentUser['friends'])) : ?>
                <button class="user-status-btn"><i class="fas fa-check"></i> Friends</button>
              <?php elseif (!in_array($user['userId'], empty($currentUser['friends']) ? [] : $currentUser['friends']) && $user['userId'] !== $_SESSION['profileId']) :  ?>
                <form action="users.php" method="POST">
                  <input type="hidden" name="requestmaker" value="<?php echo htmlspecialchars($_SESSION['profileId']); ?>" />
                  <input type="hidden" name="requestedid" value="<?php echo htmlspecialchars($user['userId']); ?>" />
                  <button id="add-friend-btn" type="submit" name="friendrequest"><i class="fas fa-user-plus"></i>Add as Friend</button>
                </form>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <?php if (isset($searchedUsers)) : ?>
        <div class="users-container">
          <h4>Found <span><?php echo $resultsLength ?></span> results for "<?php echo htmlspecialchars($searchValue); ?>"</h4>
          <?php foreach ($searchedUsers as $user) : ?>
            <h3><?php echo htmlspecialchars($user['username']); ?></h3>
            <?php if (in_array($_SESSION['profileId'], !empty(unserialize($user['pending_requests']))  ? unserialize($user['pending_requests']) : [])) : ?>
              <button class="user-status-btn">Pending...</button>
            <?php elseif (!empty($currentUser['friends']) && in_array($user['userId'], $currentUser['friends'])) : ?>
              <button class="user-status-btn"><i class="fas fa-check"></i> Friends</button>
            <?php elseif (!in_array($user['userId'], empty($currentUser['friends']) ? [] : $currentUser['friends']) && $user['userId'] !== $_SESSION['profileId']) :  ?>
              <form action="users.php" method="POST">
                <input type="hidden" name="requestmaker" value="<?php echo htmlspecialchars($_SESSION['profileId']); ?>" />
                <input type="hidden" name="requestedid" value="<?php echo htmlspecialchars($user['userId']); ?>" />
                <button id="add-friend-btn" type="submit" name="friendrequest"><i class="fas fa-user-plus"></i>Add as Friend</button>
              </form>
            <?php endif; ?>
            <div class="user-image-container">
              <?php if (!empty($currentUser['friends']) && in_array($user['userId'], $currentUser['friends'])) : ?>
                <a href="viewprofile.php?userId=<?php echo htmlspecialchars($user['userId']); ?>"><img src="<?php echo !empty($user['picture']) ? $user['picture'] : 'img/no-profile-pic.png'; ?>" /></a>
              <?php else : ?>
                <img src="<?php echo !empty($user['picture']) ? $user['picture'] : 'img/no-profile-pic.png'; ?>" alt="<?php echo htmlspecialchars($user['username']); ?>" />
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <?php include('public/templates/footer.php'); ?>
</body>

</html>