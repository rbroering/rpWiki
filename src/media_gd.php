<?php

// THIS DOES NOT WORK RIGHT NOW. PLEASE USE THE DEFAULT media.php INSTEAD.

if (!extension_loaded('gd')) {
    die("GD Extension is missing.");
}

require_once( 'getdata.php' );

$allowed_mime_types = [
    'image/png',
    'image/jpeg',
    'image/gif',
    'image/bmp',
    'image/svg+xml',
    'image/tiff',
    'image/x-icon'
];

function print_image($image, $resize_width = false, $resize_height = false, $resize_crop = false) {
    list($width, $height) = getimagesizefromstring($image);
    $r = $width / $height;

    if (!$resize_width) {
        $resize_width = $width;
    }

	if (!$resize_height) {
		$resize_height = $resize_width * $r;
	}

    if ($resize_crop) {
        if ($width > $height) {
            $width = ceil($width-($width*abs($r-$resize_width/$resize_height)));
        } else {
            $height = ceil($height-($height*abs($r-$resize_width/$resize_height)));
        }
        $newwidth = $resize_width;
        $newheight = $resize_height;
    } else {
        if ($resize_width/$resize_height > $r) {
            $newwidth = $resize_height*$r;
            $newheight = $resize_height;
        } else {
            $newheight = $resize_width/$r;
            $newwidth = $resize_width;
        }
		if (isset( $_GET['bymax'] )) {
			if ($width < $height) {
				$newwidth = $resize_width;
				$newheight = $resize_width / $r;
			} elseIf ($width > $height) {
				$newheight = $resize_width;
				$newwidth = $resize_width * $r;
			} elseIf ($width == $height) {
				$newwidth = $resize_width;
				$newheight = $resize_width;
			}
		}
    }

    $dst = @imagecreatetruecolor($newwidth, $newheight);
	imagealphablending($dst, false);
	imagesavealpha($dst,true);
	$transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
	imagefilledrectangle($dst, 0, 0, $newwidth, $newheight, $transparent);
    imagecopyresampled($dst, imagecreatefromstring($image), 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

    echo imagepng($dst);
}

if (!empty($_GET[$Wiki['config']['urlparam']['media-id']])) {
    $get = $dbc->prepare("SELECT * FROM media WHERE url = :url LIMIT 1");
    $get->execute([
        ':url' => $_GET[$Wiki['config']['urlparam']['media-id']]
    ]);
    $get = $get->fetch();

    if ($get) {
        $accessible = true;

        if (!empty(json_decode($get['access']))) {
            $access = json_decode($get['access'], true);

            if (array_key_exists('permission', $access) && !empty($access['permission'])) {
                $accessible = false;
                foreach (array_keys($access['permission']) as $permission) {
                    if (p($permission)) {
                        $accessible = true;
                        break;
                    }
                }
            }
        }

        if ($accessible) {
            $mime = getimagesizefromstring($get['file'])['mime'];

            if (in_array($mime, $allowed_mime_types)) {
                /* HEADER */
                if (isset($_GET['debug']) && $_GET['debug']) header("Content-Type: $mime");

                $print_options = [
                    'height'    => false,
                    'width'     => false,
                    'crop'      => false
                ];


                if ($get['type'] == 'usericon') {
                    $print_options['width']     = 200;
                    $print_options['height']    = false;
                    $print_options['crop']      = false;
                }


                if (
                    !empty($_GET[$Wiki['config']['urlparam']['media-height']]) &&
                    is_integer($_GET[$Wiki['config']['urlparam']['media-height']])
                ) {
                    $print_options['height'] = $_GET[$Wiki['config']['urlparam']['media-height']];
                }

                if (
                    !empty($_GET[$Wiki['config']['urlparam']['media-width']]) &&
                    is_integer($_GET[$Wiki['config']['urlparam']['media-width']])
                ) {
                    $print_options['width'] = $_GET[$Wiki['config']['urlparam']['media-width']];
                }

                if (
                    !empty($_GET[$Wiki['config']['urlparam']['media-crop']]) &&
                    is_integer($_GET[$Wiki['config']['urlparam']['media-crop']])
                ) {
                    $print_options['crop'] = $_GET[$Wiki['config']['urlparam']['media-crop']];
                }


                if (!empty($_GET[$Wiki['config']['urlparam']['media-size']])) {
                    $size_string    = $_GET[$Wiki['config']['urlparam']['media-size']];
                    $size_array     = [];
                    preg_match('/(?:(w)([0-9]+))?(?:-?(h)([0-9]+))?/', $size_string, $size_array);
                    $size_array = array_filter($size_array);
                    unset($size_array[0]);

                    $size_next = '';
                    foreach ($size_array as $val) {
                        if (empty($size_next))
                            $size_next = $val;
                        else {
                            if ($size_next === 'h')
                                $print_options['height']    = $val;
                            elseif ($size_next === 'w')
                                $print_options['width']     = $val;

                            $size_next = '';
                        }
                    }
                }

                print_image($get['file'], $print_options['width'], $print_options['height'], $print_options['crop']);
            }
        }
    }
}
