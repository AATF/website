<?php
$gallery_name = isset($_GET['name']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['name']) : '';

$galleries = [
    'archives' => 'Past Performance Archives',
    'acda-dance-competition' => 'Dance Competition Awards',
    '2007-new-years-parade' => "New Years' Parade",
    '2005-dance-festival' => '2005 AATF Dance Festival',
    '2007-dance-festival' => '2007 AATF Dance Festival',
    '2009-dance-festival' => '2009 AATF Dance Festival',
    '2010-dance-recital' => '2010 AATF Dance Recital',
    '2013-performances' => '2013 AATF Performances',
];

if ($gallery_name && isset($galleries[$gallery_name])) {
    $title = $galleries[$gallery_name];
} else {
    $title = 'Gallery';
}

include_once('header.php');
?>

<div class="container">
  <div class="page-header">
    <h1><?php print $title ?></h1>
  </div>

<?php
if ($gallery_name) {
?>
  <p><a href="?" class="btn btn-secondary mb-3">&larr; Back to Gallery</a></p>
  <div class="row">
    <?php
    $images = get_gallery_images($gallery_name);
    var_dump($images);
    if (empty($images)) {
    ?>
      <div class="col-12">
        <p>No images found.</p>
      </div>
    <?php
    } else {
        foreach ($images as $img) {
    ?>
      <div class="col-6 col-md-3 mb-4">
        <a href="<?php print $img; ?>" target="_blank">
          <img src="<?php print $img; ?>" alt="<?php print htmlspecialchars($gallery_name); ?>" class="img-fluid img-thumbnail">
        </a>
      </div>
    <?php
        }
    }
    ?>
  </div>
  <p><a href="?" class="btn btn-secondary mt-3">&larr; Back to Gallery</a></p>
<?php
} else {
  foreach ($galleries as $slug => $name) {
?>
    <p><a href="?name=<?php print $slug; ?>"><?php print $name; ?></a></p>
  <?php
  }
}
?>
</div>

<?php include_once('footer.php'); ?>
