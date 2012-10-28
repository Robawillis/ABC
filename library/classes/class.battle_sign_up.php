<?php
/**
 * CLASS: Battle Sign Up
 *
 * Contains information about a soldier for a specific battle.
 */

class Battle_sign_up {
	
	public $id   		= -1; //ID for each signup
	public $user_id 	= -1; //ID for each user
	public $battle_id	= -1; //ID for each battle
	public $hours 		= -1;  //sign up hours, have set to int for time being thinking of changing that to array for bitmask
	public $time_stamp	= -1;
	public $soldiers	= array(); //array to load every soldier details with each signup
	
	
	/**
	 * Class constructor 
	 *
	 * Take variables passed into $data and store them in public variables.
	 */
	function Battle_sign_up ($data = array()) {
		global $batm, $mysqli, $campaign;
		foreach($data as $k => $v) {
			$k = str_replace("sign_up_", "", $k);
			$this->$k = $v;
		}
		$query = "SELECT pu.username, r.rank_name, r.rank_short, r.rank_img FROM abc_users u LEFT JOIN phpbb_users pu USING (user_id) LEFT JOIN abc_ranks r USING (rank_id) WHERE u.user_id = " . (int)$this->user_id . " and u.campaign_id = " . (int)$campaign->id . "";
		$result = $mysqli->query($query);
		while($row = $result->fetch_assoc())
			$this->soldiers[] = $row;
		$this->hours = decbin($this->hours);
		$this->hours = str_split($this->hours);
		$this->hours = array_reverse($this->hours);
	}
	
	/**
	 * Create
	 *
	 * Creates a signup into the database using varibles set
	 */
	function create() {
		global $mysqli;
		$query = "INSERT INTO abc_battle_sign_ups
			(battle_id, user_id, sign_up_hours, sign_up_time_stamp) VALUES 
			(" . (int)$this->battle_id . ", 
			'" . (int)$this->user_id . "', 
			" . (int)$this->hours . ", 
			" . time() . ")";
		if($mysqli->query($query)) {
			$this->id = $mysqli->insert_id;
			return FALSE;
		} else
			return $mysqli->error;
			
		
	}
	
	/**
	 * Save
	 *
	 * Save the current information in the variables to the database.
	 */
	function save() {
		global $mysqli;
		$query = "UPDATE abc_battle_sign_ups SET 
			battle_id = '" . (int)$this->battle_id . "',
			user_id = '" . (int)$this->user_id . "', 
			sign_up_hours = " . (int)$this->hours . ", 
			sign_up_time_stamp = " . time() . " 
			WHERE sign_up_id = " . (int)$this->id;
		if($mysqli->query($query)) {
			return FALSE;
		} else
			return $mysqli->error;
	}
	
	/**
	 * Delete
	 *
	 * Delete the battle from the database.
	 */
	function delete() {
		global $mysqli;
		$query = "DELETE FROM abc_battle_sign_ups WHERE sign_up_id = " . (int)$this->id;
		if($mysqli->query($query)) {
			return FALSE;
		} else
			return $mysqli->error;
	}
	
}
?>