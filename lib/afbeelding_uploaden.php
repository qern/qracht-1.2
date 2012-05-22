<?php
# --------------------------------------------------------------------------------------
#          ***** BESTAND UPLOADEN / OP DE JUISTE PLEK ZETTEN *****
# --------------------------------------------------------------------------------------
// Author: Ruben Vandenbussche
// Website: http://www.RVandenbussche.nl
// Contact: info (at) RVandenbussche (dot) nl
// Date: 11 Nov 2008
// BRON: http://www.phphulp.nl/php/script/php-algemeen/image-upload-script/1484/
//*******************************************
// Script: Image Upload v1.0
//It does:
//  1.  Upload the image.
//  2.  Rename the image.
//  3.  Move the image.
//  4.  Rename if already exists.
//  5.  Check if image is valid if not it gets deleted
//*
//It doesn't:
//  1.  Upload other than .JPG, .JPEG, .GIF and .PNG.
//  2.  Do anything to the image itself.
//  3.  Create directories
//*******************************************

// Summary:
// Function to upload an image from the formfield ($img_ff) to a specified path ($dst_path), check the image and give it an
// other name ($dst_img).

# ---------------------------------------------------
# TOEGEVOEGD DOOR JS MANUPUTTIJ IVM ONTBREKEN php_exif.dll OP DE WEBSERVER
# ZIE OOK: http://nl.php.net/exif_imagetype
# ---------------------------------------------------
if ( ! function_exists( 'exif_imagetype' ) ) {
    function exif_imagetype ( $filename ) {
        if ( ( list($width, $height, $type, $attr) = getimagesize( $filename ) ) !== false ) {
            return $type;
        }
    return false;
    }
}
# ---------------------------------------------------
//Script start here.

function uploadImage($img_ff, $dst_path, $dst_img){

    //Get variables for the function.
            //complete path of the destination image.
    $dst_cpl = $dst_path . basename($dst_img);
            //name without extension of the destination image.
    $dst_name = preg_replace('/\.[^.]*$/', '', $dst_img);
            //extension of the destination image without a "." (dot).
    $dst_ext = strtolower(end(explode(".", $dst_img)));

//Check if destination image already exists, if so, the image will get an extra number added.
    while(file_exists($dst_cpl) == true){
        $i = $i+1;
        $dst_img = $dst_name . $i . '.' . $dst_ext;
        $dst_cpl = $dst_path . basename($dst_img);
    }

        //upload the file and move it to the specified folder.
    move_uploaded_file($_FILES[$img_ff]['tmp_name'], $dst_cpl);
	
	// Nu de bestandsnaam beschikbaar maken om in de database te stoppen
	global $afbeelding;
	$afbeelding = $dst_img;

/*
        //get type of image.
    $dst_type = exif_imagetype($dst_cpl);

        //Checking extension and imagetype of the destination image and delete if it is wrong.
    if(( (($dst_ext =="jpg") && ($dst_type =="2")) || (($dst_ext =="jpeg") && ($dst_type =="2")) || (($dst_ext =="gif") && ($dst_type =="1")) || (($dst_ext =="png") && ($dst_type =="3") )) == false){
        unlink($dst_cpl);
        die('<p class="example">Het bestand "'. $dst_img . '" met de extensie "' . $dst_ext . '" en afbeeldingstype "' . $dst_type . '" is geen geldige afbeelding. Upload een afbeelding met de extensie JPG, JPEG, PNG of GIF met een geldig afbeeldingstype.</p>');
    }
*/

}
//Script ends here. 
?>