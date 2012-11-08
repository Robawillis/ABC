<?php
/**
 * CLASS: Division
 *
 * Contains all information about a division.
 */

class Division {
	
	public $army_id			= -1;
	public $army_ptr		= -1; //Pointer holding the class army# the army_id is in
	public $commander		= -1; //PHPBB ID of current division commander
	public $id				= -1;
	public $is_default		= 0;  //Default division for new members assigned to the army
	public $is_hc			= 0;  //High command division
	public $name			= "";
	public $tag				= "";
	public $time_stamp		= -1; //Time stamp of last update
	
	public $num_soldiers	= 0;
	
	/**
	 * Class constructor
	 *
	 * @$data		array of results from database
	 * @$pointer	pointer for array $armies. Points to the army this 
	 * 				division belongs to.
	 */
	function Division($data, $pointer) {
		foreach($data as $k => $v) {
			$k = str_replace("division_", "", $k);
			$this->$k = $v;
		}
		$this->army_ptr = $pointer;
	}
	
	/**
	 * Create
	 *
	 * Insert a new division into the database using the information in
	 * the variables above.
	 */
	function create() {
		global $mysqli;
		$query = "INSERT INTO abc_divisions 
			(army_id, division_name, division_commander, division_is_default, division_is_hc, division_tag, division_time_stamp) VALUES 
			(" . (int)$this->army_id . ", 
			'" . $mysqli->real_escape_string($this->name) . "', 
			" . (int)$this->commander . ", 
			" . (int)$this->is_default . ",
			" . (int)$this->is_hc . ", 
			'" . $mysqli->real_escape_string($this->tag) . "', 
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
	 * Delete the division from the database.
	 */
	function delete() {
		global $mysqli;
		$query = "DELETE FROM abc_divisions WHERE division_id = " . (int)$this->id;
		if($mysqli->query($query))
			return FALSE;
		else
			return $mysqli->error;
	}
	
	/**
	 * Load Number of Soldiers
	 *
	 * Loads the number of soldiers currently in the division. Used in
	 * army_divisions.php to stop divisions with soldiers in from being
	 * deleted.
	 */
	function load_num_soldiers() {
		global $mysqli;
		$query = "SELECT count(*) FROM abc_users WHERE division_id = " . (int)$this->id;
		if($result = $mysqli->query($query)) {
			while($row = $result->fetch_row())
				$this->num_soldiers = $row[0];
		}
	}
	
	/**
	 * Save
	 *
	 * Save the current information in the variables to the database.
	 */
	function save() {
		global $mysqli;
		$query = "UPDATE abc_divisions SET 
			army_id = " . (int)$this->army_id . ", 
			division_name = '" . $mysqli->real_escape_string($this->name) . "', 
			division_commander = " . (int)$this->commander . ", 
			division_is_default = " . (int)$this->is_default . ", 
			division_is_hc = " . (int)$this->is_hc . ", 
			division_tag = '" . $mysqli->real_escape_string($this->tag) . "', 
			division_time_stamp = " . time() . " 
			WHERE division_id = " . (int)$this->id;
		if($mysqli->query($query))
			return FALSE;
		else
			return $mysqli->error;
	}
	
}
?>