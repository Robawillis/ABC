<?php
/**
 * CLASS: PHPBB Interaction
 *
 * NOTE: This file must be included at the top of any ABC file, otherwise no 
 * information on the user will be available and it is assumed a user is not 
 * logged in.
 * Deals with all interaction with PHPBB, from grabing a loged in users details 
 * to updating forums and groups.
 */

class phpbb_interaction {
	
	private $user_search_js = FALSE; //User search javascript code only needs outputing once, this keeps track of whether it has been output or not.
	
	/**
	 * Convert Username / ID
	 *
	 * Takes a username and returns both. If provided with a single 
	 * variable it will return a single variable. If provided with an 
	 * array it will return an array.
	 * @$username	Var or array of username(s)
	 * @$id			Var or array of user ID(s)
	 *
	 * NOTE: This will return results in ID order, not in the order the
	 * query is submitted. Be mindful of that when processing the results.
	 */
	function convert_user($username = FALSE, $id = FALSE) {
		global $mysqli;
		$return = '';
		if(!$username && !$id) {
			return FALSE;
		}
		/* We must check to see if the input provided is in an array,
		   and if not put it in an array. */
		if($username !== FALSE && !is_array($username))
			$username = array($username);
		if($id !== FALSE && !is_array($id))
			$id = array($id);
		$query = "SELECT username, user_id FROM phpbb_users WHERE " . ($id === FALSE ? "username IN(" : "user_id IN(");
		$start = "";
		if($id === FALSE) {
			foreach($username as $u) {
				$query .= $start . "'" . $mysqli->real_escape_string($u) . "'";
				$start = ", ";
			}
		} else {
			foreach($id as $u) {
				$query .= $start . (int)$u;
				$start = ", ";
			}
		}
		$query .= ")";
		$result = $mysqli->query($query);
		if(!$result->num_rows) {
			return FALSE;
		} elseif($result->num_rows > 1) {
			$return = array();
			while($row = $result->fetch_assoc()) {
				$return[] = $row;
			}
		} else {
			while($row = $result->fetch_assoc()) {
				$return = ($id === FALSE ? $row['user_id'] : $row['username']);
			}
		}
		return $return;
	}
	
	/**
	 * Group: Add Users
	 *
	 * Add user(s) to forum group(s).
	 * @$users	Array of PHPBB IDs of users to add to the group(s).
	 			Format as: array(0 => [user_id]).
	 * @$groups	Array of group IDs which the users are to be added and
	 *			whether the group is to be the default for the user(s).
	 *			Format as: array([group_id] => [is_default]).
	 * @$colour	If the group is default set the users to this colour.
	 */
	function group_add_users($users, $groups, $colour) {
		global $mysqli;
		$default = FALSE;
		/* First we need to make sure that something has been passed to 
		   the function. */
		if(!is_array($users) || !is_array($groups))
			return 'MISSING_INFO';
		$query = "INSERT INTO phpbb_user_group (group_id, user_id, user_pending) VALUES";
		$start = " (";
		/* Next we need to add the data to the query. We need to add 
		   every user to all the groups passed. */
		foreach($groups as $g => $d) {
			if($d)
				$default = TRUE;
			foreach($users as $u) {
				if($g > 0 && $u > 0) {
					$query .= $start . (int)$g . "," . (int)$u . ",0)";
					$start = ", (";
				}
			}
		}
		if($start == ", (") {
			$mysqli->query($query);
			if($mysqli->error)
				return $mysqli->error;
		} else {
			return 'MISSING_INFO';
		}
		/* Now we set any default groups if necessary. */
		if($default) {
			$query = "UPDATE phpbb_users SET user_colour = '" . $mysqli->real_escape_string($colour) . "', group_id = ";
			foreach($groups as $g => $d) {
				if($d) {
					$query .= (int)$d . " WHERE user_id IN";
					$start = " (";
					foreach($users as $u) {
						if($u) {
							$query .= $start . (int)$u;
							$start = ", ";
						}
					}
					if($start == ", ") {
						$query .= ")";
						$mysqli->query($query);
						if($mysqli->error)
							return $mysqli->error;
					}
				}
			}
		}
		return FALSE;		
	}
	
	/**
	 * Group: Remove Users
	 *
	 * Remove user(s) from forum group(s).
	 * @$users	Array of PHPBB IDs of users to remove from the group(s).
	 			Format as: array(0 => [user_id]).
	 * @$groups	Array of group IDs which the users are to be removed.
	 *			Format as: array(0 => [group_id]).
	 */
	function group_remove_users($users, $groups) {
		global $mysqli;
		/* First we need to make sure that something has been passed to 
		   the function. */
		if(!is_array($users) || !is_array($groups))
			return 'MISSING_INFO';
		
		/* Next we update any default group for users who have one of 
		   the groups they are being removed from set as default. */
		$query = "UPDATE phpbb_users SET group_id = 273 WHERE user_id IN(";
		$start = "";
		foreach($users as $u) {
			if($u) {
				$query .= $start . $u;
				$start = ",";
			}
		}
		if($start == "")
			return 'NO_USERS';
		$query .= ") AND group_id IN(";
		$start = "";
		foreach($groups as $g) {
			if($g) {
				$query .= $start . $g;
				$start = ",";
			}
		}
		if($start == "")
			return 'NO_GROUPS';
		$query .= ")";
		if(!$mysqli->query($query))
			return $mysqli->error;
		
		/* Finally we remove people from the groups. */
		$query = "DELETE FROM phpbb_user_group WHERE user_id IN(";
		$start = "";
		foreach($users as $u) {
			if($u) {
				$query .= $start . $u;
				$start = ",";
			}
		}
		$query .= ") AND group_id IN(";
		$start = "";
		foreach($groups as $g) {
			if($g) {
				$query .= $start . $g;
				$start = ",";
			}
		}
		$query .= ")";
		if($mysqli->query($query))
			return FALSE;
		else
			return $mysqli->error;
	}
	
	/**
	 * Replace General
	 *
	 * Removes the old general from forum groups and adds the new general
	 * as the group leader.
	 * @$old	PHPBB ID of the old general.
	 * @$new	PHPBB ID of the new general.
	 */
	function replace_general($old, $new) {
	}
	
	/**
	 * User Search
	 *
	 * Outputs all the necessary code for a user search.
	 * @$form	The form the input is part of.
	 * @$field	The id of the input.
	 */
	function user_search($form, $field) {
		global $phpbb_root_path;
		if(!$this->user_search_js) {
			echo "<script type=\"text/javascript\" language=\"javascript\">
				function find_username(url) {
					popup(url, 800, 770, '_usersearch');
					return false;
				}
				
				function popup(url, width, height, name) {
					if(!name) {
						name = '_popup';
					}
				
					window.open(url.replace(/&amp;/g, '&'), name, 'height=' + height + ',resizable=yes,scrollbars=yes, width=' + width);
					return false;
				}
			</script>";
			$this->user_search_js = TRUE;
		}
		echo '<a href="' . $phpbb_root_path . 'memberlist.php?mode=searchuser&amp;form=' . $form . '&amp;field=' . $field . '&amp;select_single=true" onclick="find_username(this.href); return false;">Find Soldier</a>';
	}

}
$phpbb_interaction = new phpbb_interaction;
?>