<?php
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} elseif (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
}

use Aws\S3\S3Client;

$sharedConfig = [
    "region" => "us-west-2",
    "version" => "latest",
];
//$sdk = new Aws\Sdk($sharedConfig);
//$s3 = $sdk->createS3();

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
    return $o->Key;
}

function get_image_files() {
//    $objects = $s3->listObjectsV2([
//        "Bucket" => "aatf-us",
//        "MaxKeys" => 1000,
//    ])->Contents;
//    $keys = array_map("get_s3_key", $objects);
//    print($keys);

    return ["//" . $cdn_url . "/images/2005-dance-festival/2005aatffrdfPicture12.jpg"];
}

function get_gallery_images($name, $s3Client = null) {
    global $cdn_url, $sharedConfig;
    $images = [];

    $clean_name = preg_replace('/[^a-zA-Z0-9_-]/', '', $name);
    if (empty($clean_name)) {
        return $images;
    }

    $prefix = "images/" . $clean_name . "/";

    try {
        if ($s3Client === null && class_exists('Aws\S3\S3Client')) {
            $s3Client = new Aws\S3\S3Client($sharedConfig);
        }

        if ($s3Client) {
            $objects = $s3Client->listObjectsV2([
                "Bucket" => "aatf-us",
                "Prefix" => $prefix,
            ]);

            $contents = $objects['Contents'] ?? (isset($objects->Contents) ? $objects->Contents : []);
            foreach ($contents as $object) {
                $key = is_array($object) ? ($object['Key'] ?? '') : ($object->Key ?? '');
                if (preg_match('/(\.jpg|\.jpeg|\.png|\.gif)$/i', $key)) {
                    $images[] = "//" . $cdn_url . "/" . ltrim($key, "/");
                }
            }
        }
    } catch (Exception $e) {
        error_log("Failed to list S3 objects for gallery " . $clean_name . ": " . $e->getMessage());
    }

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
