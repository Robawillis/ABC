<?php
/**
 * CLASS: Battle
 *
 * Contains all information about a battle.
 */

class Battle { 

	public $campaign_id				= 0;
   	public $id						= -1;
	public $is_bfi				    = FALSE; //Army is neutral, does not have results in battle days
	public $length					= -1; //length for each battle
	public $name					= "";
	public $start    		        = -1; //start time for each battle
	public $time_stamp				= -1; //Time stamp of last update
	
	
	/**
	 * Class constructor 
	 *
	 * Take variables passed into $data and store them in public variables.
	 */
	function Battle($data = array()) {
		//Make sure the data passed is an array, otherwise abort
		if(!is_array($data))
			return 'NOT_ARRAY';
		foreach($data as $k => $v) {
			$k = str_replace("battle_", "", $k);
			$this->$k = $v;
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
	 * Create
	 *
	 * Creates a new battle in the database using varibles set
	 */
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
}
?>