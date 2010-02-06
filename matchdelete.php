<?php
/**
* matchdelete.php
*
* This page is for users to edit their account information
* such as their password, email address, etc. Their
* usernames can not be edited. When changing their
* password, they must first confirm their current password.
*
*/
require_once("../../class2.php");
require_once(e_PLUGIN."ebattles/include/main.php");
require_once(e_PLUGIN."ebattles/include/match.php");

/*******************************************************************
********************************************************************/
require_once(HEADERF);

global $sql;

$text = '';

/* Event Name */
$event_id = $_GET['eventid'];

if (!$event_id)
{
    header("Location: ./events.php");
    exit();
}
else
{
    if (!isset($_POST['deletematch']))
    {
        $text .= '<br />'.EB_MATCHDEL_L2.'<br />';
    }
    else
    {
        $match_id = $_POST['matchid'];
        deleteMatchScores($match_id);

        $q = "UPDATE ".TBL_EVENTS." SET IsChanged = 1 WHERE (EventID = '$event_id')";
        $result = $sql->db_Query($q);

        $text .= '<br />'.EB_MATCHDEL_L3.'<br />';
    }
    $text .= '<br />'.EB_MATCHDEL_L4.' [<a href="'.e_PLUGIN.'ebattles/eventinfo.php?eventid='.$event_id.'">'.EB_MATCHDEL_L5.'</a>]<br />';
}
$ns->tablerender(EB_MATCHDEL_L1, $text);
require_once(FOOTERF);
exit;
?>
