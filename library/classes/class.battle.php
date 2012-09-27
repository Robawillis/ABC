<?php
/**
 * CLASS: Battle
 *
 * Contains all information about a battle.
 */

class Battle { 

   	public $id						= -1;
	public $is_bfi				    = FALSE; //Army is neutral, does not have results in battle days
	public $name					= "";
	public $time_stamp				= -1; //Time stamp of last update
	public $start    		        = -1; //start time for each battle
	public $length					= -1; //length for each battle
	
	public $num_bat					= 0;
	public $campaign_id				= 0;
	
	
	
	/**
	 * Class constructor 
	 *
	 * Detect if campaign is currently running and if so load the details.
	 */
	function Battle($data = array()) {
		foreach($data as $k => $v) {
		$k = str_replace("battle_", "", $k);
		$this->$k = $v;
		}
		
	}
	
	function load_num_battles() {
		global $mysqli;
		$query = "SELECT count(*) FROM abc_battles WHERE campaign_id = " . (int)$this->campaign_id;
		if($result = $mysqli->query($query)) {
			while($row = $result->fetch_row())
				$this->num_soldiers = $row[0];
		}
	}
		
	function delete() {
		global $mysqli;
		$query = "DELETE FROM abc_battles WHERE battle_id = " . (int)$this->id;
		if($mysqli->query($query))
			return FALSE;
		else
			return $mysqli->error;
	}

	
	/**
	 *
	 *Create
	 *
	 *Creates a new battle into the database using varibles set
	 *
	 *
	 **/
	
	
	function create() {
		global $mysqli;
		$query = "INSERT INTO abc_battles
			(campaign_id, battle_name, battle_start, battle_length, battle_is_bfi, battle_time_stamp) VALUES 
			(" . (int)$this->campaign_id . ", 
			'" . $mysqli->real_escape_string($this->name) . "', 
			" . (int)$this->start . ", 
			" . (int)$this->length . ",
			" . (int)$this->is_bfi . ", 
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
		$query = "UPDATE abc_battles SET 
			campaign_id = '" . (int)$this->campaign_id . "',
			battle_name = '" . $mysqli->real_escape_string($this->name) . "', 
			battle_start = " . (int)$this->start . ", 
			battle_length = " . (int)$this->length . ", 
			battle_is_bfi = " . (int)$this->is_bfi . ", 
			battle_time_stamp = " . time() . " 
			WHERE battle_id = " . (int)$this->id;
		if($mysqli->query($query))
			return FALSE;
		else
			return $mysqli->error;
	}
	
	/*function num_battles () {
	global $mysqli, $campaign;
	$query = "SELECT count(*) from abc_battles WHERE campaign_id = " . (int)$campaign->id;
	if($result = $mysqli->query($query)){
		while($row = $result->fetch_row())
		$this->num_battles = $row[0];	
	}
*/
}
?>