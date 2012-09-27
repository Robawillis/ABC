<?php
/**
 * CLASS: ABC User
 *
 * Contains all information about a user. Most commonly used for loading
 * information about the most currently logged in user, but also used 
 * when viewing information on other users.
 */

class Abc_user {
	
	public $admin_id		= -1; //Holds the admin ID, if the user is an admin
	public $army_id			= -1;
	public $army_ptr		= -1; //Pointer holding the class army# the army_id is in
	public $availability	= "";
	public $bf3_name		= "";
	public $division_id		= -1;
	public $division_ptr	= -1; //Pointer holding the class division# the div_id is in
	public $id				= -1; //ABC ID, Forum user ID is held in $user
	public $is_admin		= FALSE; //Has ABC admin rights
	public $is_dc			= FALSE; //Division commander
	public $is_hc			= FALSE; //Member of army high command
	public $is_officer		= FALSE; //Officer in army
	public $is_signed_up	= FALSE; //Has signed up to the campaign
	public $location		= "";
	public $medals_sig		= ""; //String containing relative URL to image of users medals
	public $other_notes		= "";
	public $rank_id			= -1;
	public $rank_ptr		= -1; //Pointer holding the class rank# the rank_id is in
	public $tags			= ""; //String containing the soldiers tags
	public $time_stamp		= -1; //Time stamp of last update
	public $vehicles		= "";
	public $Role			= "";
	
	/**
	 * Class constructor
	 *
	 * @$user_id	PHPBB user ID
	 */
	function Abc_user($user_id) {
		$this->_load($user_id);
	}
	
	/**
	 * Create
	 *
	 * Create a copy of the user for the current campaign
	 * @$user_id	PHPBB user ID
	 */
	private function _create($user_id) {
		global $campaign, $mysqli;
		$query = "SELECT
			user_bf3_name, 
			user_availability, 
			user_location, 
			user_vehicles
			FROM abc_users 
			WHERE user_id = " . (int)$user_id . " 
			ORDER BY campaign_id DESC 
			LIMIT 1";
		$result = $mysqli->query($query);
		$query = "INSERT INTO abc_users (
			user_id, 
			campaign_id, 
			user_time_stamp";
		if($result->num_rows)
			$query .= ", 
				user_bf3_name, 
				user_availability, 
				user_location, 
				user_vehicles";
		$query .= ") VALUES ( 
			" . (int)$user_id . ", 
			" . (int)$campaign->id . ", 
			" . time();
		if($result->num_rows) {
			while($row = $result->fetch_assoc) {
				$query .= ",
					'" . $row['user_bf3_name'] . "', 
					'" . $row['user_availability'] . "', 
					'" . $row['user_location'] . "', 
					'" . $row['user_vehicles'] . "'";
			}
		}
			$query .= ")";
		$mysqli->query($query);
	}
	
	/**
	 * Join Army
	 *
	 * Join the army specified in the default division with the default
	 * rank.
	 * @$ptr		Pointer to the army to join
	 */
	function join_army($ptr) {
		global $armies;
		$this->army_id = $armies[$ptr]['army']->id;
		$this->army_ptr = $ptr;
		foreach($armies[$ptr]['divisions'] as $div) {
			if($div->is_default)
				$this->division_id = $div->id;
		}
		foreach($armies[$ptr]['ranks'] as $rank) {
			if($rank->order == 99)
				$this->rank_id = $rank->id;
		}
		$this->is_signed_up = 0;
		return $this->save();
	}
	
	/**
	 * Load
	 *
	 * Fetch user information from the database for the current campaign.
	 * If no user exists run create once then rerun load.
	 * If no campaign is under way just check if the user is an admin.
	 * @$user_id	PHPBB user ID
	 */
	private function _load($user_id) {
		global $armies, $campaign, $mysqli, $user;
		/* We need to load the users campaign specific information, 
		   but only if a campaign is currently running. */
		if($campaign->is_running) {
			$query = "SELECT 
				abc_user_id, 
				army_id, 
				division_id, 
				rank_id, 
				user_medals_sig, 
				user_is_signed_up, 
				user_bf3_name, 
				user_availability, 
				user_location, 
				user_vehicles, 
				user_other_notes, 
				user_time_stamp 
				FROM abc_users 
				WHERE user_id = " . (int)$user_id . " 
				AND campaign_id = " . (int)$campaign->id;
			$result = $mysqli->query($query);
			if($result->num_rows) {
				$row = $result->fetch_assoc();
				$find = array("abc_user_", "user_");
				$replace = array("", "");
				foreach($row as $k => $v) {
					$k = str_replace($find, $replace, $k);
					$this->$k = $v;
				}
			} else {
				$this->_create($user_id);
				$this->_load($user_id);
				return;
			}
			$result->free();
			/* We now check to see if the user is in an army, and if
			   so we identify the pointer for army, division and rank
			   location in the $armies array. We also work out the users
			   tags. */
			if($this->army_id) {
				for($i = 0; $i < $campaign->num_armies; $i++) {
					if($this->army_id == $armies[$i]['army']->id) {
						$this->army_ptr = $i;
						if($user->data['user_id'] == $armies[$i]['army']->general) {
							$this->is_hc = TRUE;
							$this->is_officer = TRUE;
						}
						continue;
					}
				}
				for($i = 0; $i < $armies[$this->army_ptr]['army']->num_divs; $i++) {
					if($this->division_id == $armies[$this->army_ptr]['divisions'][$i]->id) {
						$this->division_ptr = $i;
						if($user->data['user_id'] == $armies[$this->army_ptr]['divisions'][$i]->commander) {
							$this->is_dc = TRUE;
							$this->is_officer = TRUE;
						}
						if($armies[$this->army_ptr]['divisions'][$i]->is_hc)
							$this->is_hc = TRUE;
						continue;
					}
				}
				for($i = 0; $i < $armies[$this->army_ptr]['army']->num_ranks; $i++) {
					if($this->rank_id == $armies[$this->army_ptr]['ranks'][$i]->id) {
						$this->rank_ptr = $i;
						if($armies[$this->army_ptr]['ranks'][$i]->is_officer)
							$this->is_officer = TRUE;
						continue;
					}
				}
				$tag_array = str_split($armies[$this->army_ptr]['army']->tag_structure);
				$army_tag = str_split($armies[$this->army_ptr]['army']->tag);
				$div_tag = str_split($armies[$this->army_ptr]['divisions'][$this->division_ptr]->tag);
				$rank_tag = str_split($armies[$this->army_ptr]['ranks'][$this->rank_ptr]->tag);
				$a = 0;
				$d = 0;
				$r = 0;
				foreach($tag_array as $t) {
					switch($t) {
						default:
							$this->tags .= $t;
							break;
						
						case 'A':
							$this->tags .= $army_tag[$a++];
							break;
						
						case 'D':
							$this->tags .= $div_tag[$d++];
							break;
						
						case 'R':
							$this->tags .= $rank_tag[$r++];
							break;
					}
				}
			}
		} //End of if($campaign->is_running)
		/* The last stage is to check if a user is also an admin for
		   ABC. This is done whether a campaign is running or not. */
		$query = "SELECT 
			admin_id 
			FROM abc_admins 
			WHERE user_id = " . (int)$user_id;
		$result = $mysqli->query($query);
		if($result->num_rows) {
			$this->admin_id = $result->fetch_field();
			$this->is_admin = TRUE;
		}
	}
	
	/**
	 * Output: Soldier Info
	 *
	 * This function outputs the relevant information in the Soldier
	 * Info box on the right of the page.
	 */
	function output_soldier_info() {
		global $armies, $campaign, $user;
		//Global information show no matter what
		echo '<table>
			<tr>
				<th>Username:</th>
				<td>' . $user->data['username'] . '</td>
			</tr>' . PHP_EOL;
		if($campaign->is_running) {
			/* Output primarily changes based on whether the user is 
			   in an army or not. */
			if($this->army_id) {
				echo '<tr>
					<td colspan="2">You have been assigned to ' . $armies[$this->army_ptr]['army']->name . '. Don&rsquo;t forget to check your forums for more information.</td>
				</tr>
				<tr>
					<th>Tags:</th>
					<td>' . $this->tags . '</td>
				</tr>
				<tr>
					<th>Rank:</th>
					<td>' . $armies[$this->army_ptr]['ranks'][$this->rank_ptr]->name . '</td>
				</tr>
				<tr>
					<th>Division:</th>
					<td>' . $armies[$this->army_ptr]['divisions'][$this->division_ptr]->name . '</td>
				</tr>
				<tr>
					<th>TS Pass:</th>
					<td>' . (empty($armies[$this->army_ptr]['army']->ts_pw) ? 'No password set' : $armies[$this->army_ptr]['army']->ts_pw) . '</td>
				</tr>' . PHP_EOL;
			} else {
				/* If the user is not in an army then either show sign 
				   up button or message stating their application is
				   pending. */
				if($this->is_signed_up && $campaign->state < 4) {
					echo '<tr>
						<td colspan="2">You have been entered into the draft, which is scheduled to take place on ' . date('D jS M', $campaign->draft_date) . '.<br /><br />
						If you no longer wish to be a part of the draft you may remove yourself by <a href="abc_soldier_remove.php">clicking here</a></td>
					</tr>' . PHP_EOL;
				} elseif($this->is_signed_up) {
					echo '<tr>
						<td colspan="2">Your application is currently pending. Please check back soon to see which army you have been assigned to.</td>
					</tr>' . PHP_EOL;
				} elseif($campaign->state <> 4) {
					echo '<tr>
						<td colspan="2">' . ($campaign->state < 4 ? 'The campaign will be starting soon, but before you can take part you must join the draft.' : 'The campaign is currently underway. However don&rsquo;t dispair, you can still take part. Just sign up and a TA will assign you to an army.') . '</td>
					</tr><tr>
						<td colspan="2"><input type="button" name="soldier_sign_up" id="soldier_sign_up_btn" value="' . ($campaign->state < 4 ? 'Enter Draft' : 'Join Campaign') . '" /></td>
					</tr>' . PHP_EOL;
				} else {
					echo '<tr>
						<td colspan="2">You cannot sign up during the draft. Please wait until the end of the draft and then refresh this page.</td>
					</tr>' . PHP_EOL;
				}
			}
		} else {
			echo '<tr>
				<td colspan="2">There is currently no campaign running. Please check back soon.</td>
			</tr>' . PHP_EOL;
		}
		echo '</table>' . PHP_EOL;
	}
	
	/**
	 * Output: Soldier Info
	 *
	 * This function outputs the relevant information in the Soldier
	 * signup box .
	 */
	
	function output_soldier_rank() {
		global $armies, $campaign, $user;
		//Global information show no matter what
		echo  $armies[$this->army_ptr]['ranks'][$this->rank_ptr]->name . ' ' . $user->data['username'];
			}
	
	/**
	 * Save
	 *
	 * Takes an array of information to store directly to the database or
	 * save whatever is currently set in the class variables.
	 * @$input	array of information to save. Key must match column name.
	 */
	function save($input = FALSE) {
		global $mysqli;
		$query = "UPDATE abc_users SET ";
		$user_id = (int)$this->id; //If an ID is passed in the array we update that ID instead.
		$start = "";
		if(is_array($input)) {
			foreach($input as $k => $v) {
				//First check to see if the input is the ID to be updated.
				if($k == "abc_user_id") {
					$user_id = (int)$v;
				} else {
					//Next check to make sure that the input is a valid column.
					$valid_cols = array(
						'army_id',
						'division_id',
						'rank_id',
						'user_medals_sig',
						'user_is_signed_up',
						'user_bf3_name', 
						'user_availability', 
						'user_location', 
						'user_vehicles', 
						'user_other_notes', 
						'user_time_stamp'
					);
					if(in_array($k, $valid_cols)) {
						$string_vars = array("user_medals_sig", "user_bf3_name", "user_availability", "user_location", "user_vehicles", "user_other_notes");
						$query .= $start . $k . " = " . (in_array($k, $string_vars) ? "'" . $mysqli->real_escape_string($v) . "'" : (int)$v);
						$start = ", ";
					}
				}
			}
		} else {
			$query .= "army_id = " . (int)$this->army_id . ", 
			division_id = " . (int)$this->division_id . ", 
			rank_id = " . (int)$this->rank_id . ", 
			user_medals_sig = '" . $mysqli->real_escape_string($this->medals_sig) . "', 
			user_is_signed_up = " . (int)$this->is_signed_up . ", 
			user_bf3_name = '" . $mysqli->real_escape_string($this->bf3_name) . "', 
			user_availability = '" . $mysqli->real_escape_string($this->availability) . "', 
			user_location = '" . $mysqli->real_escape_string($this->location) . "', 
			user_vehicles = '" . $mysqli->real_escape_string($this->vehicles) . "', 
			user_other_notes = '" . $mysqli->real_escape_string($this->other_notes) . "',
			Role = '" . $mysqli->real_escape_string($this->Role) . "', 
			user_time_stamp = " . time();
		}
		$query .= " WHERE abc_user_id = $user_id";
		if(!$mysqli->query($query)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
}
?>