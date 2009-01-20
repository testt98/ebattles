<?php
/**
* matchreport.php
*
* This page is for users to edit their account information
* such as their password, email address, etc. Their
* usernames can not be edited. When changing their
* password, they must first confirm their current password.
*
*/
require_once("../../class2.php");
include_once(e_PLUGIN."ebattles/include/main.php");
require_once e_PLUGIN.'ebattles/include/ELO.php';
/*******************************************************************
********************************************************************/
require_once(HEADERF);

$text = '
<script language="JavaScript" type="text/javascript" src="./js/tool-man/core.js"></script>
<script language="JavaScript" type="text/javascript" src="./js/tool-man/events.js"></script>
<script language="JavaScript" type="text/javascript" src="./js/tool-man/css.js"></script>
<script language="JavaScript" type="text/javascript" src="./js/tool-man/coordinates.js"></script>
<script language="JavaScript" type="text/javascript" src="./js/tool-man/drag.js"></script>
<script language="JavaScript" type="text/javascript" src="./js/tool-man/dragsort.js"></script>
<script language="JavaScript" type="text/javascript" src="./js/tool-man/cookies.js"></script>
<script language="JavaScript" type="text/javascript">
';
$text .= "
<!--
var dragsort = ToolMan.dragsort()
var junkdrawer = ToolMan.junkdrawer()
window.onload = function() {
junkdrawer.restoreListOrder('matchresultlist')
dragsort.makeListSortable(document.getElementById('matchresultlist'),
verticalOnly, saveOrder)
}

function verticalOnly(item) {
item.toolManDragGroup.verticalOnly()
}

function speak(id, what) {
var element = document.getElementById(id);
element.innerHTML = 'Clicked ' + what;
}

function saveOrder(item) {
var group = item.toolManDragGroup
var list = group.element.parentNode
var id = list.getAttribute('id')
if (id == null) return
group.register('dragend', function() {
ToolMan.cookies().set('list-' + id,
junkdrawer.serializeList(list), 365)
})
}

//-->
";
$text .= '
</script>
<script language="javascript">
';
$text .= "
<!--
function get_ranks(nbr_ranks)
{
for(i=1;i<=nbr_ranks;i++)
{
var rank = document.getElementsByName('rank'+i);
rank[0].value = junkdrawer.inspectItem('matchresultlist', (i-1))
//    alert('rank'+i);
}
}
//-->
";
$text .= '
</script>
';

/* Event Name */
$event_id = $_GET['eventid'];

$q = "SELECT ".TBL_EVENTS.".*"
." FROM ".TBL_EVENTS
." WHERE (".TBL_EVENTS.".eventid = '$event_id')";

$result = $sql->db_Query($q);
$ename = mysql_result($result,0 , TBL_EVENTS.".Name");
$etype = mysql_result($result,0 , TBL_EVENTS.".Type");
$eELO_K = mysql_result($result,0 , TBL_EVENTS.".ELO_K");
$eELO_M = mysql_result($result,0 , TBL_EVENTS.".ELO_M");

$q = "SELECT ".TBL_PLAYERS.".*, "
.TBL_USERS.".*"
." FROM ".TBL_PLAYERS.", "
.TBL_USERS
." WHERE (".TBL_PLAYERS.".Event = '$event_id')"
." AND (".TBL_USERS.".user_id = ".TBL_PLAYERS.".User)"
." ORDER BY ".TBL_USERS.".user_name";

$result = $sql->db_Query($q);
$num_rows = mysql_numrows($result);

$players_id[0] = '-- select --';
$players_uid[0] = '-- select --';
$players_name[0] = '-- select --';
for($i=0; $i<$num_rows; $i++){
    $pid  = mysql_result($result,$i, TBL_PLAYERS.".PlayerID");
    $puid  = mysql_result($result,$i, TBL_USERS.".user_id");
    $prank  = mysql_result($result,$i, TBL_PLAYERS.".Rank");
    $pname  = mysql_result($result,$i, TBL_USERS.".user_name");
    $pteam  = mysql_result($result,$i, TBL_PLAYERS.".Team");

    $pclan = '';
    $pclantag = '';
    if ($etype == "Team Ladder")
    {
        $q_2 = "SELECT ".TBL_CLANS.".*, "
        .TBL_DIVISIONS.".*, "
        .TBL_TEAMS.".* "
        ." FROM ".TBL_CLANS.", "
        .TBL_DIVISIONS.", "
        .TBL_TEAMS
        ." WHERE (".TBL_TEAMS.".TeamID = '$pteam')"
        ." AND (".TBL_DIVISIONS.".DivisionID = ".TBL_TEAMS.".Division)"
        ." AND (".TBL_CLANS.".ClanID = ".TBL_DIVISIONS.".Clan)";
        $result_2 = $sql->db_Query($q_2);
        $num_rows_2 = mysql_numrows($result_2);
        if ($num_rows_2 == 1)
        {
            $pclan  = mysql_result($result_2,0, TBL_CLANS.".Name");
            $pclantag  = mysql_result($result_2,0, TBL_CLANS.".Tag") ."_";
        }
    }
    $players_id[$i+1] = $pid;
    $players_uid[$i+1] = $puid;
    $players_name[$i+1] = $pclantag.$pname;
}

$text .= '
<div class="news">
';

// assuming we saved the above function in "functions.php", let's make sure it's available
require_once e_PLUGIN.'ebattles/matchreport_functions.php';

// has the form been submitted?
if (isset($_POST['submit']))
{
    // the form has been submitted
    // perform data checks.
    $error_str = ''; // initialise $error_str as empty

    $reported_by = $_POST['reported_by'];
    //$text .= "reported by: $reported_by<br />";

    $allowedTags='<p><strong><em><u><h1><h2><h3><h4><h5><h6><img>';
    $allowedTags.='<li><ol><ul><span><div><br /><ins><del>';
    $comments = strip_tags(stripslashes($_POST['elm1']),$allowedTags);

    $nbr_players = $_POST['nbr_players'];
    $nbr_teams = $_POST['nbr_teams'];
    for($i=1;$i<=$nbr_players;$i++)
    {
        $pid = $_POST['player'.$i];
        $q =
        "SELECT ".TBL_USERS.".*, "
        .TBL_PLAYERS.".*"
        ." FROM ".TBL_USERS.", "
        .TBL_PLAYERS
        ." WHERE (".TBL_PLAYERS.".PlayerID = '$pid')"
        ."   AND (".TBL_PLAYERS.".User     = ".TBL_USERS.".user_id)";
        $result = $sql->db_Query($q);
        $row = mysql_fetch_array($result);
        $puid = $row['user_id'];

        if ($pid == $players_name[0])
        $error_str .= '<li>Player #'.$i.' not selected</li>';

        for($j=$i+1;$j<=$nbr_players;$j++)
        {
            //if ($_POST['player'.$i] == $_POST['player'.$j])
            $pjid = $_POST['player'.$j];
            $q =
            "SELECT ".TBL_USERS.".*, "
            .TBL_PLAYERS.".*"
            ." FROM ".TBL_USERS.", "
            .TBL_PLAYERS
            ." WHERE (".TBL_PLAYERS.".PlayerID = '$pjid')"
            ."   AND (".TBL_PLAYERS.".User   = ".TBL_USERS.".user_id)";
            $result = $sql->db_Query($q);
            $row = mysql_fetch_array($result);
            $pjuid = $row['user_id'];

            if ($puid == $pjuid)
            $error_str .= '<li>Player #'.$i.' is the same as Player #'.$j.'</li>';
        }
    }

    for($i=1;$i<=$nbr_teams;$i++)
    {
        $team_players = 0;
        for($j=1;$j<=$nbr_players;$j++)
        {
            if ($_POST['team'.$j] == 'Team #'.$i)
            $team_players ++;
        }
        if ($team_players == 0)
        $error_str .= '<li>Team #'.$i.' has no player</li>';
    }

    //??? if (empty($_POST['player1'])) $error_str .= '<li>You did not enter your player 1.</li>';

    // we could do more data checks, but you get the idea.
    // we could also strip any HTML from the variables, convert it to entities, have a maximum character limit on the values, etc etc, but this is just an example.
    // now, have any of these errors happened? We can find out by checking if $error_str is empty

    //$error_str = 'test';

    if (!empty($error_str)) {
        // show form again
        user_form($players_id, $players_name, $event_id);
        // errors have occured, halt execution and show form again.
        $text .= '<p style="color:red">There were errors in the information you entered, they are listed below:';
        $text .= '<ul style="color:red">'.$error_str.'</ul></p>';
    }
    else
    {
        //$text .= "OK<br />";
        $nbr_players = $_POST['nbr_players'];

        for($i=1;$i<=$nbr_teams;$i++)
        {
            $text .= 'Rank #'.$i.': '.$_POST['rank'.$i];
            $text .= '<br />';
        }
        $text .= '--------------------<br />';

        $text .= 'Comments: '.$comments.'<br />';

        // Create Match ------------------------------------------
        $time = GMT_time();
        $q =
        "INSERT INTO ".TBL_MATCHS."(Event,ReportedBy,TimeReported,Comments)
        VALUES ($event_id,'$reported_by',$time, '$comments')";
        $result = $sql->db_Query($q);

        $last_id = mysql_insert_id();
        $match_id = $last_id;

        // Create Scores ------------------------------------------
        for($i=1;$i<=$nbr_players;$i++)
        {
            $pid = $_POST['player'.$i];
            $pteam = str_replace("Team #","",$_POST['team'.$i]);

            $q =
            "SELECT ".TBL_USERS.".*, "
            .TBL_PLAYERS.".*"
            ." FROM ".TBL_USERS.", "
            .TBL_PLAYERS
            ." WHERE (".TBL_PLAYERS.".PlayerID = '$pid')"
            ."   AND (".TBL_PLAYERS.".User     = ".TBL_USERS.".user_id)";
            $result = $sql->db_Query($q);
            $row = mysql_fetch_array($result);
            $pname = $row['user_name'];
            $puid = $row['user_id'];

            for($j=1;$j<=$nbr_teams;$j++)
            {
                if( $_POST['rank'.$j] == "Team #".$pteam)
                $prank = $j;
            }

            $deltaELO = 0;

            $q =
            "INSERT INTO ".TBL_SCORES."(MatchID,Player,Player_MatchTeam,Player_deltaELO,Player_Score,Player_Rank)
            VALUES ($last_id,$pid,$pteam,$deltaELO,$nbr_teams-$prank,$prank)
            ";
            $result = $sql->db_Query($q);

            $text .= 'Player #'.$i.': '.$pname.' (user id:'.$puid.') (player id:'.$pid.')';
            $text .= ' in team '.$pteam;
            $text .= '<br />';
            /**/
        }
        $text .= '--------------------<br />';

        for($i=1;$i<=$nbr_teams-1;$i++)
        {
            for($j=($i+1);$j<=$nbr_teams;$j++)
            {
                $text .= "Team $i vs. Team $j<br />";

                $text .= "event: $event_id<br />";

                $q = "SELECT ".TBL_MATCHS.".*, "
                .TBL_SCORES.".*, "
                .TBL_PLAYERS.".*, "
                .TBL_USERS.".*"
                ." FROM ".TBL_MATCHS.", "
                .TBL_SCORES.", "
                .TBL_PLAYERS.", "
                .TBL_USERS
                ." WHERE (".TBL_MATCHS.".MatchID = '$match_id')"
                ." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
                ." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
                ." AND (".TBL_USERS.".user_id = ".TBL_PLAYERS.".User)"
                ." AND (".TBL_SCORES.".Player_MatchTeam = '$i')";
                $resultA = $sql->db_Query($q);
                $NbrPlayersTeamA = mysql_numrows($resultA);
                $teamA_Score= mysql_result($resultA,0, TBL_SCORES.".Player_Score");
                $teamA_ELO=0;
                for ($k=0;$k<$NbrPlayersTeamA;$k++)
                {
                    $teamA_ELO += mysql_result($resultA,$k, TBL_PLAYERS.".ELORanking");
                }
                $text .= "Team $i ELO: $teamA_ELO, score: $teamA_Score<br />";

                $q = "SELECT ".TBL_MATCHS.".*, "
                .TBL_SCORES.".*, "
                .TBL_PLAYERS.".*, "
                .TBL_USERS.".*"
                ." FROM ".TBL_MATCHS.", "
                .TBL_SCORES.", "
                .TBL_PLAYERS.", "
                .TBL_USERS
                ." WHERE (".TBL_MATCHS.".MatchID = '$match_id')"
                ." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
                ." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
                ." AND (".TBL_USERS.".user_id = ".TBL_PLAYERS.".User)"
                ." AND (".TBL_SCORES.".Player_MatchTeam = '$j')";
                $resultB = $sql->db_Query($q);
                $NbrPlayersTeamB = mysql_numrows($resultB);
                $teamB_Score= mysql_result($resultB,0, TBL_SCORES.".Player_Score");
                $teamB_ELO=0;
                for ($k=0;$k<$NbrPlayersTeamB;$k++)
                {
                    $teamB_ELO += mysql_result($resultB,$k, TBL_PLAYERS.".ELORanking");
                }
                $text .= "Team $j ELO: $teamB_ELO, score: $teamB_Score<br />";

                // New ELO ------------------------------------------
                $M=min($NbrPlayersTeamA,$NbrPlayersTeamB)*$eELO_M;      // Span
                $K=$eELO_K;	// Max adjustment per game
                $deltaELO = ELO($M, $K, $teamA_ELO, $teamB_ELO, $teamA_Score, $teamB_Score);
                $text .= "deltaELO: $deltaELO<br />";

                // Update Scores ------------------------------------------
                for ($k=0;$k<$NbrPlayersTeamA;$k++)
                {
                    $scoreELO = mysql_result($resultA,$k, TBL_SCORES.".Player_deltaELO");
                    $pid = mysql_result($resultA,$k, TBL_PLAYERS.".PlayerID");
                    $scoreELO += $deltaELO;
                    $q = "UPDATE ".TBL_SCORES." SET Player_deltaELO = $scoreELO"
                    ." WHERE (MatchID = '$match_id')"
                    ." AND (Player = '$pid')";
                    $result = $sql->db_Query($q);
                }
                for ($k=0;$k<$NbrPlayersTeamB;$k++)
                {
                    $scoreELO = mysql_result($resultB,$k, TBL_SCORES.".Player_deltaELO");
                    $pid = mysql_result($resultB,$k, TBL_PLAYERS.".PlayerID");
                    $scoreELO -= $deltaELO;
                    $q = "UPDATE ".TBL_SCORES." SET Player_deltaELO = $scoreELO"
                    ." WHERE (MatchID = '$match_id')"
                    ." AND (Player = '$pid')";
                    $result = $sql->db_Query($q);
                }
            }
        }
        $text .= '<br />';
        $text .= '<br />';

        // Update Players with scores
        $q = "SELECT ".TBL_MATCHS.".*, "
        .TBL_SCORES.".*, "
        .TBL_PLAYERS.".*, "
        .TBL_USERS.".*"
        ." FROM ".TBL_MATCHS.", "
        .TBL_SCORES.", "
        .TBL_PLAYERS.", "
        .TBL_USERS
        ." WHERE (".TBL_MATCHS.".MatchID = '$match_id')"
        ." AND (".TBL_SCORES.".MatchID = ".TBL_MATCHS.".MatchID)"
        ." AND (".TBL_PLAYERS.".PlayerID = ".TBL_SCORES.".Player)"
        ." AND (".TBL_USERS.".user_id = ".TBL_PLAYERS.".User)";
        $result = $sql->db_Query($q);
        $num_rows = mysql_numrows($result);
        for($i=0;$i<$num_rows;$i++)
        {
            $pdeltaELO = mysql_result($result,$i, TBL_SCORES.".Player_deltaELO");
            $pscore = mysql_result($result,$i, TBL_SCORES.".Player_Score");
            $pid= mysql_result($result,$i, TBL_PLAYERS.".PlayerID");
            $puid= mysql_result($result,$i, TBL_USERS.".user_id");
            $pName= mysql_result($result,$i, TBL_USERS.".user_name");
            $pELO= mysql_result($result,$i, TBL_PLAYERS.".ELORanking");
            $pGamesPlayed= mysql_result($result,$i, TBL_PLAYERS.".GamesPlayed");
            $pWins= mysql_result($result,$i, TBL_PLAYERS.".Win");
            $pLosses= mysql_result($result,$i, TBL_PLAYERS.".Loss");
            $pStreak= mysql_result($result,$i, TBL_PLAYERS.".Streak");
            $pStreak_Best= mysql_result($result,$i, TBL_PLAYERS.".Streak_Best");
            $pStreak_Worst= mysql_result($result,$i, TBL_PLAYERS.".Streak_Worst");

            $pELO += $pdeltaELO;
            $pGamesPlayed += 1;
            $pLosses = $pLosses + $nbr_teams - $pscore - 1;
            $pWins = $pWins + $pscore;

            $text .= "Player $pName, new ELO:$pELO<br />";

            $q = "UPDATE ".TBL_PLAYERS." SET ELORanking = $pELO WHERE (PlayerID = '$pid')";
            $result2 = $sql->db_Query($q);
            $q = "UPDATE ".TBL_PLAYERS." SET GamesPlayed = $pGamesPlayed WHERE (PlayerID = '$pid')";
            $result2 = $sql->db_Query($q);
            $q = "UPDATE ".TBL_PLAYERS." SET Loss = $pLosses WHERE (PlayerID = '$pid')";
            $result2 = $sql->db_Query($q);
            $q = "UPDATE ".TBL_PLAYERS." SET Win = $pWins WHERE (PlayerID = '$pid')";
            $result2 = $sql->db_Query($q);

            $gain = 2*$pscore - $nbr_teams +1;
            if ($gain * $pStreak > 0)
            {
                // same sign
                $pStreak += $gain;
            }
            else
            {
                // opposite sign
                $pStreak = $gain;
            }

            if ($pStreak > $pStreak_Best) $pStreak_Best = $pStreak;
            if ($pStreak < $pStreak_Worst) $pStreak_Worst = $pStreak;
            $q3 = "UPDATE ".TBL_PLAYERS." SET Streak = $pStreak WHERE (PlayerID = '$pid')";
            $result3 = $sql->db_Query($q3);
            $q3 = "UPDATE ".TBL_PLAYERS." SET Streak_Best = $pStreak_Best WHERE (PlayerID = '$pid')";
            $result3 = $sql->db_Query($q3);
            $q3 = "UPDATE ".TBL_PLAYERS." SET Streak_Worst = $pStreak_Worst WHERE (PlayerID = '$pid')";
            $result3 = $sql->db_Query($q3);
        }

        $q = "UPDATE ".TBL_EVENTS." SET IsChanged = 1 WHERE (EventID = '$event_id')";
        $result = $sql->db_Query($q);

        $text .= "<p>";
        $text .= "<br />Back to [<a href=\"".e_PLUGIN."ebattles/eventinfo.php?eventid=$event_id\">Event</a>]<br />";
        $text .= "</p>";

        //debug header("Location: eventinfo.php?eventid=$event_id");
    }
    // if we get here, all data checks were okay, process information as you wish.
} else {

    if (!isset($_POST['matchreport']))
    {
        $text .= "<p>You are not authorized to report a match.</p>";
        $text .= "<p>Back to [<a href=\"".e_PLUGIN."ebattles/eventinfo.php?eventid=$event_id\">Event</a>]</p>";
    }
    else if (!check_class(e_UC_MEMBER))
    {
        $text .= "<p>You are not logged in.</p>";
        $text .= "<p>Back to [<a href=\"".e_PLUGIN."ebattles/eventinfo.php?eventid=$event_id\">Event</a>]</p>";
    }
    else
    {
        // the form has not been submitted, let's show it
        user_form($players_id, $players_name, $event_id);
    }
}

$text .= '
</div>
';

$ns->tablerender('Match Report', $text);
require_once(FOOTERF);
exit;
?>