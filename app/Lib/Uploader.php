<?php


namespace App\Lib;

use Illuminate\Database\Eloquent\Model;

class Uploader extends Model
{
    protected $fillable = ['related_id', 'related_name', 'filename', 'filesize'];
    protected $table = 'hts_uploads';

    /**
     * Upload mixed files images and others as specified in $allowedExt array
     * @param $file
     * @param $relatedId | related id
     * @param $relatedName | related name
     * @param $path | the folder to store your files e.f upload/
     * @param string[] $allowedExt e.g. ['png', 'gif', 'jpg', 'jpeg', 'pdf']
     * @param int $maxSize | should be in bytes e.g 10MB = 10485760 bytes
     */
    static function mixed($file, $relatedId, $relatedName, $path = 'public/uploads/', $allowedExt = ['png', 'gif', 'jpg', 'jpeg', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'zip', 'rar', 'txt', 'html', 'psd'], $maxSize = 10485760)
    {
        if (is_array($file)) {
            self::multiple($file, $relatedId, $relatedName, $path, $allowedExt, $maxSize);
        } else {
            self::single($file, $relatedId, $relatedName, $path, $allowedExt, $maxSize);
        }
    }

    /**
     * Single file upload
     * @param $file
     * @param $relatedId
     * @param $relatedName
     * @param $path
     * @param int $maxSize
     */
    static function single($file, $relatedId, $relatedName, $path, $allowedExt, $maxSize)
    {
        if ($file['name'] != '') {
            if (!file_exists($path)) {
                die("Make sure upload directory exist!");
            }
            if ($_POST) {
                $filepath = $file['tmp_name'];
                $fileSize = filesize($filepath);
                $FileName = strtolower($file['name']);
                $fileExt = substr($FileName, strrpos($FileName, '.'));
                $RandNumber = randomStr(20); //Random number.
                $mianext = substr($fileExt, 1);

                if ($file['error']) {
                    die(self::upload_errors($file['error']));
                }
                if ($fileSize === 0) {
                    die("The file is empty.");
                }
                if ($fileSize > $maxSize) { //(1 byte * 1024 * 1024 * 10 (10MB) = 10485760 byte
                    die("The file is too large");
                }
                //valid entensions
                self::ValidFileExt($mianext, $allowedExt);
                $dirName = $name = date('Y/m');
                $name = $dirName . '/' . $RandNumber . $fileExt;
                $filePath = base_path($path . $name);

                self::createDir($dirName); //create directory if not exist

                if (move_uploaded_file($filepath, $filePath)) {
                    //return $name;
                    self::create([
                        'related_id' => $relatedId,
                        'related_name' => $relatedName,
                        'filename' => $name,
                        'filesize' => $fileSize,
                    ]);
                } else {
                    die('An error occured while trying to upload File!');
                }
            }
        }
    } //end


    /**
     * Multiple file upload
     * @param $file
     * @param $relatedId
     * @param $relatedName
     * @param $path
     * @param int $maxSize
     */
    static function multiple($file, $relatedId, $relatedName, $path, $allowedExt, $maxSize)
    {
        for ($i = 0; $i < count($file['name']); $i++) {
            if (!file_exists($path)) {
                die("Make sure Upload directory exist!");
            }
            if ($_POST and $file['name'][$i]) {
                $filepath = $file['tmp_name'][$i];
                $fileSize = filesize($filepath);
                $FileName = strtolower($file['name'][$i]);
                $fileExt = substr($FileName, strrpos($FileName, '.'));
                $mianext = substr($fileExt, 1);

                if ($file['error'][$i]) {
                    die(self::upload_errors($file['error'][$i]));
                }
                if ($fileSize === 0) {
                    die("The file is empty.");
                }
                if ($fileSize > $maxSize) { //(1 byte * 1024 * 1024 * 10 (10MB) = 10485760 byte
                    die("The file is too large");
                }

                //valid entensions
                self::ValidFileExt($mianext, $allowedExt);

                $dirName = $name = date('Y/m');
                $name = $dirName . '/' . randomStr(20) . $fileExt;
                $filePath = base_path($path . $name);
                self::createDir($dirName); //create directory if not exist

                if (move_uploaded_file($filepath, $filePath)) {
                    self::create([
                        'related_id' => $relatedId,
                        'related_name' => $relatedName,
                        'filename' => $name,
                        'filesize' => $fileSize,
                    ]);
                } else {
                    die('An error occured while trying to upload File!');
                }
            }
        }
    } //end fileupload

    /**
     * valid files extension. You can add more
     * @param $newext
     * @param $validext
     * @return mixed
     */
    static function ValidFileExt($newext, $validext)
    {
        if (!in_array($newext, $validext)) {
            die('Please Upload a Valid File Format!'); //error
        }
        return $validext;
    }

    /**
     * errors handling
     * @param $err_code
     * @return string
     */
    static function upload_errors($err_code)
    {
        switch ($err_code) {
            case UPLOAD_ERR_INI_SIZE:
                return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
            case UPLOAD_ERR_FORM_SIZE:
                return 'The uploaded file exceeds the MAX_FILE_SIZE specified in the HTML form';
            case UPLOAD_ERR_PARTIAL:
                return 'The uploaded file was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing a temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'File upload stopped by extension';
            default:
                return 'Unknown upload error';
        }
    }

    /**
     * Delete file if exist
     * @param $id
     */
    static function deleteFiles($id)
    {
        $query = self::where('id', $id)->first();
        if ($query) {
            deleteFile(public_path('uploads/' . $query->filename));
            self::where('id', $id)->delete();
        }
    }

    static function createDir($dir)
    {
        if (!file_exists(public_path('uploads/' . $dir))) {
            mkdir(public_path('uploads/' . $dir), 0777, true);
        }
    }
}
