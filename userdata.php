<?php 
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require_once 'library/abc_start_up.php';
include_once 'library/functions/functions.output_options.php';
if($campaign->is_running) {
	$array = array("username" => $user->data['username'], "rank" => $armies[$abc_user->army_ptr]['ranks'][$abc_user->rank_ptr]->name, "division" => $armies[$abc_user->army_ptr]['divisions'][$abc_user->division_ptr]->name);
	echo json_encode($array);
} 
?>