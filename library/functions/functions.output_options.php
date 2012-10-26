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

/**
 * battles
 *
 * Outputs all the battles. Optional selector for having a none choice at 
 * the top.
 * @$none	Boolian variable. If set to true then a none option is at the 
 *			top with a value of 0. Default set to TRUE.
 * @$s		Int variable. During the for loop if the current army ID 
 *			matches then the option will be selected.
 */
function oo_battles($s = 0, $none = FALSE) {
	global $battles, $campaign;
	if($none)
		echo '<option value="0">None</option>';		
	foreach($battles['battle'] as $bat){
		echo '<option value="' . $bat->id . '"' . ($s == $bat->id ? ' selected="selected"' : '') . '>' . $bat->name . ' , ' . date("d-M-y", $bat->start) . '</option>';
		
		}
}

/**
 * battles
 *
 * Outputs all the battles. Optional selector for having a none choice at 
 * the top.
 * @$none	Boolian variable. If set to true then a none option is at the 
 *			top with a value of 0. Default set to TRUE.
 * @$s		Int variable. During the for loop if the current army ID 
 *			matches then the option will be selected.
 */
function oo_indatebattles($s = 0, $none = FALSE) {
	global $battles, $campaign;
	if($none)
		echo '<option value="0">None</option>';		
	foreach($battles['battle'] as $bat){
		if(date("d-m-y", time()) <= date("d-m-y", $bat->start)){
		echo '<option value="' . $bat->id . '"' . ($s == $bat->id ? ' selected="selected"' : '') . '>' . $bat->name . ' , ' . date("d-M-y", $bat->start) . '</option>';
		}
		}
}

/**
 * Signups
 * <input type="hidden" name="signup" value=".$battle_sign[$i]'
 * Outputs all the users who have signedup for the specfic battle
 * <input type="checkbox" name="hour_[][]" id="player' . $b .'-hour' . $i .'" value="hour' .  $i .' checked="checked"  disabled=true />
 * <input type="checkbox" name="hour_[][]" id="player' . $b .'-hour' . $i .'" value="hour' .  $i .'   disabled=true />
 */

function oo_signups() {
	global $batm ,$signed_up, $battle_sign, $user;
	for($b = ((int)$batm->length - 1) ; $b >= 0; $b--){
	$totalhours[$b] = 0;
	}
	for($i = 0; $i < count($battle_sign); $i++){
	if($battle_sign[$i]['battle']->soldiers[0]['username'] == $user->data['username']){
	echo '<TBODY CLASS="user"><tr>
	<td> <input type="hidden" name="battle_signid" value="' . $i . '"/>
	<input type="hidden" name="signup_id" value="' . $battle_sign[$i]['battle']->id . '"/>' . $battle_sign[$i]['battle']->soldiers[0]['rank_name'] . '    ' . $battle_sign[$i]['battle']->soldiers[0]['username']. ' </td> ';
	} else {
	echo '<tr>
	<td> ' . $battle_sign[$i]['battle']->soldiers[0]['rank_name'] . '    ' . $battle_sign[$i]['battle']->soldiers[0]['username']. ' </td> '; } 
	for($b = ((int)$batm->length - 1) ; $b >= 0; $b--){
	if(($battle_sign[$i]['battle']->hours[$b] != 1) && ($battle_sign[$i]['battle']->soldiers[0]['username'] == $user->data['username'])){
	echo '<td bgcolor=#ffff00> <input type="checkbox" name="signuphour_' . $b .'" value="1" /> </td>';
	$signed_up = 1;
	} else if(($battle_sign[$i]['battle']->hours[$b] == 1) && ($battle_sign[$i]['battle']->soldiers[0]['username'] == $user->data['username'])) {
	$signed_up = 1;
	$totalhours[$b] = $totalhours[$b] + 1;
	echo '<td bgcolor=#ffff00> <input type="checkbox" name="signuphour_' . $b .'" value="1" checked="checked" /> </td>';
	} else if($battle_sign[$i]['battle']->hours[$b] == 1) {
	echo '<td bgcolor=#ffff00> <input type="checkbox" name="hour_[][]" id="player' . $b .'-hour' . $i .'" value="1" checked="checked" disabled="true" /> </td>';
	$totalhours[$b] = $totalhours[$b] + 1;
	} else {
	echo '<td> <input type="checkbox" name="hour_[][]" id="player' . $b .'-hour' . $i .'" value="hour' .  $i .'"   disabled="true" /> </td>';
	}
	}
	echo '</tr>
	</TBODY>' ;	
	}
	if ($signed_up == 1){
	echo '<tr>
	<td> Total Players: </td>';
	for($b = ((int)$batm->length - 1) ; $b >= 0; $b--){
	echo '<td>  ' .  $totalhours[$b] . ' </td>';
	}
	}
	
}

function oo_signups_old() {
	global $batm ,$signed_up, $battle_sign, $user;
	for($b = ((int)$batm->length - 1) ; $b >= 0; $b--){
	$totalhours[$b] = 0;
	}
	for($i = 0; $i < count($battle_sign); $i++){
	echo '<tr>
	<td> ' . $battle_sign[$i]['battle']->soldiers[0]['rank_name'] . '    ' . $battle_sign[$i]['battle']->soldiers[0]['username']. ' </td> ';  
	for($b = ((int)$batm->length - 1) ; $b >= 0; $b--){
	if($battle_sign[$i]['battle']->hours[$b] == 1) {
	echo '<td bgcolor=#ffff00> <input type="checkbox" name="hour_[][]" id="player' . $b .'-hour' . $i .'" value="1" checked="checked" disabled="true" /> </td>';
	$totalhours[$b] = $totalhours[$b] + 1;
	} else {
	echo '<td> <input type="checkbox" name="hour_[][]" id="player' . $b .'-hour' . $i .'" value="hour' .  $i .'"   disabled="true" /> </td>';
	}
	}
	echo '</tr>' ;	
	}
	echo '<tr>
	<td> Total Players: </td>';
	for($b = ((int)$batm->length - 1) ; $b >= 0; $b--){
	echo '<td>  ' .  $totalhours[$b] . ' </td>';
		}
	
	
	}

?>