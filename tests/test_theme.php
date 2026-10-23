<?php
// tests/test_theme.php

$passed = 0;
$failed = 0;

function assert_true($condition, $message) {
    global $passed, $failed;
    if ($condition) {
        echo "PASS: $message\n";
        $passed++;
    } else {
        echo "FAIL: $message\n";
        $failed++;
    }
}

function assert_equals($expected, $actual, $message) {
    global $passed, $failed;
    if ($expected === $actual) {
        echo "PASS: $message\n";
        $passed++;
    } else {
        echo "FAIL: $message (expected " . json_encode($expected) . ", got " . json_encode($actual) . ")\n";
        $failed++;
    }
}

echo "--- Testing Bootstrap Built-in Color Modes in bootstrap.min.css ---\n";

$bootstrapCss = file_get_contents(__DIR__ . '/../cdn/assets/css/bootstrap.min.css');
assert_true(strpos($bootstrapCss, '[data-bs-theme=dark]') !== false || strpos($bootstrapCss, '[data-bs-theme="dark"]') !== false, 'Bootstrap CSS has built-in [data-bs-theme=dark]');
assert_true(strpos($bootstrapCss, '[data-bs-theme=light]') !== false || strpos($bootstrapCss, '[data-bs-theme="light"]') !== false, 'Bootstrap CSS has built-in [data-bs-theme=light]');

echo "\n--- Testing Theme Support in header.php ---\n";

// Test 1: Default theme rendering (no cookie)
$_COOKIE = [];
ob_start();
include __DIR__ . '/../src/header.php';
$defaultHeader = ob_get_clean();

assert_true(strpos($defaultHeader, 'data-bs-theme-preference="auto"') !== false, 'Default theme preference is auto');
assert_true(strpos($defaultHeader, '<meta name="color-scheme" content="light dark">') !== false, 'Includes color-scheme meta tag');
assert_true(strpos($defaultHeader, 'id="bd-theme"') !== false, 'Renders Bootstrap theme toggle dropdown in navbar');
assert_true(strpos($defaultHeader, 'data-bs-theme-value="light"') !== false, 'Renders light mode option with data-bs-theme-value');
assert_true(strpos($defaultHeader, 'data-bs-theme-value="dark"') !== false, 'Renders dark mode option with data-bs-theme-value');
assert_true(strpos($defaultHeader, 'data-bs-theme-value="auto"') !== false, 'Renders auto mode option with data-bs-theme-value');
assert_true(strpos($defaultHeader, '--bg-primary') === false, 'Header does not use custom theme color variables');

// Test 2: Dark mode cookie preference
$_COOKIE['theme'] = 'dark';
ob_start();
include __DIR__ . '/../src/header.php';
$darkHeader = ob_get_clean();

assert_true(strpos($darkHeader, 'data-bs-theme-preference="dark"') !== false, 'Cookie set to dark reflects data-bs-theme-preference="dark"');
assert_true(strpos($darkHeader, 'data-bs-theme="dark"') !== false, 'Cookie set to dark sets data-bs-theme="dark" attribute');

// Test 3: Light mode cookie preference
$_COOKIE['theme'] = 'light';
ob_start();
include __DIR__ . '/../src/header.php';
$lightHeader = ob_get_clean();

assert_true(strpos($lightHeader, 'data-bs-theme-preference="light"') !== false, 'Cookie set to light reflects data-bs-theme-preference="light"');
assert_true(strpos($lightHeader, 'data-bs-theme="light"') !== false, 'Cookie set to light sets data-bs-theme="light" attribute');

// Test 4: Invalid cookie preference falls back to auto
$_COOKIE['theme'] = 'invalid_theme';
ob_start();
include __DIR__ . '/../src/header.php';
$fallbackHeader = ob_get_clean();

assert_true(strpos($fallbackHeader, 'data-bs-theme-preference="auto"') !== false, 'Invalid cookie falls back to auto preference');

echo "\n--- Testing Theme Support in footer.php ---\n";

// Test 5: Footer theme support and text colors
$_SERVER['SCRIPT_NAME'] = '/index.php';
ob_start();
include __DIR__ . '/../src/footer.php';
$footerOutput = ob_get_clean();

assert_true(strpos($footerOutput, 'footer-org-info') !== false, 'Renders footer-org-info class in footer');
assert_true(strpos($footerOutput, 'footer-version-info') !== false, 'Renders footer-version-info class in footer');
assert_true(strpos($footerOutput, '#767676') !== false, 'Footer includes lighter light-mode org info color #767676');
assert_true(strpos($footerOutput, '#8A9198') !== false, 'Footer includes lighter dark-mode org info color #8A9198');
assert_true(strpos($footerOutput, '#CBCBCB') !== false, 'Footer includes light mode version info color #CBCBCB');
assert_true(strpos($footerOutput, '#555B61') !== false, 'Footer includes dark mode version info color #555B61');
assert_true(strpos($footerOutput, '[data-bs-theme="dark"] .footer .footer-org-info') !== false, 'Footer includes dark mode org info color rule');
assert_true(strpos($footerOutput, '[data-bs-theme="dark"] .footer .footer-version-info') !== false, 'Footer includes dark mode version info color rule');
assert_true(strpos($footerOutput, 'theme.js') !== false, 'Loads theme.js in footer');

echo "\n--- Testing custom.css uses Bootstrap variables and light/dark footer colors ---\n";

$css = file_get_contents(__DIR__ . '/../cdn/assets/css/custom.css');

assert_true(strpos($css, '--bs-tertiary-bg') !== false, 'Footer uses Bootstrap built-in --bs-tertiary-bg');
assert_true(strpos($css, '--bs-link-color') !== false, 'Footer uses Bootstrap built-in --bs-link-color');
assert_true(strpos($css, '--bg-primary') === false, 'custom.css does not define redundant custom theme variables');
assert_true(strpos($css, '#767676') !== false, 'custom.css defines #767676 for light mode org info');
assert_true(strpos($css, '#8A9198') !== false, 'custom.css defines #8A9198 for dark mode org info');
assert_true(strpos($css, '#CBCBCB') !== false, 'custom.css defines #CBCBCB for light mode version info');
assert_true(strpos($css, '#555B61') !== false, 'custom.css defines #555B61 for dark mode version info');
assert_true(strpos($css, '[data-bs-theme="dark"] .footer .footer-org-info') !== false, 'custom.css defines dark mode override for org info');
assert_true(strpos($css, '[data-bs-theme="dark"] .footer .footer-version-info') !== false, 'custom.css defines dark mode override for version info');

echo "\n--- Testing JavaScript in theme.js ---\n";

$js = file_get_contents(__DIR__ . '/../cdn/assets/js/theme.js');

assert_true(strpos($js, 'data-bs-theme') !== false, 'Sets data-bs-theme in theme.js');
assert_true(strpos($js, 'prefers-color-scheme: dark') !== false, 'Listens to system preference in theme.js');
assert_true(strpos($js, 'window.AATFTheme') !== false, 'Exposes public AATFTheme API in theme.js');
assert_true(strpos($js, 'addEventListener(\'storage\'') !== false, 'Supports cross-tab theme synchronization');

echo "\n============================\n";
echo "Theme Tests Passed: $passed, Failed: $failed\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
