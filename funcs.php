<?php

/**
 * Functions for back-end of Flexslider_XH.
 */


if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}



function activate()
{
	global $pth,$plugin,$plugin_cf;

    $speed_slideshow                = isset($plugin_cf['flexslider']['speed_slideshow'])
                                    ? $plugin_cf['flexslider']['speed_slideshow']
                                    : '5000';
    $speed_animation                = isset($plugin_cf['flexslider']['speed_animation'])
                                    ? $plugin_cf['flexslider']['speed_animation']
                                    : '1600';
    $speed_in_list                  = isset($plugin_cf['flexslider']['speed_also_in_image_list'])
                                    ? $plugin_cf['flexslider']['speed_also_in_image_list']
                                    : '';
    $pathdatafiles                  = isset($plugin_tx['flexslider']['path_flexslider_image_files'])
                                    ? $plugin_tx['flexslider']['path_flexslider_image_files']
                                    : (isset($plugin_tx['flexslider']['path_flexslider_data_files'])
                                    ? $plugin_tx['flexslider']['path_flexslider_data_files']
                                    : '');
    $autojump                       = isset($plugin_cf['flexslider']['admin_autojump_to_next_image'])
                                    ? $plugin_cf['flexslider']['admin_autojump_to_next_image']
                                    : 'true';
    $tableheight                    = isset($plugin_cf['flexslider']['admin_images_table_height'])
                                    ? $plugin_cf['flexslider']['admin_images_table_height']
                                    : '';
    $margintop                      = isset($plugin_cf['flexslider']['textelement_margintop'])
                                    ? $plugin_cf['flexslider']['textelement_margintop']
                                    : '5';
    $marginhorz                     = isset($plugin_cf['flexslider']['textelement_marginhorizontal'])
                                    ? $plugin_cf['flexslider']['textelement_marginhorizontal']
                                    : '10';
    $pdata                          = isset($plugin_cf['flexslider']['pagedata-imagelist_enabled'])
                                    ? $plugin_cf['flexslider']['pagedata-imagelist_enabled']
                                    : '';

    $text = "<?php\n\n"
          . '$plugin_cf[\'flexslider\'][\'speed_slideshow\']="'            .$speed_slideshow.'";' . "\n"
          . '$plugin_cf[\'flexslider\'][\'speed_animation\']="'            .$speed_animation.'";' . "\n"
          . '$plugin_cf[\'flexslider\'][\'speed_also_in_image_list\']="'     .$speed_in_list.'";' . "\n"
          . '$plugin_cf[\'flexslider\'][\'pagedata-imagelist_enabled\']="'           .$pdata.'";' . "\n"
          . '$plugin_cf[\'flexslider\'][\'path_flexslider_data_files\']="'   .$pathdatafiles.'";' . "\n"
          . '$plugin_cf[\'flexslider\'][\'admin_autojump_to_next_image\']="'      .$autojump.'";' . "\n"
          . '$plugin_cf[\'flexslider\'][\'admin_images_table_height\']="'      .$tableheight.'";' . "\n"
          . '$plugin_cf[\'flexslider\'][\'textelement_margintop\']="'            .$margintop.'";' . "\n"
          . '$plugin_cf[\'flexslider\'][\'textelement_marginhorizontal\']="'    .$marginhorz.'";' . "\n"
          . '$plugin_cf[\'flexslider\'][\'version\']="'    . constant('FLEXSLIDER_VERSION') .'";' . "\n\n"
          . '?>';

    if (!file_put_contents($pth['folder']['plugins'] . $plugin . '/config/config.php',$text)) {
            e('cntwriteto', 'folder', $pth['folder']['plugins'] . $plugin . '/config/');
    } else {
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($pth['folder']['plugins'] . $plugin . '/config/config.php');
        }
    }

}



/**
 * Part of Image Browser: Change the image folder which is being read for image selection
 *
 */
function flx_ChBrowserDir()
{
    global $pth,$plugin,$flx_browse_dir;

    $chdir_images = isset($_POST['chdir_images'])? $_POST['chdir_images']: '';
    if ($chdir_images == '..') {
        $newpth = substr($flx_browse_dir, 0, strpos(rtrim($flx_browse_dir,'/'),'/'));
    } else {
        $newpth = $flx_browse_dir . $chdir_images;
    }
    $newpth = $newpth? rtrim($newpth,'/') .'/' : '';
    $_SESSION['flx_browse_dir'] = $newpth;
}



/**
 * Java Script (b) to put a clicked image name from the image folder into the flexslider image list
 * Java Script (a) to use image editor
 *
 */
function flx_JsImgSelector()
{
    global $hjs,$plugin_cf,$pth,$plugin,$flx_activated;

    $hjs .= '<script type="text/javascript">

// functions (a) for image resizing/cropping

        function imgEdit(key) {
            document.getElementById("imgEdit").style.display = "block";
            document.getElementById("blackOverlay").style.display = "block";
            document.getElementById("key").value = key;

            var newimg = "dimage1[" + key + "]";
            var imgsrc = document.getElementById(newimg).src;
            document.getElementById("imgEditImage").src = imgsrc;

            // clear height value which may have been changed
            document.getElementById("imgEditImage").style.height = "";

            var n = imgsrc.lastIndexOf("/");
            var imgname = imgsrc.substring(n+1);
            document.getElementById("editImgName").innerHTML = imgname;

            var clientHeight = document.getElementById("imgEdit").clientHeight;

            var dataimg = "dataimage1[" + key + "]";
            var dataimg = document.getElementById(dataimg).innerHTML;
            document.getElementById("dataEditImg").innerHTML = dataimg;

            var m = dataimg.indexOf(" ");
            var imgwidth = dataimg.substring(0,m);
            document.getElementById("newwidth").value = imgwidth;

            var imgheight = dataimg.substring(m+3);
            n = imgheight.indexOf(" ");
            imgheight = imgheight.substring(0,m);
            document.getElementById("newheight").value = imgheight;

            // suggest a name for the changes image
            var imgtype = imgname.slice(-3);
            var imgstart = imgname.substring(0,imgname.lastIndexOf("."));
            document.getElementById("newname").value = imgstart + "_" + imgwidth + "x" + imgheight + "." + imgtype;

            if (imgheight > clientHeight-100) {
                var newheight = clientHeight-100;
                document.getElementById("imgEditImage").style.height = newheight + "px";
            }
        }

        function calc(x) {
            var dataimg = document.getElementById("dataEditImg").innerHTML;
            var m = dataimg.indexOf(" ");
            var imgwidth = dataimg.substring(0,m);
            var imgheight = dataimg.substring(m+3);
            n = imgheight.indexOf(" ");
            imgheight = imgheight.substring(0,m);

            var newwidth;
            var newheight;

            if (x == "height") {
                newwidth = document.getElementById("newwidth").value;
                newheight = Math.round((imgheight * newwidth) / imgwidth);
                document.getElementById("newheight").value = newheight;
            } else {
                newheight = document.getElementById("newheight").value;
                newwidth = Math.round((imgwidth * newheight) / imgheight);
                document.getElementById("newwidth").value = newwidth;
            }


            // suggest a name for the changes image
            var imgname = document.getElementById("editImgName").innerHTML;
            var imgtype = imgname.slice(-3);
            var imgstart = imgname.substring(0,imgname.lastIndexOf("."));
            document.getElementById("newname").value = imgstart + "_" + newwidth + "x" + newheight + "." + imgtype;
        }

        function numValidate(x) {
            var y = x.value;
            if (y-0!=y) alert("ERROR: " + y + " is not numeric");
        }


//functions (b) for putting images into the flexslider image list

        function addtotemp(position) {
            document.getElementById("d"+position).className = "imagefocus";
            if (document.getElementById("temp").value != position) {
                var image = "d"+document.getElementById("temp").value;
                document.getElementById(image).className = "";
            }
            document.getElementById("temp").value = position;
        }

        function addfile(image,dimension) {
            var position = document.getElementById("temp").value;

            var key = position.slice(7);
            key = key.slice(0,-1);
            document.getElementById("margin[" + key + "]").style.display = "none";

            var dimage = "d"+position;

            document.getElementById(position).value = image;
            document.getElementById(dimage).src = \''.$pth['folder']['images'].'\' + image;
            document.getElementById(\'data\'+position).innerHTML = dimension;
            document.getElementById(position).focus();
            addtotemp(position);';

    if ($flx_activated && $plugin_cf[$plugin]['admin_autojump_to_next_image']) {
        $hjs .= 'setTimeout(function(){

                    var element = document.getElementById(position);
                    //element.scrollIntoView(true);

                    var newpos;
                    newpos = position.slice(7);
                    newpos = newpos.slice(0,-1);
                    newpos++;
                    newpos = "image1["+newpos+"]";
                    if (document.getElementById(newpos)) {
                        position = newpos;
                        addtotemp(position);
                    }
                    //document.getElementById(spacer).style.backgroundColor= "#f00";
                    document.getElementById(position).focus();
                    document.getElementById("imagelist").scrollTop += 100;

                },350);';
    }

    $hjs .= '}
        </script>';
}



/**
 * The image resizing/cropping field which pops up in light box style
 *
 */
function flx_ImgEdit()
{
    global $noimage,$plugin_tx,$imagefile_pth,$activefile,$pth,$plugin,$sn,$languagefile,$tx;
    $o = '';

    $o .= '<div id="imgEdit">'
       .  '<img src="'.$noimage.'" id="imgEditImage">'
       .  '<input type="hidden" name="key" id="key">'

       .  '<p><span id="editImgName"></span>, &nbsp; <span id="dataEditImg"></span></p>'

       .  '<p>'. $plugin_tx['flexslider']['new_size'] .': '
       .  "\n" . $plugin_tx['flexslider']['width']
       .  "\n" . '<input type="text" style="width:3em;" name="newwidth"
                 id="newwidth" OnChange="numValidate(this);calc(\'height\');">px, '
       .  "\n" . $plugin_tx['flexslider']['height']
       .  "\n" . '<input type="text" style="width:3em;" name="newheight"
                 id="newheight" OnChange="numValidate(this);calc(\'width\');">px'

       .  '</p>'
       .  "\n" . '<input type="submit" value="'.$plugin_tx['flexslider']['save_as'].'">'
       .  "\n" . '<input type="text" style="width:23em;" name="newname" id="newname">'

       .  '<button type="button"
          OnClick="document.getElementById(\'imgEdit\').style.display = \'none\';
          document.getElementById(\'blackOverlay\').style.display = \'none\';
          document.getElementById(\'newname\').value = \'\';">'
       .  $plugin_tx['flexslider']['close']
       .  '</button>'
       .  '</div><div id="blackOverlay"
          OnClick="document.getElementById(\'imgEdit\').style.display = \'none\';
          document.getElementById(\'blackOverlay\').style.display = \'none\';
          document.getElementById(\'newname\').value = \'\';"></div>';

    return $o;
}



/**
 * create, copy, delete and select the file where the images of the imagelist are stored
 *
 * @return string The error message
 */
function flx_FileAdmin()
{
    global $plugin_tx,$imagefile_pth,$activefile,$pth,$plugin,$sl,$languagefile;
    $o = '';
    $newimgbrowserdir = false;

    $newimagefile = isset($_POST['newimagefile'])? $_POST['newimagefile']: '';
    $newname      = isset($_POST['newname'])   ?   $_POST['newname']     : '';
    $deletefile   = isset($_POST['deletefile'])?   $_POST['deletefile']  : '';
    $newfile = '';


    if (($newimagefile == 'add' || $newimagefile == 'copy') &&  $newname) {
        if (preg_match('/[^a-zA-Z0-9\-\._]/',$newname)) {
            $wrongchar = true;
            $o .= '<p class="cmsimplecore_warning">' . $plugin_tx[$plugin]['file-manager_wrong_char'] . '</p>';
        } else {
            $newfile = $newname;
            if (substr($newfile,-4,4)!='.php') $newfile .= '.php';


            // copying
            if ($newimagefile == 'copy')  {
                $oldfile = file_get_contents($imagefile_pth.$activefile);
                if ($oldfile === false) e('cntopen', 'content', $imagefile_pth.$activefile);
                if (file_put_contents($imagefile_pth.$newfile, $oldfile) === false) {
                    e('cntwriteto', 'content', $imagefile_pth.$activefile);
                } else {
                    if (function_exists('opcache_invalidate')) {
                        opcache_invalidate($imagefile_pth.$newfile);
                    }
                }
            }
        }

    } elseif ($deletefile && $newimagefile == 'del') {
        unlink($imagefile_pth . $deletefile);
        $newimgbrowserdir = true;


    } else {
        // this is the case when only the active file is being changed
        $newfile = $newimagefile;
        $newimgbrowserdir = true;

    }

    if ($newimgbrowserdir) {
        // read from the new active file the directory of the first image
        // of the new image list and change the image browser to that directory
        // so that the fitting image folder is seen right away
        $newfile = $newfile? $newfile : 'flexslider_'.$sl.'.php';
        $imagearray = json_decode(file_get_contents($imagefile_pth.$newfile),true);
        // in case the first slides have no images, go to the first slide with image
        for ($i = 0; $i < count($imagearray['image1']); $i++ ) {
            if ($imagearray['image1'][$i]) {
                $newpth = substr($imagearray['image1'][$i],0,strpos($imagearray['image1'][$i],'/'));
                $newpth = $newpth? $newpth.'/':'';
                $_SESSION['flx_browse_dir'] = $newpth;
                break;
            }
        }
    }

    // write to lang config which imagelist file is "active"
    $activefile = $_SESSION['flx_activefile'] = $newfile;

    return $o;
}



/**
 * Create a json file with all variables as default image list
 *
 */
function flx_MakeDefaultFile()
{
    global $imagefile_pth,$activefile;

    if (!is_file($imagefile_pth.$activefile)){
       $x = json_encode(array(
            'image1'        =>array(''),
            'text'          =>array(''),
            'markdown'      =>array(''),
            'link'          =>array(''),
            'display'       =>array(''),
            'margintop'     =>array('5'),
            'marginhorz'    =>array('10'),
            'type'          =>array(''),
            'caption'       =>'',
            'random'        =>'',
            'randomstart'   =>'',
            'halt'          =>'',
            'speedshow'     =>'',
            'speedanim'     =>'',
            'cnav'          =>'1',
            'dnav'          =>'1',
            'pdata'         =>''));
        if (!file_put_contents($imagefile_pth.$activefile, $x)) {
            e('notwritable', 'folder', $imagefile_pth);
            e('missing', 'file', $imagefile_pth.$activefile);
        }
    }
}



/**
 * Create a resized sharpened image from a selected image
 *
 */
function flx_NewImage($folder,$oldname,$newname,$width,$height)
{
    global $pth;

    $uri = $pth['folder']['images'].$folder.$oldname;
    list($oldwidth,$oldheight) = getimagesize($uri);
    $newuri = $pth['folder']['images'].$folder.$newname;

	$type = substr($uri, strrpos($uri, '.')+1);
    switch ($type) {
        case 'jpg':
        case 'jpeg':
            $source = imagecreatefromjpeg($uri);
            break;
        case 'png':
            $source = imagecreatefrompng($uri);
            break;
        case 'gif':
            $source = imagecreatefromgif($uri);
            break;
        default:
            e('undefined','file',$uri);
    }
    $new = imagecreatetruecolor($width, $height);

    imagecopyresampled($new, $source, 0, 0, 0, 0, $width, $height, $oldwidth, $oldheight);

    if (function_exists('imageconvolution')){

        // define the sharpen matrix
        $sharpen = array(
        	array(-0.5, -1, -0.5),
        	array(-1, 16, -1),
        	array(-0.5, -1, -0.5)
        );

        // calculate the sharpen divisor
        $divisor = array_sum(array_map('array_sum', $sharpen));

        // apply the matrix
        imageconvolution($new, $sharpen, $divisor, 0);
    }

    switch($type){
        case 'gif': imagegif($new, $newuri); break;
        case 'jpg': imagejpeg($new, $newuri); break;
        case 'png': imagepng($new, $newuri); break;
    }
}



/**
 * Receive as post which images in the imagelist image list should be saved, rearranged or deleted
 *
 */
function flx_SaveImgList($fill=NULL)
{
    global $plugin,$imagefile_pth,$activefile,$plugin_cf;

    $imglistaddmore         =isset($_POST['addmore'])   ?  $_POST['addmore']    : '';
    $imglistadd             =isset($_POST['add'])       ?  $_POST['add']        : array();
    $imglistup              =isset($_POST['up'])        ?  $_POST['up']         : array();
    $imglistdown            =isset($_POST['down'])      ?  $_POST['down']       : array();
    $imglistdelete          =isset($_POST['delete'])    ?  $_POST['delete']     : array();
    $imglist['image1']      =isset($_POST['image1'])    ?  $_POST['image1']     : array();
    $imglist['text']        =isset($_POST['text'])      ?  $_POST['text']       : array();
    $imglist['link']        =isset($_POST['link'])      ?  $_POST['link']       : array();
    $imglist['extlink']     =isset($_POST['extlink'])   ?  $_POST['extlink']    : array();
    $imglist['display']     =isset($_POST['display'])   ?  $_POST['display']    : array();
    $imglist['margintop']   =isset($_POST['margintop']) ?  $_POST['margintop']  : array();
    $imglist['marginhorz']  =isset($_POST['marginhorz'])?  $_POST['marginhorz'] : array();
    $imglist['type']        =isset($_POST['type'])      ?  $_POST['type']       : array();
    $imglist['caption']     =isset($_POST['caption'])   ?  $_POST['caption']    : array();
    $imglist['random']      =isset($_POST['random'])    ?  $_POST['random']     : array();
    $imglist['randomstart'] =isset($_POST['randomstart'])? $_POST['randomstart']: array();
    $imglist['halt']        =isset($_POST['halt'])      ?  $_POST['halt']       : array();
    $imglist['cnav']        =isset($_POST['cnav'])      ?  $_POST['cnav']       : array('1');
    $imglist['dnav']        =isset($_POST['dnav'])      ?  $_POST['dnav']       : array('1');
    $imglist['speedshow']   =isset($_POST['speedshow']) ?  $_POST['speedshow']  : array();
    $imglist['speedanim']   =isset($_POST['speedanim']) ?  $_POST['speedanim']  : array();
    $imglist['pdata']       =isset($_POST['pdata'])     ?  $_POST['pdata']      : array();

    $width                  =isset($_POST['newwidth']) ? $_POST['newwidth']   : '';
    $height                 =isset($_POST['newheight'])? $_POST['newheight']  : '';
    $newname                =isset($_POST['newname'])  ? $_POST['newname']    : '';
    $key                    =isset($_POST['key'])      ? $_POST['key']        : '';

    // for some funny reasons these values, when submit is trigered by java script,
    // are always send as arrays (not however with normal submit). To make everything
    // work, I am using now arrays in the names. These arrays have to be converted
    // to simple variables, otherwise they won't survive reordering and deleting images.
    $display     = $imglist['display'][0];
    $type        = $imglist['type'][0];
    $caption     = $imglist['caption'][0];
    $random      = $imglist['random'][0];
    $randomstart = $imglist['randomstart'][0];
    $halt        = $imglist['halt'][0];
    $cnav        = $imglist['cnav'][0];
    $dnav        = $imglist['dnav'][0];
    $speedshow   = $imglist['speedshow'][0];
    $speedanim   = $imglist['speedanim'][0];
    $pdata       = $imglist['pdata'][0];
    $calcwidth   = $display && $display != 'standard' && $display != 'flexible'
                            ? flx_CalcWidths($display,$imglist['image1'])
                            : '';


// for image resizing
if ($newname) {
    $x = strrpos($imglist['image1'][$key],'/');
    $x = $x? $x + 1 : 0;
    $folder  = substr($imglist['image1'][$key],0,$x);
    $oldname = substr($imglist['image1'][$key],$x);
    $imglist['image1'][$key]=$folder.$newname;
    //echo '$folder: '.$folder.', $oldname: '.$oldname.', $newname: '.$newname;
    flx_NewImage($folder,$oldname,$newname,$width,$height);

}

    // Add multiple empty items to the image list
    if ($imglistaddmore) {
        for($i=0;$i<$imglistaddmore;$i++) {
            // go through all variables an item of the image list has
            foreach ($imglist as $key=>$value) {
                if (!is_array($imglist[$key])) $imglist[$key] = array();

                // in adding empty items the margins of the text only field are preset
                if ($key=='margintop') {
                    array_push($imglist[$key],$plugin_cf[$plugin]['textelement_margintop']);
                } elseif ($key=='marginhorz') {
                    array_push($imglist[$key],$plugin_cf[$plugin]['textelement_marginhorizontal']);
                } else array_push($imglist[$key],'');
            }
        }
    } 

    // Add one item after a specific item in the image list
    $addkey = array_search(TRUE, $imglistadd);
    if ($addkey > 0 || $addkey === 0) {
        foreach ($imglist as $key=>$value) {
            if (!is_array($imglist[$key])) $imglist[$key] = array();
            if ($key=='margintop') {
                array_splice($imglist[$key],($addkey + 1),0,array($key=>$plugin_cf[$plugin]['textelement_margintop']));
            } elseif ($key=='marginhorz') {
                array_splice($imglist[$key],($addkey + 1),0,array($key=>$plugin_cf[$plugin]['textelement_marginhorizontal']));
            } else array_splice($imglist[$key],($addkey + 1),0,array($key=>''));
        }
    }

    // Move an item up the list
    $upkey = array_search(TRUE, $imglistup);
    if ($upkey || $upkey === 0) {
        foreach ($imglist as $key=>$value) {
            if (!is_array($imglist[$key])) $imglist[$key] = array();
            // extract values
            $moving_up = array_slice($imglist[$key],$upkey,1);
            // delete extracted values in the original array
            array_splice($imglist[$key],$upkey,1);
            // add extracted value higher into the original array
            if ($upkey > 0) array_splice($imglist[$key],($upkey - 1),0,$moving_up);
            if ($upkey === 0) array_splice($imglist[$key],count($imglist),0,$moving_up);
        }
    }

    // Move an item down the list
    $downkey = array_search(TRUE, $imglistdown);
    if ($downkey || $downkey === 0) {
        foreach ($imglist as $key=>$value) {
            if (!is_array($imglist[$key])) $imglist[$key] = array();
            // extract values
            $moving_down = array_slice($imglist[$key],$downkey,1);
            // delete extracted values in the original array
            array_splice($imglist[$key],$downkey,1);
            // add extracted value lower into the original array
            if ($downkey < (count($imglist['image1']))) {
                array_splice($imglist[$key],($downkey + 1),0,$moving_down);
            } else array_splice($imglist[$key],0,0,$moving_down);
        }
    }

    // Delete an item from the image list
    $delkey = array_search(TRUE, $imglistdelete);
    if ($delkey > 0 || $delkey === 0) {
        foreach ($imglist as $key=>$value) {
            if (!is_array($imglist[$key])) $imglist[$key] = array();
            array_splice($imglist[$key],$delkey,1);
        }
    }

    // A little clean up
    foreach ($imglist['image1'] as $key=>$value) {
    	$imglist['text'][$key] = str_replace('"','\'',stsl($imglist['text'][$key]));
        if (!isset($imglist['image1'][$key]) || !$imglist['image1'][$key]) {
            $imglist['markdown'][$key] = $imglist['text'][$key];
            $imglist['text'][$key] = flx_MarkdownToHtml($imglist['text'][$key]);
        } else {
            $imglist['markdown'][$key] = '';
            $imglist['text'][$key] = str_replace(array("\r\n", "\r", "\n") , '<br>' , $imglist['text'][$key]);
        }

        if ($imglist['link'][$key] == 'ext' && $imglist['extlink'][$key]) {
                $imglist['link'][$key] = strpos($imglist['extlink'][$key],'http') !== 0
                                    ? 'http://'.$imglist['extlink'][$key]
                                    : $imglist['extlink'][$key];
        } elseif ($imglist['link'][$key] == 'ext' && !$imglist['extlink'][$key]) {
                $imglist['link'][$key] = '';
        }
    }
    unset($imglist['extlink']);

    $imglist['display']     = $display;
    $imglist['type']        = $type;
    $imglist['caption']     = $caption;
    $imglist['random']      = $random;
    $imglist['randomstart'] = $randomstart;
    $imglist['halt']        = $halt;
    $imglist['cnav']        = $cnav;
    $imglist['dnav']        = $dnav;
    $imglist['width']       = $calcwidth;
    $imglist['speedshow']   = $speedshow;
    $imglist['speedanim']   = $speedanim;
    $imglist['pdata']       = $pdata;

    if ($fill) $imglist = flx_FillFromDir($imglist);

    file_put_contents($imagefile_pth.$activefile,json_encode($imglist));
    if (function_exists('opcache_invalidate')) {
        opcache_invalidate($imagefile_pth.$activefile);
    }
}



/**
 * Put all images from a selected folder into an image list, without duplicates
 *
 */
function flx_FillFromDir($imglist)
{
    global $pth,$flx_browse_dir;

    $newimages=array();
    if ($handle = opendir($pth['folder']['images'].$flx_browse_dir)) {
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..' && !is_dir($pth['folder']['images'].$flx_browse_dir.$file)) {
                $newimages[] = $file;
            }
        }
        closedir($handle);
    }

    foreach ($newimages as $key=>$value) {
        foreach ($imglist['image1'] as $imgkey=>$imgvalue) {
        	if (($imgvalue) == $flx_browse_dir.$value) unset($newimages[$key]);
        }
    }

    foreach ($newimages as $value) {
    	$imglist['image1'][] = $flx_browse_dir.$value;
        $imglist['text'][] = '';
        $imglist['link'][] = '';
        $imglist['margintop'][] = '';
        $imglist['marginhorz'][] = '';
        $imglist['markdown'][] = '';
    }

    return $imglist;
}



/**
 * Calculate the necessary margin width to fit an image into a fixed frameratio
 *
 */
function flx_CalcWidths($frameratio, $images)
{
    global $pth;

    $t = array();
    foreach ($images as $key=>$value) { 
        if ($value) {
            list($width,$height) = getimagesize($pth['folder']['images'].$value);
            $imageratio = $width / $height;
        	$t[$key] = $imageratio && is_numeric($frameratio)
                ? round(($imageratio * 100) / $frameratio,2)
                : '';
            if ($t[$key] >= 100) $t[$key] = '';
        }
    }
    return $t;
}



/**
 * Create a json file with all variables as default image list
 *
 */
function flx_MarkdownToHtml($text)
{
   $Parsedown = new Parsedown();
return   $Parsedown->text($text);
}



/**
 * Send as post which flexslider file to select, copy, deleate or create
 *
 */
function flx_FileManager()
{
    global $sn,$sl,$imagefile_pth,$plugin_tx,$plugin,$tx;

    $o = '';
    $o .= '<form method="POST" style="display:inline-block;" action="' . $sn
        .  '?&'.$plugin.'&amp;admin=plugin_main&amp;action=save_imagefile" name="filemanagement">';

    $handle=opendir($imagefile_pth);
    $imagefiles = array();
    if ($handle) {
        while (false !== ($imagefile = readdir($handle))) {
            if ($imagefile != "." && $imagefile != ".." && $imagefile != $plugin.'_'.$sl.'.php') {
                $imagefiles[] = $imagefile;
            }
        }
    }
    closedir($handle);
    natcasesort($imagefiles);
    $imagefiles_select = '';
    foreach($imagefiles as $value){
        $selected = '';
        if ($_SESSION['flx_activefile'] == $value) {$selected = ' selected';}
        $imagefiles_select .= "\n<option value=$value$selected>$value</option>";
    }
    $o .= '<select name="newimagefile" OnChange="
            if (this.options[this.selectedIndex].value == \'add\' || this.options[this.selectedIndex].value == \'copy\') {
                document.getElementById(\'newfile\').style.display = \'inline\';
                document.getElementById(\'delete\').style.display = \'none\';
                document.getElementById(\'previewtop\').style.display = \'none\';
            } else if (this.options[this.selectedIndex].value == \'del\') {
                document.getElementById(\'delete\').style.display = \'inline\';
                document.getElementById(\'newfile\').style.display = \'none\';
                document.getElementById(\'previewtop\').style.display = \'none\';
            } else {
                document.getElementById(\'delete\').style.display = \'none\';
                document.getElementById(\'newfile\').style.display = \'none\';
                document.getElementById(\'previewtop\').style.display = \'inline\';
                this.form.submit();
            }  ; ">'

       .  "\n" . '<optgroup><option value="">' . $plugin_tx[$plugin]['file-manager_standard_file'] . '</option>'
       .  "\n" . $imagefiles_select .'</optgroup>'
       .  "\n" . '<option value="add">' . $plugin_tx[$plugin]['file-manager_create_new_file'] . '</option>'
       .  "\n" . '<option value="copy">'. $plugin_tx[$plugin]['file-manager_copy_file']       . '</option>'
       .  "\n" . '<option value="del">' . $plugin_tx[$plugin]['file-manager_delete_file']     . '</option>'
       .  '</select>'

        //delete imagelist file
       .  '<span id="delete" style="display:none"> &nbsp; '
       .  '<select name="deletefile">'
       .  "\n" . $imagefiles_select
       .  '</select>'
       .  '<input type="submit" name="delete" style="background:#fbb;" value="' . ucfirst($tx['action']['delete']).'">'
       .  '</span>'

        //create new imagelist file
       .  '<span id="newfile" style="display:none">'
       .  '<input type="text" name="newname" placeholder="'.$plugin_tx[$plugin]['file-manager_enter_new_file_name'] .'">'
       .  '<input type="submit" value="' . ucfirst($plugin_tx[$plugin]['file-manager_create_new_file'])
       .  '" name="newfilename">'
       .  '</span>'

       .  '</form>';

    return $o;
}



/**
 * Calculate the image aspect ratio
 *
 */
function flx_AspectRatio($width=0,$height=0)
{
	if (!$width || !$height) return '';

    $ratio = $width / $height;

    if ($ratio > 0.63 && $ratio < 0.68) return '2:3, ';
    if ($ratio > 0.73 && $ratio < 0.77) return '3:4, ';
    if ($ratio > 0.98 && $ratio < 1.02) return '1:1, ';
    if ($ratio > 1.31 && $ratio < 1.35) return '4:3, ';
    if ($ratio > 1.48 && $ratio < 1.52) return '3:2, ';
    if ($ratio > 1.75 && $ratio < 1.79) return '16:9, ';
    if ($ratio > 1.98 && $ratio < 2.02) return '2:1, ';
    for ($i=1;$i<=20;$i++ ) {
        $t = round($i * $ratio, 2);
        $t = round($t)==$t
           ? $t
           : round($t)== $t + .01
           ? $t
           : round($t)== $t - .01
           ? $t
           : 0;
        if ($t) {
            return round($t) .':'.$i.', ';
            break;
        }
    }
    return '1:'.round($height/$width,2) . ', ';
}



/**
 * Display a list of found image files (in the choosen folder) and make images selectable via java script
 *
 */
function flx_JsImgBrowser()
{
    global $pth,$flx_browse_dir,$sn,$plugin_cf,$plugin_tx,$plugin,$tx;

    $o = '';

    $dir = $flx_browse_dir? $flx_browse_dir : '';
    $handle=opendir($pth['folder']['images'].$dir);
    $imagefiles = array();
    $i=0;
    $temp= '<form method="POST" action="' . $sn
         . '?&'.$plugin.'&amp;admin=plugin_main&amp;action=chdir_images" id="chdir" class="imagebrowser">'
         . '<input type="hidden" name="chdir_images" id="chdir_images" value="">'
         . "\n" . '<input type="hidden" name="scroll1" id="scroll1" value="0">';

    if ($handle) {
        while (false !== ($file = readdir($handle))) {
            $fileend = substr($file, -4, 4);
            if ($file != '.' && $file != '..') {
                if (!is_dir($pth['folder']['images'].$flx_browse_dir.$file)
                    && ($fileend == '.jpg' || $fileend == '.gif' || $fileend == '.png'))
                {
                    $imagefiles[] = $file;
                    $i++;
                }
                if (is_dir($pth['folder']['images'].$flx_browse_dir.$file)){
                    $levelup[] = $file;
                }
            }
        }
    }
    $temp = '';
    closedir($handle);
    natcasesort($imagefiles);
    if ($_SESSION['flx_browse_dir']) {
        $temp .= "\n" . '<a class="imgbrowser_back" href="javascript:void(0);"
                 OnClick="document.getElementById(\'scrollpos\').value = document.documentElement.scrollTop;
                 document.getElementById(\'scrollpos2\').value = document.getElementById(\'imagelist\').scrollTop;
                 document.getElementById(\'chdir_images\').value=\'..\';
                 document.getElementById(\'imagesform\').submit();"><b>'.$plugin_tx[$plugin]['back'].'</b></a> ';
    }
    if (isset($levelup)) {
        foreach ($levelup as $key=>$value) {
            $temp .= "\n" . '<a href="javascript:void(0);"
                     OnClick="document.getElementById(\'scrollpos\').value = document.documentElement.scrollTop;
                     document.getElementById(\'scrollpos2\').value = document.getElementById(\'imagelist\').scrollTop;
                     document.getElementById(\'chdir_images\').value=\''.$value.'\';
                     document.getElementById(\'imagesform\').submit();"><b>'
                  .  $value . '</b></a> ';
        }
    }
    foreach ($imagefiles as $key2=>$value2) {
        if (list($width,$height) = getimagesize($pth['folder']['images'].$flx_browse_dir.$value2)) {
            $aspectratio = flx_AspectRatio($width,$height);
            $filesize = round(filesize($pth['folder']['images'].$flx_browse_dir.$value2) / 1024,1);
            $dimension = $width.' x '.$height.' px, '. $aspectratio . $filesize.' kB ';
        } else {
            $width = $height = 0;
            $dimension = 'ERROR';
        }
        if ($height>100) {
            $width = $width * 100 / $height;
            $height = '100';
        }
        $imagefiles[$key2] = '<a href="javascript:;"  onClick="addfile(\''
                      . $flx_browse_dir . $value2.'\',\''.$dimension.'\');" class="img_imgbrowser">'.$value2.'<span>'.$dimension.'</span><img src="'
                      . $pth['folder']['images'].$flx_browse_dir . $value2
                      . '" style="height:'.$height.'px;width:'.$width.'px;" height="'.$height.'" width="'.$width.'"></a> ';
    }
    $img_imgbrowser = implode(' ',$imagefiles);

    $o .= "\n"
       .  '<div id="imagefiles">'
       .  $plugin_tx[$plugin]['imagebrowser_usage_hint']
       .  "\n" . '(' . $i . ' '
       .  $tx['editmenu']['files'] . ')' . '<br>'
       .  "\n" . '<small>' . $temp. '</form>' . $img_imgbrowser . '</small>' . '</div>';

    $o .= '<input type="hidden" id="temp" value="image1[0]">';

    return $o;
}



/**
 * create an option field with all selectable pages of the site and the possibility to enter an external link
 *
 */
function flx_LinkSelect()
{
	global $plugin,$plugin_tx,$cl,$u,$l,$h,$link,$key;

    $o = '';

    $pages_select = '';
    $x = 0;
    for ($i = 0; $i < $cl; $i++) {
        $selected = '';
        if (substr($link,1) == $u[$i]) {$selected = ' selected="selected"'; $x++;}
        $levelindicator = '';
        for ($j = 1; $j < $l[$i]; $j++) {$levelindicator .= '&ndash;&nbsp;';}
        $page = $levelindicator.$h[$i];
        $page = strlen($page)>45? substr($page,0,43).'...':$page;
        $pages_select .= '<option value="?'.$u[$i].'"'.$selected.'>'."\n".$page.'</option>';
    }
    $selected = $extlinkinput = $extlink = '';
    $extlinkinput = 'display:none;';
    if ($link && !$x) {
        $extlinkinput = 'display:inline;';
        $selected     = ' selected';
        $extlink      = $link;
    }
    $goto_extlink = '<option value="ext"'.$selected.'>'.$plugin_tx[$plugin]['link_external'].'</option>';


    $o .= '<select name="link['.$key.']" OnChange="
           if (this.options[this.selectedIndex].value == \'ext\') {
               document.getElementById(\'extlink['.$key.']\').style.display = \'inline\';
           } else {
               document.getElementById(\'extlink['.$key.']\').style.display = \'none\';
           } ; ">'
       .  "\n"
       .  '<option value="">'.$plugin_tx[$plugin]['link_no'].'</option>'
       .  "\n"
       .  $goto_extlink
       .  "\n"
       .  $pages_select . '</select>'
       .  '<br>'
       .  '<input type="text" style="'.$extlinkinput.';width:97%;" name="extlink['
       .  $key.']" id="extlink['.$key.']" placeholder="'
       .  $plugin_tx[$plugin]['link_enter_external'].'" value="' . $extlink . '">';


    return $o;
}


