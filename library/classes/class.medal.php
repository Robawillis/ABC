<?php
/**
 * CLASS: Medal
 *
 * Contains all information about a medal.
 */

class Medal {

	public $army_id		= -1;
	public $id			= -1;
	public $img			= ""; //String containing relative URL to image used when displaying detailed info
	public $name		= "";
	public $ribbon		= ""; //String containing relative URL to image used in medal signatures
	public $time_stamp	= -1; //Time stamp of last update

	/**
	 * Class constructor
	 *
	 * @$data		array of results from database
	 * @$pointer	pointer for array $armies. Points to the army this 
	 * 				rank belongs to.
	 */
	function Medal($data, $pointer) {
		foreach($data as $k => $v) {
			$k = str_replace("medal_", "", $k);
			$this->$k = $v;
		}
		$this->army_ptr = $pointer;
	}


	/**
	 * Create
	 *
	 * Insert a new medal into the database using the information in
	 * the variables above.
	 */
	function create() {
		global $mysqli;
		$query = "INSERT INTO abc_medals 
			(army_id, medal_name, medal_img, medal_ribbon, medal_time_stamp) VALUES 
			(" . (int)$this->army_id . ", 
			'" . $mysqli->real_escape_string($this->name) . "', 
			'" . $mysqli->real_escape_string($this->img) . "', 
			'" . $mysqli->real_escape_string($this->ribbon) . "',
			" . time() . ")";
		if($mysqli->query($query)) {
			$this->id = $mysqli->insert_id;
			return FALSE;
		} else
			return $mysqli->error;
	}

	/**
	 * Delete
	 *
	 * Delete the medal from the database.
	 */
	function delete() {
		global $mysqli;
		$query = "DELETE FROM abc_medals WHERE medal_id = " . (int)$this->id . "; DELETE FROM abc_medal_awards WHERE medal_id = " . (int)$this->id . ";";
		if($mysqli->multi_query($query))
			return FALSE;
		else
			return $mysqli->error;
	}

	/**
	 * Save
	 *
	 * Save the current information in the variables to the database.
	 */
	function save() {
		global $mysqli;
		$query .= "UPDATE abc_medals SET 
			army_id = " . (int)$this->army_id . ", 
			medal_name = '" . $mysqli->real_escape_string($this->name) . "', 
			medal_img = '" . $mysqli->real_escape_string($this->img) . "',
			medal_ribbon = '" . $mysqli->real_escape_string($this->ribbon) . "', 
			medal_time_stamp = " . time() . " 
			WHERE medal_id = " . (int)$this->id . ";";
		if($mysqli->multi_query($query))
			return FALSE;
		else
			return $mysqli->error;
	}	
}