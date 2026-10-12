<?php
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} elseif (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
} elseif (file_exists('/app/vendor/autoload.php')) {
    require_once '/app/vendor/autoload.php';
} else {
    error_log("Autoload file not found.");
}

use Aws\S3\S3Client;

$sharedConfig = [
    "region" => "us-west-2",
    "version" => "latest",
];
$s3client = new S3Client($sharedConfig);

$version_file = __DIR__ . "/VERSION";
if (!file_exists($version_file)) {
    $version_file = __DIR__ . "/../VERSION";
}
$site_version = file_exists($version_file) ? trim(file_get_contents($version_file)) : '';

$cdn_url = "cdn.aatf.us";

$current_year = date("Y");
$current_month = date("F");

global $current_year;
global $current_month;
global $extra_head;

$activitiesfile = $current_year . "-activities.txt";
$activitiesarr = @file("https://" . $cdn_url . "/" . $activitiesfile);

$scholarshipwinnershandle = @file("https://" . $cdn_url . "/scholarshipwinners_files/scholarshipwinners.txt");

function format_date($date, $format = null) {
    if ($format) {
        return strftime($format, strtotime($date));
    }
    else {
        return strftime("%B %e, %Y", strtotime($date));
    }
}

function ordinal($num) {
    $num = (int) $num;

    $ones = $num % 10;
    $tens = floor($num / 10) % 10;
    if ($tens == 1) {
        $suffix = "th";
    } else {
        switch ($ones) {
            case 1 : $suffix = "st"; break;
            case 2 : $suffix = "nd"; break;
            case 3 : $suffix = "rd"; break;
            default : $suffix = "th";
        }
    }

    return $num . $suffix;
}

function flatten(array $array) {
    $return = array();

    array_walk_recursive($array, function($a) use (&$return) { $return[] = $a; });

    return $return;
}

function get_s3_key($o) {
    return $o['Key'];
}

function get_image_files() {
    global $cdn_url, $s3client;

    $prefix = "images/";
    error_log("Listing S3 objects with prefix: " . $prefix);
    $objects = $s3client->listObjectsV2([
        "Bucket" => "aatf-us",
        "Prefix" => $prefix,
        "MaxKeys" => 50,
    ])['Contents'];
    error_log("S3 objects retrieved: " . print_r($objects, true));
    $keys = array_map("get_s3_key", $objects);
    error_log("S3 image keys: " . print_r($keys, true));

    return array_map(function($key) use ($cdn_url) {
        return "//" . $cdn_url . "/" . ltrim($key, "/");
    }, $keys);
}

function get_gallery_images($name, $client = null) {
    global $cdn_url, $s3client;

    $images = [];

    $clean_name = preg_replace('/[^a-zA-Z0-9_-]/', '', $name);
    if (empty($clean_name)) {
        return $images;
    }

    $prefix = "images/" . $clean_name . "/";

    try {
        $s3 = $client ?? $s3client;
        $objects = $s3->listObjectsV2([
            "Bucket" => "aatf-us",
            "Prefix" => $prefix,
        ]);
        error_log("Listed S3 objects for prefix: " . $prefix);
        error_log("S3 objects response: " . print_r($objects, true));

        $contents = $objects['Contents'] ?? (isset($objects->Contents) ? $objects->Contents : []);
        foreach ($contents as $object) {
            $key = is_array($object) ? ($object['Key'] ?? '') : ($object->Key ?? '');
            if (preg_match('/(\.jpg|\.jpeg|\.png|\.gif)$/i', $key)) {
                $images[] = "//" . $cdn_url . "/" . ltrim($key, "/");
            }
        }
    } catch (Exception $e) {
        error_log("Failed to list S3 objects for gallery " . $clean_name . ": " . $e->getMessage());
    }
    error_log("Total images found for gallery " . $clean_name . ": " . count($images));
    error_log("Gallery images: " . print_r($images, true));

    return $images;
}

function recurse_dir($dirname) {
    $dir_contents = scandir($dirname);
    foreach ($dir_contents as $orig_dir_or_file) {
        if (!in_array($orig_dir_or_file, [".", ".."])) {
            $dir_or_file = $dirname . DIRECTORY_SEPARATOR . $orig_dir_or_file;

            if (is_dir($dir_or_file)) {
                $arr[] = recurse_dir($dir_or_file);
            } else {
                $arr[] = $dir_or_file;
            };
        };
    };

    return flatten($arr);
};
?>
