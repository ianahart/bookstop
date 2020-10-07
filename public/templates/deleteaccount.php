<?php

function getUserName($conn, $userId)
{
  $sql = "SELECT username FROM profiles WHERE userId = '$userId'";

  $result = mysqli_query($conn, $sql);

  $userName = mysqli_fetch_assoc($result);

  mysqli_free_result($result);

  return $userName['username'];
}

function deleteAssociatedBooks($conn, $userId)
{
  $sql = "DELETE FROM books WHERE userId = '$userId'";

  mysqli_query($conn, $sql);
}

function deleteProfile($conn, $userId)
{
  $sql = "DELETE FROM profiles WHERE userId = '$userId'";

  mysqli_query($conn, $sql);
}

function deleteUser($conn, $userId)
{
  $sql = "DELETE FROM users WHERE id = '$userId'";

  mysqli_query($conn, $sql);
}

function deleteReviews($conn, $userId)
{
  $sql = "DELETE FROM reviews WHERE userId = '$userId'";

  mysqli_query($conn, $sql);
}

function filterRequests($conn, $request, $userId)
{
  $filterIdToRemove = array_map(function ($key, $value) use ($userId) {
    if ($key !== 'userId') {
      return serialize(array_filter(unserialize($value), function ($id) use ($userId) {
        return $id !== $userId;
      }));
    } else {
      return ["userId" => $value];
    }
  }, array_keys($request), $request);

  $id = $filterIdToRemove[1]['userId'];

  $sql = "UPDATE profiles SET friends = '$filterIdToRemove[0]' WHERE userId = '$id'";

  if (mysqli_query($conn, $sql)) {
    //echo 'updated';
  } else {
    // echo 'Update error ' . mysqli_error($conn);
  }
}

function deleteFromFriends($conn, $userId)
{

  $sql = "SELECT friends, userId FROM profiles";

  $result = mysqli_query($conn, $sql);

  $requests  = mysqli_fetch_all($result, MYSQLI_ASSOC);

  mysqli_free_result($result);

  $test = filterRequests($conn, $requests[0], $userId);

  print_r($test);

  foreach ($requests as $request) {
    filterRequests($conn, $request, $userId);
  }
}

function deleteSentMessages($conn, $name)
{
  $sql = "DELETE FROM messages WHERE sender = '$name'";

  mysqli_query($conn, $sql);
}

function deleteRecievedMessages($conn, $name)
{
  $sql = "DELETE FROM messages WHERE recipient = '$name'";

  mysqli_query($conn, $sql);
}



if (isset($_POST['deleteaccount'])) {
  $userId = $_SESSION['user'];
  $userName = getUserName($conn, $userId);

  deleteSentMessages($conn, $userName);
  deleteRecievedMessages($conn, $userName);
  deleteFromFriends($conn, $userId);
  deleteAssociatedBooks($conn, $userId);
  deleteProfile($conn, $userId);
  deleteReviews($conn, $userId);
  deleteUser($conn, $userId);


  mysqli_close($conn);

  session_destroy();

  header('Location: index.php');
}
?>

<p>Are you sure you want to delete your account?</p>
<div class="delete-account-form-container">
  <form action="<?php echo basename($_SERVER['PHP_SELF']); ?>" method="POST">
    <input type="hidden" name="useridtodelete" value="<?php echo htmlspecialchars($_SESSION['user']); ?>" />
    <input type="submit" name="deleteaccount" value="Yes" />
  </form>
  <div>
    <button class="cancel-delete-popup">Cancel</button>
  </div>
</div>