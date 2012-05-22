<?php
//session_start();
function force_download ($id, $name)
    {
        global $config;
        $extensie = get_extensions($name);
        $config['upload_path'] = $_SERVER['DOCUMENT_ROOT'].'/portal/files/bestand/'.$id.'/';
        
        /**
         * Bepalen bestandsgrootte
         */
        $filesize = filesize($config['upload_path'] . $name);
        
        /**
         * Bepalen juiste mimetype
         */
        $mimetype = set_mimetype($extensie);
    
        /**
         * Al de verdere output leegmaken
         */
        ob_clean_all();
        
        /* de map e.d. van de naam afhalen */
        //list($map,$slash, $naam) = explode('/', $name);
        /**
         * Verzenden headers
         */
         
         
        header("Pragma: public"); // required
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private",false); // required for certain browsers
        header("Content-Transfer-Encoding: binary");
        header("Content-Type: " . $mimetype);
        header("Content-Length: " . $filesize);
        header("Content-Disposition: attachment; filename=\"" . $name . "\";" );
        
        readfile($config['upload_path'] . $name);
        
        //Send data
        //echo $data;
        die();
    }
    
    function ob_clean_all ()
    {
        $ob_active = ob_get_length () !== false;
        while($ob_active)
        {
            ob_end_clean();
            $ob_active = ob_get_length () !== false;
        }
    
        return true;
    }
    
    /**
     * Bepalen extensie van een file
     */
    function get_extensions($filenaam)
    {
        $filenaam_array = explode(".", $filenaam);
        $filenaam_aantal = count($filenaam_array);
        $extensie = $filenaam_array[$filenaam_aantal-1];
        return $extensie;
    }
        
    /**
     * Ophalen juiste mime-types
     */
    function set_mimetype($extensie)
    {
        $mimetype_array = array(
            'application/andrew-inset' => 'ez',
            'application/mac-binhex40' => 'hqx',
            'application/mac-compactpro' => 'cpt',
            'application/mathml+xml' => 'mathml',
            'application/msword' => 'doc',
            'application/octet-stream' => 'bin',
            'application/octet-stream' => 'dms',
            'application/octet-stream' => 'lha',
            'application/octet-stream' => 'lzh',
            'application/octet-stream' => 'exe',
            'application/octet-stream' => 'dll',
            'application/octet-stream' => 'dmg',
            'application/oda' => 'oda',
            'application/ogg' => 'ogg',
            'application/pdf' => 'pdf',
            'application/postscript' => 'ai',
            'application/postscript' => 'eps',
            'application/postscript' => 'ps',
            'application/rdf+xml' => 'rdf',
            'application/smil' => 'smi',
            'application/smil' => 'smil',
            'application/srgs' => 'gram',
            'application/srgs+xml' => 'grxml',
            'application/vnd.mif' => 'mif',
            'application/vnd.mozilla.xul+xml' => 'xul',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.ms-powerpoint' => 'ppt',
            'application/vnd.wap.wbxml' => 'wbxml',
            'application/vnd.wap.wmlc' => '.wmlc',
            'application/vnd.wap.wmlc' => 'wmlc',
            'application/vnd.wap.wmlscriptc' => 'wmlsc',
            'application/voicexml+xml' => 'vxml',
            'application/x-bcpio' => 'bcpio',
            'application/x-cdlink' => 'vcd',
            'application/x-chess-pgn' => 'pgn',
            'application/x-cpio' => 'cpio',
            'application/x-csh' => 'csh',
            'application/x-director' => 'dcr',
            'application/x-director' => 'dir',
            'application/x-director' => 'dxr',
            'application/x-dvi' => 'dvi',
            'application/x-futuresplash' => 'spl',
            'application/x-gtar' => 'gtar',
            'application/x-hdf' => 'hdf',
            'application/x-httpd-php' => 'php',
            'application/x-httpd-php' => 'php4',
            'application/x-httpd-php' => 'php3',
            'application/x-httpd-php' => 'phtml',
            'application/x-httpd-php-source' => 'phps',
            'application/x-javascript' => 'js',
            'application/x-koan' => 'skp',
            'application/x-koan' => 'skd',
            'application/x-koan' => 'skt',
            'application/x-koan' => 'skm',
            'application/x-latex' => 'latex',
            'application/x-netcdf' => 'nc',
            'application/x-netcdf' => 'cdf',
            'application/x-pkcs7-crl' => '.crl',
            'application/x-sh' => 'sh',
            'application/x-shar' => 'shar',
            'application/x-shockwave-flash' => 'swf',
            'application/x-stuffit' => 'sit',
            'application/x-sv4cpio' => 'sv4cpio',
            'application/x-sv4crc' => 'sv4crc',
            'application/x-tar' => '.tgz',
            'application/x-tar' => 'tar',
            'application/x-tcl' => 'tcl',
            'application/x-tex' => 'tex',
            'application/x-texinfo' => 'texinfo',
            'application/x-texinfo' => 'texi',
            'application/x-troff' => 't',
            'application/x-troff' => 'tr',
            'application/x-troff' => 'roff',
            'application/x-troff-man' => 'man',
            'application/x-troff-me' => 'me',
            'application/x-troff-ms' => 'ms',
            'application/x-ustar' => 'ustar',
            'application/x-wais-source' => 'src',
            'application/x-x509-ca-cert' => '.crt',
            'application/xhtml+xml' => 'xhtml',
            'application/xhtml+xml' => 'xht',
            'application/xml' => 'xml',
            'application/xml' => 'xsl',
            'application/xml-dtd' => 'dtd',
            'application/xslt+xml' => 'xslt',
            'application/zip' => 'zip',
            'audio/basic' => 'au',
            'audio/basic' => 'snd',
            'audio/midi' => 'mid',
            'audio/midi' => 'midi',
            'audio/midi' => 'kar',
            'audio/mpeg' => 'mpga',
            'audio/mpeg' => 'mp2',
            'audio/mpeg' => 'mp3',
            'audio/x-aiff' => 'aif',
            'audio/x-aiff' => 'aiff',
            'audio/x-aiff' => 'aifc',
            'audio/x-mpegurl' => 'm3u',
            'audio/x-pn-realaudio' => 'ram',
            'audio/x-pn-realaudio' => 'rm',
            'audio/x-pn-realaudio-plugin' => 'rpm',
            'audio/x-realaudio' => 'ra',
            'audio/x-wav' => 'wav',
            'chemical/x-pdb' => 'pdb',
            'chemical/x-xyz' => 'xyz',
            'image/bmp' => 'bmp',
            'image/cgm' => 'cgm',
            'image/gif' => 'gif',
            'image/ief' => 'ief',
            'image/jpeg' => 'jpeg',
            'image/jpeg' => 'jpg',
            'image/jpeg' => 'jpe',
            'image/png' => 'png',
            'image/svg+xml' => 'svg',
            'image/tiff' => 'tiff',
            'image/tiff' => 'tif',
            'image/vnd.djvu' => 'djvu',
            'image/vnd.djvu' => 'djv',
            'image/vnd.wap.wbmp' => 'wbmp',
            'image/x-cmu-raster' => 'ras',
            'image/x-icon' => 'ico',
            'image/x-portable-anymap' => 'pnm',
            'image/x-portable-bitmap' => 'pbm',
            'image/x-portable-graymap' => 'pgm',
            'image/x-portable-pixmap' => 'ppm',
            'image/x-rgb' => 'rgb',
            'image/x-xbitmap' => 'xbm',
            'image/x-xpixmap' => 'xpm',
            'image/x-xwindowdump' => 'xwd',
            'model/iges' => 'igs',
            'model/iges' => 'iges',
            'model/mesh' => 'msh',
            'model/iges' => 'iges',
            'model/mesh' => 'mesh',
            'model/iges' => 'iges',
            'model/mesh' => 'silo',
            'model/vrml' => 'wrl',
            'model/vrml' => 'vrml',
            'text/calendar' => 'ics',
            'text/calendar' => 'ifb',
            'text/css' => 'css',
            'text/html' => 'shtml',
            'text/html' => 'html',
            'text/html' => 'htm',
            'text/plain' => 'asc',
            'text/plain' => 'txt',
            'text/richtext' => 'rtx',
            'text/rtf' => 'rtf',
            'text/sgml' => 'sgml',
            'text/sgml' => 'sgm',
            'text/tab-separated-values' => 'tsv',
            'text/vnd.wap.wml' => '.wml',
            'text/vnd.wap.wml' => 'wml',
            'text/vnd.wap.wmlscript' => 'wmls',
            'text/vnd.wap.wmlscript' => 'wmls',
            'text/x-setext' => 'etx',
            'video/mpeg' => 'mpeg',
            'video/mpeg' => 'mpg',
            'video/mpeg' => 'mpeg',
            'video/mpeg' => 'mpe',
            'video/quicktime' => 'qt',
            'video/quicktime' => 'mov',
            'video/vnd.mpegurl' => 'mxu',
            'video/vnd.mpegurl' => 'm4u',
            'video/x-msvideo' => 'avi',
            'video/x-sgi-movie' => 'movie'
            );
        while ($mimetype_extensie = current($mimetype_array))
        {
           if ($mimetype_extensie == $extensie) {
               //echo key($mimetype_array).'<br />';
               $mimetype = key($mimetype_array);
           }
           next($mimetype_array);
        }
    }
    
    /**
     * Bepalen naam file
     */
    $name = $item['upload_naam'];
    
    //echo $extensie;
    force_download($_GET['id'], $_GET['file']);
?>
