<?php

/**
 * Back-end of Flexslider_XH.
 * Copyright (c) 2014-15 svasti@svasti.de
 */


if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

define('FLEXSLIDER_VERSION', '0.5.4');

/**
 * Plugin administration
 */
if (function_exists('XH_wantsPluginAdministration') && XH_wantsPluginAdministration('flexslider')
    || isset($flexslider) && $flexslider
) {
    $hjs .= '<link rel="stylesheet" type="text/css" href="'.$pth['folder']['plugins'].$plugin.'/css/backend.css">';
    $o .= print_plugin_admin('on');

// This is only to avoid errormessages before plugin activation
if (is_file($pth['file']['plugin_config'])) {
    $flx_activated = true;
    define('FLEXSLIDER_CONFIG_VERSION', $plugin_cf['flexslider']['version']);
} else {
    $flx_activated = false;
    define('FLEXSLIDER_CONFIG_VERSION', '');
}


    // Preparation and reception of post data
    //=========================================

    include 'funcs.php';
    include 'parsedown/Parsedown.php';

    if (isset($_POST['activate'])) {
        activate();
        include $pth['folder']['plugins'] . $plugin . '/config/config.php';
    }

    if ($flx_activated) {

        // Get folder for flexslider image files
        $imagefile_pth = $plugin_cf[$plugin]['path_flexslider_data_files']
                      ? $plugin_cf[$plugin]['path_flexslider_data_files']
                      : $pth['folder']['userfiles'].'plugins/'.$plugin.'/';
        // Make folder if missing
        if (!is_dir($imagefile_pth)) {
            if (mkdir($imagefile_pth,0777,true)===false) e('missing','folder',$imagefile_pth);
        }
        // Placeholder for missing image
        $noimage  = $pth['folder']['plugins'] . $plugin . '/images/empty100x30.gif';

        // Paths for imagebrowser (not for the flexslider image files)
        $imgdir    = $pth['folder']['images'];
        if (!isset($_SESSION['flx_browse_dir'])) $_SESSION['flx_browse_dir'] = '';
        $flx_browse_dir =  $_SESSION['flx_browse_dir'];
   }

    // get the last "active" flexslider file from the session,
    // if there is none, the standard flexslider file will be set as active
    if (!isset($_SESSION['flx_activefile'])) $_SESSION['flx_activefile'] = $plugin.'_'.$sl.'.php';
    $activefile = $_SESSION['flx_activefile'];

    // return to the same scroll positions on reloading the page
    //===========================================================
    $scroll1 = isset($_POST['scroll1'])? $_POST['scroll1'] : '0';
    $scroll2 = isset($_POST['scroll2'])? $_POST['scroll2'] : '0';
    $bjs .= '<script type="text/javascript">';
    if ($scroll1)  $bjs .= 'window.scrollTo(0,'.($scroll1).');';
    if ($scroll2) $bjs .= 'document.getElementById(\'imagelist\').scrollTop = '.$scroll2 .';';
    if (isset($_POST['scroll2'])) {
        $bjs .= 'if (document.getElementById(\'list_saved\')) {
                 document.getElementById(\'list_saved\').style.visibility = \'visible\';
                 setTimeout(function(){document.getElementById(\'list_saved\').style.visibility = \'hidden\'},1500);
                 }';
    }

    // load Java script for autogrowing text area input field
    //=========================================================
    $bjs .= '// the following code is adapted from Opera s Neil Jenkins, see
             // http://www.alistapart.com/articles/expanding-text-areas-made-elegant/
            function makeExpandingArea(container) {
                var area = container.querySelector("textarea");
                var span = container.querySelector("span");
                if (area.addEventListener) {
                    area.addEventListener("input", function() {
                        span.textContent = area.value;
                    }, false);
                    span.textContent = area.value;
                } else if (area.attachEvent) {
                    // IE8 compatibility
                    area.attachEvent("onpropertychange", function() {
                        span.innerText = area.value;
                    });
                    span.innerText = area.value;
                }
                // Enable extra CSS
                container.className += " active";
            }

            var areas = document.querySelectorAll(".expandingArea");
            var l = areas.length;

            while (l--) {
             makeExpandingArea(areas[l]);
            }
          // end of code for autoadjusting textareas
          </script>';


    // receive and save which Flexslider image file should be displayed, created, deleted, copied
    if ($action=='save_imagefile') {
        $o .= flx_FileAdmin();
        $flx_browse_dir =  $_SESSION['flx_browse_dir'];
        include($pth['folder']['plugins'].$plugin .'/config/config.php');
    }


    // get directory changes of the image file browser
    if (isset($_POST['chdir_images'])) {
        flx_ChBrowserDir();
        $flx_browse_dir = $_SESSION['flx_browse_dir'];
    }


    // write Java Scripts to $hjs
    flx_JsImgSelector();


    flx_MakeDefaultFile();


    // receive and save changes to the image list
    if ($action=='save_images')  {
        if (isset($_POST['fill'])) {
            flx_SaveImgList(1);
        }
        else flx_SaveImgList();
    }


    // read the flexslider image list into an array
    $imagearray = json_decode(file_get_contents($imagefile_pth.$activefile),true);




    // standard admin mode, with image browser and flexslider image list. Start of HTML-body
    //======================================================================================
    if (!$admin || $admin=='plugin_main' && (!isset($_POST['preview'])
        || (isset($_POST['preview']) && !$_POST['preview']))) {


        // Selector (and manager) for Flexslider images list
        //==================================================
        if ($flx_activated) {
            $o .= $plugin_tx['flexslider']['file-manager_select'] . ': ' . flx_FileManager();
            // Preview Link (has to be on the top because otherwise during preview the mouse cursor will be right
            // on the image and will prevent the sliding action, in case halt-onmouseover is set)
            $o .= '<button id="previewtop" OnClick="document.getElementById(\'preview\').value=true; document.getElementById(\'imagesform\').submit();">'
               .  $plugin_tx[$plugin]['preview']
               .  '</button>';
        }

        // Title and License
        //====================
        $o .= '<h5>Flexslider_XH '.FLEXSLIDER_CONFIG_VERSION
           .  '<small><small> by <a href="http://svasti.de" target="_blank">svasti</a> &nbsp; '
           .  '<input type="button" value="license?" style="font-size:80%;" OnClick="
              if (document.getElementById(\'license\').style.display == \'none\') {
                  document.getElementById(\'license\').style.display = \'inline\';
                  } else {
                  document.getElementById(\'license\').style.display = \'none\';
                  }
              ">'
           . '</small></small></h5>' . "\n"
           . '<p id="license" style="display:none;">'
           . 'This plugin is free software under the terms of the GNU General Public License v. 3 or '
           . 'later; see https://www.gnu.org/licenses/gpl.html for details.<br>'
           . '<b>Acknowledgements:</b><br>'
           . 'This plugin uses jQuery/Flexslider by '
           . '<a href="http://twitter.com/mbmufffin" target="_blank">Tyler Smith</a> '
           . 'distributed by <a href="http://www.woothemes.com/flexslider/" target="_blank">WooThemes</a><br>'
           . 'For Markdown to HTML the <a href="http://parsedown.org" target="_blank">Parsedown</a> '
           . 'parser by Emanuil Rusev is used.'
           . '<br><br></p>'. "\n\n" ;


        // Easy update: check if updating/activation process should start or not
        //=======================================================================
        if (!isset($plugin_cf['flexslider']['version'])
            || version_compare(FLEXSLIDER_CONFIG_VERSION,constant('FLEXSLIDER_VERSION'),'!=')) {

            $o .=  '<h1 style="border:2px red solid;background:yellow;text-align:center;">'
                .  'Plugin not activated'
                .  '<br>'
                .  '<form action="" method="POST">'
                .  '<input type="submit" style="font-weight:bold;padding:0 1em;letter-spacing:.05em;"
                    value="click here" name="activate">'
                .  '</form> '
                .  'to activate plugin'
                .  '</h1>';
        } else {


            // Javascript image browser
            $o .= flx_JsImgBrowser();






            // Main Admin Area with display of chosen images, text and options
            //===================================================================================
            $o .= '<p id="list_saved">'.$plugin_tx['flexslider']['list_saved'].'</p>';
            $o .= '<form method="POST"
                   
                   id="imagesform" action="' . $sn
                .  '?&'.$plugin.'&amp;admin=plugin_main&amp;action=save_images">';

            // Image Editor, active only if called from an image of the flexslider image list
            $o .= flx_ImgEdit();

            $o .=  "\n". '<input type="submit"
                    onClick="document.getElementById(\'scrollpos\').value = document.documentElement.scrollTop;
                    document.getElementById(\'scrollpos2\').value = document.getElementById(\'imagelist\').scrollTop;"
                    value="'.ucfirst($tx['action']['save']).'">';
            $o .=  '<input type="hidden" id="preview" name="preview">';
            $o .=  '<input type="hidden" name="chdir_images" id="chdir_images">';
            $o .=  '<input type="hidden" name="scroll1" id="scrollpos">';
            $o .=  '<input type="hidden" name="scroll2" id="scrollpos2">';

            // Fill the list with all images from the actual folder which are not yet in the list
            $o .=  '<button name="fill" value="1"
                    onClick="document.getElementById(\'scrollpos\').value = document.documentElement.scrollTop;
                    document.getElementById(\'scrollpos2\').value = document.getElementById(\'imagelist\').scrollTop;"
                    title="'.$plugin_tx['flexslider']['fill_from_folder_title'].'">'
               .  $plugin_tx['flexslider']['fill_from_folder']
               .   '</button>';

            $o .= '<select name="addmore"
                  OnChange="document.getElementById(\'scrollpos\').value = document.documentElement.scrollTop;
                  document.getElementById(\'scrollpos2\').value = document.getElementById(\'imagelist\').scrollTop;
                  this.form.submit();">'
               .  "\n".'<option value="" selected>' . $plugin_tx[$plugin]['add_items'] . '?</option>';
            for ($i=1;$i<=20;$i++) {
                $o .= "\n".'<option value="'.$i.'">' . sprintf($plugin_tx[$plugin]['add_n_items'],$i) . '</option>';
            }
            $o .= '</select> ';

            $o .= '('. count($imagearray['image1']). ' ' . $plugin_tx[$plugin]['items'] . ') ';

            // Options for display size
            $o .= '<span title="' . $plugin_tx[$plugin]['slider_size_title']
               . '" class="nowrap">' . $plugin_tx[$plugin]['slider_size'] . ' ';
            $i = $imagearray['display'];
            $o .= '<select name="display[0]">';
            foreach (array(
                'standard'=>'standard',
                '1:1'=>1,
                '4:3'=>1.3333,
                '3:2'=>1.5,
                '16:9'=>1.7777,
                '2:1'=>2,
                '3:1'=>3,
                '4:1'=>4,
                '5:1'=>5,
                '6:1'=>6,
                'flexible'=>'flexible'
                ) as $key=>$value) {
                $o .= "\n".'<option value="'.$value.'"';
                $o .= $i==$value? 'selected':'';
                $o .= ' >'.$key.'</option>';
            }
            $o .= '</select></span>';

            // Options for animation
            $o .= ' <span class="nowrap">' . $plugin_tx[$plugin]['animation_type'] . ' ';
            $i = $imagearray['type'];
            $o .= '<select name="type[0]">'
               .  "\n".'<option value="slide"';
            $o .= $i=='slide'? 'selected':'';
            $o .= ' >slide</option>'
               .  "\n".'<option value="reverse"';
            $o .= $i=='reverse'? 'selected':'';
            $o .= ' >reverse</option>'
               .  "\n".'<option value="vertical"';
            $o .= $i=='vertical'? 'selected':'';
            $o .= ' >vertical</option>'
               .  "\n".'<option value="fade"';
            $o .= $i=='fade'? 'selected':'';
            $o .= ' >fade</option>'
               .  "\n".'<option value="static"';
            $o .= $i=='static'? 'selected':'';
            $o .= ' >static</option>'
               . '</select></span>';

            // Caption style options
            $o .= ' <span class="nowrap">' . $plugin_tx[$plugin]['caption_type'] . ' ';
            $i = $imagearray['caption'];
            $o .= '<select name="caption[0]">'
               .  "\n".'<option value="overlay"';
            $o .= $i=='overlay'? 'selected':'';
            $o .= ' >overlay</option>'
               .  "\n".'<option value="overlay2"';
            $o .= $i=='overlay2'? 'selected':'';
            $o .= ' >overlay2</option>'
               .  "\n".'<option value="overlay3"';
            $o .= $i=='overlay3'? 'selected':'';
            $o .= ' >overlay3</option>'
               .  "\n".'<option value="normal"';
            $o .= $i=='normal'? 'selected':'';
            $o .= ' >normal</option>'
               .  "\n".'<option value="inverted"';
            $o .= $i=='inverted'? 'selected':'';
            $o .= ' >inverted</option>'
               . '</select></span>';

            // Start with random image?
            $checked = $imagearray['randomstart']? ' checked="checked"':'';
            $o .= ' <span class="nowrap">' . $plugin_tx[$plugin]['randomstart']
               .  '<input type="hidden" value="0" name="randomstart[0]">'
               .  '<input type="checkbox"'.$checked.' value="1" name="randomstart[0]"></span>';

            // Image sequence: random or not
            $checked = $imagearray['random']? ' checked="checked"':'';
            $o .= ' <span class="nowrap">' . $plugin_tx[$plugin]['random']
               .  '<input type="hidden" value="0" name="random[0]">'
               .  '<input type="checkbox"'.$checked.' value="1" name="random[0]"></span>';

            // Pause on mouse over?
            $checked = $imagearray['halt']? ' checked="checked"':'';
            $o .= ' <span class="nowrap">' . $plugin_tx[$plugin]['halt_on_mouseover']
               .  '<input type="hidden" value="0" name="halt[0]">'
               .  '<input type="checkbox"'.$checked.' value="1" name="halt[0]"></span>';

            // Show direction navigation?
            $checked = $imagearray['dnav']? ' checked="checked"':'';
            $o .= ' <span class="nowrap"title="'
               .  $plugin_tx[$plugin]['nav_direction_title'] . '">' . $plugin_tx[$plugin]['nav_direction']
               .  '<input type="hidden" value="0" name="dnav[0]">'
               .  '<input type="checkbox"'.$checked.' value="1" name="dnav[0]"></span>';

            // Show dots under the slider as control navigation?
            $checked = $imagearray['cnav']? ' checked="checked"':'';
            $o .= ' <span class="nowrap"title="'
               .  $plugin_tx[$plugin]['nav_control_title'] . '">' . $plugin_tx[$plugin]['nav_control']
               .  '<input type="hidden" value="0" name="cnav[0]">'
               .  '<input type="checkbox"'.$checked.' value="1" name="cnav[0]"></span>';

            // Slideshow speed + Animaion speed?
            if (!isset($imagearray['speedshow'])) $imagearray['speedshow'] = '';
            if (!isset($imagearray['speedanim'])) $imagearray['speedanim'] = '';
            $o .= $plugin_cf[$plugin]['speed_also_in_image_list']
               ?  ' <span class="nowrap" title="'
               .  sprintf($plugin_tx[$plugin]['speed_slideshow_title'],$plugin_cf['flexslider']['speed_slideshow']) . '">'
               .  $plugin_tx[$plugin]['speed_slideshow'] . ' '
               .  '<input type="text" size="4" style="width:5ex;" value="'
               .  $imagearray['speedshow']
               .  '" name="speedshow[0]"></span>'

               .  ' <span class="nowrap" title="'
               .  sprintf($plugin_tx[$plugin]['speed_animation_title'],$plugin_cf['flexslider']['speed_animation']) . '">'
               .  $plugin_tx[$plugin]['speed_animation'] . ' '
               .  '<input type="text" size="4" style="width:5ex;" value="'
               .  $imagearray['speedanim']
               .  '" name="speedanim[0]"></span>'

               :  '<input type="hidden" value="'.$imagearray['speedshow'].'" name="speedshow[0]">'
               .  '<input type="hidden" value="'.$imagearray['speedanim'].'" name="speedanim[0]">';

            // Show dots under the slider as control navigation?
            if (!isset($imagearray['pdata'])) $imagearray['pdata'] = '';
            $checked = $imagearray['pdata']? ' checked="checked"':'';
            $o .= $plugin_cf[$plugin]['pagedata-imagelist_enabled']
               ?  ' <span class="nowrap"title="'
               .  $plugin_tx[$plugin]['pdata_title'] . '">' . $plugin_tx[$plugin]['pdata_img_control']
               .  '<input type="hidden" value="0" name="pdata[0]">'
               .  '<input type="checkbox"'.$checked.' value="1" name="pdata[0]"></span>'
               :  '<input type="hidden" value="'.$imagearray['pdata'].'" name="pdata[0]">';


            // start table for entering image
            // =================================================================
            $height = $plugin_cf[$plugin]['admin_images_table_height']
                    ? ' style="max-height:'.$plugin_cf[$plugin]['admin_images_table_height'].';" '
                    : '';
            $o .= '<div id="imagelist"'.$height.'>'."\n".'<table id="imagelisttable">';

            foreach ($imagearray['image1'] as $key=>$value) {

              	$image1 =  $imagearray['image1'][$key];
                if ($image1) {
                    $text =  preg_replace('/\<br(\s*)?\/?\>/i', PHP_EOL, $imagearray['text'][$key]);
                } else {
                    $text = $imagearray['markdown'][$key];
                }
                $link   =  $imagearray['link'][$key];
                list($width1, $height1) = $image1 ? getimagesize($imgdir.$image1) : array('0','0');

                $o .= "\n"
                        //1st row 1st td
                   .  '<tr>'
                   .  '<td class="image_admin">'

                   .  '<input type="image" src="'.$pth['folder']['plugins']
                   .  $plugin.'/images/up.gif" style="width:15px;height:20px;" value="true" name="up['
                   .  $key . ']"  alt="up" title="'
                   .  $plugin_tx['flexslider']['move_up']
                   .  '" onClick="document.getElementById(\'scrollpos\').value = document.documentElement.scrollTop;
                      document.getElementById(\'scrollpos2\').value = document.getElementById(\'imagelist\').scrollTop;">'

                   .  '<br>' . "\n"
                   .  '<input type="image" src="'.$pth['folder']['plugins']
                   .  $plugin.'/images/delete.gif" style="width:15;height:15" value="true" name="delete['.$key.']" title="'
                   .  $plugin_tx['flexslider']['delete_item']
                   .  '" value=TRUE alt="Delete entry"
                      onClick="document.getElementById(\'scrollpos\').value = document.documentElement.scrollTop;
                      document.getElementById(\'scrollpos2\').value = document.getElementById(\'imagelist\').scrollTop;">'
                   .  "\n"

                   .  '<br>' . "\n"
                   .  '<input type="image" src="'.$pth['folder']['plugins']
                   .  $plugin.'/images/add.gif" style="width:15;height:15" value="true" name="add['.$key.']" title="'
                   .  $plugin_tx['flexslider']['add_item']
                   .  '" value=TRUE alt="Add entry"
                      onClick="document.getElementById(\'scrollpos\').value = document.documentElement.scrollTop;
                      document.getElementById(\'scrollpos2\').value = document.getElementById(\'imagelist\').scrollTop;">'

                   .  '<input type="image" src="'.$pth['folder']['plugins']
                   .  $plugin.'/images/down.gif" style="width:15px;height:20px;" value="true" name="down['.$key.']"  alt="up" title="'
                   .  $plugin_tx['flexslider']['move_down']
                   .  '" onClick="document.getElementById(\'scrollpos\').value = document.documentElement.scrollTop;
                      document.getElementById(\'scrollpos2\').value = document.getElementById(\'imagelist\').scrollTop;">'

                   .  "\n" . '</td><td>';

                $o .= '<table class="innertable">'
                   .  '<tr>'
                   .  '<td style="text-align:right">'.$plugin_tx[$plugin]['image_text'].':</td>'
                   .  "\n"
                   .  '<td>'
                   .  '<div class="expandingArea" ><pre><span></span>' . '<br></pre>'
                   .  '<textarea  name="text['.$key.']">'.$text.'</textarea></div>'
                   .  '</td>'
                   .  '</tr>'
                   .  "\n";

                $flex_marginline = $image1 ? ' style="display:none;"' :'' ;

                $o .= '<tr id="margin['.$key.']"'.$flex_marginline.'>'
                   .  "\n"
                   .  '<td style="text-align:right">'.$plugin_tx['flexslider']['text_only'].':</td>'
                   .  "\n"
                   .  '<td>margin-top '
                   .  '<input type="text" style="width:2em;"  value="'
                   .  $imagearray['margintop'][$key].'" name="margintop['.$key.']">'
                   .  '%, margin-right/left '
                   .  '<input type="text" style="width:2em;"  value="'
                   .  $imagearray['marginhorz'][$key].'" name="marginhorz['.$key.']">'
                   .  '%</td>'
                   .  '</tr>'
                   .  "\n";

               $o .=  '<tr>'
                   .  "\n"
                   .  '<td style="text-align:right">'.$plugin_tx[$plugin]['link'].':</td>'
                   .  "\n"
                   .  '<td>'
                   .  flx_LinkSelect()
                   .  '</td>'
                   .  '</tr>'
                   .  "\n"

                   .  '<tr>'
                   .  '<td class="imagelist">';
                $o .= $image1
                   ?  '<img src="'.$imgdir.$image1.'" id="dimage1['.$key.']" OnClick="addtotemp(\'image1['
                   .  $key.']\');document.getElementById(\'image1['.$key.']\').focus();"
                       OnDblclick="imgEdit(\''.$key.'\');" >'
                   :  '<img src="'.$noimage.'" id="dimage1['.$key.']" OnClick="addtotemp(\'image1['
                   .  $key.']\');document.getElementById(\'image1['.$key.']\').focus();"
                      OnDblclick="imgEdit(\''.$key.'\');">';

                $o .= '</td>'
                   .  "\n"
                   .  '<td>'
                   .  '<input type="text" readonly="readonly" class="imageinput" value="'
                   .  $image1.'" name="image1['.$key.']" id="image1['.$key
                   .  ']" OnDblclick="imgEdit(\''.$key.'\');" OnClick="addtotemp(\'image1['
                   .  $key.']\');document.getElementById(\'image1['.$key.']\').focus();">'
                   // delete
                   .  '<img src="'.$pth['folder']['plugins']
                   .  $plugin.'/images/delete.gif" style="width:15;height:15" alt="Delete entry" title="'
                   .  $plugin_tx['flexslider']['remove_from_list']
                   .  '" onClick="document.getElementById(\'image1['
                   .  $key.']\').value = \'\';document.getElementById(\'margin['
                   .  $key.']\').style.display = \'table-row\';document.getElementById(\'image1['
                   .  $key.']\').focus();document.getElementById(\'d\'+\'image1['
                   .  $key.']\').src = \''.$noimage.'\';addtotemp(\'image1['
                   .  $key.']\');document.getElementById(\'dataimage1['
                   .  $key.']\')
                   .  innerHTML=\' &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 0 kB &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; \';">'
                   .  "\n"
                   .  '<span id="dataimage1['.$key.']">';
                $o .=  $image1?  $width1.' x '.$height1.' px, '
                   .   flx_AspectRatio($width1,$height1)
                   .   round(filesize($imgdir.$image1) / 1024,1).' kB':'';
                $o .= '</span>'

                   //.  ' &nbsp; <button type="button" OnClick="imgEdit(\''.$key.'\');">'
                   //.   $plugin_tx['flexslider']['image_editing']
                   //.  '</button>'
                   .  '</td>'
                   .  '</tr>'
                   .  "\n";

                $o .= "\n</table>\n</td></tr>\n";
                $o .= "<tr>\n<td colspan='3' class='displayspacer'></td>\n</tr>\n";

            }

            $o .= "\n".'</table>'
               .  "\n".'</div>'
               .  "\n". '<input type="submit"
                  onClick="document.getElementById(\'scrollpos\').value = document.documentElement.scrollTop;
                  document.getElementById(\'scrollpos2\').value = document.getElementById(\'imagelist\').scrollTop;"
                  value="'.ucfirst($tx['action']['save']).'">';

            $o .= '<input type="submit"
                  OnClick="document.getElementById(\'preview\').value=true;" value="'
               .  ucfirst($tx['action']['save']) .' + ' .$plugin_tx[$plugin]['preview']
               .  '">';

            $o .=   "\n".'</form>'."\n";

        }

    } else if (isset($_POST['preview']) && $_POST['preview']) {

        flx_SaveImgList();
        $o .= '<button onClick="location = \'?flexslider&normal\';">'
           .  $plugin_tx['flexslider']['return'] . '</button>';
        $o .= '<br>' . flexslider($_SESSION['flx_activefile']);

    } else {
        // rest of plugin menu
        $o .= plugin_admin_common($action, $admin, $plugin);
    }

}

?>
