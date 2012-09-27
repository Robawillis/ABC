<?php
/**
 * CLASS: Army
 *
 * Contains all information on an army in the current campaign. One 
 * instance of the class is created for each army and its divisions.
 * Also holds rank and medal information as classes.
 */

class Army {
	
	public $colour					= ""; //String containing HEX code of armies colour
	public $general					= -1; //PHPBB ID of General
	public $id						= -1;
	public $is_neutral				= FALSE; //Army is neutral, does not have results in battle days
	public $join_pw					= ""; //Password to join the army
	public $name					= "";
	public $tag						= "";
	public $tag_structure			= ""; //String containing format for army tags
	public $time_stamp				= -1; //Time stamp of last update
	public $ts_pw					= ""; //Password to join TeamSpeak channels
	
	public $hc_forum_group			= -1; //PHPBB ID of the HC group
	public $officer_forum_group		= -1; //PHPBB ID of the officer group
	public $soldiers_forum_group	= -1; //PHPBB ID of the soldiers group
	
	public $num_divs				= -1; //Armies are created with 2 divs, except neutral army
	public $num_ranks				= -1;
	public $num_soldiers			= -1; //Set by class.sign_ups
	
	/**
	 * Class constructor
	 *
	 * @$data	array of results from database
	 */
	function Army($data = array()) {
		global $campaign;
		foreach($data as $k => $v) {
			$k = str_replace("army_", "", $k);
			$this->$k = $v;
		}
		if($this->join_pw)
			$campaign->army_join_pw = TRUE;
	}
	
	/**
	 * Create
	 *
	 * Create new army using the variables above.
	 */
	function create() {
		global $campaign, $mysqli;
		$query = "INSERT INTO abc_armies 
			(campaign_id, 
			army_name, 
			army_general, 
			army_colour, 
			army_tag_structure, 
			army_tag, 
			army_join_pw, 
			army_ts_pw, 
			army_is_neutral, 
			army_hc_form_group, 
			army_officer_forum_group, 
			army_soldiers_forum_group, 
			army_time_stamp) 
			VALUES 
			(" . (int)$campaign->id . ", 
			'" . $mysqli->real_escape_string($this->name) . "', 
			" . (int)$this->general . ", 
			'" . $mysqli->real_escape_string($this->colour) . "', 
			'" . $mysqli->real_escape_string($this->tag_structure) . "', 
			'" . $mysqli->real_escape_string($this->tag) . "', 
			'" . $mysqli->real_escape_string($this->join_pw) . "', 
			'" . $mysqli->real_escape_string($this->ts_pw) . "', 
			" . (int)$this->is_neutral . ", 
			" . (int)$this->hc_forum_group . ", 
			" . (int)$this->officer_forum_group . ", 
			" . (int)$this->soldiers_forum_group . ", 
			" . time() . ")";
		return $query;
	}
	
	/**
	 * Save
	 *
	 * Save the current variables into the database.
	 */
	function save() {
		global $campaign, $mysqli;
		if($this->id > 0)
			$query = "UPDATE abc_armies SET 
				army_name = '" . $mysqli->real_escape_string($this->name) . "', 
				army_general = " . (int)$this->general . ", 
				army_colour = '" . $mysqli->real_escape_string($this->colour) . "', 
				army_tag_structure = '" . $mysqli->real_escape_string($this->tag_structure) . "', 
				army_tag = '" . $mysqli->real_escape_string($this->tag) . "', 
				army_join_pw = '" . $mysqli->real_escape_string($this->join_pw) . "', 
				army_ts_pw = '" . $mysqli->real_escape_string($this->ts_pw) . "', 
				army_is_neutral = " . (int)$this->is_neutral . ", 
				army_hc_forum_group = " . (int)$this->hc_forum_group . ", 
				army_officer_forum_group = " . (int)$this->officer_forum_group . ", 
				army_soldiers_forum_group = " . (int)$this->soldiers_forum_group . ", 
				army_time_stamp = " . time() . "
				WHERE army_id = " . (int)$this->id;
		else
			$query = $this->create();
		$mysqli->query($query);
		if($mysqli->errno) {
			echo "There was an error saving an army. Please notify an admin and ask them to check the database connection.";
			exit;
		}
		if($this->id < 1)
			$this->id = $mysqli->insert_id;
	}
	
}
?>