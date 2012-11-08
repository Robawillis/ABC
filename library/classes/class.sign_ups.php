<?php
/**
 * CLASS: Sign Ups
 *
 * Contains all information needed for managing sign ups
 */

class Sign_ups {
	
	public $soldiers		= array();	//Array of ABC IDs of those who have signed up
	public $num				= 0;		//Number of IDs in the $soldier array
	
	/**
	 * Class constructor
	 *
	 * Load all those who have the sign up flag set into the $soldier array
	 */
	function Sign_ups() {
		global $mysqli;
		$query = "SELECT a.abc_user_id, a.user_id, a.Role, p.username FROM abc_users a LEFT JOIN phpbb_users p USING (user_id) WHERE a.user_is_signed_up = 1 ORDER BY a.Role, p.username_clean ";
		$result = $mysqli->query($query);
		$this->num = $result->num_rows;
		while($row = $result->fetch_assoc()) {
			$this->soldiers[] = $row;
		}
	}
	
	/**
	 * Load Army Numbers
	 *
	 * Count the number of soldiers in each army and add an extra variable
	 * to the armies class for use in the admin_sign_ups page.
	 */
	/*function load_army_numbers($pointer) {
		global $armies, $campaign, $mysqli;
		$query = '';
		for($i = 0; $i < $campaign->num_armies; $i++) {
			$query = "SELECT count(*) FROM abc_users WHERE army_id = " . $armies[$i]['army']->id;
		}
		$i = 0;
		$query = "SELECT count(*) FROM abc_users WHERE army_id = " . (int)$armies[$pointer]['army']->id;
		$result = $mysqli->query($query);
		$armies[$pointer]['army']->num_soldiers = (int)$result;
	}
	*/
	
	function load_army_numbers() {
		global $armies, $campaign, $mysqli;
		$query = '';
		for($i = 0; $i < $campaign->num_armies; $i++) {
			$query .= "SELECT count(*) FROM abc_users WHERE army_id = " . $armies[$i]['army']->id . ";";
		}
		$i = 0;
		if($mysqli->multi_query($query)) {
			do {
				if($result = $mysqli->store_result()) {
					while($row = $result->fetch_row()) {
						$armies[$i++]['army']->num_soldiers = $row[0];
					}
				}
			} while($mysqli->next_result());
		}
	}
	/**
	*Load Soldiers for selected army
	*
	*/
	
	function Load_Army_Soldiers($pointer) {
		global $armies, $mysqli;
		for($i = 0; $i < $armies[$pointer]['army']->num_soldiers; $i++) {
			$armysoldiers[$armies[$pointer]['username'][$i]->id] = array();
		}
		$query = "SELECT a.abc_user_id, a.user_id, p.username FROM abc_users a LEFT JOIN phpbb_users p USING (user_id) LEFT JOIN abc_ranks r USING (rank_id) WHERE u.army_id = " . $armies[$pointer]['army']->id . " ORDER BY a.divison_id asc, r.rank_order asc";
		$result = $mysqli->query($query);
		while($row = $result->fetch_assoc()) {
			$this->armysoldiers[$row['username']][] = $row;
			
		}
	}
	
}
?>