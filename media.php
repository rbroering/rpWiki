<?php
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
                if (empty($_GET['debug'])) header("Content-Type: $mime");

                echo $get['file'];
            }
        }
    }
}
