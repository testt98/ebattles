<?php

/*
 function brackets()
 
 
 variables:
  - $matchup[round][matchup][0(top)-1(bottom)]
    . 'T1-16': team 1 to 16
    . 'Wr,m': winner of matchup
    . 'Lw,m': loser of matchup
  - brackets[row][column] -> actual html content of a table cell
  - results[round][matchup]
    . ''
    . 'bye'
    . 'top'/'bottom'
  - content[round][matchup][0(top)-1(bottom)]: content of the top/bottom cells for a matchup
    . 0-15: team index in teams list
    . '': no team (should be 0 ?) 
    . 'not played'
 
 */


function brackets($type, $nbrPlayers = 16, $teams, $results = array(), $rounds) {
/*
$teams = array(
'QamFour',
'Artosis',
'TLO',
'LiquidHuk',
'WISPEEL',
'CrunCher',
'Player7',
'Player8',
'Player9',
'Player10',
'Player11',
);
*/
	$nbrTeams=count($teams);

	switch ($type)
	{
		default:
		$file = 'include/brackets/se-'.$nbrPlayers.'.txt';
		break;
	}
	$matchups = unserialize(implode('',file($file)));
	$nbrRounds = count($matchups);

	/* */
	$brackets = array ();
	$content= array();
	// Initialize grid
	for ($row = 1; $row <= 2*$nbrPlayers; $row ++){
		for ($column = 1; $column <= $nbrRounds; $column++){
			$brackets[$row][2*$column-1] = '<td></td>';
			$brackets[$row][2*$column] = '<td class="grid border-none"></td>';
		}
	}

	$rowspan = 1;
	for ($round = 1; $round <= $nbrRounds; $round++){
		$nbrMatchups = count($matchups[$round]);
		if ($round == 1) {
			/* Round 1 */
			for ($matchup = 1; $matchup <= $nbrMatchups; $matchup ++){
				$teamTop    = substr($matchups[$round][$matchup][0],1);
				$teamBottom = substr($matchups[$round][$matchup][1],1);
				if (!$results[$round][$matchup]) $results[$round][$matchup] = '';

				$content[$round][$matchup][0] = '';
				if ($teamTop <= $nbrTeams){
					$content[$round][$matchup][0] = $teamTop;
				} else {
					$results[$round][$matchup] = 'bye';
				}
				$content[$round][$matchup][1] = '';
				if ($teamBottom <= $nbrTeams){
					$content[$round][$matchup][1] = $teamBottom;
				} else {
					$results[$round][$matchup] = 'bye';
				}

				if(($content[$round][$matchup][0]!='')&&($content[$round][$matchup][1]!='')){
					if ($results[$round][$matchup] == 'top') {
						$brackets[$matchup*4-3][2*$round-1] = html_bracket_team_cell($teams, $content[$round][$matchup][0], 'winner');
						$brackets[$matchup*4-1][2*$round-1] = html_bracket_team_cell($teams, $content[$round][$matchup][1], 'loser');
					} else if ($results[$round][$matchup] == 'bottom') {
						$brackets[$matchup*4-3][2*$round-1] = html_bracket_team_cell($teams, $content[$round][$matchup][0], 'loser');
						$brackets[$matchup*4-1][2*$round-1] = html_bracket_team_cell($teams, $content[$round][$matchup][1], 'winner');
					} else {
						$brackets[$matchup*4-3][2*$round-1] = html_bracket_team_cell($teams, $content[$round][$matchup][0]);
						$brackets[$matchup*4-1][2*$round-1] = html_bracket_team_cell($teams, $content[$round][$matchup][1]);
					}
					$brackets[$matchup*4-2][2*$round-1] = '<td rowspan='.$rowspan.' class="match-details" title="'.'M'.$round.','.$matchup.'"></td>';
				}

				$matchupsRows[$round][$matchup][0] = $matchup*4-3;
				$matchupsRows[$round][$matchup][1] = $matchup*4-1;
			}

		}
		else if ($round < $nbrRounds)
		{
			for ($matchup = 1; $matchup <= $nbrMatchups; $matchup ++){
				if (!$results[$round][$matchup]) $results[$round][$matchup] = '';
				for($match = 0; $match < 2; $match++){
					$matchupString = $matchups[$round][$matchup][$match];
					if ($matchupString[0]='W') {
						$matchupArray = explode(',',substr($matchupString,1));
						$matchupRound = $matchupArray[0];
						$matchupMatchup = $matchupArray[1];

						// Get result of matchup
						$result = $results[$matchupRound][$matchupMatchup];

						$rowTop    = $matchupsRows[$matchupRound][$matchupMatchup][0];
						$rowBottom = $matchupsRows[$matchupRound][$matchupMatchup][1];
						$row = ($rowBottom - $rowTop)/2 + $rowTop;

						// If result is not a bye, we draw the grid
						if($result != 'bye'){
							$brackets[$rowTop][2*$round-2] = '<td class="grid border-top"></td>';
							$brackets[$rowBottom][2*$round-2] = '<td class="grid border-bottom"></td>';
							for ($i = $rowTop+1; $i < $rowBottom; $i++){
								$brackets[$i][2*$round-2] = '<td class="grid border-vertical"></td>';
							}
							for ($i = $rowTop+2; $i < $rowBottom; $i++){
								$brackets[$i][2*$round-3] = '';
							}
							$brackets[$row][2*$round-2] = '<td class="grid border-middle"></td>';
						}

						$matchupsRows[$round][$matchup][$match] = $row;
						if (($result == 'top')||($result == 'bye')) {
							$content[$round][$matchup][$match] = $content[$matchupRound][$matchupMatchup][0];
						}
						else if ($result == 'bottom') {
							$content[$round][$matchup][$match] = $content[$matchupRound][$matchupMatchup][1];
						}
						else {
							$content[$round][$matchup][$match] = 'not played';
						}
					}
				}
				if (($content[$round][$matchup][0]!='')&&($content[$round][$matchup][1]!='')) {
					if ($results[$round][$matchup] == 'top') {
						$brackets[$matchupsRows[$round][$matchup][0]][2*$round-1] = html_bracket_team_cell($teams, $content[$round][$matchup][0], 'winner');
						$brackets[$matchupsRows[$round][$matchup][1]][2*$round-1] = html_bracket_team_cell($teams, $content[$round][$matchup][1], 'loser');
					} else if ($results[$round][$matchup] == 'bottom') {
						$brackets[$matchupsRows[$round][$matchup][0]][2*$round-1] = html_bracket_team_cell($teams, $content[$round][$matchup][0], 'loser');
						$brackets[$matchupsRows[$round][$matchup][1]][2*$round-1] = html_bracket_team_cell($teams, $content[$round][$matchup][1], 'winner');
					} else {
						$brackets[$matchupsRows[$round][$matchup][0]][2*$round-1] = html_bracket_team_cell($teams, $content[$round][$matchup][0]);
						$brackets[$matchupsRows[$round][$matchup][1]][2*$round-1] = html_bracket_team_cell($teams, $content[$round][$matchup][1]);
					}
					$brackets[$matchupsRows[$round][$matchup][0]+1][2*$round-1] = '<td rowspan='.$rowspan.' class="match-details" title="'.'M'.$round.','.$matchup.'"></div></td>';
				}
				if (($content[$round][$matchup][0]=='')||($content[$round][$matchup][1]=='')) {
					$results[$round][$matchup] = 'bye';
				}
			}
		}
		else
		{
			/* Last round, no match */
			for ($matchup = 1; $matchup <= $nbrMatchups; $matchup ++){
				if (!$results[$round][$matchup]) $results[$round][$matchup] = '';
				$match = 0;
				$matchupString = $matchups[$round][$matchup][$match];
				if ($matchupString[$match]='W') {

					$matchupArray = explode(',',substr($matchupString,1));
					$matchupRound = $matchupArray[0];
					$matchupMatchup = $matchupArray[1];

					$result = $results[$matchupRound][$matchupMatchup];

					$rowTop    = $matchupsRows[$matchupRound][$matchupMatchup][0];
					$rowBottom = $matchupsRows[$matchupRound][$matchupMatchup][1];
					$row = ($rowBottom - $rowTop)/2 + $rowTop;

					if($result != 'bye'){
						$brackets[$rowTop][2*$round-2] = '<td class="grid border-top"></td>';
						$brackets[$rowBottom][2*$round-2] = '<td class="grid border-bottom"></td>';
						for ($i = $rowTop+1; $i < $rowBottom; $i++){
							$brackets[$i][2*$round-2] = '<td class="grid border-vertical"></td>';
						}
						for ($i = $rowTop+2; $i < $rowBottom; $i++){
							$brackets[$i][2*$round-3] = '';
						}
						$brackets[$row][2*$round-2] = '<td class="grid border-middle"></td>';
					}

					$matchupsRows[$round][$matchup][$match] = $rowTop;
					if (($result == 'top')||($result == 'bye')) {
						$content[$round][$matchup][$match] = $content[$matchupRound][$matchupMatchup][0];
					} else if ($result == 'bottom') {
						$content[$round][$matchup][$match] = $content[$matchupRound][$matchupMatchup][1];
					} else{
						$content[$round][$matchup][$match] = 'not played';
					}

					if ($results[$round][$matchup] == 'top') {
						$brackets[$row][2*$round-1] = html_bracket_team_cell($teams, $content[$round][$matchup][$match], 'winner');
					} else {
						$brackets[$row][2*$round-1] = html_bracket_team_cell($teams, $content[$round][$matchup][$match]);
					}
				}
			}
		}
		$rowspan = 2*$rowspan + 1;
	}

	$bracket_html = '<div id="panel_brackets">';
	$bracket_html .= '<div id="brackets_frame" style="height: 800px;">';
	$bracket_html .= '<div id="brackets">';
	$bracket_html .= '<table class="brackets">';
	
	$bracket_html .= '<theader><tr>';
	for ($i = 1; $i < $nbrRounds; $i++) {
		$bracket_html .= '<th colspan="2">'.$rounds[$i]['Title'].'</th>';
	}
	$bracket_html .= '</tr></theader>';
	
	$bracket_html .= '<tbody>';
	for ($row = 1; $row <= $nbrPlayers*2; $row ++){
		$bracket_html .= '<tr>';
		for ($column = 1; $column <= 2*$nbrRounds; $column++){
			$bracket_html .= $brackets[$row][$column];
		}
		$bracket_html .= '</tr>';
	}
	$bracket_html .= '</tbody>';
	$bracket_html .= '</table>';
	$bracket_html .= '</div>'; // brackets
	$bracket_html .= '</div>'; // brackets_frame
	$bracket_html .= '<div class="clearer"></div>';
	$bracket_html .= '</div>'; // panel-brackets

	/*
	var_dump($matchups);
	var_dump($results);
	var_dump($content);
	var_dump($teams);
	*/
	
	return $bracket_html;

}

/*
function init_results(&$results)
{
	foreach ($results as $matchups) {
		foreach ($matchups as $matchup) {
			$matchup = '';
		}
	}
}
*/

function html_bracket_team_cell($teams, $team, $container_class='') {
	$text = '<td><div class="container '.$container_class.'">';
	switch ($team) {
		case 'not played':
			$text .= '&nbsp';
			break;
		case '':
			break;
		default:
			$team_name = $teams[$team-1]['Name'];
			$team_image = 'images/ranks/a1.jpg';
			$text .= '<div class="player">';
			//$text .= '<img src="'.$team_image.'" style="vertical-align:middle"/>';
			$text .= $team_name;
			$text .= '</div>';
			if ($container_class == 'winner') {
				$text .= '<div class="wins">W</div>';
			}
			if ($container_class == 'loser') {
				$text .= '<div class="wins">L</div>';
			}
			break;
	}

	$text .= '</div></td>';
	return $text;
}

?>