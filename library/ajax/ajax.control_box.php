<?php
/**
 * AJAX: Control Box
 *
 * Used by army_management.php. Updates the second label and select based on the action.
 * NOTE: The option value MUST be an integer.
 */

$p = filter_input(INPUT_POST, "p", FILTER_VALIDATE_INT);
$a = filter_input(INPUT_POST, "a", FILTER_VALIDATE_INT);
if($p === FALSE || $p === NULL || $a === FALSE || $a === NULL) exit;

$phpbb_root_path = '../../../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require_once '../abc_start_up.php';

switch($p) {
	//0 or unrecognised number
	default:
		echo '&nbsp;';
		break;
	
	//Change division
	case 1:
		echo '<select name="am-cb-decision" id="am-cb-decision" class="am-cb-select">';
		for($i = 0; $i < $armies[$a]['army']->num_divs; $i++) {
			//echo out the rank, if the user is not an admin, and the rank order is higher than the users current rank disable the option
			echo '<option value="' . $armies[$a]['divisions'][$i]->id . '">' . $armies[$a]['divisions'][$i]->name . '</option>';
		}
		echo '</select>';
		break;
	
	//Change rank
	case 2:
		echo '<select name="am-cb-decision" id="am-cb-decision" class="am-cb-select">';
		for($i = 0; $i < $armies[$a]['army']->num_ranks; $i++) {
			//echo out the rank, if the user is not an admin, and the rank order is higher than the users current rank disable the option
			echo '<option value="' . $armies[$a]['ranks'][$i]->id . '"' . (($abc_user->is_admin || $armies[$abc_user->army_ptr]['ranks'][$abc_user->rank_ptr]->order < $armies[$a]['ranks'][$i]->order) ? '' : ' disabled="disabled"') . '>' . $armies[$a]['ranks'][$i]->name . '</option>';
		}
		echo '</select>';
		break;
	
	
	//Award medal
	case 3:
		
		break;
	
	case 4:
		echo '&nbsp;';
		break;
}
?>