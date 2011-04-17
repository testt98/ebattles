<?php
/**
*LadderProcess.php
*
*/
require_once("../../class2.php");
require_once(e_PLUGIN."ebattles/include/main.php");
require_once(e_PLUGIN.'ebattles/include/ladder.php');

/*******************************************************************
********************************************************************/
echo '
<html>
<head>
<style type="text/css">
<!--
.percents {
background: #FFF;
position:absolute;
text-align: center;
}
-->
</style>
</head>
<body>
';

//dbg- print_r($_POST);
//dbg- exit;
$ladder_id = $_GET['LadderID'];
if (!$ladder_id)
{
	header("Location: ./ladders.php");
	exit();
}
else
{
	$ladder = new Ladder($ladder_id);
	
	$can_manage = 0;
	if (check_class($pref['eb_mod_class'])) $can_manage = 1;
	if (USERID==$ladder->getField('Owner')) $can_manage = 1;
	if ($can_manage == 0)
	{
		header("Location: ./ladderinfo.php?LadderID=$ladder_id");
		exit();
	}
	else{

		$q = "UPDATE ".TBL_LADDERS." SET IsChanged = 1 WHERE (LadderID = '$ladder_id')";
		$result = $sql->db_Query($q);

		if(isset($_POST['ladderchangeowner']))
		{
			$ladder_owner = $_POST['ladderowner'];

			/* Ladder Owner */
			$q2 = "UPDATE ".TBL_LADDERS." SET Owner = '$ladder_owner' WHERE (LadderID = '$ladder_id')";
			$result2 = $sql->db_Query($q2);

			//echo "-- ladderchangeowner --<br />";
			header("Location: laddermanage.php?LadderID=$ladder_id");
			exit();
		}
		if(isset($_POST['ladderdeletemod']))
		{
			$laddermod = $_POST['laddermod'];
			$q2 = "DELETE FROM ".TBL_LADDERMODS
			." WHERE (".TBL_LADDERMODS.".Ladder = '$ladder_id')"
			."   AND (".TBL_LADDERMODS.".User = '$laddermod')";
			$result2 = $sql->db_Query($q2);

			//echo "-- ladderdeletemod --<br />";
			header("Location: laddermanage.php?LadderID=$ladder_id");
			exit();
		}
		if(isset($_POST['ladderaddmod']))
		{
			$laddermod = $_POST['mod'];

			$q2 = "SELECT ".TBL_LADDERMODS.".*"
			." FROM ".TBL_LADDERMODS
			." WHERE (".TBL_LADDERMODS.".Ladder = '$ladder_id')"
			."   AND (".TBL_LADDERMODS.".User = '$laddermod')";
			$result2 = $sql->db_Query($q2);
			$num_rows_2 = mysql_numrows($result2);
			if ($num_rows_2==0)
			{
				$q2 = "INSERT INTO ".TBL_LADDERMODS."(Ladder,User,Level)"
				." VALUES ('$ladder_id','$laddermod',1)";
				$result2 = $sql->db_Query($q2);
			}
			//echo "-- ladderaddmod --<br />";
			header("Location: laddermanage.php?LadderID=$ladder_id");
			exit();
		}

		if(isset($_POST['laddersettingssave']))
		{
			/* Ladder Name */
			$new_laddername = htmlspecialchars($_POST['laddername']);
			if ($new_laddername != '')
			{
				$q2 = "UPDATE ".TBL_LADDERS." SET Name = '$new_laddername' WHERE (LadderID = '$ladder_id')";
				$result2 = $sql->db_Query($q2);
			}

			/* Ladder Password */
			$new_ladderpassword = htmlspecialchars($_POST['ladderpassword']);
			$q2 = "UPDATE ".TBL_LADDERS." SET Password = '$new_ladderpassword' WHERE (LadderID = '$ladder_id')";
			$result2 = $sql->db_Query($q2);

			/* Ladder Type */
			// Can change only if no players are signed up
			$q2 = "SELECT ".TBL_PLAYERS.".*"
			." FROM ".TBL_PLAYERS
			." WHERE (".TBL_PLAYERS.".Ladder = '$ladder_id')";
			$result2 = $sql->db_Query($q2);
			$num_rows_2 = mysql_numrows($result2);
			if ($num_rows_2==0)
			{
				$new_laddertype = $_POST['laddertype'];

				switch($new_laddertype)
				{
					case 'Individual':
					$q2 = "UPDATE ".TBL_LADDERS." SET Type = 'One Player Ladder' WHERE (LadderID = '$ladder_id')";
					$result2 = $sql->db_Query($q2);
					break;
					case 'Team':
					$q2 = "UPDATE ".TBL_LADDERS." SET Type = 'Team Ladder' WHERE (LadderID = '$ladder_id')";
					$result2 = $sql->db_Query($q2);
					break;
					case 'ClanWar':
					$q2 = "UPDATE ".TBL_LADDERS." SET Type = 'ClanWar' WHERE (LadderID = '$ladder_id')";
					$result2 = $sql->db_Query($q2);
					break;
					default:
				}
			}

			/* Ladder Ranking Type */
			$new_ladderrankingtype = $_POST['ladderrankingtype'];

			switch($new_ladderrankingtype)
			{
				case 'Classic':
				$q2 = "UPDATE ".TBL_LADDERS." SET RankingType = 'Classic' WHERE (LadderID = '$ladder_id')";
				$result2 = $sql->db_Query($q2);
				break;
				case 'CombinedStats':
				$q2 = "UPDATE ".TBL_LADDERS." SET RankingType = 'CombinedStats' WHERE (LadderID = '$ladder_id')";
				$result2 = $sql->db_Query($q2);
				break;
				default:
			}

			/* Ladder Match report userclass */
			$new_laddermatchreportuserclass = $_POST['laddermatchreportuserclass'];
			$q2 = "UPDATE ".TBL_LADDERS." SET match_report_userclass = '$new_laddermatchreportuserclass' WHERE (LadderID = '$ladder_id')";
			$result2 = $sql->db_Query($q2);

			/* Ladder Quick Loss Report */
			if ($_POST['ladderallowquickloss'] != "")
			{
				$q2 = "UPDATE ".TBL_LADDERS." SET quick_loss_report = 1 WHERE (LadderID = '$ladder_id')";
				$result2 = $sql->db_Query($q2);
			}
			else
			{
				$q2 = "UPDATE ".TBL_LADDERS." SET quick_loss_report = 0 WHERE (LadderID = '$ladder_id')";
				$result2 = $sql->db_Query($q2);
			}

			/* Ladder Allow Score */
			if ($_POST['ladderallowscore'] != "")
			{
				$q2 = "UPDATE ".TBL_LADDERS." SET AllowScore = 1 WHERE (LadderID = '$ladder_id')";
				$result2 = $sql->db_Query($q2);
			}
			else
			{
				$q2 = "UPDATE ".TBL_LADDERS." SET AllowScore = 0 WHERE (LadderID = '$ladder_id')";
				$result2 = $sql->db_Query($q2);
			}

			/* Ladder Allow Draw */
			if ($_POST['ladderallowdraw'] != "")
			{
				$q2 = "UPDATE ".TBL_LADDERS." SET AllowDraw = 1 WHERE (LadderID = '$ladder_id')";
				$result2 = $sql->db_Query($q2);
			}
			else
			{
				$q2 = "UPDATE ".TBL_LADDERS." SET AllowDraw = 0 WHERE (LadderID = '$ladder_id')";
				$result2 = $sql->db_Query($q2);
			}

			/* Ladder Match Approval */
			$new_MatchesApproval = $_POST['laddermatchapprovaluserclass'];
			$q2 = "UPDATE ".TBL_LADDERS." SET MatchesApproval = '$new_MatchesApproval' WHERE (LadderID = '$ladder_id')";
			$result2 = $sql->db_Query($q2);

			/* Points */
			$new_ladderpointsperwin = htmlspecialchars($_POST['ladderpointsperwin']);
			if (preg_match("/^\d+$/", $new_ladderpointsperwin))
			{
				$q2 = "UPDATE ".TBL_LADDERS." SET PointsPerWin = '$new_ladderpointsperwin' WHERE (LadderID = '$ladder_id')";
				$result2 = $sql->db_Query($q2);
			}
			$new_ladderpointsperdraw = htmlspecialchars($_POST['ladderpointsperdraw']);
			if (preg_match("/^\d+$/", $new_ladderpointsperdraw))
			{
				$q2 = "UPDATE ".TBL_LADDERS." SET PointsPerDraw = '$new_ladderpointsperdraw' WHERE (LadderID = '$ladder_id')";
				$result2 = $sql->db_Query($q2);
			}
			$new_ladderpointsperloss = htmlspecialchars($_POST['ladderpointsperloss']);
			if (preg_match("/^-?\d+$/", $new_ladderpointsperloss))
			{
				$q2 = "UPDATE ".TBL_LADDERS." SET PointsPerLoss = '$new_ladderpointsperloss' WHERE (LadderID = '$ladder_id')";
				$result2 = $sql->db_Query($q2);
			}

			/* Ladder Max number of Maps Per Match */
			$new_laddermaxmapspermatch = htmlspecialchars($_POST['laddermaxmapspermatch']);
			if (preg_match("/^\d+$/", $new_laddermaxmapspermatch))
			{
				$q2 = "UPDATE ".TBL_LADDERS." SET MaxMapsPerMatch = '$new_laddermaxmapspermatch' WHERE (LadderID = '$ladder_id')";
				$result2 = $sql->db_Query($q2);
			}

			/* Ladder Game */
			$new_laddergame = $_POST['laddergame'];
			$q2 = "UPDATE ".TBL_LADDERS." SET Game = '$new_laddergame' WHERE (LadderID = '$ladder_id')";
			$result2 = $sql->db_Query($q2);

			/* Ladder Start Date */
			$new_ladderstartdate = $_POST['startdate'];
			if ($new_ladderstartdate != '')
			{
				$new_ladderstart_local = strtotime($new_ladderstartdate);
				$new_ladderstart = $new_ladderstart_local - TIMEOFFSET;	// Convert to GMT time
			}
			else
			{
				$new_ladderstart = 0;
			}
			$q2 = "UPDATE ".TBL_LADDERS." SET Start_timestamp = '$new_ladderstart' WHERE (LadderID = '$ladder_id')";
			$result2 = $sql->db_Query($q2);
			//echo "$new_ladderstart, $new_ladderstartdate";

			/* Ladder End Date */
			$new_ladderenddate = $_POST['enddate'];
			if ($new_ladderenddate != '')
			{
				$new_ladderend_local = strtotime($new_ladderenddate);
				$new_ladderend = $new_ladderend_local - TIMEOFFSET;	// Convert to GMT time
			}
			else
			{
				$new_ladderend = 0;
			}
			if ($new_ladderend < $new_ladderstart)
			{
				$new_ladderend = $new_ladderstart;
			}

			$q2 = "UPDATE ".TBL_LADDERS." SET End_timestamp = '$new_ladderend' WHERE (LadderID = '$ladder_id')";
			$result2 = $sql->db_Query($q2);
			//echo "$new_ladderend, $new_ladderenddate";


			/* Ladder Description */
			$new_ladderdescription = $tp->toDB($_POST['ladderdescription']);
			$q2 = "UPDATE ".TBL_LADDERS." SET Description = '$new_ladderdescription' WHERE (LadderID = '$ladder_id')";
			$result2 = $sql->db_Query($q2);

			//echo "-- laddersettingssave --<br />";
			header("Location: laddermanage.php?LadderID=$ladder_id");
			exit();
		}
		if(isset($_POST['ladderrulessave']))
		{
			/* Ladder Rules */
			$new_ladderrules = $tp->toDB($_POST['ladderrules']);
			$q2 = "UPDATE ".TBL_LADDERS." SET Rules = '$new_ladderrules' WHERE (LadderID = '$ladder_id')";
			$result2 = $sql->db_Query($q2);

			//echo "-- ladderrulessave --<br />";
			header("Location: laddermanage.php?LadderID=$ladder_id");
			exit();
		}
		if(isset($_POST['ladderaddplayer']))
		{
			$player = $_POST['player'];
			$notify = (isset($_POST['ladderaddplayernotify'])? TRUE: FALSE);
			$ladder->ladderAddPlayer($player, 0, $notify);

			//echo "-- ladderaddplayer --<br />";
			header("Location: laddermanage.php?LadderID=$ladder_id");
			exit();
		}
		if(isset($_POST['ladderaddteam']))
		{
			$division = $_POST['division'];
			$notify = (isset($_POST['ladderaddteamnotify'])? TRUE: FALSE);
			$ladder->ladderAddDivision($ladder_id, $division, $notify);

			//echo "-- ladderaddteam --<br />";
			header("Location: laddermanage.php?LadderID=$ladder_id");
			exit();
		}
		if(isset($_POST['ban_player']) && $_POST['ban_player']!="")
		{
			$playerid = $_POST['ban_player'];
			$q2 = "UPDATE ".TBL_PLAYERS." SET Banned = '1' WHERE (PlayerID = '$playerid')";
			$result2 = $sql->db_Query($q2);
			updateStats($ladder_id, $time, TRUE);
			header("Location: laddermanage.php?LadderID=$ladder_id");
			exit();
		}
		if(isset($_POST['unban_player']) && $_POST['unban_player']!="")
		{
			$playerid = $_POST['unban_player'];
			$q2 = "UPDATE ".TBL_PLAYERS." SET Banned = '0' WHERE (PlayerID = '$playerid')";
			$result2 = $sql->db_Query($q2);
			updateStats($ladder_id, $time, TRUE);
			header("Location: laddermanage.php?LadderID=$ladder_id");
			exit();
		}
		if(isset($_POST['kick_player']) && $_POST['kick_player']!="")
		{
			$playerid = $_POST['kick_player'];
			deletePlayer($playerid);
			updateStats($ladder_id, $time, TRUE);
			header("Location: laddermanage.php?LadderID=$ladder_id");
			exit();
		}
		if(isset($_POST['del_player_games']) && $_POST['del_player_games']!="")
		{
			$playerid = $_POST['del_player_games'];
			deletePlayerMatches($playerid);
			updateStats($ladder_id, $time, TRUE);
			header("Location: laddermanage.php?LadderID=$ladder_id");
			exit();
		}
		if(isset($_POST['del_player_awards']) && $_POST['del_player_awards']!="")
		{
			$playerid = $_POST['del_player_awards'];
			deletePlayerAwards($playerid);
			header("Location: laddermanage.php?LadderID=$ladder_id");
			exit();
		}
		if(isset($_POST['ladderresetscores']))
		{
			$ladder->resetPlayers();
			$ladder->resetTeams();
			$ladder->deleteMatches();

			//echo "-- ladderresetscores --<br />";
			header("Location: laddermanage.php?LadderID=$ladder_id");
			exit();
		}
		if(isset($_POST['ladderresetladder']))
		{
			$ladder->deleteMatches();
			$ladder->deleteChallenges();
			$ladder->deletePlayers();
			$ladder->deleteTeams();

			//echo "-- ladderresetladder --<br />";
			header("Location: laddermanage.php?LadderID=$ladder_id");
			exit();
		}
		if(isset($_POST['ladderdelete']))
		{
			$ladder->deleteLadder();

			//echo "-- ladderdelete --<br />";
			header("Location: ladders.php");
			exit();
		}
		if(isset($_POST['ladderupdatescores']))
		{
			if (!isset($_POST['match'])) $_POST['match'] = 0;
			$current_match = $_POST['match'];
			$ladder->ladderScoresUpdate($current_match);
		}
		if(isset($_POST['ladderstatssave']))
		{
			//echo "-- ladderstatssave --<br />";
			$cat_index = 0;

			/* Ladder Min games to rank */
			if ($ladder->getField('Type') != "ClanWar")
			{
				$new_ladderGamesToRank = htmlspecialchars($_POST['sliderValue'.$cat_index]);
				if (is_numeric($new_ladderGamesToRank))
				{
					$q2 = "UPDATE ".TBL_LADDERS." SET nbr_games_to_rank = '$new_ladderGamesToRank' WHERE (LadderID = '$ladder_id')";
					$result2 = $sql->db_Query($q2);
				}
				$cat_index++;
			}

			if (($ladder->getField('Type') == "Team Ladder")||($ladder->getField('Type') == "ClanWar"))
			{
				/* Ladder Min Team games to rank */
				$new_ladderTeamGamesToRank = htmlspecialchars($_POST['sliderValue'.$cat_index]);
				if (is_numeric($new_ladderTeamGamesToRank))
				{
					$q2 = "UPDATE ".TBL_LADDERS." SET nbr_team_games_to_rank = '$new_ladderTeamGamesToRank' WHERE (LadderID = '$ladder_id')";
					$result2 = $sql->db_Query($q2);
				}
				$cat_index++;
			}

			$q_1 = "SELECT ".TBL_STATSCATEGORIES.".*"
			." FROM ".TBL_STATSCATEGORIES
			." WHERE (".TBL_STATSCATEGORIES.".Ladder = '$ladder_id')";

			$result_1 = $sql->db_Query($q_1);
			$numCategories = mysql_numrows($result_1);

			for($i=0; $i<$numCategories; $i++)
			{
				$cat_name = mysql_result($result_1,$i, TBL_STATSCATEGORIES.".CategoryName");

				$new_ladderStat = htmlspecialchars($_POST['sliderValue'.$cat_index]);
				if (is_numeric($new_ladderStat))
				{
					$q2 = "UPDATE ".TBL_STATSCATEGORIES." SET CategoryMaxValue = '$new_ladderStat' WHERE (Ladder = '$ladder_id') AND (CategoryName = '$cat_name')";
					$result2 = $sql->db_Query($q2);
				}

				// Display Only
				if ($_POST['infoonly'.$i] != "")
				$q2 = "UPDATE ".TBL_STATSCATEGORIES." SET InfoOnly = 1 WHERE (Ladder = '$ladder_id') AND (CategoryName = '$cat_name')";
				else
				$q2 = "UPDATE ".TBL_STATSCATEGORIES." SET InfoOnly = 0 WHERE (Ladder = '$ladder_id') AND (CategoryName = '$cat_name')";
				$result2 = $sql->db_Query($q2);

				$cat_index ++;
			}

			// Hide ratings column
			if ($_POST['hideratings'] != "")
			$q2 = "UPDATE ".TBL_LADDERS." SET hide_ratings_column = 1 WHERE (LadderID = '$ladder_id')";
			else
			$q2 = "UPDATE ".TBL_LADDERS." SET hide_ratings_column = 0 WHERE (LadderID = '$ladder_id')";
			$result2 = $sql->db_Query($q2);

			$q4 = "UPDATE ".TBL_LADDERS." SET IsChanged = 1 WHERE (LadderID = '$ladder_id')";
			$result = $sql->db_Query($q4);

			header("Location: laddermanage.php?LadderID=$ladder_id");
			exit();
		}
		if(isset($_POST['ladderchallengessave']))
		{
			/* Ladder Challenges enable/disable */
			if ($_POST['ladderchallengesenable'] != "")
			{
				$q2 = "UPDATE ".TBL_LADDERS." SET ChallengesEnable = 1 WHERE (LadderID = '$ladder_id')";
				$result2 = $sql->db_Query($q2);
			}
			else
			{
				$q2 = "UPDATE ".TBL_LADDERS." SET ChallengesEnable = 0 WHERE (LadderID = '$ladder_id')";
				$result2 = $sql->db_Query($q2);
			}

			/* Ladder Max Dates per Challenge */
			$new_ladderdatesperchallenge = htmlspecialchars($_POST['ladderdatesperchallenge']);
			if (preg_match("/^\d+$/", $new_ladderdatesperchallenge))
			{
				$q2 = "UPDATE ".TBL_LADDERS." SET MaxDatesPerChallenge = '$new_ladderdatesperchallenge' WHERE (LadderID = '$ladder_id')";
				$result2 = $sql->db_Query($q2);
			}

			header("Location: laddermanage.php?LadderID=$ladder_id");
			exit();
		}
	}
}

header("Location: laddermanage.php?LadderID=$ladder_id");
exit;

?>
