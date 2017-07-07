<?php

/**
 * Front-end of Flexslider_XH.
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @author    svasti <svasti@svasti.de>
 * @copyright 2014 svasti <http://svasti.de>
 */

/*
 * Prevent direct access.
 */
if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

function flexslider($imagefile='')
{
    global $plugin_cf,$plugin_tx,$hjs,$pth,$sl,$pd_current,$flexslider_used_in_template_or_newsbox;
    $o = $t = '';
    static $fullscreen = 0;
    static $total=1;

    $imagefile = $imagefile
        ? strpos($imagefile,'.php')
        ? $imagefile
        : $imagefile.'.php'
        : 'flexslider_'.$sl.'.php';
    $filename = substr($imagefile,0,-4);

    $imagefile_pth = $plugin_cf['flexslider']['path_flexslider_data_files']
                   ? $pth['folder']['base'] . $plugin_cf['flexslider']['path_flexslider_data_files']
                   : $pth['folder']['userfiles'] . 'plugins/flexslider/';

    $i = json_decode(file_get_contents($imagefile_pth.$imagefile),true);

    //if imagelist come via pagedata, read the images
    if (!isset($i['pdata'])) $i['pdata'] = 0;
    if ($plugin_cf['flexslider']['pagedata-imagelist_enabled'] && $i['pdata']) {
        if (isset($pd_current['flx_data']) && $pd_current['flx_data']) {
            $imagefile = $pd_current['flx_data'];
            $i = json_decode(file_get_contents($imagefile_pth.$imagefile),true);
        }
    }
    $totalslides = count($i['image1']);

    $random      = isset($i['random']) && $i['random'] ? 'randomize: true,'."\n" : '';
    $randomstart = isset($i['randomstart']) && $i['randomstart'] ? 'startAt:'. mt_rand(0,$totalslides - 1) . ",\n" : '';
    $halt        = isset($i['halt']) && $i['halt'] && isset($i['type']) && $i['type']!='static'
                 ? 'pauseOnHover: true,'."\n"
                 : '';
    $cnav        = isset($i['cnav']) && $i['cnav'] ?  '' : 'controlNav:false,'."\n";
    $dnav        = isset($i['dnav']) && $i['dnav'] ?  '' : 'directionNav:false,'."\n";
    $height      = isset($i['display']) && $i['display'] == 'flexible' ?  'smoothHeight: true,'."\n" : '';

    $extranav = $extranav1 = $extranav2 = '';
    $resume = $halt
            ? 'start: function(slider) {
slider.mouseout(function() {
slider.resume();
});
}' . "\n"
            : 'after: function(slider){
if (!slider.playing) {
                slider.play();
            }
}' . "\n";

    switch ($i['type']) {
        case 'vertical':
            $type = 'animation:"slide",'."\n".'direction: "vertical",'."\n";
        	break;
        case 'reverse':
            $type = 'animation:"slide",'."\n".'reverse:true,'."\n";
        	break;
        case 'static':
            $resume = '';
            $type = 'slideshow:false,'."\n".'animationSpeed:0,'."\n";
            if (!$dnav) {
                $extranav = '$(document).on(\'click\',\'.next'.$filename.'\',function(){
    $(\'.flexslider'.$filename.' .flex-direction-nav .flex-next:first\').trigger(\'click\');
    return false;
});
$(document).on(\'click\',\'.prev'.$filename.'\',function(){
    $(\'.flexslider'.$filename.' .flex-direction-nav .flex-prev:first\').trigger(\'click\');
    return false;
});';
                $extranav1 = 'controlsContainer: ".extradirnav'.$filename.'",
after: function(slider) {
    $(".current-slide'.$filename.'").text(slider.currentSlide + 1);
}';

                $extranav2 = '<div class="extradirnav"><div class="extradirnav'.$filename.'">'
                           . '<a href="" id="prev'.$filename.'" class="prev'.$filename.'">&#xf001; <span class="current-slide'.$filename.'">1</span></a>/'
                           . '<a href="" id="next'.$filename.'" class="next'.$filename.'">'.$totalslides.' &#xf002;</a>';
                if (is_numeric($i['display'])) {
                    $fullscreen++;
                    $extranav2 .= ' &nbsp; '
                               .  '<input type="image" src="'
                               .  $pth['folder']['plugins']
                               .  'flexslider/images/full.gif" id="flexbutton'.$filename.'" alt="fullsize" class="flexfullsizebutton"'
                               .  ' title="' .  $plugin_tx['flexslider']['changeto_fullsize'] . '"'
                               .  ' onClick="full(\''.$filename.'\' ,\''.$i['display'].'\');">';
                }
                $extranav2 .= '</div></div>';

                $hjs .= isset($i['dnav']) && $i['dnav']
                    ? ' '
                    : '<style type="text/css">.flex-direction-nav {opacity:0;}.</style>';
            }
        	break;
        default:
            $type = 'animation:"'.$i['type'].'",'."\n";
        	break;
    }

    include_once($pth['folder']['plugins'].'jquery/jquery.inc.php');
    include_jQuery();
	$hjs .= sprintf(
		'<link rel="stylesheet" href="%s" type="text/css">',
		"{$pth['folder']['plugins']}flexslider/css/flexslider.css"
	);
    include_jQueryPlugin('flexslider-min', $pth['folder']['plugins'].'flexslider/js/jquery.flexslider-min.js');

    $speed_slideshow = isset($i['speedshow']) && $i['speedshow'] !== ''
        ? 'slideshowSpeed: '.$i['speedshow'].",\n"
        : ($plugin_cf['flexslider']['speed_slideshow']
        ? 'slideshowSpeed: '.$plugin_cf['flexslider']['speed_slideshow'].",\n"
        : '');
    $speed_animation = isset($i['speedanim']) && $i['speedanim'] !== ''
        ? 'animationSpeed: '.$i['speedanim'].",\n"
        : ($plugin_cf['flexslider']['speed_animation']
        ? 'animationSpeed: '.$plugin_cf['flexslider']['speed_animation'].",\n"
        : '');

        $x = "\n" . '<script type="text/javascript">
$(document).ready(function() {
$(\'.flexslider'.$filename.'\').flexslider({
initDelay: 0,' . "\n"
          . $randomstart
          . $speed_slideshow
          . $speed_animation
          . $halt
          . $random
          . $cnav
          . $dnav
          . $type
          . $height
          . 'prevText: "",' . "\n"
          . 'nextText: "",' . "\n"
          . $extranav1
          . $resume
        . '});' . "\n"
        . $extranav
    .'});';

if ($fullscreen == 1) $x .=  "\n" .
'function full(slider,displayratio)
    {
        if (document.getElementById(\'flexfull\'+ slider + \'\').className == \'flexfullsize\')
        {
            goback(slider);
        } else {

            document.getElementById(\'flexfull\'+ slider + \'\').className = \'flexfullsize\';

            var w = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
            var h = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
            h = h - 22;
            var newh = Math.round(w / displayratio);
            var hfactor = Math.round(100 * h / newh) / 100;
            var factor = newh > h? hfactor * 100 : 100;
            cssString =\'width:\'+factor+\'%;margin:auto;position:static;\';
            document.getElementById(\'flexfullin\'+ slider).style.cssText = cssString;
            document.getElementById(\'flexbutton\'+ slider).src = \''
                .$pth['folder']['plugins'].'flexslider/images/small.gif\';
            document.getElementById(\'flexbutton\'+ slider).title = \''
                .$plugin_tx['flexslider']['changeto_smallsize'].'\';
            document.getElementById(\'flexbutton\'+ slider).style.zIndex = 10000;

            esc(slider);
            keycontrol(slider);
        }
    }
    function esc(slider)
    {
        document.onkeydown = function(esc) {
            esc = esc || window.event;
            if (esc.keyCode == 27) goback(slider);
         }
    }
    function goback(slider)
    {
        document.getElementById(\'flexfull\'+ slider).className = \'\';
        document.getElementById(\'flexfullin\'+ slider).style.cssText = \'\';
        document.getElementById(\'flexbutton\'+ slider).src = \''
            .$pth['folder']['plugins'].'flexslider/images/full.gif\';
        document.getElementById(\'flexbutton\'+ slider).title = \''
            .$plugin_tx['flexslider']['changeto_fullsize'].'\';
        document.getElementById(\'flexbutton\'+ slider).style.zIndex = 0;
        keycontrol(slider,true)
    }';

    if ($total == 2) {
        $x .=  "\n" .
'    function keycontrol(slider,off)
    {
        var fired = false;
            document.onkeyup = function() {
                fired = false;
            }
        document.onkeydown = function(evt) {
            evt = evt || window.event;
            if (evt.keyCode == 27) goback(slider);
            else if (evt.keyCode == 39 && !off) {
                if (!fired) {
                    document.getElementById(\'next\' + slider).click();
                    fired = true;
               }
            }
            else if (evt.keyCode == 37 && !off) {
                if (!fired) {
                    document.getElementById(\'prev\' + slider).click();
                    fired = true;
                }
            }
        }
    }';
}
    $total++;

    $x .= '</script>' . "\n\n";


    if (isset($flexslider_used_in_template_or_newsbox)) $o .= $x; else $hjs .= $x;

    foreach ($i['image1'] as $k=>$value) {

    	$t .= $halt? "\n" . '<li style="cursor:no-drop;">' : "\n<li>";
        if (isset($i['link'][$k]) && $i['link'][$k]) {
            $t .= strpos($i['link'][$k],'h')===0
                ? '<a href="'.$i['link'][$k].'" target="_blank">'
                : '<a href="'.$i['link'][$k].'">';
        } else $t .= '<div class="next'.$filename.'" style="user-select:none;-moz-user-select: none;-webkit-user-select: none;-ms-user-select: none;-khtml-user-select: none;" >';
      	if (isset($i['image1'][$k]) && $i['image1'][$k]) {
            $width = isset($i['width'][$k])? 'style="width:'.$i['width'][$k].'%;margin:0 auto;"' : '';
            $t .= '<div style="width:100%;">';
            /*$t .= '<div style="position:absolute;margin-left:50%; border: 1px solid blue;">
                    <div style="padding:10%">
                  This is a preparation for Markdown formattet text on the right side of an image.
                  Could be implemented when needed. The size of the image would have to be recalculated
                  according to the desired size of the text.</div></div>';
            */
            $t .= '<img '. $width .' src="' . $pth['folder']['images'].$i['image1'][$k] . '" alt="'
               .  $i['image1'][$k] . '">';
            $t .= '</div>';
            if (isset($i['text'][$k]) && $i['text'][$k]) $t .= '<p class="flex-caption flex-'.$i['caption'].'">'
                                                           .  $i['text'][$k] . '</p>';
        } else {
            if (isset($i['text'][$k]) && $i['text'][$k]) {
                $t .= '<div class="flex-text" style="margin-top:'
                   . $i['margintop'][$k]
                   . '%; margin-right:'
                   . $i['marginhorz'][$k]
                   . '%; margin-left:'
                   . $i['marginhorz'][$k]
                   . '%;">' . $i['text'][$k] . '</div>';
            }
        }
        if (isset($i['link'][$k]) && $i['link'][$k]) $t .='</a>'; else $t .='</div>';
        $t .= '</li>';
    }

    // now the noscript part
    //=======================
    if (isset($i['image1'][0]) && $i['image1'][0]) {
        $width = isset($i['width'][0]) && $i['type']!='static'
            ? ' style="width:'.$i['width'][0].'%;margin:0 0 0 '.((100-$i['width'][0])/2).'%;" '
            : ' style="max-width:100%;"';

        $noscript = '<noscript>'
            . '<img '
            . $width
            . ' src="'
            . $pth['folder']['images'] . $i['image1'][0]
            . '" alt="' . $i['image1'][0]
            . '">'
            . '</noscript>';
    } else {
        $noscript = '<noscript>'
            . '<ul class="slides"><li><div class="flex-text" style="margin:'
            . $i['margintop'][0]
            . '% '
            . $i['marginhorz'][0]
            . '%;">'
            . $i['text'][0]
            . '</div></li></ul>'
            . '</noscript>';
    }


    $o .= "\n\n\n<!-- F L E X S L I D E R   S T A R T -->\n\n"
       .  '<div id="flexfull'.$filename.'">'
       .  '<div id="flexfullin'.$filename.'">'

       .  '<div class="flexslider">'
       .  '<div class="flexslider'.$filename.'">'
       .  $extranav2
       .  '<ul class="slides">'
       .  $t
       .  '</ul>'
       .  $noscript
       .  '</div>'
       .  '</div>'
       .  '</div>'
       .  '</div>'
       .  "\n<!-- F L E X S L I D E R   E N D -->\n\n";


    return $o;
}

/**
 * function only for use of the plugin in a template, has to be called in the <head> area
 *
 */
function flexslider_init()
{
    global $hjs,$pth,$plugin_cf, $flexslider_used_in_template_or_newsbox;

    include_once($pth['folder']['plugins'].'jquery/jquery.inc.php');
    include_jQuery();
	$hjs .= sprintf(
		'<link rel="stylesheet" href="%s" type="text/css">',
		"{$pth['folder']['plugins']}flexslider/css/flexslider.css"
	);
    include_jQueryPlugin('flexslider-min', $pth['folder']['plugins'].'flexslider/js/jquery.flexslider-min.js');

    $flexslider_used_in_template_or_newsbox = true;
}
?>