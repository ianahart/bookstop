<?php
include(dirname(__DIR__) . '/config/db.php');
session_start();

if (!isset($_SESSION['user'])) {
  header("Location: login.php");
}

if (!isset($_SESSION['profileId']) && isset($_SESSION['user'])) {
  header("Location: index.php");
}



if (!isset($_COOKIE['cur_time'])) {
  header("Refresh: 0");
} else {
  $timeMessageReceived = $_COOKIE["cur_time"];
}

function getPendingFriendRequestsIDs($conn, $userId)
{
  $sql = "SELECT  pending_requests FROM profiles WHERE userId = '$userId'";

  $result = mysqli_query($conn, $sql);

  $userData = mysqli_fetch_assoc($result);

  mysqli_free_result($result);

  return $userData;
}

$friendRequests = getPendingFriendRequestsIDs($conn, $_SESSION['profileId']);

if (!empty($friendRequests['pending_requests'])) {
  $friendRequestsArray = unserialize($friendRequests['pending_requests']);

  $friendRequestData = [];
}



function formatDate($unformattedDate)
{
  $month = date('M', strtotime($unformattedDate));

  $year = date('Y', strtotime($unformattedDate));

  $formattedDate = "Member since: " . $month . ", " . $year;

  return $formattedDate;
}

function getJoinedDate($conn, $user)
{

  $sql = "SELECT created_at FROM users WHERE id = '$user'";

  $result = mysqli_query($conn, $sql);

  $data = mysqli_fetch_assoc($result);


  if ($data) {
    $createdAt = formatDate($data['created_at']);
  }
  mysqli_free_result($result);



  return $createdAt;
}


function getUserData($conn, $user)
{
  $sql = "SELECT username, userId, picture FROM profiles WHERE userId = '$user'";

  $result = mysqli_query($conn, $sql);

  $userData = mysqli_fetch_assoc($result);

  $joinedDate = getJoinedDate($conn, $user);

  $userData['joined'] = $joinedDate;

  mysqli_free_result($result);

  return $userData;
}
if (!empty($friendRequestsArray)) {
  foreach ($friendRequestsArray as $friendRequest) {

    $friendRequestData[] = getUserData($conn, $friendRequest);
  }
}



function getFriendsList($conn, $user)
{
  $sql = "SELECT friends FROM profiles WHERE userId = '$user'";

  $result = mysqli_query($conn, $sql);

  $fetchedFriends = mysqli_fetch_assoc($result);

  mysqli_free_result($result);

  if (empty($fetchedFriends['friends'])) {
    return [];
  } else {
    return $fetchedFriends;
  }
}

function addFriend($conn, $friend, $currentUser)
{
  $myList = getFriendsList($conn, $currentUser);
  $requesterList = getFriendsList($conn, $friend);

  if (empty($myList['friends'])) {

    $myList['friends'][] = $friend;

    $serializedMyList = serialize($myList['friends']);
  } else {
    $deserializedMyList = unserialize($myList['friends']);

    $deserializedMyList[] = $friend;

    $serializedMyList = serialize($deserializedMyList);
  }

  if (empty($requesterList['friends'])) {

    $requesterList['friends'][] = $currentUser;

    $serializedRequesterList = serialize($requesterList['friends']);
  } else {
    $deserializedRequesterList = unserialize($requesterList['friends']);

    $deserializedRequesterList[] = $currentUser;

    $serializedRequesterList = serialize($deserializedRequesterList);
  }

  echo $friend;
  $sql = "UPDATE profiles SET friends = '$serializedMyList' WHERE userId = '$currentUser'";

  mysqli_query($conn, $sql);

  $sql = "UPDATE profiles SET friends = '$serializedRequesterList' WHERE userId = '$friend'";

  mysqli_query($conn, $sql);
}

function removeFriendRequest($conn, $request, $currentUser)
{
  $sql = "SELECT pending_requests FROM profiles WHERE userId = $currentUser";

  $result = mysqli_query($conn, $sql);

  $user = mysqli_fetch_assoc($result);

  mysqli_free_result($result);

  $deserializedPendingRequests = unserialize($user['pending_requests']);

  $filteredRequests = array_filter($deserializedPendingRequests, function ($pendingRequest) use ($request) {
    return $pendingRequest !== $request;
  });

  $serializedRequests = serialize($filteredRequests);

  $sql = "UPDATE profiles SET pending_requests = '$serializedRequests' WHERE userId = $currentUser";

  mysqli_query($conn, $sql);
}

function getCurrentUserFriends($conn, $userId)
{
  $sql = "SELECT friends FROM profiles WHERE userId = '$userId'";

  $result = mysqli_query($conn, $sql);

  $friendsData = mysqli_fetch_assoc($result);

  mysqli_free_result($result);

  $friends = [];
  if (!empty($friendsData['friends'])) {
    $unserializedFriends = unserialize($friendsData['friends']);



    foreach ($unserializedFriends as $friend) {
      $friends[] = getUserData($conn, $friend);
    }
  }

  return $friends;
}

function removeFriendship($conn, $userToRemove, $user)
{
  $sql = "SELECT friends FROM profiles WHERE userId = '$user'";

  $result = mysqli_query($conn, $sql);

  $serializedFriendsList = mysqli_fetch_assoc($result);

  mysqli_free_result($result);

  $friendsList = unserialize($serializedFriendsList['friends']);

  $filteredList = array_filter($friendsList, function ($friend) use ($userToRemove) {
    return $friend !== $userToRemove;
  });

  $updatedFriendsList = serialize($filteredList);

  $sql = "UPDATE profiles SET friends = '$updatedFriendsList' WHERE userId = '$user'";

  mysqli_query($conn, $sql);
}


if (isset($_POST['accept-friend'])) {
  $newFriend = $_POST['friendrequest'];

  addFriend($conn, $newFriend, $_SESSION['profileId']);

  removeFriendRequest($conn, $newFriend, $_SESSION['profileId']);
  removeFriendRequest($conn, $_SESSION['profileId'], $newFriend);


  header("Location: myfriends.php");
}

if (isset($_POST['deny-friend'])) {
  $friendRequest = $_POST['friendrequest'];
  removeFriendRequest($conn, $friendRequest, $_SESSION['profileId']);


  header("Location: myfriends.php");
}


if (isset($_POST['removefriend'])) {
  $friendToRemove = $_POST['friendtoremove'];

  removeFriendship($conn, $friendToRemove, $_SESSION['profileId']);
  removeFriendship($conn, $_SESSION['profileId'], $friendToRemove);

  header("Location: myfriends.php");
}

function checkForExistingMessages($conn, $recipient, $sender)
{
  $sql = "SELECT * FROM messages WHERE recipient = '$recipient' AND sender = '$sender'";

  $result = mysqli_query($conn, $sql);

  $matchFound = mysqli_fetch_assoc($result);

  mysqli_free_result($result);

  return $matchFound;
}

function addMessage($conn, $message, $recipient, $senderId, $sender, $time)
{

  $matchFound = checkForExistingMessages($conn, $recipient, $sender);

  if ($matchFound) {
    $deserializedArray = unserialize(base64_decode($matchFound['messages']));
    $deserializedArray[] = $message;
    $serializedMessage = base64_encode(serialize($deserializedArray));

    $sql = "UPDATE messages SET messages = '$serializedMessage', message_recieved_time = '$time', mark_as_read = 'false' WHERE recipient = '$recipient' AND sender = '$sender'";

    mysqli_query($conn, $sql);
  } else {

    $serializedMessage = base64_encode(serialize([$message]));
    $sql = "INSERT INTO messages (recipient, sender, senderId, messages, mark_as_read) VALUES ('$recipient', '$sender', '$senderId', '$serializedMessage', 'false')";

    mysqli_query($conn, $sql);
  }
}

$currentUser = getUserData($conn, $_SESSION['profileId']);


if (isset($_POST['sendmessage'])) {
  $message = mysqli_real_escape_string($conn, $_POST['hiddeninput']);
  $recipient = mysqli_real_escape_string($conn, $_POST['recipient']);
  $senderId = mysqli_real_escape_string($conn, $currentUser['userId']);
  $sender = mysqli_real_escape_string($conn, $currentUser['username']);

  addMessage($conn, $message, $recipient, $senderId, $sender, $timeMessageReceived);
}


$friends = getCurrentUserFriends($conn, $_SESSION['profileId']);
?>

<!DOCTYPE html>
<html lang="en">
<?php include('public/templates/head.php'); ?>

<body>
  <div class="content">
    <?php include('public/templates/nav.php'); ?>
    <div class="friend-requests-container">
      <div class="requests-header">
        <h3>You have <span>(<?php echo isset($friendRequestData) ? htmlspecialchars(count($friendRequestData)) : '0'; ?>)</span> friend requests</h3>
      </div>
      <?php if (!empty($friendRequestData)) : ?>
        <?php foreach ($friendRequestData as $friendRequest) : ?>
          <div class="friend-request">
            <div>
              <h3><?php echo htmlspecialchars($friendRequest['username']); ?></h3>
              <p><?php echo htmlspecialchars($friendRequest['joined']); ?></p>
              <div class="friend-request-image-container">
                <img src="<?php echo !empty($friendRequest['picture']) ? $friendRequest['picture'] : 'public/img/no-profile-pic.png'; ?>" alt="<?php echo htmlspecialchars($friendRequest['username']); ?>" />
              </div>
            </div>
            <div>
              <form action="myfriends.php" method="POST">
                <input type="hidden" name="friendrequest" value="<?php echo htmlspecialchars($friendRequest['userId']); ?>" />
                <button type="submit" name="accept-friend">Accept</button>
                <button type="submit" name="deny-friend">Decline</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <div class="friends-list-container">
      <div class="friends-list-header">
        <h3><?php echo htmlspecialchars($currentUser['username']); ?>, Here are your friends... <span>(<?php echo isset($friends) ? count($friends) : '0'; ?>)</span></h3>
      </div>
      <?php if (!empty($friends)) : ?>
        <div class="filter-container">
          <input type="text" name="filter" placeholder="Filter Friends..." />
        </div>
        <div class="friends-list">
          <?php foreach ($friends as $friend) : ?>
            <div class="friend">
              <div id="message-modal" class="hidden">
                <div class="modal-content">
                  <form action="myfriends.php" method="POST">
                    <h3>Sending message to: <?php echo htmlspecialchars($friend['username']); ?></h3>
                    <div contentEditable="true" name="message"></div>
                    <input type="hidden" name="recipient" value="<?php echo htmlspecialchars($friend['username']); ?>" />
                    <input class="hiddeninput" type="hidden" name="hiddeninput"></input>
                    <div class="modal-content-btns">
                      <button type="submit" name="sendmessage"><i id="send-icon" class="fas fa-paper-plane"></i>SEND</button>
                      <button class="cancel-message">CANCEL</button>
                    </div>
                  </form>
                </div>
              </div>
              <h3 class="friend-username"><?php echo htmlspecialchars($friend['username']); ?></h3>

              <button id="message-btn"><i class="fas fa-comment"></i>Message</button>
              <form action="myfriends.php" method="POST">
                <input type="hidden" name="friendtoremove" value="<?php echo htmlspecialchars($friend['userId']); ?>" />
                <button type="submit" name="removefriend"><i class="fas fa-user-slash"></i>Unfriend</button>
              </form>
              <div class="friend-picture-container">
                <a href="viewprofile.php?userId=<?php echo htmlspecialchars($friend['userId']); ?>"><img src="<?php echo !empty($friend['picture']) ? $friend['picture'] : 'public/img/no-profile-pic.png'; ?>" alt="<?php echo htmlspecialchars($friend['username']); ?>" /></a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <?php include('public/templates/footer.php'); ?>
  <script src="public/js/myFriends.js"></script>
</body>

</html>