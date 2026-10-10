<?php
$title = "Home";

include_once("header.php");
?>

<main role="main">
  <div class="container">
<?php
$width = 593;
$height = round($width / 1.5);
?>
          <!-- Jssor Slider Begin -->
          <!-- To move inline styles to css file/block, please specify a class name for each element. -->
          <!-- ================================================== -->
          <div id="slider1_container" style="display: none; position: relative; margin: 0 auto; width: <?php print $width ?>px; height: <?php print $height ?>px; overflow: hidden;">

              <!-- Loading Screen -->
              <div data-u="loading" class="jssorl-009-spin" style="position:absolute;top:0px;left:0px;width:100%;height:100%;text-align:center;background-color:rgba(0,0,0,0.7);">
                <img alt="spinner" style="margin-top:-19px;position:relative;top:50%;width:38px;height:38px;" src="assets/svg/loading/static-svg/spin.svg" />
              </div>

              <!-- Slides Container -->
              <div data-u="slides" style="cursor: move; position: absolute; left: 0px; top: 0px; width: <?php print $width ?>px; height: <?php print $height ?>px; overflow: hidden;">
<?php
$pattern = "/(\.jpg$)|(\.png$)|(\.jpeg$)|(\.gif$)/"; // valid image extensions
$dirname = "images";
$count = 0;

$files = get_image_files();
foreach ($files as $file) {
  if (preg_match($pattern, $file)) { // if this file is a valid image
    $slide = "slide" . $count;
?>
                <div>
                  <a href="<?php print $file; ?>"><img alt="<?php print $slide ?>" data-u="image" id="<?php print $slide ?>" src="<?php print $file; ?>" /></a>
                </div>
<?php
      $count++;
  };
};
?>
              </div>

         <!--#region Arrow Navigator Skin Begin -->
        <!-- Help: https://www.jssor.com/development/slider-with-arrow-navigator.html -->
        <div data-u="arrowleft" class="jssora055" style="width:55px;height:55px;top:0px;left:25px;" data-autocenter="2" data-scale="0.75" data-scale-left="0.75">
            <svg viewBox="0 0 16000 16000" style="position:absolute;top:0;left:0;width:100%;height:100%;">
                <polyline class="a" points="11040,1920 4960,8000 11040,14080 "></polyline>
            </svg>
        </div>
        <div data-u="arrowright" class="jssora055" style="width:55px;height:55px;top:0px;right:25px;" data-autocenter="2" data-scale="0.75" data-scale-right="0.75">
            <svg viewBox="0 0 16000 16000" style="position:absolute;top:0;left:0;width:100%;height:100%;">
                <polyline class="a" points="4960,1920 11040,8000 4960,14080 "></polyline>
            </svg>
        </div>
        <!--#endregion Arrow Navigator Skin End -->


          </div>
          <!-- Jssor Slider End -->

      <div class="container marketing">
        <div class="row featurette">
          <div class="col-md-5">
            <img class="featurette-image img-responsive" src="//<?php print $cdn_url; ?>/images/aatflogo-bw-transparent.png" alt="AATF Logo">
          </div>
          <div class="col-md-7">
            <h2 class="featurette-heading">Purpose</h2>
              <p class="lead">The specific purpose of Asian Arts Talents Foundation is to:</p>
                <ul>
                  <li><p class="lead">raise funds to support Asian arts, education, and culture</p></li>
                  <li><p class="lead">sponsor public performances</p></li>
                  <li><p class="lead">offer grants and scholarships for talented, young Asians worldwide</p></li>
                  <li><p class="lead">help and get involved with the community during disasters and catastrophes</p></li>
                  <li><p class="lead">organize summer activities for younger generations</p></li>
                </ul>
                <p class="lead">AATF has been a non-profit organization since 2003.</p>
          </div>
        </div>

        <hr class="featurette-divider">
<?php
if (file_exists("banner.html")) {
    include_once "banner.html";
?>
        <hr class="featurette-divider">
<?php
}
?>

        <div class="row featurette">
          <div class="col-md-7">
            <h2 class="featurette-heading">Current Month's Activities</h2>
          </div>
            <div class="col-md-5">
                <div class="lead">
<?php
$count = 0;
if ($activitieshandle) {
  while (($buffer = fgets($activitieshandle)) != false) {
    list($date, $event) = preg_split("/\s-\s/", trim($buffer));
    $month = format_date($date, "%B");
    if ($current_month == $month) {
?>
  <p><strong><?php print format_date($date) ?></strong></p>
  <p><?php print $event ?></p>
<?php
      $count++;
    }
  }
  if ($count == 0) {
?>
  <p>No events scheduled for this month</p>
<?php
  }
} else {
?>
  <p>None Currently</p>
<?php
}
?>
            </div>
          </div>
        </div>
        <!-- /END THE FEATURETTES -->

      <!-- jssor slider scripts-->
      <!-- use jssor.js + jssor.slider.js instead for development -->
      <!-- jssor.slider.mini.js = (jssor.js + jssor.slider.js) -->
      <script src="//code.jquery.com/jquery-3.4.1.min.js"></script>
      <script src="assets/js/jssor.slider.min.js"></script>

      <script>

          jQuery(document).ready(function ($) {
              var options = {
                  $ArrowKeyNavigation: true,
                  $AutoPlayInterval: 2000,
                  $AutoPlaySteps: 1,
                  $AutoPlay: true,
                  $DisplayPieces: 1,
                  $DragOrientation: 1,
                  $LazyLoading: true,
                  $MinDragOffsetToSlide: 20,
                  $ParkingPosition: 0,
                  $PauseOnHover: 1,
                  $PlayOrientation: 1,
                  $SlideDuration: 800,
                  $SlideEasing: $Jease$.$EaseOutQuint,
                  $SlideSpacing: 0,
                  $UISearchMode: 1,

                  $ArrowNavigatorOptions: {
                      $ChanceToShow: 1,
                      $Class: $JssorArrowNavigator$,
                      $Steps: 1
                  },

                  $BulletNavigatorOptions: {
                      $ChanceToShow: 0,
                      $Class: $JssorBulletNavigator$,
                      $Lanes: 1,
                      $Orientation: 1,
                      $SpacingX: 12,
                      $SpacingY: 4,
                      $Steps: 1
                  }
              };

              //Make the element "slider1_container" visible before initialize jssor slider.
              $("#slider1_container").css("display", "block");
              var jssor_slider1 = new $JssorSlider$("slider1_container", options);

              //responsive code begin
              //you can remove responsive code if you don"t want the slider scales while window resizes
              function ScaleSlider() {
                  var parentWidth = jssor_slider1.$Elmt.parentNode.clientWidth;
                  if (parentWidth) {
                      jssor_slider1.$ScaleWidth(parentWidth - 30);
                  }
                  else
                      window.setTimeout(ScaleSlider, 30);
              }
              ScaleSlider();

              $(window).bind("load", ScaleSlider);
              $(window).bind("resize", ScaleSlider);
              $(window).bind("orientationchange", ScaleSlider);
              //responsive code end
          });
      </script>
    </div>
  </div>
</main>

<?php include_once("footer.php"); ?>
