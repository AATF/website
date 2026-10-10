<?php
include_once('functions.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Asian Arts Talents Foundation (AATF)">
  <meta name="author" content="">
  <link rel="shortcut icon" href="//<?php print $cdn_url; ?>/images/favicon.png">

  <title>Asian Arts Talents Foundation - <?php global $title; print $title ?></title>

  <!-- Bootstrap core CSS -->
  <link href="//<?php print $cdn_url; ?>/assets/css/bootstrap.min.css" rel="stylesheet">
  <!-- custom styles -->
  <link href="//<?php print $cdn_url; ?>/assets/css/custom.css" rel="stylesheet">
  <link href="//<?php print $cdn_url; ?>/assets/css/jssor.css" rel="stylesheet">

  <!-- Just for debugging purposes. Don't actually copy this line! -->
  <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

  <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
  <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
  <![endif]-->

  <?php print $extra_head; ?>
</head>

<body>
  <header>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top bg-dark">
      <div class="container">
        <a class="navbar-brand" href="/">Asian Arts Talents Foundation</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbar">
          <ul class="navbar-nav mr-auto">
            <li class="nav-item"><a class="nav-link" href="/">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="/about">About</a></li>
            <li class="nav-item"><a class="nav-link" href="/activities"><?php print $current_year ?> Activities</a></li>
            <li class="nav-item"><a class="nav-link" href="/gallery">Gallery</a></li>
            <li class="nav-item"><a class="nav-link" href="/videos">Videos</a></li>
            <li class="nav-item"><a class="nav-link" href="/forms">Forms</a></li>
            <li class="nav-item"><a class="nav-link" href="/scholarshipwinners">Scholarship Winners</a></li>
            <li class="nav-item"><a class="nav-link" href="/donate">Donate</a></li>
            <li class="nav-item"><a class="nav-link" href="/contact">Contact</a></li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Links <b class="caret"></b></a>
              <div class="dropdown-menu" aria-labelledby="dropdown">
                <a class="dropdown-item" href="/forums" target="_blank">AATF Forums</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="//twitch.tv/aatfus" target="_blank">Twitch</a>
                <a class="dropdown-item twitter-follow-button" href="//twitter.com/aatfus" target="_blank" id="gl2" data-show-count="false" onmouseover="ehandler(event,menuitem2);">Twitter</a>
                <a class="dropdown-item" href="//www.facebook.com/aatfus" target="_blank" id="gl1" onmouseover="ehandler(event,menuitem1);">Facebook Page</a>
                <a class="dropdown-item" href="//www.youtube.com/user/aatfus" target="_blank" id="gl4" onmouseover="ehandler(event,menuitem2);">YouTube</a>
                <a class="dropdown-item" href="//www.linkedin.com/groups/Asian-Arts-Talents-Foundation-6620148" target="_blank" id="gl5" onmouseover="ehandler(event,menuitem2);">LinkedIn Group</a>
                <a class="dropdown-item" href="//www.linkedin.com/company/asian-arts-talents-foundation" target="_blank" id="gl6" onmouseover="ehandler(event,menuitem2);">LinkedIn Company</a>
              </div>
            </li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>
  </header>
