<?php
/**
 * CLASS: Army Management
 *
 * Contains all information needed for managing the army
 */

class Army_management {
	
	public $data		= array();	//Array of divisions with their soldiers
	
	/**
	 * Class constructor
	 *
	 * Load all soldiers into an array, sort by division.
	 * @$pointer	Pointer to army in $armies
	 */
	function Army_management($pointer) {
		global $armies, $mysqli;
		for($i = 0; $i < $armies[$pointer]['army']->num_divs; $i++) {
			$data[$armies[$pointer]['divisions'][$i]->id] = array();
		}
		$query = "SELECT u.abc_user_id, u.division_id, pu.username, r.rank_name, r.rank_short, r.rank_img FROM abc_users u LEFT JOIN phpbb_users pu USING (user_id) LEFT JOIN abc_ranks r USING (rank_id) WHERE u.army_id = " . (int)$armies[$pointer]['army']->id . " ORDER BY r.rank_order, pu.username";
		$result = $mysqli->query($query);
		while($row = $result->fetch_assoc()) {
			$this->data[$row['division_id']][] = $row;
		}
	}
	
}
?>