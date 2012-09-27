<?php
/**
 * CLASS: Campaign
 *
 * Contains all information on the currently running campaign, or flags 
 * up no running campaign.
 * Campaigns have 8 states:
 * 0. Error
 * 1. Set up
 * 2. Draft sign up
 * 3. Coin flip
 * 4. Draft
 * 5. Campaign in progress
 * 6. Campaign over
 * 7. Locked and archived
 */

class Campaign {
	
	public $coin_choice			= -1;
	public $coin_result			= -1;
	public $draft_date			= -1;		//Time stamp containing date / time of draft
	public $draft_round			= -1;		//Keeps track of the number of draft rounds
	public $draftees_per_round	= -1;		//The number of people each army can draft per round
	public $id					= -1;
	public $is_group_draft		= FALSE;	//Allows users to sign up and be drafted in groups
	public $is_running			= FALSE;	//A campaign is running. Set to true if any campaign is currently in state 0-6
	public $name				= "";
	public $state				= -1; 
	public $time_stamp			= -1;		//Time stamp of last update
	
	public $num_armies			= -1;		//Stores the number of armies whilst they are being loaded
	public $num_signed_up		= 0;		//Stores the number of people currently signed up
	
	public $army_join_pw		= FALSE;	//TRUE if at least one army has a pw to join set
	
	public $states				= array(	//Array of campaign states. Template for use throughout the site.
								"Error Mode", 
								"Pre Draft", 
								"Draft Open", 
								"Coin Flip", 
								"Live Draft", 
								"Active Campaign", 
								"Campaign Over", 
								"Archive Campaign");
	
	/**
	 * Class constructor
	 *
	 * Detect if campaign is currently running and if so load the details.
	 */
	function Campaign() {
		global $mysqli;
		$query = "SELECT 
			campaign_id, 
			campaign_state, 
			campaign_name, 
			campaign_coin_choice, 
			campaign_coin_result, 
			campaign_draft_date, 
			campaign_draft_round, 
			campaign_draftees_per_round, 
			campaign_is_group_draft, 
			campaign_time_stamp 
			FROM abc_campaigns 
			WHERE campaign_state < 7 
			LIMIT 1";
		$result = $mysqli->query($query);
		if($result->num_rows) {
			$row = $result->fetch_assoc();
			foreach($row as $k => $v) {
				$k = str_replace("campaign_", "", $k);
				$this->$k = $v;
			}
			$this->is_running = TRUE;
			$query = "SELECT count(*) FROM abc_users WHERE user_is_signed_up = 1";
			if($result = $mysqli->query($query)) {
				$row = $result->fetch_row();
				$this->num_signed_up = $row[0];
			}
				
		}
	}
	
	/**
	 * Create
	 *
	 * Creates a new campaign based on the variables above.
	 */
	function create() {
		global $mysqli;
		$query = "INSERT INTO abc_campaigns 
			(campaign_state, 
			campaign_name, 
			campaign_draft_date, 
			campaign_draftees_per_round, 
			campaign_is_group_draft, 
			campaign_time_stamp) 
			VALUES 
			(" . (int)$this->state . ", 
			'" . $mysqli->real_escape_string($this->name) . "', 
			" . (int)$this->draft_date . ", 
			" . (int)$this->draftees_per_round . ", 
			" . (int)$this->is_group_draft . ", 
			" . time() . ")";
		return $query;
	}
	
	/**
	 * Save
	 *
	 * Saves the current variables into the database.
	 */
	function save() {
		global $mysqli;
		if($this->is_running)
			$query = "UPDATE abc_campaigns SET 
				campaign_state = " . (int)$this->state . ", 
				campaign_name = '" . $mysqli->real_escape_string($this->name) . "', 
				campaign_coin_choice = " . (int)$this->coin_choice . ", 
				campaign_coin_result = " . (int)$this->coin_result . ", 
				campaign_draft_date = " . (int)$this->draft_date . ", 
				campaign_draft_round = " . (int)$this->draft_round . ", 
				campaign_draftees_per_round = " . (int)$this->draftees_per_round . ", 
				campaign_is_group_draft = " . (int)$this->is_group_draft . ", 
				campaign_time_stamp = " . time() . " 
				WHERE campaign_id = " . (int)$this->id;
		else
			$query = $this->create();
		$mysqli->query($query);
		if($mysqli->errno) {
			echo "There was an error saving the campaign. Please notify an admin and ask them to check the database connection.";
			exit;
		}
		if(!$this->is_running) {
			$this->id = $mysqli->insert_id;
			$this->is_running = TRUE;
		}
	}
	
}
?>