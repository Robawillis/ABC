<?php 
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require_once 'library/abc_start_up.php';
require_once 'library/classes/class.army_management.php';
require_once 'library/functions/functions.output_options.php';
require_once 'library/classes/class.battle.php';
require_once 'library/classes/class.battle_sign_up.php';
/* $army_to_manage contains the pointer to the armies location in the array. 
 * As armies are loaded in the same way every page it is safe to use this on
 * other pages. */
$army_to_manage = ($abc_user->is_admin && isset($_REQUEST['army'])) ? (int)$_REQUEST['army'] : $abc_user->army_ptr;
$army_management = new Army_management($army_to_manage);
$army_users = new Sign_ups ($army_to_manage);
$army_users->load_army_numbers();
$battle_to_manage = (isset($_REQUEST['bds-id'])) ? (int)$_REQUEST['bds-id'] : 0;
$signed_up = 0;


//Deal with submitted form
$form_head = "";
$form_msg = "";
$signed_hours = array();
$battle_sign = array();
if($battle_to_manage) {
	$bquery = "SELECT 
		battle_id, 
		campaign_id, 
		battle_name,
		battle_start,	
		battle_length, 
		battle_is_bfi, 
		battle_time_stamp 
		FROM abc_battles 
		WHERE battle_id = $battle_to_manage";
	if($result = $mysqli->query($bquery)) {
		while($row = $result->fetch_assoc()) {
		$batm = new Battle($row);
		}
	}
	
	$s_query = "SELECT
	b.sign_up_id,
	b.battle_id,
	b.user_id,
	b.sign_up_hours,
	b.sign_up_time_stamp
	FROM abc_battle_sign_ups b LEFT JOIN abc_users u USING (user_id) LEFT JOIN abc_ranks r USING (rank_id)
	WHERE b.battle_id = $battle_to_manage AND u.army_id = ". (int)$armies[$army_to_manage]['army']->id . " ORDER BY r.rank_order";
	if($s_result = $mysqli->query($s_query)) {
	$i = 0;
	while ($s_row = $s_result->fetch_assoc()){
	$battle_sign[$i]['battle'] = new Battle_sign_up($s_row);
	$i++;
		}
	}
}

if(isset($_POST['bd-s-submit'])) {
		for($i = 0; $i < ($batm->length); $i++) {		
		$signed_hours[$i] = (int)$_POST['signuphour_'. $i .''];
		}
		
		switch($_POST['bds-action']) {	
		case 'edit':
		$signed_hours = array_reverse($signed_hours);
		$shours = bindec(implode($signed_hours));
		$i = (int)$_POST['battle_signid'];
		$battle_sign[$i]['battle']->id = (int)$_POST['signup_id'];
		if ($shours == 0){
			if($battle_sign[$i]['battle']->delete()) {
				$form_head = 'Error!';
				$form_msg = 'There was an error. Please check the database connection.';
			} else {
				$form_head = 'Deleted Sign Up';
				$form_msg = 'Sign up successfully deleted.<meta http-equiv="refresh" content="3">';
			}
						
		} else 
			$battle_sign[$i]['battle']->battle_id = $battle_to_manage;
			$battle_sign[$i]['battle']->user_id = (int)$user->data['user_id'];
			$battle_sign[$i]['battle']->hours = (int)$shours;
			if($battle_sign[$i]['battle']->save()) {
				$form_head = 'Error!';
				$form_msg = 'There was an error. Please check the database connection.';
			} else {
				$form_head = 'Sign Up Confirmed';
				$form_msg = 'You have successfully signed up to this battle.<meta http-equiv="refresh" content="3">';
			}
		
			
			break;
		
		case 'new':
			$shours = bindec(implode($signed_hours));
			$battle_sign[0]['battle'] = new Battle_sign_up(array());
			$battle_sign[0]['battle']->battle_id = (int)$battle_to_manage;
			$battle_sign[0]['battle']->user_id = (int)$user->data['user_id'];
			$battle_sign[0]['battle']->hours = (int)$shours;
			if($battle_sign[0]['battle']->create()) {
				$form_head = 'Error!';
				$form_msg = 'There was an error. Please check the database connection.';
			} else {
				$form_head = 'Sign Up Confirmed';
				$form_msg = 'You have successfully signed up to this battle.<meta http-equiv="refresh" content="3">';
			} 
			break;
	}
	
}
	

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
		$(document).ready(function(e) {
            <?php if($abc_user->is_admin) { ?>
			$('#army-picker').change(function(e) {
                $(window).attr("location", "battleday_signup.php?army=" + $(this).val());
            });
			<?php } ?>
			$('#am-cb-action').change(function() {
				var p = $(this).val();
                $.ajax({
					type:	'POST',
					url:	'library/ajax/ajax.control_box.php',
					data:	{ p: p, a: <?php echo $army_to_manage; ?> },
					success: function(data) {
						$('#am-cb-ajax').html(data);
					}
				});
            });
        });
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
					<li><a href="battleday_signup.php">Battle Day Sign Up</a></li>
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
                    <div class="small-heading"><img src="images/icon_menu.png" align="left" />ABC ARMY MENU</div>
                    <ul>
                        <li><a href="index.php">ABC Home</a></li>
						<li><a href="battleday_signup.php">Battle Day Sign Up</a></li>
                    </ul>
                </div>
                <div class="content-left-box">
                    <div class="small-heading"><img src="images/icon_user.png" align="left" />SOLDIER INFO</div>
                    <?php $abc_user->output_soldier_info(); 
					
					?>
                </div>
            </div>
            <div class="content-middle">
                <div class="content-middle-box">
                <?php 
					if(isset($_POST['bd-s-submit'])) {
					//Display outcome of submitted form
                    echo '<div class="large-heading">' . $form_head . '</div> 
                    ' . $form_msg;
                    } else { ?>
                    <div class="large-heading">
                        <?php if($abc_user->is_admin) {
                            echo '<select name="army-picker" id="army-picker">' . PHP_EOL;
                            for($i = 0; $i < $campaign->num_armies; $i++) {
                                echo '<option value="' . $i . '"' . ($army_to_manage == $i ? ' selected="selected"' : '') . '>' . $armies[$i]['army']->name . '</option>' . PHP_EOL;
                            }
                            echo '</select>' . PHP_EOL;
                        } ?>
                    </div>
                    Please select the relevant battle to sign up.
					<div class="am-control-box">
                    <form name="amb" method="POST">
                        <label for="bat">Battle: </label>
                        <select name="bds-id" class="am-cb-select">
                        <?php oo_indatebattles($battle_to_manage, TRUE)?>
                        </select>
                        <input type="submit" name="submit_amb" value="Go" />
                    </form>
                    </div>
					<?php if((isset($batm)) && ((($abc_user->army_id == $armies[$army_to_manage]['army']->id)) || $abc_user->is_admin)){ ?>
					<div class="bd-sign">
					<div class="large-heading middle-text"> Battle: <?php echo $batm->name; ?></div>
					
					<form name="battle-signup" method="POST">
					<table class="bd-soldier-details">
					<tr>
					<td>
					</td>
					<td colspan="<?php echo (int)$batm->length; ?>" align=center>
					<font size="5"><B>Battle Hours in (SBT)</B></font>
					</td>
					</tr>
					<tr>
					<td>
					Signed Up Users:
					</td>
					<?php for($i = 0; $i < ((int)$batm->length); $i++) {
					echo '<td> SBT '  . $i . ' </td>' . PHP_EOL; 
					} ?>
					</tr>
					<?php 

	
					for($b = ((int)$batm->length - 1) ; $b >= 0; $b--){
					$totalhours[$b] = 0;
					}	
					for($i = 0; $i < count($battle_sign); $i++){
					if($battle_sign[$i]['battle']->soldiers[0]['username'] == $user->data['username']){
					echo '<tr class="user">
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
					echo '<td class="signed_up"> <input type="checkbox" name="hour_[][]" id="player' . $b .'-hour' . $i .'" value="1" checked="checked" disabled="true" /> </td>';
					$totalhours[$b] = $totalhours[$b] + 1;
					} else {
					echo '<td class="not_signed_up"> <input type="checkbox" name="hour_[][]" id="player' . $b .'-hour' . $i .'" value="hour' .  $i .'"   disabled="true" /> </td>';
					}
					}
					echo '</tr>
					' ;	
					}
					if ($signed_up == 1){
					echo '<tr>
					<td> Total Players: </td>';
					for($b = ((int)$batm->length - 1) ; $b >= 0; $b--){
					echo '<td>  ' .  $totalhours[$b] . ' </td>';
					}
					}
	

										
					
					?>
					</tr>
					<?php
					 if ($signed_up == 1){ 
					echo' <input type="hidden" name="bds-action" id="bds-action" value="edit" /> <input type="hidden" name="bds-id" id="bds-id" value="' . (int)$battle_to_manage . '" /> ' . PHP_EOL;
					} else if (($signed_up == 0) && ($abc_user->army_id == $armies[$army_to_manage]['army']->id)){	?>
					<input type="hidden" name="bds-action" id="bds-action" value="new" />
					<input type="hidden" name="bds-id" id="bds-id" value="<?php echo (int)$battle_to_manage; ?>" />
					<tr>
					<td width="100">
					<?php $abc_user->output_soldier_rank(); ?>
					</td>
					<?php for($i = 0; $i < ((int)$batm->length); $i++) {
					echo '<td><input type="checkbox" name="signuphour_' . $i .'" value="1" /></td> ' . PHP_EOL; 
					} ?>
					</tr>
					<?php } ?>
					</table>
					<br>
					<div class="middle-text">
					<input type="submit" name="bd-s-submit" value="Submit" />
					</div>
					</form>
					</div>	
							
                    
					
					<?php 
					
					} 
                	 } 
				  
                   ?>
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