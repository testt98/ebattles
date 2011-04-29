<?php
/**
* Main.php
*
*/

global $PLUGINS_DIRECTORY;
$lan_file = e_PLUGIN."ebattles/languages/".e_LANGUAGE.".php";
require_once(file_exists($lan_file) ? $lan_file : e_PLUGIN."ebattles/languages/English.php");

require_once(e_PLUGIN."ebattles/include/constants.php");
require_once(e_PLUGIN."ebattles/include/time.php");
require_once(e_HANDLER."rate_class.php");

global $pref;
global $sql;
global $time;

$time = time();

switch ($pref['eb_tab_theme'])
{
    case 'ebattles':
    $tab_theme = 'css/tab.ebattles.css';
    break;
    case 'dark':
    $tab_theme = 'css/tab.dark.css';
    break;
    case 'winclassic':
    $tab_theme = 'css/tab.winclassic.css';
    break;
    case 'webfx':
    $tab_theme = 'css/tab.webfx.css';
    break;
    case 'luna':
    $tab_theme = 'css/luna/tab.css';
    break;
    default:
    $tab_theme = 'css/tab.css';
}

$eplug_css = array(
"js/calendar/calendar-blue.css",
"css/paginate.css",
"css/custom-theme/jquery-ui-1.8.11.custom.css",
"http://jqueryui.com/themes/base/ui.all.css",
"css/ebattles.css",
"css/brackets.css"
);

///-------------- Functions ----------------------
class DatabaseTable
{
	protected $tablename;
	protected $primary_key;
	protected $fields = array();

	function __construct($primaryID) {
		global $sql;
		$q = "SELECT *"
		." FROM $this->tablename"
		." WHERE ($this->primary_key = '$primaryID')";
		$result = $sql->db_Query($q);
	
		if ($row = mysql_fetch_assoc($result)) {
	         $this->fields = $row;
	    } // while
	}

	function getField($field) {
		return $this->fields[$field];
	}
}

function multi2dSortAsc(&$arr, $key, $sort)
{
    $sort_col = array();
    $sort_type = SORT_NUMERIC;
    foreach ($arr as $sub)
    {
        $string = $sub[$key];

        // remove html tags
        $string = preg_replace("/<[^>]*>/e","", $string);
        // remove thousand separator & decimal point
        $string = preg_replace("/[\,\.]/e","", $string);
        if (!is_numeric($string[0])) $sort_type = SORT_REGULAR;
        // split "/ " or "|" or "<br" or "[" or "%" or " ("
        $string = preg_split("/\/\s|\||(<br)|\[|%|\s\(/", $string);

        //echo "$sub[$key] --> $string[0]<br>";
        $sort_col[] = $string[0];
    }
    //echo "sort_type: $sort_type<br>";
    array_multisort($sort_col, $sort, $sort_type, $arr);
}

function getRanking($arr, $keys)
{
    $rows = count($arr);
    $columns = count($arr[0]);

    $out = array();
    for ($i = 0; $i < $columns; $i++) $out[] = $i;

    if(!empty($keys))
    {
        $i=0;
        foreach($keys as $key)
        {
            if($i>0){$sort.=',';}
            $sort_col[$i] = $arr[$key];
            $sort .= '$sort_col['.$i.'], SORT_ASC, SORT_NUMERIC';
            $i++;
        }
        $sort .= ', &$out';

        $sort='array_multisort('.$sort.');';
        eval($sort);
    }

    // $out is an array of indexes
    // The 1st value is the index of the player with rank last
    // The 2nd value is the index of the player with rank 2nd to last
    // ...
    // The last value is the index of the player with rank 1st
    // ...
    return $out;

}

/**
* Searches haystack for needle and
* returns an array of the key path if
* it is found in the (multidimensional)
* array, FALSE otherwise.
*
* @mixed array_searchRecursive ( mixed needle,
* array haystack [, bool strict[, array path]] )
*/
function array_searchRecursive( $needle, $haystack, $strict=false, $path=array() )
{
    if( !is_array($haystack) ) {
        return false;
    }

    foreach( $haystack as $key => $val ) {
        $pos = strpos($val,$needle);
        if( is_array($val) && $subPath = array_searchRecursive($needle, $val, $strict, $path) ) {
            $path = array_merge($path, array($key), $subPath);
            return $path;
        } elseif( (!$strict && $val == $needle) || ($strict && $val === $needle) || (!$strict && $pos !== false)) {
            $path[] = $key;
            return $path;
        }
    }
    return false;
}

function getImagePath($image, $dir)
{
    if (preg_match("/\//", $image))
    {
        // External link
        return $image;
    }
    else
    {
        // Internal link
        return e_PLUGIN."ebattles/images/$dir/$image";
    }
}

function imageResize($image, $target, $force_resize=FALSE) {
    // Resize image so it does not exceeds the max size.
    //fm (too slow): $image_dims = getimagesize($image);

    if ($image_dims != '')
    {
        $width  = $image_dims[0];
        $height = $image_dims[1];

        if((max($width,$height)>$target)||($force_resize==TRUE))
        {
            //takes the larger size of the width and height and applies the
            //formula accordingly...this is so this script will work
            //dynamically with any size image
            if ($width > $height) {
                $percentage = ($target / $width);
            } else {
                $percentage = ($target / $height);
            }

            //gets the new value and applies the percentage, then rounds the value
            $width = round($width * $percentage);
            $height = round($height * $percentage);

            //returns the new sizes in html image tag format...this is so you
            //can plug this function inside an image tag and just get the
            return 'width="'.$width.'" height="'.$height.'"';
        }
        else
        {
            return '';
        }
    }
    else
    {
        return 'width="'.$target.'"';
    }
}

function getImageResize($icon, $max_size, $enable_max_resize=TRUE, $force_resize=FALSE) {
    global $pref;

    if (($enable_max_resize == TRUE)||($force_resize==TRUE))
    {
        return 'src="'.$icon.'" '.imageResize($icon, $max_size, $force_resize);
    }
    else
    {
        return 'src="'.$icon.'"';
    }
}

function getGameIconResize($gicon) {
    global $pref;
    return getImageResize(getImagePath($gicon, 'games_icons'), $pref['eb_max_image_size'], $pref['eb_max_image_size_check']).' alt="'.$gicon.'"';
}

function getActivityIconResize($icon) {
    global $pref;
    return getImageResize($icon, $pref['eb_activity_max_image_size'], $pref['eb_activity_max_image_size_check']);
}

function getActivityGameIconResize($gicon) {
    global $pref;
    return getImageResize(getImagePath($gicon, 'games_icons'), $pref['eb_activity_max_image_size'], $pref['eb_activity_max_image_size_check']).' alt="'.$gicon.'"';
}

function getAvatarResize($icon) {
    global $pref;
    return getImageResize($icon, $pref['eb_max_avatar_size']).' alt="'.$icon.'"';
}

function getFactionIconResize($ficon) {
    global $pref;
    return getImageResize(getImagePath($ficon, 'games_factions'), $pref['eb_max_image_size'], $pref['eb_max_image_size_check']).' alt="'.$ficon.'"';
}
function getMapImageResize($mimage) {
    global $pref;
    return getImageResize(getImagePath($mimage, 'games_maps'), $pref['eb_max_map_image_size'], $pref['eb_max_map_image_size_check']).' alt="'.$mimage.'"';
}

function floatToSQL($number)
{
    return number_format($number, 5, ".", "");
}

// ************************************************
// Miscellaneous Helper Functions
// ************************************************

/**
* @return true if current version of e107 is v0.7, otherwise false
*/
function isV07() {
    return true;
}

// ************************************************
// Comment Helper Functions
// ************************************************

/**
* Get number of comments for an item.
* <p>This method returns the number of comments for the supplied plugin/item id.</p>
* @param   string   a unique ID for this plugin, maximum of 10 character
* @param   int      id of the item comments are allowed for
* @return  int      number of comments for the supplied parameters
*/
function getCommentTotal($pluginid, $id) {
    global $pref, $e107cache, $tp;
    $query = "where comment_item_id='$id' AND comment_type='$pluginid'";
    $mysql = new db();
    return $mysql->db_Count("comments", "(*)", $query);
}

/**
* Add comments to a plugins
* <p>This method returns the HTML for a comment form. In addition, it will post comments to the e107v7
* comments database and get any existing comments for the current item.</p>
* @param   string   a unique ID for this plugin, maximum of 10 character
* @param   int      id of the item comments are allowed for
* @return  string   HTML for existing comments for an item and the comments form to allow new comments to be posted
*/
function getComment($pluginid, $id) {
    global $pref, $e107cache, $tp;

    // Include the comment class. Normally, this file is included at a global level, so we need to make the variable
    // it decalares global so it is available inside the comment class
    require_once(e_HANDLER."comment_class.php");
    require(e_FILE."shortcode/batch/comment_shortcodes.php");
    $GLOBALS["comment_shortcodes"] = $comment_shortcodes;

    $pid = 0; // What is this w.r.t. comment table? Parent ID?

    // Define a comment object
    $cobj = new comment();

    // See if we need to post a comment to the database
    if (isset($_POST['commentsubmit'])) {
        $cobj->enter_comment($_POST['author_name'], $_POST['comment'], $pluginid, $id, $pid, $_POST['subject']);
        if ($pref['cachestatus']){
            $e107cache->clear("comment.$pluginid.{$sub_action}");
        }
    }

    // Specific e107 0.617 processing to render existing comments
    if (!isV07()) {
        $query = $pref['nested_comments'] ?
        "comment_item_id='$id' AND comment_type='$pluginid' AND comment_pid='0' ORDER BY comment_datestamp" :
        "comment_item_id='$id' AND comment_type='$pluginid' ORDER BY comment_datestamp";
        unset($text);
        $mysql = new db();
        if ($comment_total = $mysql->db_Select("comments", "*", $query)) {
            $width = 0;
            while ($row = $mysql->db_Fetch()) {
                // ** Need to sort out how to do nested comments here
                if ($pref['nested_comments']) {
                    $text .= $cobj->render_comment($row, $pluginid, "comment", $id, $width, $subject, true);
                } else {
                    $text .= $cobj->render_comment($row, $pluginid, "comment", $id, $width, $subject, true);
                }
            }
            if (ADMIN && getperms("B")) {
                $text .= "<div style='text-align:right'><a href='".e_ADMIN."modcomment.php?$pluginid.$id'>".LAN_314."</a></div>";
            }
        }
    }

    // Get comment form - e107 sends this to the output buffer so we must grab it and assign to our return string
    ob_start();
    if (isV07()) {
        // e107 0.7
        $cobj->compose_comment($pluginid, "comment", $id, $width, $subject, false);
    } else {
        // e107 0.617
        if (strlen($text) > 0) {
            $ns = new e107table();
            $ns->tablerender(LAN_5, $text);
        }
        $cobj->form_comment("comment", $pluginid, $id, $subject, $content_type);
    }
    $text = ob_get_contents();
    ob_end_clean();

    return $text;
}

/**
* Add ratings to a plugins
* <p>This method returns the HTML for a ratings form. In addition, it will post ratings to the e107v7
* ratings database and get any existing ratings for the current item.</p>
* @param   string   a unique ID for this plugin, maximum of 10 character
* @param   int      id of the item comments are allowed for
* @param   boolean  true to show the rating selection drop down if user not already rated this item
* @return  string   HTML for existing comments for an item and the comments form to allow new comments to be posted
*/
function getRating($pluginid, $id, $allowrating=true, $notext=false, $userid=false) {
    $rater = new rater();

    $text = "";
    $ratearray = $rater->getrating($pluginid, $id, $userid);
    if ($ratearray)
    {
        if ($ratearray[0] == 0)
        {
            if($ratearray[1] > 0)
            {
                for ($c=1; $c<=$ratearray[1]; $c++) {
                    $text .= "<img src='".e_IMAGE."rate/".IMODE."/star.png' alt='' />";
                }
                $text .= "&nbsp;".$ratearray[1];
            }
            else
            {
                $text .= EB_RATELAN_4;
            }
        }
        else
        {
            for ($c=1; $c<=$ratearray[1]; $c++) {
                $text .= "<img src='".e_IMAGE."rate/".IMODE."/star.png' alt='' />";
            }
            if ($ratearray[2]) {
                $text .= "<img src='".e_IMAGE."rate/".IMODE."/".$ratearray[2].".png'  alt='' />";
            }
            if ($ratearray[2] == "") {
                $ratearray[2] = 0;
            }
            $text .= "&nbsp;".$ratearray[1].".".$ratearray[2];
            if (!$notext) {
                $text .= " - ".$ratearray[0]."&nbsp;" . ($ratearray[0] == 1 ? EB_RATELAN_0 : EB_RATELAN_1);
            }
        }
    } else {
        $text .= EB_RATELAN_4;
    }

    if ($allowrating) {
        if (!$rater->checkrated($pluginid, $id) && USER) {
            $ratetext = $rater->rateselect("&nbsp;&nbsp;&nbsp;&nbsp;<b>".EB_RATELAN_2, $pluginid, $id)."</b>";
            $ratetext = str_replace("../../rate.php", e_PLUGIN."ebattles/rate.php", $ratetext);
            if ($userid)
            {
                $text = $ratetext;
            }
            else
            {
                $text .= $ratetext;
            }
        } else if (!USER) {
            $text .= "&nbsp;";
        } else {
            //$text .= "&nbsp;-&nbsp;".EB_RATELAN_3;
        }
    }

    return $text;
}


function displayRating($rate, $votes) {
    $text = "";
    $ratearray[0]=$votes;
    $ratearray[1]=floor($rate);
    $ratearray[2]=floor(($rate-$ratearray[1])*10);

    if ($ratearray[0]>0) {
        for ($c=1; $c<=$ratearray[1]; $c++) {
            $text .= "<img src='".e_IMAGE."rate/".IMODE."/star.png' alt='' />";
        }
        if ($ratearray[2]) {
            $text .= "<img src='".e_IMAGE."rate/".IMODE."/".$ratearray[2].".png'  alt='' />";
        }
        if ($ratearray[2] == "") {
            $ratearray[2] = 0;
        }
        $text .= "<div class='smalltext'>&nbsp;".$ratearray[1].".".$ratearray[2]." - ".$ratearray[0]."&nbsp;";
        $text .= ($ratearray[0] == 1 ? EB_RATELAN_0 : EB_RATELAN_1)."</div>";
    } else {
        $text .= "<div class='smalltext'>".EB_RATELAN_4."</div>";
    }
    return $text;
}

function purgeComments($table)
{
    global $sql, $tp;

    // Delete any related comments
    require_once(e_HANDLER."comment_class.php");
    $_com = new comment;

    $q = "SELECT DISTINCT ".MPREFIX."comments.comment_item_id"
    ." FROM ".MPREFIX."comments "
    ." WHERE (comment_type='$table')";
    $text .= $q.'<br>';

    $result = $sql->db_Query($q);
    $num_rows = mysql_numrows($result);
    for($i=0; $i<$num_rows; $i++)
    {

        $id = mysql_result($result,$i, "comment_item_id");
        $text .= "comment id: $id<br>";
        $num = $_com->delete_comments($table, $id);
    }
}

function purgeRatings($table)
{
    global $sql, $tp;

    // Delete any related ratings
    require_once(e_HANDLER."rate_class.php");
    $_rate = new rater;

    $q = "SELECT DISTINCT ".MPREFIX."rate.rate_itemid"
    ." FROM ".MPREFIX."rate "
    ." WHERE (rate_table='$table')";
    $text .= $q.'<br>';

    $result = $sql->db_Query($q);
    $num_rows = mysql_numrows($result);
    for($i=0; $i<$num_rows; $i++)
    {

        $id = mysql_result($result,$i, "rate_itemid");
        $text .= "rate id: $id<br>";
        $num = $_rate->delete_ratings($table, $id);
    }
}

/**
* Send a notification to one or more users.
* <p>Current implementation just sends a Private Message, so the PM plugin must be enabled.</p>
* @param   sendto      an array of userid(s) or a sinle userclass (not array) to send notifications to
* @param   subject     the subject of the message
* @param   message     the message itself
* @param   fromid      id of user sending PM (they will get a copy in theor outbox), defaults to 0 (no user)
* @return  TBC
* @TODO add option to PM multiple users
* @TODO add option to PM a userclass
* @TODO proper return values
* @TODO localization
*/
function sendNotification($sendto, $subject, $message, $fromid=0) {
    global $sql, $pm_prefs, $pm, $pref, $sysprefs, $tp;

    // Include Private Message class if not already defined
    if (!class_exists("private_message")) {
        if (file_exists(e_PLUGIN."pm/pm_class.php")) {
            require_once(e_PLUGIN."pm/pm_class.php");
            include_lan(e_PLUGIN.'pm/languages/'.e_LANGUAGE.'.php');
        } else {
            return;
        }
    }

    /*
    // Check user is allowed to send PMs
    $pm_prefs = $sysprefs->getArray("pm_prefs");
    if (!check_class($pm_prefs['pm_class'])) {
        return NOT_AUTHORIZED;
    }
    */

    /*
    // Annotate message with senders details
    if (USERID !== false) {
    $message = "Notification from ".USERNAME."\n\n".$message;
    } else {
    $message = "Notification from Guest\n\n".$message;
    }
    */

    $pm = new private_message();
    // Array of userids to PM
    if (!$sql->db_Select("user", "user_id, user_name, user_class, user_email", "user_id=$sendto")) {
        return USER_NOT_FOUND;
    }
    $touser = $sql->db_Fetch();

    $vars = array(
    "pm_subject"      => $subject,
    "pm_message"      => $message,
    "from_id"         => $fromid,
    "to_info"         => array(
    "user_id"      => $touser["user_id"],
    "user_name"    => $touser["user_name"],
    "user_email"   => $touser["user_email"]
    )
    );
    return $pm->add($vars);
}

function disclaimer()
{
    global $pref;
    global $tp;

    return '<span class="smalltext" style="float:right">'.$tp->toHTML($pref['eb_disclaimer']).'</span><span style="clear:both"><br /></span>';
}

function versionsCompare($version1, $version2)
{
    $version1 = explode('.', $version1, 3);
    $version2 = explode('.', $version2, 3);
    if (!$version2[2]) $version2[2] = 0;

    if ($version1[0] < $version2[0])
    return -1;
    else if ($version1[0] > $version2[0])
    return 1;
    else
    {
        if ($version1[1] < $version2[1])
        return -1;
        else if ($version1[1] > $version2[1])
        return 1;
        else
        {
            if ($version1[2] < $version2[2])
            return -1;
            else if ($version2[2] > $version2[2])
            return 1;
            else
            return 0;
        }
    }
}

function ebImageTextButton($name, $image, $text, $class='', $confirm='', $title='', $other='')
{
	$image_str   = ($image!='') ? '<img src="'.e_PLUGIN.'ebattles/images/'.$image.'" alt="'.$text.'" style="vertical-align:middle"/>' : '';
	$confirm_str = ($confirm!='') ? 'onclick="return confirm(\''.$confirm.'\');"' : '';
	$class_str   = ($class!='') ? 'class="'.$class.' ui-button ui-state-default ui-corner-all"' : 'class="ui-button ui-state-default ui-corner-all"';
	$title_str   = ($title!='') ? 'title="'.$title.'"' : '';
	$text_str    = ($text != '') ? '&nbsp;'.$text : '';
	return '<div class="buttons"><button '.$class_str.' type="submit" name="'.$name.'" '.$title_str.' '.$confirm_str.' '.$other.'>'.$image_str.$text_str.'</button></div>
            <div style="clear:both"></div>';
}

// Append associative array elements
function array_push_associative(&$arr) {
	$args = func_get_args();
	foreach ($args as $arg) {
		if (is_array($arg)) {
			foreach ($arg as $key => $value) {
				$arr[$key] = $value;
			}
		}else{
			$arr[$arg] = "";
		}
	}
}

?>