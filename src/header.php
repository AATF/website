<?php
include_once('functions.php');
$preferred_theme = isset($_COOKIE['theme']) && in_array($_COOKIE['theme'], ['light', 'dark', 'auto']) ? $_COOKIE['theme'] : (isset($_COOKIE['aatf_theme']) && in_array($_COOKIE['aatf_theme'], ['light', 'dark', 'auto']) ? $_COOKIE['aatf_theme'] : 'auto');
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php print ($preferred_theme !== 'auto' ? htmlspecialchars($preferred_theme) : 'light'); ?>" data-bs-theme-preference="<?php print htmlspecialchars($preferred_theme); ?>">
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="color-scheme" content="light dark">
  <meta name="description" content="Asian Arts Talents Foundation (AATF)">
  <meta name="author" content="">
  <link rel="shortcut icon" href="//<?php print $cdn_url; ?>/images/favicon.png">

  <title>Asian Arts Talents Foundation - <?php global $title; print $title ?></title>

  <!-- Prevent theme flicker before DOM renders using Bootstrap built-in color mode -->
  <script>
    (() => {
      'use strict';
      const storedTheme = localStorage.getItem('theme') || localStorage.getItem('aatf_theme') || '<?php print $preferred_theme; ?>' || 'auto';
      const setTheme = theme => {
        const resolved = theme === 'auto'
          ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
          : theme;
        document.documentElement.setAttribute('data-bs-theme', resolved);
        document.documentElement.setAttribute('data-bs-theme-preference', theme);
      };
      setTheme(storedTheme);
    })();
  </script>

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
    <nav class="navbar navbar-expand-lg fixed-top bg-body-tertiary border-bottom">
      <div class="container">
        <a class="navbar-brand" href="/">Asian Arts Talents Foundation</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar" data-toggle="collapse" data-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbar">
          <ul class="navbar-nav me-auto mr-auto">
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
              <a class="nav-link dropdown-toggle" href="#" id="dropdown" data-bs-toggle="dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Links <b class="caret"></b></a>
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
          <ul class="navbar-nav ms-auto ml-auto">
            <li class="nav-item dropdown">
              <button class="nav-link btn btn-link dropdown-toggle d-flex align-items-center" id="bd-theme" type="button" aria-expanded="false" data-bs-toggle="dropdown" data-toggle="dropdown" aria-label="Toggle theme (auto)">
                <span class="theme-icon-active d-flex align-items-center me-1 mr-1">
                  <svg class="bi" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 0 8 1zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16"/></svg>
                </span>
                <span class="d-lg-none ms-1 ml-1" id="bd-theme-text">Theme</span>
              </button>
              <ul class="dropdown-menu dropdown-menu-end dropdown-menu-right" aria-labelledby="bd-theme-text">
                <li>
                  <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="light" aria-pressed="false">
                    <svg class="bi me-2 mr-2 opacity-50" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8M8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0m0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13m8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5M3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8m10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0m-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0m9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707M4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708"/></svg>
                    <span class="flex-grow-1">Light</span>
                    <svg class="bi ms-auto ml-auto check-icon d-none" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0"/></svg>
                  </button>
                </li>
                <li>
                  <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="dark" aria-pressed="false">
                    <svg class="bi me-2 mr-2 opacity-50" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278"/><path d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.73 1.73 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.73 1.73 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.73 1.73 0 0 0 1.097-1.097zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.16 1.16 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.16 1.16 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732z"/></svg>
                    <span class="flex-grow-1">Dark</span>
                    <svg class="bi ms-auto ml-auto check-icon d-none" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0"/></svg>
                  </button>
                </li>
                <li>
                  <button type="button" class="dropdown-item d-flex align-items-center active" data-bs-theme-value="auto" aria-pressed="true">
                    <svg class="bi me-2 mr-2 opacity-50" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 0 8 1zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16"/></svg>
                    <span class="flex-grow-1">Auto (System)</span>
                    <svg class="bi ms-auto ml-auto check-icon d-none" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0"/></svg>
                  </button>
                </li>
              </ul>
            </li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>
  </header>
