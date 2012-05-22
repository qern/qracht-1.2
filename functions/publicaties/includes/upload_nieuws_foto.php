<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/check_configuration.php');
/**
 * Handle file uploads via XMLHttpRequest
 */
class qqUploadedFileXhr {
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path) {    
        $input = fopen("php://input", "r");
        $temp = tmpfile();
        $realSize = stream_copy_to_stream($input, $temp);
        fclose($input);
        
        if ($realSize != $this->getSize()){            
            return false;
        }
        
        $target = fopen($path, "w");        
        fseek($temp, 0, SEEK_SET);
        stream_copy_to_stream($temp, $target);
        fclose($target);
        
        return true;
    }
    function getName() {
        return $_GET['qqfile'];
    }
    function getSize() {
        if (isset($_SERVER["CONTENT_LENGTH"])){
            return (int)$_SERVER["CONTENT_LENGTH"];            
        } else {
            throw new Exception('Getting content length is not supported.');
        }      
    }   
}

/**
 * Handle file uploads via regular form post (uses the $_FILES array)
 */
class qqUploadedFileForm {  
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path) {
        if(!move_uploaded_file($_FILES['qqfile']['tmp_name'], $path)){
            return false;
        }
        return true;
    }
    function getName() {
        return $_FILES['qqfile']['name'];
    }
    function getSize() {
        return $_FILES['qqfile']['size'];
    }
}

class qqFileUploader {
    private $allowedExtensions = array();
    private $sizeLimit = 10485760;
    private $file;

    function __construct(array $allowedExtensions = array(), $sizeLimit = 10485760){        
        $allowedExtensions = array_map("strtolower", $allowedExtensions);
            
        $this->allowedExtensions = $allowedExtensions;        
        $this->sizeLimit = $sizeLimit;
        
        $this->checkServerSettings();       

        if (isset($_GET['qqfile'])) {
            $this->file = new qqUploadedFileXhr();
        } elseif (isset($_FILES['qqfile'])) {
            $this->file = new qqUploadedFileForm();
        } else {
            $this->file = false; 
        }
    }
    
    private function checkServerSettings(){        
        $postSize = $this->toBytes(ini_get('post_max_size'));
        $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));        
        
        if ($postSize < $this->sizeLimit || $uploadSize < $this->sizeLimit){
            $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';             
            die("{'error':'increase post_max_size and upload_max_filesize to $size'}");    
        }        
    }
    
    private function toBytes($str){
        $val = trim($str);
        $last = strtolower($str[strlen($str)-1]);
        switch($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;        
        }
        return $val;
    }
    
    /**
     * Returns array('success'=>true) or array('error'=>'error message')
     */
    function handleUpload($uploadDirectory, $replaceOldFile = FALSE){
        if (!is_writable($uploadDirectory)){
            return array('error' => "Server error. Upload directory isn't writable.");
        }
        
        if (!$this->file){
            return array('error' => 'No files were uploaded.');
        }
        
        $size = $this->file->getSize();
        
        if ($size == 0) {
            return array('error' => 'File is empty');
        }
        
        if ($size > $this->sizeLimit) {
            return array('error' => 'File is too large');
        }
        
        $pathinfo = pathinfo($this->file->getName());
        $filename = $pathinfo['filename'];
        //$filename = md5(uniqid());
        $ext = $pathinfo['extension'];

        if($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)){
            $these = implode(', ', $this->allowedExtensions);
            return array('error' => 'File has an invalid extension, it should be one of '. $these . '.');
        }
        
        if(!$replaceOldFile){
            /// don't overwrite previous files that were uploaded
            while (file_exists($uploadDirectory . $filename . '.' . $ext)) {
                $filename .= rand(10, 99);
            }
        }
        
        if ($this->file->save($uploadDirectory . $filename . '.' . $ext)){
            return array('success'=>true);
        } else {
            return array('error'=> 'Could not save uploaded file.' .
                'The upload was cancelled, or server error encountered');
        }
        
    }    
}

// list of valid extensions, ex. array("jpeg", "xml", "bmp")
$allowedExtensions = array('jpeg', 'jpg', 'png', 'gif');
// max file size in bytes
$sizeLimit = 10 * 1024 * 1024;
$path = $_SERVER['DOCUMENT_ROOT'].$etc_root.'files/album/'.$_GET['album'].'/';
if(is_dir($path)){}else{mkdir($path);} //bestaat de map al ? nee ? aanmaken dan.
$uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
$result = $uploader->handleUpload($path);
resizeImage($path, $_GET['qqfile']);

//haal de verzameling op
    $what = 'MAX(verzameling) AS verzameling'; 
    $from = 'portal_image';
    $where = '(UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(geupload_op)) <= 2400 AND album='.$_GET['album'];
    
    $verzameling_res = mysql_fetch_assoc(sqlSelect($what, $from, $where));
   
   //if($verzameling_res['verzameling'] == 0 || $verzameling_res['verzameling'] == null){ $verzameling = '10';}
    if($verzameling_res['verzameling'] == null){ 
       $what = 'MAX(verzameling) + 1 AS verzameling'; $from = 'portal_image'; $where = "1";
            $nieuwe_verzameling = mysql_fetch_assoc(sqlSelect($what, $from, $where));
            $verzameling = $nieuwe_verzameling['verzameling'];
            if($verzameling ==  null || $verzameling == 0){
                $verzameling = 1;
            }
    }elseif($verzameling_res['verzameling'] == 0){
       $verzameling = 1;
    }
    else{$verzameling = $verzameling_res['verzameling'];}
    
    //haal de volgorde op
    $what = 'MAX(volgorde) AS volgorde'; 
    $from = 'portal_image';
    $where = 'album='.$_GET['album'];
    
    $volgorde_res = mysql_fetch_assoc(sqlSelect($what, $from, $where));
    if($volgorde_res['volgorde'] == null || $volgorde_res['volgorde'] == 0){
        $volgorde = 1;
    }else{
        $volgorde = $volgorde_res['volgorde'];
    }
    
    $table='portal_image';
    $what='album, path, volgorde, verzameling, geupload_door, geupload_op, update_datum';
    $with_what="".$_GET['album'].", '".$_GET['qqfile']."', '$volgorde' , '$verzameling', '$login_id', NOW(), NOW()";
        $insert_foto_bestand = sqlInsert($table, $what, $with_what);


// to pass data through iframe you will need to encode all html tags
echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
?>
