  <footer class="footer bg-body-tertiary border-top">
    <div class="container">
      <p class="text-muted credit"><a href="/privacy-policy" target="_blank">Privacy Policy</a> - <a href="/terms-of-service" target="_blank">Terms of Service</a></p>
      <p>Copyright &copy; 2003-present Asian Arts Talents Foundation. All Rights Reserved.</p>
      <p class="footer-org-info">Asian Arts Talents Foundation is a United States 501(c)(3) non-profit organization | EIN: 57-1193358</p>
      <p class="footer-version-info">Site Version: <?php print $site_version; ?> | Source code located at <a href="//github.com/aatf/aatf.us">GitHub</a></p>
    </div>
  </footer>

  <script>google_ad_client="ca-pub-4985266849377192";google_ad_slot="8481272763";google_ad_width=728;google_ad_height=90;</script>
  <script src="pagead2.googlesyndication.com/pagead/show_ads.js"></script>
  <!-- Place this tag after the last widget tag. -->
  <script>(function(){var po=document.createElement('script');po.type='text/javascript';po.async=true;po.src='https://apis.google.com/js/platform.js';var s=document.getElementsByTagName('script')[0];s.parentNode.insertBefore(po,s);})();</script>

  <!-- Bootstrap core JavaScript
  ================================================== -->
  <!-- Placed at the end of the document so the pages load faster -->
  <?php
  if ($_SERVER['SCRIPT_NAME'] != "/index.php") {
  ?>
  <script src="//code.jquery.com/jquery-2.1.3.min.js"></script>
  <?php
  };
  ?>
  <script src="//<?php print $cdn_url; ?>/assets/js/bootstrap.bundle.min.js"></script>
  <script src="//<?php print $cdn_url; ?>/assets/js/theme.js"></script>
  <script>
    if (typeof window.AATFTheme === 'undefined') {
      (() => {
        const STORAGE_KEY = 'theme';
        const getStoredPref = () => {
          try {
            const v = localStorage.getItem(STORAGE_KEY) || localStorage.getItem('aatf_theme');
            if (v === 'light' || v === 'dark' || v === 'auto') return v;
          } catch(e) {}
          return 'auto';
        };
        const resolve = p => {
          if (p === 'auto') return (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) ? 'dark' : 'light';
          return p === 'dark' ? 'dark' : 'light';
        };
        const updateUI = (pref, resolved) => {
          const btn = document.querySelector('#bd-theme');
          if (btn) btn.setAttribute('aria-label', 'Toggle theme (' + pref + ')');
          document.querySelectorAll('[data-bs-theme-value]').forEach(el => {
            const isAct = el.getAttribute('data-bs-theme-value') === pref;
            el.classList.toggle('active', isAct);
            el.setAttribute('aria-pressed', isAct ? 'true' : 'false');
            const chk = el.querySelector('.check-icon');
            if (chk) chk.classList.toggle('d-none', !isAct);
          });
          document.querySelectorAll('.theme-footer-btn').forEach(fb => {
            const isFAct = fb.getAttribute('data-bs-theme-value') === pref;
            fb.classList.toggle('active', isFAct);
            fb.classList.toggle('fw-bold', isFAct);
            fb.setAttribute('aria-pressed', isFAct ? 'true' : 'false');
          });
        };
        const apply = p => {
          if (p !== 'light' && p !== 'dark' && p !== 'auto') p = 'auto';
          const r = resolve(p);
          document.documentElement.setAttribute('data-bs-theme', r);
          document.documentElement.setAttribute('data-bs-theme-preference', p);
          try {
            localStorage.setItem(STORAGE_KEY, p);
            localStorage.setItem('aatf_theme', p);
            document.cookie = 'theme=' + encodeURIComponent(p) + ';path=/;max-age=31536000;SameSite=Lax';
            document.cookie = 'aatf_theme=' + encodeURIComponent(p) + ';path=/;max-age=31536000;SameSite=Lax';
          } catch(e) {}
          updateUI(p, r);
        };
        apply(getStoredPref());
        document.addEventListener('click', e => {
          const target = e.target.closest('[data-bs-theme-value]');
          if (target) {
            e.preventDefault();
            apply(target.getAttribute('data-bs-theme-value'));
          }
        });
        if (window.matchMedia) {
          const mq = window.matchMedia('(prefers-color-scheme: dark)');
          const handler = () => { if (getStoredPref() === 'auto') apply('auto'); };
          if (mq.addEventListener) mq.addEventListener('change', handler);
          else if (mq.addListener) mq.addListener(handler);
        }
        window.addEventListener('storage', e => {
          if (e.key === STORAGE_KEY || e.key === 'aatf_theme') apply(e.newValue || 'auto');
        });
        window.AATFTheme = { getPreference: getStoredPref, setPreference: apply, getResolvedTheme: () => resolve(getStoredPref()) };
      })();
    }
  </script>

  <!-- Google Analytics -->
  <script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

    ga('create', 'UA-28786220-1', 'auto');
    ga('send', 'pageview');

  </script>

  <a href="https://aatf.us/conditionalgrowing.php"><!-- representational --></a>

  <a href="https://aatf.us/conditionalgrowing.php"><img src="representational.gif" height="1" width="1" border="0"></a>

  <a href="https://aatf.us/conditionalgrowing.php" style="display: none;">representational</a>

  <div style="display: none;"><a href="https://aatf.us/conditionalgrowing.php">representational</a></div>

  <a href="https://aatf.us/conditionalgrowing.php"></a>

  <!-- <a href="https://aatf.us/conditionalgrowing.php">representational</a> -->

  <div style="position: absolute; top: -250px; left: -250px;"><a href="https://aatf.us/conditionalgrowing.php">representational</a></div>

  <a href="https://aatf.us/conditionalgrowing.php"><span style="display: none;">representational</span></a>

  <a href="https://aatf.us/conditionalgrowing.php"><div style="height: 0px; width: 0px;"></div></a>
  <a href="https://www.aatf.us/wistfulcuneiform.php"><!-- respectable --></a>

  <a href="https://www.aatf.us/wistfulcuneiform.php"><img src="respectable.gif" height="1" width="1" border="0"></a>

  <a href="https://www.aatf.us/wistfulcuneiform.php" style="display: none;">respectable</a>

  <div style="display: none;"><a href="https://www.aatf.us/wistfulcuneiform.php">respectable</a></div>

  <a href="https://www.aatf.us/wistfulcuneiform.php"></a>

  <!-- <a href="https://www.aatf.us/wistfulcuneiform.php">respectable</a> -->

  <div style="position: absolute; top: -250px; left: -250px;"><a href="https://www.aatf.us/wistfulcuneiform.php">respectable</a></div>

  <a href="https://www.aatf.us/wistfulcuneiform.php"><span style="display: none;">respectable</span></a>

  <a href="https://www.aatf.us/wistfulcuneiform.php"><div style="height: 0px; width: 0px;"></div></a>
  </body>
</html>
