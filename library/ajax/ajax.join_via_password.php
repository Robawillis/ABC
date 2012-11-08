<?php
/**
 * AJAX: Join via Password
 *
 * Used to check the password entered for joining an army by password.
 */

$army = filter_input(INPUT_POST, "army", FILTER_VALIDATE_INT);
$password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_STRING);
if(!$army || !$password) {
	echo 'MISSING_DATA';
	exit;
}

$phpbb_root_path = '../../../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require_once '../abc_start_up.php';
$ptr = 0;
for($i = 0; $i < $campaign->num_armies; $i++) {
	if($armies[$i]['army']->id == $army)
		$ptr = $i;
}

if($armies[$ptr]['army']->join_pw == $password) {
	if($abc_user->join_army($ptr))
		echo 'ERROR_SAVING';
	else
		echo 'SUCCESS';
} else
	echo 'WRONG_PASSWORD';
?>