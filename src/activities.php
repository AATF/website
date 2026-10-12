<?php
include_once('functions.php');
$title = $current_year . ' Activities';
include_once('header.php');
?>

<div class="container">
  <div class="page-header">
    <h1><?php print $title?></h1>
  </div>

  <?php
    if ($activitiesarr) {
  ?>
  <table class="table">
    <?php
      natsort($activitiesarr);

      foreach ($activitiesarr as $buffer) {
        if (substr(trim($buffer), 0, 1) != "#") {
          list($date, $event) = explode(' - ', trim($buffer), 2);
    ?>
    <tr>
      <td><?php print format_date($date) ?></td>
      <td><?php print $event ?></td>
    </tr>
    <?php
        }
      }
    ?>
  </table>
  <?php
  } else {
  ?>
  <p>Coming Soon</p>
  <?php
  }
  ?>
</div>

<?php include_once('footer.php'); ?>
