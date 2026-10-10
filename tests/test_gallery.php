<?php
// tests/test_gallery.php

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

// Mock S3 Client for testing
class MockS3Client {
    public $lastPrefix = null;
    public $lastBucket = null;
    public $shouldThrow = false;
    public $mockContents = [];

    public function listObjectsV2(array $args) {
        if ($this->shouldThrow) {
            throw new Exception("S3 connection error");
        }
        $this->lastBucket = $args['Bucket'] ?? null;
        $this->lastPrefix = $args['Prefix'] ?? null;

        return [
            'Contents' => $this->mockContents,
        ];
    }
}

// 1. Test functions.php functions
require_once __DIR__ . '/../src/functions.php';

echo "--- Testing get_gallery_images() ---\n";

// Test S3 object listing and filtering
$mockS3 = new MockS3Client();
$mockS3->mockContents = [
    ['Key' => 'images/archives/Picture_001.jpg'],
    ['Key' => 'images/archives/aatf_1_resize.png'],
    ['Key' => 'images/archives/thumbnail.GIF'],
    ['Key' => 'images/archives/notes.txt'],       // non-image file
    ['Key' => 'images/archives/'],               // folder key
];

$images = get_gallery_images('archives', $mockS3);

assert_equals('aatf-us', $mockS3->lastBucket, 'Bucket is aatf-us');
assert_equals('images/archives/', $mockS3->lastPrefix, 'Prefix is images/archives/');
assert_equals(3, count($images), 'Only valid image files are returned');
assert_equals('//cdn.aatf.us/images/archives/Picture_001.jpg', $images[0], 'First image URL format is correct');
assert_equals('//cdn.aatf.us/images/archives/aatf_1_resize.png', $images[1], 'Second image URL format is correct');
assert_equals('//cdn.aatf.us/images/archives/thumbnail.GIF', $images[2], 'Third image URL format is correct');

// Test input sanitization
$mockS3->mockContents = [];
get_gallery_images('../../etc/passwd', $mockS3);
assert_equals('images/etcpasswd/', $mockS3->lastPrefix, 'Path traversal characters are stripped');

$emptyResult = get_gallery_images('', $mockS3);
assert_equals([], $emptyResult, 'Empty name returns empty array');

$specialCharResult = get_gallery_images('!@#$%^&*()', $mockS3);
assert_equals([], $specialCharResult, 'Special characters only returns empty array');

// Test S3 exception handling
$mockS3Fail = new MockS3Client();
$mockS3Fail->shouldThrow = true;
$errorResult = get_gallery_images('archives', $mockS3Fail);
assert_equals([], $errorResult, 'S3 exception handled gracefully and returns empty array');

echo "\n--- Testing gallery.php rendering ---\n";

// Test gallery index rendering (no name param)
$_GET = [];
ob_start();
include __DIR__ . '/../src/gallery.php';
$output = ob_get_clean();

assert_true(strpos($output, 'Past Performance Archives') !== false, 'Renders album names when name is not specified');
assert_true(strpos($output, '?name=archives') !== false, 'Renders links with ?name= query param');
assert_true(strpos($output, '?name=2013-performances') !== false, 'Renders link for 2013-performances');

// Test gallery album rendering with name param
$_GET = ['name' => 'archives'];
ob_start();
include __DIR__ . '/../src/gallery.php';
$outputAlbum = ob_get_clean();

assert_true(strpos($outputAlbum, 'Back to Gallery') !== false, 'Renders Back to Gallery button');
assert_true(strpos($outputAlbum, 'Past Performance Archives') !== false, 'Renders album title in header');

// Test unknown gallery name rendering
$_GET = ['name' => 'nonexistent_album'];
ob_start();
include __DIR__ . '/../src/gallery.php';
$outputEmpty = ob_get_clean();

assert_true(strpos($outputEmpty, 'No images found.') !== false, 'Renders No images found for album with no images');

echo "\n============================\n";
echo "Tests Passed: $passed, Failed: $failed\n";

if ($failed > 0) {
    exit(1);
}
exit(0);

