<?php
// tests/test_docker_config.php

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

echo "--- Testing PHP-FPM Configuration ---\n";

$fpmConfigPath = __DIR__ . '/../docker/files/etc/php-fpm.conf';
assert_true(file_exists($fpmConfigPath), 'php-fpm.conf exists');

$fpmConfig = file_get_contents($fpmConfigPath);

assert_true(
    preg_match('/^\s*clear_env\s*=\s*no/m', $fpmConfig) === 1,
    'php-fpm.conf has clear_env = no so workers preserve container env vars (AWS_ROLE_ARN, etc.)'
);

echo "\n============================\n";
echo "Docker Config Tests Passed: $passed, Failed: $failed\n";

if ($failed > 0) {
    exit(1);
}
exit(0);

