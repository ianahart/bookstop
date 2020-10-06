<?php
session_start();





?>

<!DOCTYPE html>
<html lang="en">

<?php include('./templates/head.php'); ?>

<body>
  <div class="content">
    <?php include('./templates/nav.php'); ?>
    <section class="about-jumbotron">
      <div class="about-header">
        <h1>About Bookstop</h1>
        <h3>Reliable, smooth, and accurate </h3>
      </div>
      <section class="about-information">
        <section class="about-row">
          <div class="about-image-container">
            <img src="img/about-1.jpg" alt="office" />
          </div>
          <div>
            <h3>Our Story</h3>
            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Natus eius dolorem molestiae temporibus aliquid aliquam laboriosam, vero adipisci commodi. At, optio voluptate consequatur repellendus consequuntur perferendis quod sapiente ullam hic libero, nisi possimus nostrum qui sunt quaerat? Temporibus modi animi, sapiente quibusdam in quam harum saepe, voluptates illum provident nostrum.</p>
          </div>
        </section>
        <section class="about-row">
          <div>
            <h3>Users Are Our #1 Priority</h3>
            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Natus eius dolorem molestiae temporibus aliquid aliquam laboriosam, vero adipisci commodi. At, optio voluptate consequatur repellendus consequuntur perferendis quod sapiente ullam hic libero, nisi possimus nostrum qui sunt quaerat? Temporibus modi animi, sapiente quibusdam in quam harum saepe, voluptates illum provident nostrum.</p>
          </div>
          <div class="about-image-container">
            <img src="img/about-2.jpg" alt="books" />
          </div>
        </section>
      </section>
  </div>
  <?php include('./templates/footer.php'); ?>
</body>

</html>