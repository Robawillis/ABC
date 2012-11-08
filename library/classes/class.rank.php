<?php
/**
 * CLASS: Rank
 *
 * Contains all information about a rank.
 */

class Rank {
	
	public $army_id		= -1;
	public $army_ptr	= -1;	//Pointer holding the class army# the army_id is in
	public $id			= -1;
	public $img			= "";	//String containing relative URL to the ranks image
	public $is_officer	= 0;	//Users with this rank are officers in the army
	public $name		= "";
	public $order		= -1;	//Rank order, 1 being General and 99 being new recruit
	public $phpbb_id	= -1;
	public $short		= "";	//Short version of the ranks name, normally the initials
	public $tag			= "";
	public $time_stamp	= -1;	//Time stamp of last update
	
	/**
	 * Class constructor
	 *
	 * @$data		array of results from database
	 * @$pointer	pointer for array $armies. Points to the army this 
	 * 				rank belongs to.
	 */
	function Rank($data, $pointer) {
		foreach($data as $k => $v) {
			$k = str_replace("rank_", "", $k);
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
		//First we store the rank in the forums and get the forum ID.
		$query = "INSERT INTO phpbb_ranks
			(rank_title, rank_min, rank_special, rank_image) VALUES 
			('" . $mysqli->real_escape_string($this->army_id . "." . $this->name) . "', 0, 1, '" . $mysqli->real_escape_string(str_replace("images/cache/ranks/", "", $this->img)) . "')";
		if($mysqli->query($query))
			$this->phpbb_id = $mysqli->insert_id;
		else
			return $mysqli->error;
		//Then we store the rank in ABC.
		$query = "INSERT INTO abc_ranks 
			(rank_phpbb_id, army_id, rank_name, rank_short, rank_order, rank_is_officer, rank_img, rank_tag, rank_time_stamp) VALUES 
			(" . (int)$this->phpbb_id . ", 
			" . (int)$this->army_id . ", 
			'" . $mysqli->real_escape_string($this->name) . "', 
			'" . $mysqli->real_escape_string($this->short) . "', 
			" . (int)$this->order . ", 
			" . (int)$this->is_officer . ",
			'" . $mysqli->real_escape_string($this->img) . "', 
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
		$query = "DELETE FROM phpbb_ranks WHERE rank_id = " . (int)$this->phpbb_id . ";
		DELETE FROM abc_ranks WHERE rank_id = " . (int)$this->id . ";";
		if($mysqli->multi_query($query))
			return FALSE;
		else
			return $mysqli->error;
	}
	
	/**
	 * Load Number of Soldiers
	 *
	 * Loads the number of soldiers currently using this ranks. Used in
	 * army_ranks.php to stop ranks currently in use from being deleted.
	 */
	function load_num_soldiers() {
		global $mysqli;
		$query = "SELECT count(*) FROM abc_users WHERE rank_id = " . (int)$this->id;
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
		$query = "UPDATE phpbb_ranks SET
			rank_title = '" . $mysqli->real_escape_string($this->army_id . "." . $this->name) . "', 
			rank_image = '" . $mysqli->real_escape_string(str_replace("images/cache/ranks/", "", $this->img)) . "' 
			WHERE rank_id = " . (int)$this->phpbb_id . ";";
		$query .= "UPDATE abc_ranks SET 
			army_id = " . (int)$this->army_id . ", 
			rank_name = '" . $mysqli->real_escape_string($this->name) . "', 
			rank_short = '" . $mysqli->real_escape_string($this->short) . "', 
			rank_order = " . (int)$this->order . ", 
			rank_is_officer = " . (int)$this->is_officer . ", 
			rank_img = '" . $mysqli->real_escape_string($this->img) . "', 
			rank_tag = '" . $mysqli->real_escape_string($this->tag) . "', 
			rank_time_stamp = " . time() . " 
			WHERE rank_id = " . (int)$this->id . ";";
		if($mysqli->multi_query($query))
			return FALSE;
		else
			return $mysqli->error;
	}
}