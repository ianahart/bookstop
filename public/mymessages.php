<?php
session_start();
include(dirname(__DIR__) . '/config/db.php');

if (!isset($_SESSION['user'])) {
  header("Location: login.php");
}

if (isset($_SESSION['user']) && !isset($_SESSION['profileId'])) {
  header("Location: index.php");
}




if (!isset($_COOKIE['cur_time'])) {
  header("Refresh: 0");
} else {
  $timeMessageReceived = $_COOKIE["cur_time"];
}




function getUser($conn, $userId)
{
  $sql = "SELECT username, userId FROM profiles WHERE userId = '$userId'";

  $result = mysqli_query($conn, $sql);

  $user = mysqli_fetch_assoc($result);

  mysqli_free_result($result);

  return $user;
}

function getUserMessages($conn, $userName)
{
  $sql = "SELECT * FROM messages WHERE recipient = '$userName'";

  $result = mysqli_query($conn, $sql);

  $messages = mysqli_fetch_all($result, MYSQLI_ASSOC);

  mysqli_free_result($result);

  return $messages;
}

function formatTimestamp($time)
{
  $message_created_at = date('m/d/Y', strtotime($time));

  return $message_created_at;
}

function formatReturnedMessages($message)
{
  return array_combine(
    array_keys($message),
    array_map(function ($key, $column) {

      if ($key === 'messages') {
        return unserialize(base64_decode($column));
      } else if ($key === 'created_at') {
        return formatTimestamp($column);
      } else {
        return $column;
      }
    }, array_keys($message), $message)
  );
}

function limitText($text, $limit)
{
  if (str_word_count($text, 0) > $limit) {
    $words = str_word_count($text, 2);

    $pos   = array_keys($words);

    $text  = substr($text, 0, $pos[$limit]) . '...';
  }
  return $text;
}

function markAsRead($conn, $sender, $recipient)
{
  $sql = "UPDATE messages SET mark_as_read = 'true' WHERE sender = '$sender' AND recipient = '$recipient'";

  mysqli_query($conn, $sql);
}

function getNumOfNewMessages($conn, $recipient)
{
  $sql = "SELECT mark_as_read FROM messages WHERE recipient = '$recipient' AND mark_as_read = 'false'";

  $result = mysqli_query($conn, $sql);

  $newMessages = mysqli_num_rows($result);

  mysqli_free_result($result);

  return $newMessages;
}

function deleteMessage($conn, $id)
{
  $sql = "DELETE FROM messages WHERE id = '$id'";

  mysqli_query($conn, $sql);
}

function checkExistingMessages($conn, $sender, $recipient)
{
  $sql = "SELECT messages FROM messages WHERE sender = '$sender' AND recipient = '$recipient'";

  $messages = null;

  $result = mysqli_query($conn, $sql);

  $found = mysqli_num_rows($result);

  if ($found === 0) {
    $messages = 0;
  } else {
    $messages =  mysqli_fetch_assoc($result);
  }

  mysqli_free_result($result);

  return $messages;
}

function sendReplyMessage($conn, $message, $sender, $senderId, $recipient, $time)
{
  $matchFound = checkExistingMessages($conn, $sender, $recipient);
  if ($matchFound !== 0) {
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


$currentUser = getUser($conn, $_SESSION['profileId']);

$fetchedMessages = getUserMessages($conn, $currentUser['username']);




$numOfNewMessages = getNumOfNewMessages($conn, $currentUser['username']);



$messages = null;
foreach ($fetchedMessages as $message) {
  $messages[] = formatReturnedMessages($message);
}


if (isset($_POST['goback'])) {
  $sender = $_POST['sender'];
  $recipient = $fetchedMessages[0]['recipient'];

  markAsRead($conn, $sender, $recipient);

  header("Location: mymessages.php");
}

if (isset($_POST['read'])) {
  $sender = $_POST['read'];
  $recipient = $fetchedMessages[0]['recipient'];

  markAsRead($conn, $sender, $recipient);

  header("Location: mymessages.php");
}

if (isset($_POST['delete'])) {
  $messageId = $_POST['delete'];

  deleteMessage($conn, $messageId);

  header("Location: mymessages.php");
}

if (isset($_POST['reply'])) {
  $message = $_POST['message'];
  $correspondent = $_POST['usersento'];
  $messageSender = $currentUser['username'];
  $messageSenderId = $currentUser['userId'];

  sendReplyMessage($conn, $message, $messageSender, $messageSenderId, $correspondent, $timeMessageReceived);
}

?>


<!DOCTYPE html>
<html lang="en">
<?php include('public/templates/head.php'); ?>

<body>
  <div class="content">
    <?php include('public/templates/nav.php'); ?>
    <div class="messages-container">
      <header>
        <h1><?php echo htmlspecialchars($currentUser['username']); ?>, You have <span class="messages-count">(<?php echo $numOfNewMessages; ?>)</span> new messages...</h1>
      </header>
      <?php if ($messages) : ?>
        <div class="messages">
          <?php foreach ($messages as $array) : ?>
            <div class="reply-hidden">
              <div class="reply-form-container">
                <h3>Replying to: <?php echo htmlspecialchars($array['sender']); ?></h3>
                <form action="mymessages.php" method="POST" class="reply-form">
                  <div class="editable-div" contentEditable="true"></div>
                  <input type="hidden" name="usersento" value="<?php echo htmlspecialchars($array['sender']); ?>" />
                  <input class="hiddeninput" type="hidden" name="message" />
                  <div class="reply-form-btns">
                    <button type="submit" name="reply"><i class="fas fa-paper-plane"></i> SEND</button>
                    <button name="cancel">CANCEL</button>
                  </div>
                </form>
              </div>
            </div>
            <div class="inbox-message">
              <div class="message-preview" data-mark_as_read="<?php echo htmlspecialchars($array['mark_as_read']); ?>">
                <?php if ($array['mark_as_read'] === 'false') : ?>
                  <div class="dot-icon"></div>
                <?php endif; ?>
                <h4 class="new-message">
                  <?php echo htmlspecialchars($array['sender']); ?>
                </h4>
                <h4 class="new-message"><?php echo isset($array['message_recieved_time']) ? htmlspecialchars($array['message_recieved_time']) : htmlspecialchars($array['created_at']); ?></h4>
                <p class="new-message"><?php echo str_word_count(end($array['messages'])) > 15 ? limitText(str_replace("\\", "", end($array['messages'])), 15) : str_replace("\\", "", end($array['messages'])) ?></p>
              </div>
              <form action="mymessages.php" method="POST" class="message-controls">
                <button id="reply-trigger"><i class="fas fa-reply"></i> Reply</button>
                <?php if ($array['mark_as_read'] === 'false') : ?>
                  <button type="submit" name="read" value="<?php echo htmlspecialchars($array['sender']); ?>">Mark Read</button>
                <?php else : ?>
                  <button disabled><i class="fas fa-check"></i> Read</button>
                <?php endif; ?>
                <button type="submit" name="delete" value="<?php echo htmlspecialchars($array['id']); ?>"><i class="fas fa-trash"></i> Delete</button>
              </form>
            </div>
            <div class="inner-message hidden">
              <form action="mymessages.php" method="POST">
                <input type="hidden" name="sender" value="<?php echo $array['sender'];  ?>" />
                <button type="submit" name="goback" id="go-back-btn"><i class="fas fa-arrow-left left-arrow-icon"></i>Go back to inbox</button>
              </form>
              <h3 class="field new-message"><?php echo htmlspecialchars($array['sender']); ?></h3>
              <h5 class="read-date">Conversation started: <?php echo htmlspecialchars($array['created_at']); ?></h5>
              <?php foreach ($array['messages'] as $text) : ?>
                <p class="read-message"><?php echo htmlspecialchars(str_replace("\\", "", $text)); ?></p>
                <hr class="message-separator">
              <?php endforeach; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <?php include('public/templates/footer.php'); ?>
  <script src="public/js/messages.js"></script>
</body>

</html>