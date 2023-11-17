<?php

require_once('getdata.php');

$IM = extension_loaded('imagick');

$url_params = $Wiki['config']['urlparam'];
$id = $_GET[$url_params['media-id']] ?? '';

if (empty($id)) {
    die("No media specified");
}

/**
 * Represents a media file
 * 
 * @todo Implement check whether ImageMagick is active and allow operations such as resizing and converting to AVIF or WEBP
 */
final class File {
    /** The file id which is used in the URL to access it */
    private string $id;
    /** The name of the file */
    private string $filename = "";
    /** The file content */
    private string|false $blob = false;
    /** Allowed mime types, or all if empty */
    private array $allowed_mimes = [];
    /** */
    private array $errors = [];
    /** */
    private bool $access_permitted = false;
    /** */
    private bool $file_can_be_loaded = false;

    public function __construct(string $file_id) {
        global $Wiki;

        $this->id = $file_id;
        // $this->blob = file_get_contents('missing.svg');

        $this->getFile($file_id);
    }

    public function setAllowedMimeTypes(array $mimes) {
        $this->allowed_mimes = $mimes;
    }

    public function getName(): string {
        return $this->filename;
    }

    /** Returns the file mime type, or false in case of an error or unaccepted mime */
    public function getMime(): string|false {
        $mime = false;

        try {
            $mime = $this->file_can_be_loaded && getimagesizefromstring($this->blob)['mime'];
        } catch(Exception) {
            array_push($this->errors, 'read_mime');
        }

        if ($mime && !in_array($mime, $this->allowed_mimes)) {
            array_push($this->errors, 'bad_mime');
            $mime = false;
        }

        return $mime;
    }

    private function getFile(string $file_id): bool {
        global $dbc;

        // Fetch the file from the database
        $get = $dbc->prepare("SELECT name, file, access FROM media WHERE url = :url LIMIT 1");
        $get->execute([
            ':url' => $file_id
        ]);
        $get = $get->fetch();

        // File not found
        if (!$get) return false;

        // User has no permission to access file
        if (!$this->checkIfUserCanAccess($get['access'])) return false;

        // Store data to variables
        $this->filename = $get['name'];
        $this->blob = $get['file'];

        // Success
        $this->file_can_be_loaded = true;
        return true;
    }

    public function userCanAccess(): bool {
        return $this->access_permitted;
    }

    private function checkIfUserCanAccess(string $access_json): bool {
        global $Actor;

        $accessible = true;

        if (empty($access_json)) {
            $this->access_permitted = $accessible;
            return true;
        }
        
        $access = json_decode($access_json, true);

        if (!$access) return false;
        
        if ($accessible && array_key_exists('permission', $access) && !empty($access['permission'])) {
            $accessible = false;
            
            foreach ($access['permission'] as $permission) {
                if ($Actor->hasPermission($permission)) {
                    $accessible = true;
                    break;
                }
            }
        }
        
        if ($accessible && array_key_exists('groups', $access) && !empty($access['groups'])) {
            $accessible = false;
            
            foreach ($access['groups'] as $group) {
                if ($Actor->isInGroup($group)) {
                    $accessible = true;
                    break;
                }
            }
        }

        $this->access_permitted = $accessible;
        return $accessible;
    }

    /** Returns the processed file */
    public function print(): void {
        echo $this->blob;
    }
}

$File = new File($id);

$File->setAllowedMimeTypes([
    'image/png',
    // 'image/jpeg',
    'image/gif',
    'image/bmp',
    'image/svg+xml',
    'image/tiff',
    'image/x-icon',
]);

if (!$File->userCanAccess()) {
    die("You do not have access to this file.");
}

if (!$File->getMime()) {
    die("Invalid Mime Type");
}

header('Content-Type: ' . $File->getMime());
header('Content-Disposition: inline; filename="' . $File->getName() . '"');

$File->print();
