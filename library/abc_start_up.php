<?php
/**
 * ABC Start up
 *
 * Initiates all classes needed by every page. Other classes not listed here
 * must be included individually on a page before it can be used.
 */

require_once 'classes/class.phpbb_interaction.php';	//Must be included first to load all user information from PHPBB
require_once 'config.php'; 							//Config file containing database connection details
require_once 'classes/class.army.php';
require_once 'classes/class.campaign.php';
require_once 'classes/class.division.php';
require_once 'classes/class.medal.php';
require_once 'classes/class.rank.php';
require_once 'classes/class.user.php';

//Initiate forum information
define('IN_PHPBB', true);
include($phpbb_root_path . 'common.' . $phpEx);
$user->session_begin();
$auth->acl($user->data);
$user->setup();
if($user->data['user_id'] == ANONYMOUS) {
	//The user is not logged in, we go to the forum home page
    header("Location: " . $phpbb_root_path . "ucp.php?mode=login");
	exit;
}

//Initiate ABC
$mysqli = new MySQLi($dbhost, $dbuser, $dbpass, $dbname);
unset($pass); //Unset the password for security
if($mysqli->connect_errno) {
	echo "Error connecting to the database: [" . $mysqli->connect_errno . "] " . $mysqli->connect_error;
	exit;
}
$campaign = new Campaign();
$armies = array();
//If a campaign is running we need to load all the armies for it
if($campaign->is_running) {
	$query = "SELECT
		army_id, 
		army_name, 
		army_general, 
		army_colour, 
		army_tag_structure, 
		army_tag, 
		army_join_pw, 
		army_ts_pw, 
		army_is_neutral, 
		army_hc_forum_group, 
		army_officer_forum_group, 
		army_soldiers_forum_group, 
		army_time_stamp 
		FROM abc_armies 
		WHERE campaign_id = " . $campaign->id . " 
		ORDER BY army_id";
	$result = $mysqli->query($query);
	$campaign->num_armies = $result->num_rows;
	$i = 0;
	while($row = $result->fetch_assoc()) {
		$armies[$i]['army'] = new Army($row);
		
		//Now we need to load all the divisions for the army
		$d_query = "SELECT 
			division_id, 
			army_id, 
			division_name, 
			division_commander, 
			division_is_default, 
			division_is_hc, 
			division_tag, 
			division_time_stamp 
			FROM abc_divisions 
			WHERE army_id = " . $armies[$i]['army']->id . " 
			ORDER BY division_is_hc DESC, division_is_default, division_id";
		$d_result = $mysqli->query($d_query);
		$armies[$i]['army']->num_divs = $d_result->num_rows;
		$j = 0;
		while($d_row = $d_result->fetch_assoc()) {
			$armies[$i]['divisions'][$j++] = new Division($d_row, $i);
		}
		$d_result->free();
		
		//Followed by all the ranks for the army
		$r_query = "SELECT 
			rank_id, 
			rank_phpbb_id, 
			army_id, 
			rank_name, 
			rank_short, 
			rank_order, 
			rank_is_officer, 
			rank_img, 
			rank_tag, 
			rank_time_stamp 
			FROM abc_ranks 
			WHERE army_id = " . $armies[$i]['army']->id . " 
			ORDER BY rank_order, rank_id";
		$r_result = $mysqli->query($r_query);
		$armies[$i]['army']->num_ranks = $r_result->num_rows;
		$k = 0;
		while($r_row = $r_result->fetch_assoc()) {
			$armies[$i]['ranks'][$k++] = new Rank($r_row, $i);
		}
    $r_result->free();
		
		//And the medals
		$m_query = "SELECT 
			medal_id,  
			army_id, 
			medal_name, 
			medal_img, 
			medal_ribbon,
			medal_time_stamp 
			FROM abc_medals 
			WHERE army_id = " . $armies[$i]['army']->id . " 
			ORDER BY medal_name, medal_id";
		$m_result = $mysqli->query($m_query);
		$armies[$i]['army']->num_medals = $m_result->num_rows;
		$l = 0;
		while($m_row = $m_result->fetch_assoc()) {
			$armies[$i]['medals'][$l++] = new Medal($m_row, $i);
		}
		$i++;
	}
}
//Finally we load user information
$abc_user = new Abc_user($user->data['user_id']);
?>