<?php
/**
* eb_activity_menu.php
*
*/

if (!defined('e107_INIT')) { exit; }

require_once(e_PLUGIN."ebattles/include/main.php");
require_once(e_PLUGIN."ebattles/include/clan.php");
require_once(e_PLUGIN."ebattles/include/match.php");

$ebattles_title = $pref['eb_activity_menuheading'];
$text = displayRecentActivity();

$ns->tablerender($ebattles_title,$text);

/***************************************************************************************
Functions
***************************************************************************************/
/**
* displayLatestGames - Displays Latest Games
*/
function displayRecentActivity(){
    global $sql;
    global $time;
    global $pref;

    $events = array();
    $nbr_events = 0;

    // Add recent games
    $rowsPerPage = $pref['eb_activity_number_of_items'];
    /* Stats/Results */
    $q = "SELECT DISTINCT ".TBL_MATCHS.".*"
    ." FROM ".TBL_MATCHS.", "
    .TBL_SCORES
    ." WHERE (".TBL_MATCHS.".Status = 'active')"
    ." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
    ." ORDER BY ".TBL_MATCHS.".TimeReported DESC"
    ." LIMIT 0, $rowsPerPage";

    $result = $sql->db_Query($q);
    $num_rows = mysql_numrows($result);
    if ($num_rows>0)
    {
        /* Display table contents */
        for($i=0; $i<$num_rows; $i++)
        {
            $mID  = mysql_result($result,$i, TBL_MATCHS.".MatchID");
            $events[$nbr_events][0] = $mTime;
            $events[$nbr_events][1] = displayMatchInfo($mID);
            $nbr_events ++;
        }
    }

    // Add Awards events
    $q = "SELECT ".TBL_AWARDS.".*, "
    .TBL_PLAYERS.".*, "
    .TBL_USERS.".*, "
    .TBL_EVENTS.".*, "
    .TBL_GAMES.".*"
    ." FROM ".TBL_AWARDS.", "
    .TBL_PLAYERS.", "
    .TBL_USERS.", "
    .TBL_EVENTS.", "
    .TBL_GAMES
    ." WHERE (".TBL_AWARDS.".Player = ".TBL_PLAYERS.".PlayerID)"
    ." AND (".TBL_PLAYERS.".User = ".TBL_USERS.".user_id)"
    ." AND (".TBL_PLAYERS.".Event = ".TBL_EVENTS.".EventID)"
    ." AND (".TBL_EVENTS.".Game = ".TBL_GAMES.".GameID)"
    ." ORDER BY ".TBL_AWARDS.".timestamp DESC"
    ." LIMIT 0, $rowsPerPage";

    $result = $sql->db_Query($q);
    $num_rows = mysql_numrows($result);

    if ($num_rows>0)
    {
        /* Display table contents */
        for($i=0; $i<$num_rows; $i++)
        {
            $aID  = mysql_result($result,$i, TBL_AWARDS.".AwardID");
            $aUser  = mysql_result($result,$i, TBL_USERS.".user_id");
            $aUserNickName  = mysql_result($result,$i, TBL_USERS.".user_name");
            $aEventID  = mysql_result($result,$i, TBL_EVENTS.".EventID");
            $aEventName  = mysql_result($result,$i, TBL_EVENTS.".Name");
            $aEventgame = mysql_result($result,$i , TBL_GAMES.".Name");
            $aEventgameicon = mysql_result($result,$i , TBL_GAMES.".Icon");
            $aType  = mysql_result($result,$i, TBL_AWARDS.".Type");
            $aTime  = mysql_result($result,$i, TBL_AWARDS.".timestamp");
            $aTime_local = $aTime + TIMEOFFSET;
            $date = date("d M Y, h:i A",$aTime_local);

            switch ($aType) {
                case 'PlayerTookFirstPlace':
                $award = EB_AWARD_L2;
                $icon = '<img '.getActivityIconResize(e_PLUGIN."ebattles/images/awards/award_star_gold_3.png").' alt="'.EB_AWARD_L3.'" title="'.EB_AWARD_L3.'"/> ';
                break;
                case 'PlayerInTopTen':
                $award = EB_AWARD_L4;
                $icon = '<img '.getActivityIconResize(e_PLUGIN."ebattles/images/awards/award_star_bronze_3.png").' alt="'.EB_AWARD_L5.'" title="'.EB_AWARD_L5.'"/> ';
                break;
                case 'PlayerStreak5':
                $award = EB_AWARD_L6;
                $icon = '<img '.getActivityIconResize(e_PLUGIN."ebattles/images/awards/medal_bronze_3.png").' alt="'.EB_AWARD_L7.'" title="'.EB_AWARD_L7.'"/> ';
                break;
                case 'PlayerStreak10':
                $award = EB_AWARD_L8;
                $icon = '<img '.getActivityIconResize(e_PLUGIN."ebattles/images/awards/medal_silver_3.png").' alt="'.EB_AWARD_L9.'" title="'.EB_AWARD_L9.'"/> ';
                break;
                case 'PlayerStreak25':
                $award = EB_AWARD_L10;
                $icon = '<img '.getActivityIconResize(e_PLUGIN."ebattles/images/awards/medal_gold_3.png").' alt="'.EB_AWARD_L11.'" title="'.EB_AWARD_L11.'"/> ';
                break;
            }

            $award_string = '<tr><td style="vertical-align:top">'.$icon.'</td>';
            $award_string .= '<td><a href="'.e_PLUGIN.'ebattles/userinfo.php?user='.$aUser.'">'.$aUserNickName.'</a>';
            $award_string .= '&nbsp;'.$award;
            $award_string .= '&nbsp;'.EB_MATCH_L12.'&nbsp;'.$aEventgame.' (<a href="'.e_PLUGIN.'ebattles/eventinfo.php?eventid='.$aEventID.'">'.$aEventName.'</a>)';

            $award_string .= ' <div class="smalltext">';
            if (($time-$aTime) < INT_MINUTE )
            {
                $award_string .= EB_MATCH_L7;
            }
            else if (($time-$aTime) < INT_DAY )
            {
                $award_string .= get_formatted_timediff($aTime, $time).'&nbsp;'.EB_MATCH_L8;
            }
            else
            {
                $award_string .= $date;
            }
            $award_string .= '</div></td></tr>';

            $events[$nbr_events][0] = $aTime;
            $events[$nbr_events][1] = $award_string;
            $nbr_events ++;
        }
    }

    $text = '<table style="margin-left: 0px; margin-right: auto;">';
    multi2dSortAsc($events, 0, SORT_DESC);
    for ($index = 0; $index<min($nbr_events, $rowsPerPage); $index++)
    {
        $text .= $events[$index][1];
    }
    $text .= '</table>';

    return $text;
}

?>
