<?php
/**
 * FUNCTIONS: Output Options
 *
 * Contains a selection of function that output options for comonly used 
 * select inputs.
 */

/**
 * Armies
 *
 * Outputs all the armies. Optional selector for having a none choice at 
 * the top.
 * @$none	Boolian variable. If set to true then a none option is at the 
 *			top with a value of 0. Default set to TRUE.
 * @$s		Int variable. During the for loop if the current army ID 
 *			matches then the option will be selected.
 */
function oo_armies($none = TRUE, $s = 0) {
	global $campaign, $armies;
	if($none)
		echo '<option value="0">None</option>';
	for($i = 0; $i < $campaign->num_armies; $i++)
		echo '<option value="' . $armies[$i]['army']->id . '"' . ($s == $armies[$i]['army']->id ? ' selected="selected"' : '') . '>' . $armies[$i]['army']->name . '</option>';
}

/**
 * Armies with passwords
 *
 * Outputs all the armies with join passwords set, with the army ID as 
 * the value.
 */
function oo_armies_with_passwords() {
	global $campaign, $armies;
	echo '<option value="0">Choose Army</option>';
	for($i = 0; $i < $campaign->num_armies; $i++) {
		if($armies[$i]['army']->join_pw)
			echo '<option value="' . $armies[$i]['army']->id . '">' . $armies[$i]['army']->name . '</option>';
	}
}

/**
 * Divisions
 *
 * Outputs all the divisions for the specified army pointer.
 * @$p		Pointer to the army whos divisions we're outputting.
 * @$s		Int variable. During the for loop if the current division ID 
 *			matches then the option will be selected.
 * @$none	Boolian variable. If set to true then a none option is at the 
 *			top with a value of 0. Default set to FALSE.
 */
function oo_divisions($p, $s = 0, $none = FALSE) {
	global $armies;
	if($none)
		echo '<option value="0">None</option>';
	foreach($armies[$p]['divisions'] as $div)
		echo '<option value="' . $div->id . '"' . ($s == $div->id ? ' selected="selected"' : '') . '>' . $div->name . '</option>';
}

/**
 * Ranks
 *
 * Outputs all the ranks for the specified army pointer.
 * @$p		Pointer to the army whos ranks we're outputting.
 * @$s		Int variable. During the for loop if the current rank ID 
 *			matches then the option will be selected.
 * @$none	Boolian variable. If set to true then a none option is at the 
 *			top with a value of 0. Default set to FALSE.
 */
function oo_ranks($p, $s = 0, $none = FALSE) {
	global $armies;
	if($none)
		echo '<option value="0">None</option>';
	foreach($armies[$p]['ranks'] as $rank)
		echo '<option value="' . $rank->id . '"' . ($s == $rank->id ? ' selected="selected"' : '') . '>' . $rank->name . '</option>';
}

/**
 * Medals
 *
 * Outputs all the medals for the specified army pointer.
 * @$p		Pointer to the army whos ranks we're outputting.
 * @$s		Int variable. During the for loop if the current rank ID 
 *			matches then the option will be selected.
 * @$none	Boolian variable. If set to true then a none option is at the 
 *			top with a value of 0. Default set to FALSE.
 */
function oo_medals($p, $s = 0, $none = FALSE) {
	global $armies;
	if($none)
		echo '<option value="0">None</option>';
	foreach($armies[$p]['medals'] as $medal)
		echo '<option value="' . $medal->id . '"' . ($s == $medal->id ? ' selected="selected"' : '') . '>' . $medal->name . '</option>';
}

/**
 * States
 *
 * Outputs all the states of campaigns, selecting the current state.
 */
function oo_states() {
	global $campaign;
	for($i = 1; $i < count($campaign->states); $i++) {
		echo '<option value="' . $i . '"' . ($campaign->state == $i ? ' selected="selected"' : '') . '>' . $campaign->states[$i] . '</option>';
	}
	echo '<option value="0"' . ($campaign->state == 0 ? ' selected="selected"' : '') . '>' . $campaign->states[0] . '</option>';
}

/**
 * Tags
 *
 * Outputs the possible choices for tag structure.
 * @$cur	Current tag selected.
 */
function oo_tags($cur) {
	$array = array(
		"A"	=> "Army Tag",
		"D" => "Divisional Tag",
		"R" => "Rank Tag"
	);
	foreach($array as $v => $t)
		echo '<option value="' . $v . '"' . ($cur == $v ? ' selected="selected"' : '') . '>' . $t . '</option>';
}
?>