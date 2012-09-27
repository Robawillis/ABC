<?php 
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require_once 'library/abc_start_up.php';
require_once 'library/classes/class.sign_ups.php';
require_once 'library/functions/functions.output_options.php';

$err_msg = "";
$err_hdr = "";

if(isset($_POST['asu_save'])) {
	/* First we load the default division and rank IDs, along with the 
	   soldiers group ID for each army. We use the army ID as the array  
	   pointer for ease of access later. */
	$div_ids = array();
	$rank_ids = array();
	$rank_php = array();
	$colours = array();
	$soldier_groups = array();
	for($i = 0; $i < $campaign->num_armies; $i++) {
		foreach($armies[$i]['divisions'] as $div) {
			if($div->is_default) {
				$div_ids[$armies[$i]['army']->id] = $div->id;
				break;
			}
		}
		foreach($armies[$i]['ranks'] as $rank) {
			if($rank->order == 99) {
				$rank_ids[$armies[$i]['army']->id] = $rank->id;
				$rank_php[$armies[$i]['army']->id] = $rank->phpbb_id;
				break;
			}
		}
		$colours[$armies[$i]['army']->id] = $armies[$i]['army']->colour;
		$soldier_groups[$armies[$i]['army']->id] = $armies[$i]['army']->soldiers_forum_group;
	}
	
	
	//Next we load all the information submitted by the admin.
	$array = array(
		'abc_user_id'	=> array(
			'filter'		=> FILTER_VALIDATE_INT,
			'flags'			=> FILTER_REQUIRE_ARRAY),
		'army_id'		=> array(
			'filter'		=> FILTER_VALIDATE_INT,
			'flags'			=> FILTER_REQUIRE_ARRAY),
		'user_id'		=> array(
			'filter'		=> FILTER_VALIDATE_INT,
			'flags'			=> FILTER_REQUIRE_ARRAY)
	);
	$finput = filter_input_array(INPUT_POST, $array);
	
	//We need to resort the array and add the default division and rank ID to the data.
	$input = array();
	$num_input = 0;
	$add_to_groups = array();
	for($i = 0; $i < count($finput['abc_user_id']); $i++) {
		if($finput['army_id'][$i]) {		
			$input[$num_input]['abc_user_id'] = $finput['abc_user_id'][$i];
			$input[$num_input]['user_id'] = $finput['user_id'][$i];
			$input[$num_input]['army_id'] = $finput['army_id'][$i];
			$input[$num_input]['division_id'] = $div_ids[$finput['army_id'][$i]];
			$input[$num_input]['rank_id'] = $rank_ids[$finput['army_id'][$i]];
			$input[$num_input]['rank_php'] = $rank_php[$finput['army_id'][$i]];
			$add_to_groups[$soldier_groups[$finput['army_id'][$i]]]['soldiers'][] = $finput['user_id'][$i];
			$add_to_groups[$soldier_groups[$finput['army_id'][$i]]]['colour'] = $colours[$finput['army_id'][$i]];
		}
	}
	
	//Finally we save the data.
	if(count($input)) {
		$query = "UPDATE abc_users SET army_id = CASE abc_user_id";
		foreach($input as $i) {
			$query .= " WHEN " . $i['abc_user_id'] . " THEN " . $i['army_id'];
		}
		$query .= " END, division_id = CASE abc_user_id";
		foreach($input as $i) {
			$query .= " WHEN " . $i['abc_user_id'] . " THEN " . $i['division_id'];
		}
		$query .= " END, rank_id = CASE abc_user_id";
		foreach($input as $i) {
			$query .= " WHEN " . $i['abc_user_id'] . " THEN " . $i['rank_id'];
		}
		$query .= " END, user_is_signed_up = 0, user_time_stamp = " . time() . " WHERE abc_user_id IN(";
		$start = "";
		foreach($input as $i) {
			$query .= $start . $i['abc_user_id'];
			$start = ", ";
		}
		$query .= ")";
		if($mysqli->query($query)) {
			//If the query is successfull we add the users to the forum groups.
			foreach($add_to_groups as $group => $users) {
				//First we make sure that the group has a valid ID.
				if($group && !$err_msg) {
					$arr_groups = array($group => 1);
					$arr_users = array();
					foreach($add_to_groups[$group]['soldiers'] as $user)
						$arr_users[] = $user;
					if($err = $phpbb_interaction->group_add_users($arr_users, $arr_groups, $add_to_groups[$group]['colour'])) {
						echo $err;
						switch($err) {
							default:
								$err_msg = "A database error has occured. If you are seeing this message then there is a good chance that some of the data was saved, so have someone check over the database for the individual users. Alternatively if you can still see them in the list below, try again later.";
								$err_hdr = "Database error";
								break;
								
							case 'MISSING_INFO':
								$err_msg = "An error occured in whilst adding the users to the forum groups. Please inform Styphon he fucked up with the group_add_users function and let him know what you were doing at the time. In the mean time you will need to manually add the people to their respective forum groups.";
								$err_hdr = "Failure adding to groups";
								break;
						}
					}
				}
			}
			
			//Next we set ranks in the forums
			$query = "UPDATE phpbb_users SET rank_id = CASE user_id";
			foreach($input as $i)
				$query .= " WHEN " . $i['user_id'] . " THEN " . $i['rank_php'];
			$query .= " END WHERE user_id IN (";
			$start = "";
			foreach($input as $i) {
				$query .= $start . $i['user_id'];
				$start = ", ";
			}
			$query .= ")";
			$mysqli->query($query);
		}
	}
}

$sign_ups = new Sign_ups();
$sign_ups->load_army_numbers();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Army Base Camp &bull; Army Management</title>
    <link rel="stylesheet" type="text/css" href="css/abc_style.css" />
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/jquery.fullscreen.js"></script>
    <script type="text/javascript" language="javascript">
		var FullscreenrOptions = {  width: 1920, height: 1080, bgID: '#bgimg' };
		jQuery.fn.fullscreenr(FullscreenrOptions);
	</script>
</head>

<body>
	<!-- Background image, uses the same image as the forum -->
	<img src="<?php echo $phpbb_root_path; ?>styles/DirtyBoard2/theme/images/bg_body.jpg" id="bgimg" />
    <div class="new-body">
        <div class="header">
            <div class="logo">
            </div>
            <div class="nav-bar">
                <ul>
                    <li><a href="../portal.php">Home</a></li>
                    <li><a href="../ucp.php">User Control Panel</a></li>
                    <li><a href="index.php">ABC Soldiers Home</a></li>
                    <?php if($abc_user->is_dc || $abc_user->is_hc || $abc_user->is_admin) { ?>
                    <li><a href="army_management.php">ABC Army Management</a></li>
                    <?php }
                    if($abc_user->is_admin) { ?>
                    <li><a href="admin_cp.php">ABC Admin CP</a></li>
                    <?php } ?>
                </ul>
            </div>
        </div>
        <div class="content">
            <div class="content-left">
                <div class="content-left-box">
                    <div class="small-heading"><img src="images/icon_menu.png" align="left" />ABC ADMIN MENU</div>
                    <ul>
                        <li><a href="index.php">ABC Home</a></li>
                        <li><a href="admin_cp.php">Admin CP</a></li>
                        <li><a href="admin_battles.php">Battles</a></li>
                        <li><a href="admin_users.php">Soldiers</a></li>
                        <li><a href="admin_sign_ups.php">Sign Ups</a></li>
                        <li><a href="admin_admins.php">Administrators</a></li>
                        <?php if($campaign->state == 4) { ?>
                        <li><a href="admin_draft.php">Live Draft</a></li>
                        <?php } ?>
                    </ul>
                </div>
                <div class="content-left-box">
                    <div class="small-heading"><img src="images/icon_user.png" align="left" />SOLDIER INFO</div>
                    <?php $abc_user->output_soldier_info(); ?>
                </div>
            </div>
            <div class="content-middle">
                <div class="content-middle-box">
                <?php if($abc_user->is_admin) { ?>
                    <div class="large-heading">Admin Sign Ups</div>
                    Current number of soldiers in each army.<br />
                    <?php for($i = 0; $i < $campaign->num_armies; $i++) {
                        echo '<strong>' . $armies[$i]['army']->name . ':</strong> ' . $armies[$i]['army']->num_soldiers . '<br />';
                    } ?>
                    <br />
                    <div class="asu-name asu-heading">Name</div><div class="asu-army asu-heading">Army</div>
                    <div class="clear"></div>
                    <form name="asu" method="POST">
                    <?php
					$sign_up_air = $sign_up_armour = $sign_up_infantry = array();
					for($c = 0; $c < count($sign_ups->soldiers); $c++){
						if($sign_ups->soldiers[$c]['Role'] == 'Air')
							$sign_up_air[] = $sign_ups->soldiers[$c];
						elseif($sign_ups->soldiers[$c]['Role'] == 'Armour')
							$sign_up_armour[] = $sign_ups->soldiers[$c];
						elseif($sign_ups->soldiers[$c]['Role'] == 'Infantry')
							$sign_up_infantry[] = $sign_ups->soldiers[$c];
					}					
					if(count($sign_up_air)) {
						echo ' <div class="small-heading"> Air Sign Ups </div> ';
						$a = 1;
						foreach($sign_up_air as $airsoldier) {
							echo '<div class="asu-soldier"><div class="asu-info">
								<strong>' . $a++ . ". " . htmlentities($airsoldier['username']) . '</strong> [<a href="http://battlelog.battlefield.com/bf3/user/' . $airsoldier['user_bf3_name'] . '" title="BF3 Soldier" target="_blank">' . $airsoldier['user_bf3_name'] . '</a>]<br />
								<strong>Availability</strong>: ' . $airsoldier['user_availability'] . '<br />
								<strong>Location</strong>: ' . $airsoldier['user_location'] . '<br />
								<strong>Vehicles</strong>: ' . $airsoldier['user_vehicles'] . '<br />
								<strong>Other Notes</strong>: ' . $airsoldier['user_other_notes'] . '<br />
								</div>
							<div class="asu-army"><select name="army_id[]">';
							oo_armies();
							echo '</select></div>
							<input type="hidden" name="abc_user_id[]" value="' . $airsoldier['abc_user_id'] . '">
							<input type="hidden" name="user_id[]" value="' . $airsoldier['user_id'] . '">
							<br clear="all" /><br />
							<div class="clear"></div></div>';
						}
					}
					if(count($sign_up_armour)){
						echo ' <div class="small-heading"> Armour Sign Ups </div> ';
						$ar = 1;
						foreach ($sign_up_armour as $armoursoldier){
							echo '<div class="asu-soldier"><div class="asu-info">
								<strong>' . $ar++ . ". " . htmlentities($armoursoldier['username']) . '</strong> [<a href="http://battlelog.battlefield.com/bf3/user/' . $armoursoldier['user_bf3_name'] . '" title="BF3 Soldier" target="_blank">' . $armoursoldier['user_bf3_name'] . '</a>]<br />
								<strong>Availability</strong>: ' . $armoursoldier['user_availability'] . '<br />
								<strong>Location</strong>: ' . $armoursoldier['user_location'] . '<br />
								<strong>Vehicles</strong>: ' . $armoursoldier['user_vehicles'] . '<br />
								<strong>Other Notes</strong>: ' . $armoursoldier['user_other_notes'] . '<br />
								</div>
							<div class="asu-army"><select name="army_id[]">';
							oo_armies();
							echo '</select></div>
							<input type="hidden" name="abc_user_id[]" value="' . $armoursoldier['abc_user_id'] . '">
							<input type="hidden" name="user_id[]" value="' . $armoursoldier['user_id'] . '">
							<br clear="all" /><br />
							<div class="clear"></div></div>';
						}
					}
					if(count($sign_up_infantry)){
						echo ' <div class="small-heading"> Infantry Sign Ups </div> ';
						$i = 1;
						foreach($sign_up_infantry as $infantrysoldier) {
							echo '<div class="asu-soldier"><div class="asu-info">
								<strong>' . $i++ . ". " . htmlentities($infantrysoldier['username']) . '</strong> [<a href="http://battlelog.battlefield.com/bf3/user/' . $infantrysoldier['user_bf3_name'] . '" title="BF3 Soldier" target="_blank">' . $infantrysoldier['user_bf3_name'] . '</a>]<br />
								<strong>Availability</strong>: ' . $infantrysoldier['user_availability'] . '<br />
								<strong>Location</strong>: ' . $infantrysoldier['user_location'] . '<br />
								<strong>Vehicles</strong>: ' . $infantrysoldier['user_vehicles'] . '<br />
								<strong>Other Notes</strong>: ' . $infantrysoldier['user_other_notes'] . '<br />
								</div>
							<div class="asu-army"><select name="army_id[]">';
							oo_armies();
							echo '</select></div>
							<input type="hidden" name="abc_user_id[]" value="' . $infantrysoldier['abc_user_id'] . '">
							<input type="hidden" name="user_id[]" value="' . $infantrysoldier['user_id'] . '">
							<br clear="all" /><br />
							<div class="clear"></div></div>';
						}
					}	?>
                        <input type="submit" name="asu_save" value="Submit" />
                    </form>
                    <div class="clear"></div>
					
                <?php  } else { ?>
                    <div class="large-heading">Unauthorised access!</div>
                    You do not have permission to view this page.
                <?php } ?>
                </div>
            </div>
            <div class="clear"></div>
        </div>
        <div class="footer">
        </div>
    </div>
</body>
</html>
<?php $mysqli->close(); ?>