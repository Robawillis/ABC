<?php 
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require_once 'library/abc_start_up.php';
require_once 'library/classes/class.army_management.php';
/* $army_to_manage contains the pointer to the armies location in the array. 
 * As armies are loaded in the same way every page it is safe to use this on
 * other pages. */
$army_to_manage = ($abc_user->is_admin && isset($_REQUEST['army'])) ? (int)$_REQUEST['army'] : $abc_user->army_ptr;
$army_management = new Army_management($army_to_manage);

//Deal with submitted form
$form_head = "";
$form_msg = "";
if(isset($_POST['am-cb-submit'])) {
	$array = array(
		'am-cb-action'		=> array(
			'filter'			=> FILTER_VALIDATE_INT,
			'options'			=> array(
				'min_range'			=> 1,
				'max_range'			=> 4
								)
							),
		'am-cb-decision'	=> FILTER_VALIDATE_INT,
		'am-checked'		=> array(
			'filter'			=> FILTER_VALIDATE_INT,
			'flags'				=> FILTER_REQUIRE_ARRAY
							)
	);
	$input = filter_input_array(INPUT_POST, $array);
	if($input['am-cb-action'] === FALSE || $input['am-cb-action'] === NULL) {
		$form_head = 'Error: No Action';
		$form_msg = 'No action was selected. Please <a href="javascript:history.go(-1);">go back</a> and select a valid action.';
	} elseif(count($input['am-checked']) < 1) {
		$form_head = 'Error: No Soldiers';
		$form_msg = 'No soldiers were selected. Please <a href="javascript:history.go(-1);">go back</a> and select at least one soldier.';
	} else {
		switch($input['am-cb-action']) {
			default:
				$form_head = 'Error: Unrecognised Action';
				$form_msg = 'An unregonised action was selected. Please <a href="javascript:history.go(-1);">go back</a> and select a valid action. If this message repeats please inform an admin.';
				break;
			
			case 1:
				//Change division
				if($input['am-cb-decision']) {
					/* First we need to determine if the new division is the HC division or not. */
					$query = "SELECT division_is_hc FROM abc_divisions WHERE division_id = " . (int)$input['am-cb-decision'];
					$result = $mysqli->query($query);
					$is_hc = 0;
					while($row = $result->fetch_row())
						$is_hc = $row[0];
					/* Next we only need to grab users if their HC division status has changed (joined or left). */
					$query = "SELECT user_id FROM abc_users LEFT JOIN abc_divisions USING (division_id) WHERE division_is_hc = " . ($is_hc ? 0 : 1) . " AND abc_user_id IN(";
					$start = "";
					for($i = 0; $i < count($input['am-checked']); $i++) {
						$query .= $start .  (int)$input['am-checked'][$i];
						$start = ", "; 
					}
					$query .= ")";
					$result = $mysqli->query($query);
					$users = array();
					while($row = $result->fetch_row())
						$users[] = $row[0];
					if(count($users)) {
						/* How the groups array is formatted depends on if we are adding or removing. */
						if($is_hc) {
							$groups = array($armies[$army_to_manage]['army']->hc_forum_group => 0);
							$phpbb_interaction->group_add_users($users, $groups, $armies[$army_to_manage]['army']->colour);
						} else {
							$groups = array($armies[$army_to_manage]['army']->hc_forum_group);
							$phpbb_interaction->group_remove_users($users, $groups);
						}
					}
					
					$query = "UPDATE abc_users SET division_id = " . (int)$input['am-cb-decision'] . ", user_time_stamp = " . time() . " WHERE abc_user_id IN(";
					$start = "";
					for($i = 0; $i < count($input['am-checked']); $i++) {
						$query .= $start .  (int)$input['am-checked'][$i];
						$start = ", "; 
					}
					$query .= ")";
					if($mysqli->query($query)) {
						$form_head = 'Success: Soldiers Moved';
						$form_msg = 'Soldiers have been moved successfuly. If this page does not automatically return to the previous page please <a href="army_management.php">click here</a>.
						<meta http-equiv="refresh" content="3">';
					} else {
						$form_head = 'Error: Could Not Save';
						$form_msg = 'There was an error saving the changes to the database. Please <a href="javascript:history.go(-1);">go back</a> and try again. If this message repeats please inform an admin.';
					}
				} else {
					$form_head = 'Error: No Division';
					$form_msg = 'Could not determine which division to move soldiers to. Please <a href="javascript:history.go(-1);">go back</a> and select a valid division.';
				}
				break;
			
			case 2:
				//Change rank
				if($input['am-cb-decision']) {
					/* Firstly we determine if the new rank is an officer rank or not. */
					$query = "SELECT rank_phpbb_id, rank_is_officer FROM abc_ranks WHERE rank_id = " . (int)$input['am-cb-decision'];
					$result = $mysqli->query($query);
					$phpbb_id = 0;
					$is_officer = 0;
					while($row = $result->fetch_row()) {
						$phpbb_id = $row[0];
						$is_officer = $row[1];
					}
					/* Next we only need to grab users if their officer status has changed (promoted or demoted). */
					$query = "SELECT user_id FROM abc_users LEFT JOIN abc_ranks USING (rank_id) WHERE rank_is_officer = " . ($is_officer ? 0 : 1) . " AND abc_user_id IN(";
					$start = "";
					for($i = 0; $i < count($input['am-checked']); $i++) {
						$query .= $start .  (int)$input['am-checked'][$i];
						$start = ", "; 
					}
					$query .= ")";
					$result = $mysqli->query($query);
					$users = array();
					while($row = $result->fetch_row())
						$users[] = $row[0];
					if(count($users)) {
						/* How the groups array is formatted depends on if we are adding or removing. */
						if($is_officer) {
							$groups = array($armies[$army_to_manage]['army']->officer_forum_group => 0);
							$phpbb_interaction->group_add_users($users, $groups, $armies[$army_to_manage]['army']->colour);
						} else {
							$groups = array($armies[$army_to_manage]['army']->officer_forum_group);
							$phpbb_interaction->group_remove_users($users, $groups);
						}
					}				
					
					$query = "UPDATE abc_users SET rank_id = " . (int)$input['am-cb-decision'] . ", user_time_stamp = " . time() . " WHERE abc_user_id IN(";
					$start = "";
					for($i = 0; $i < count($input['am-checked']); $i++) {
						$query .= $start .  (int)$input['am-checked'][$i];
						$start = ", "; 
					}
					$query .= ")";
					if($mysqli->query($query)) {
						$query = "SELECT user_id FROM abc_users WHERE abc_user_id IN(";
						$start = "";
						for($i = 0; $i < count($input['am-checked']); $i++) {
							$query .= $start . (int)$input['am-checked'][$i];
							$start = ", "; 
						}
						$query .= ")";
						$result = $mysqli->query($query);						
						$query = "UPDATE phpbb_users SET user_rank = $phpbb_id WHERE user_id IN("; 
						$start = "";
						while($row = $result->fetch_row()) {
							$query .= $start . $row[0];
							$start = ",";
						}
						$query .= ")";
						if($start = ",")
							$mysqli->query($query);
						$form_head = 'Success: Soldiers Promoted / Demoted';
						$form_msg = 'Soldiers have been promoted / demoted successfuly. If this page does not automatically return to the previous page please <a href="army_management.php">click here</a>.
						<meta http-equiv="refresh" content="3">';
					} else {
						$form_head = 'Error: Could Not Save';
						$form_msg = 'There was an error saving the changes to the database. Please <a href="javascript:history.go(-1);">go back</a> and try again. If this message repeats please inform an admin.';
						//Uncomment for debugging
						$form_msg .= "<br /><br />Query: $query<br /><br />Error: " . $mysqli->error;
					}
				} else {
					$form_head = 'Error: No Rank';
					$form_msg = 'Could not determine which rank to set the soldiers to. Please <a href="javascript:history.go(-1);">go back</a> and select a valid rank.';
				}
				break;
			
			case 3:
				//Award medal
				if($input['am-cb-decision']) {
					$query = "INSERT INTO abc_medal_awards (campaign_id, user_id, medal_id, award_time_stamp) VALUES";
					$limit = count($input['am-checked']);
					for($i = 0; $i < $limit; $i++) {
						$query .= "({$campaign->id}, {$input['am-checked'][$i]}, {$input['am-cb-decision']}, " . time();
						$query .= ($i < ($limit - 1)) ? "), " : ")";
					}
					if($mysqli->query($query)) {
  					for($i = 0; $i < $limit; $i++) {
  						ribbons($input['am-checked'][$i]);
  					}
						$form_head = 'Success: Medals Awarded';
						$form_msg = 'The medals have been awarded successfuly. If this page does not automatically return to the previous page please <a href="army_management.php">click here</a>.
						<meta http-equiv="refresh" content="3">';
					} else {
						$form_head = 'Error: Could Not Save';
						$form_msg = 'There was an error saving the changes to the database. Please <a href="javascript:history.go(-1);">go back</a> and try again. If this message repeats please inform an admin.';
					}
				} else {
					$form_head = 'Error: No Medal';
					$form_msg = 'Could not determine which medal to award to the soldiers. Please <a href="javascript:history.go(-1);">go back</a> and select a valid medal.';
				}
				break;
			
			case 4:
				//Remove from the army
				$users = array();
				$groups = array();
				foreach($input['am-checked'] as $u)
					$users[] = $u;
				if($armies[$army_to_manage]['army']->hc_forum_group)
					$groups[] = $armies[$army_to_manage]['army']->hc_forum_group;
				if($armies[$army_to_manage]['army']->officer_forum_group)
					$groups[] = $armies[$army_to_manage]['army']->officer_forum_group;
				if($armies[$army_to_manage]['army']->soldiers_forum_group)
					$groups[] = $armies[$army_to_manage]['army']->soldiers_forum_group;
				if(count($users) && count($groups))
					$phpbb_interaction->group_remove_users($users, $groups);
				$query = "UPDATE abc_users SET army_id = 0, division_id = 0, rank_id = 0, user_time_stamp = " . time() . " WHERE abc_user_id IN(";
				$start = "";
				for($i = 0; $i < count($input['am-checked']); $i++) {
					$query .= $start .  $input['am-checked'][$i];
					$start = ", "; 
				}
				$query .= ")";
				if($mysqli->query($query)) {
					$form_head = 'Success: Soldiers Removed';
					$form_msg = 'Soldiers have been removed from the army successfuly. If this page does not automatically return to the previous page please <a href="army_management.php">click here</a>.
					<meta http-equiv="refresh" content="3">';
				} else {
					$form_head = 'Error: Could Not Save';
					$form_msg = 'There was an error saving the changes to the database. Please <a href="javascript:history.go(-1);">go back</a> and try again. If this message repeats please inform an admin.';
				}
				break;
		}
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
		/* Controls the upcoming battles movements */
		var cur_battle = 0;
		var max_battle = <?php echo (count($bat_left_bar) - 1); ?>;
		$(document).ready(function(e) {
			$('.battle-left-window').css('height', $('.battle-left-wrapper').height());
			$('.battle-left-wrapper').css('width', ((max_battle + 1) * 211));
			if(max_battle == 1)
				$('.battle-left-next').hide();
		});
		$(document).on('click', '.battle-left-prev', function(e) {
			$('.battle-left-wrapper').animate({ left: '+=210' }, 250);
			cur_battle--;
			if(cur_battle == 0)
				$('.battle-left-prev').hide();
			if(max_battle > cur_battle && !$('.battle-left-next').is(':visible'))
				$('.battle-left-next').show();
		});
		$(document).on('click', '.battle-left-next', function(e) {
			$('.battle-left-wrapper').animate({ left: '-=210' }, 250);
			cur_battle++;
			if(cur_battle == max_battle)
				$('.battle-left-next').hide();
			if(cur_battle > 0 && !$('.battle-left-prev').is(':visible'))
				$('.battle-left-prev').show();
		});
		var FullscreenrOptions = {  width: 1920, height: 1080, bgID: '#bgimg' };
		jQuery.fn.fullscreenr(FullscreenrOptions);
		$(document).ready(function(e) {
            <?php if($abc_user->is_admin) { ?>
			$('#army-picker').change(function(e) {
                $(window).attr("location", "army_management.php?army=" + $(this).val());
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
                        <li><a href="army_management.php<?php if($abc_user->is_admin) echo '?army=' . $army_to_manage; ?>">Army Management</a></li>
						<li><a href="army_battleday_signup.php">Army Battle Signup Review</a></li>
                        <li><a href="army_divisions.php<?php if($abc_user->is_admin) echo '?army=' . $army_to_manage; ?>">Divisions</a></li>
                        <li><a href="army_medals.php<?php if($abc_user->is_admin) echo '?army=' . $army_to_manage; ?>">Medals</a></li>
                        <li><a href="army_ranks.php<?php if($abc_user->is_admin) echo '?army=' . $army_to_manage; ?>">Ranks</a></li>
                        <li><a href="army_soldiers.php<?php if($abc_user->is_admin) echo '?army=' . $army_to_manage; ?>">Soldiers</a></li>
                        <?php if($campaign->state == 4) { ?>
                        <li><a href="army_draft.php<?php if($abc_user->is_admin) echo '?army=' . $army_to_manage; ?>">Live Draft</a></li>
                        <?php } elseif($campaign->state > 1 && $campaign->state < 4 && $campaign->num_signed_up > 0) { ?>
                        <li><a href="army_draft.php<?php if($abc_user->is_admin) echo '?army=' . $army_to_manage; ?>">Draft List</a></li>
                        <?php } ?>
                    </ul>
                </div>
                <div class="content-left-box">
                    <div class="small-heading"><img src="images/icon_user.png" align="left" />SOLDIER INFO</div>
                    <?php $abc_user->output_soldier_info(); ?>
                </div>
                <?php if(count($bat_left_bar)) { ?>
                <div class="content-left-box">
                    <div class="small-heading"><img src="images/icon_menu.png" align="left" />UPCOMING BATTLES</div>
                    <div class="battle-left-window">
                    	<div class="battle-left-wrapper">
							<?php $i = 0;
                            foreach($bat_left_bar as $b => $a) { ?>
                            <div class="battle-left-battle" id="battle<?php echo $i; ?>">
                                <div class="battle-left-heading"><?php echo $b; ?></div>
                                <table width="210" cellpadding="0" cellspacing="0">
                                    <tr><td>
                                    <table style="width: 100px; float: left;">
                                        <tr>
                                        <?php foreach($a[$armies[0]['army']->name] as $hours) { ?>
                                            <td><?php echo $hours; ?></td>
                                        <?php } ?>
                                        </tr><tr>
                                        <?php foreach($a[$armies[0]['army']->name] as $hours) { ?>
                                            <td height="<?php echo ($max_sign_ups * 3); ?>" valign="bottom">
                                            	<div style="width: 4px; height: <?php echo ($hours * 3); ?>px; background-color: #<?php echo $armies[0]['army']->colour; ?>; margin: <?php echo ($max_sign_ups * 3) > ($hours * 3) ? (($max_sign_ups * 3) - ($hours * 3)) : 0; ?>px auto 0 auto;"></div>
                                            </td>
                                        <?php } ?>
                                        </tr><tr>
                                            <th colspan="<?php echo count($a[$armies[0]['army']->name]); ?>"><?php echo $armies[0]['army']->name; ?></td>
                                        </tr>
                                    </table>
                                    <table style="width: 100px; float: right;">
                                        <tr>
                                        <?php foreach($a[$armies[1]['army']->name] as $hours) { ?>
                                            <td><?php echo $hours; ?></td>
                                        <?php } ?>
                                        </tr><tr>
                                        <?php foreach($a[$armies[1]['army']->name] as $hours) { ?>
                                            <td height="<?php echo ($max_sign_ups * 3); ?>" valign="bottom">
                                            	<div style="width: 4px; height: <?php echo ($hours * 3); ?>px; background-color: #<?php echo $armies[1]['army']->colour; ?>; margin: <?php echo ($max_sign_ups * 3) > ($hours * 3) ? (($max_sign_ups * 3) - ($hours * 3)) : 0; ?>px auto 0 auto;"></div>
                                            </td>
                                        <?php } ?>
                                        </tr><tr>
                                            <th colspan="<?php echo count($a[$armies[0]['army']->name]); ?>"><?php echo $armies[1]['army']->name; ?></td>
                                        </tr>
                                    </table>
                                    </td></tr>
                                </table>
                            </div>
                            <?php $i++;
                            } ?>
                            <div class="clear"></div>
                        </div>
                    </div>
                    <div class="battle-left-controls">
                    	<span class="battle-left-prev">Previous</span>
                        <span class="battle-left-next">Next</span>
                    </div>
                </div>
                <?php } ?>
            </div>
            <div class="content-middle">
                <div class="content-middle-box">
                <?php if($abc_user->is_dc || $abc_user->is_hc || $abc_user->is_admin) {
					if(isset($_POST['am-cb-submit'])) { 
					//Display outcome of submitted form
                    echo '<div class="large-heading">' . $form_head . '</div>
                    ' . $form_msg;
                    } else { ?>
                    <div class="large-heading">
                        Army Management 
                        <?php if($abc_user->is_admin) {
                            echo '<select name="army-picker" id="army-picker">' . PHP_EOL;
                            for($i = 0; $i < $campaign->num_armies; $i++) {
                                echo '<option value="' . $i . '"' . ($army_to_manage == $i ? ' selected="selected"' : '') . '>' . $armies[$i]['army']->name . '</option>' . PHP_EOL;
                            }
                            echo '</select>' . PHP_EOL;
                        } ?>
                    </div>
                    <form name="am-update" id="am-update" method="POST">
                    <div class="am-control-box">
                    	<div class="small-heading">Control Box</div>
                        Check the soldiers you wish to effect in the divisions below and then use the controls here to modify all those soldiers at once.
                        <br /><br />
                        <label for="am-cb-action">Action:</label>
                        <select name="am-cb-action" id="am-cb-action" class="am-cb-select">
                        	<option value="0">Please Select...</option>
                            <option value="1"<?php if(!$abc_user->is_hc && !$abc_user->is_admin) echo ' disabled="disabled"'; ?>>Change Division</option>
                            <option value="2">Change Rank</option>
                            <!-- Medal system not yet implemented<option value="3">Award Medal</option>-->
                            <option value="4">Remove From Army</option>
                        </select>
                        <div class="left" id="am-cb-ajax"></div>
                        <input type="submit" name="am-cb-submit" value="Go" />
                        <div class="clear"></div>
                    </div>
							
                    <?php for($i = 0; $i < $armies[$army_to_manage]['army']->num_divs; $i++) {
                        //First and last division are special
                        if($i == 0) {
                            echo '<div class="army-div-hc">';
                        } elseif ($i == $armies[$army_to_manage]['army']->num_divs - 1) {
                            echo '<div class="clear"></div><div class="army-div-default">';
                        } else {
                            echo '<div class="army-div-normal">';
                        }
                        echo '
                            <div class="small-heading middle-text">' . $armies[$army_to_manage]['divisions'][$i]->name . '</div>
                            <table class="am-soldier-details">';
                            //Now we output each soldier from the army_management->data array
                            $div_id = $armies[$army_to_manage]['divisions'][$i]->id; //Get current divs ID.
                            for($j = 0; $j < @count($army_management->data[$div_id]); $j++) {
                                echo '
                                <tr>
                                    <td width="18"><input type="checkbox" name="am-checked[]" value="' . $army_management->data[$div_id][$j]['abc_user_id'] . '" /></td>
                                    <td width="25"><img src="' . $army_management->data[$div_id][$j]['rank_img'] . '" alt="Rank image" title="' . $army_management->data[$div_id][$j]['rank_name'] . '" /></td>
                                    <td><a href="army_soldiers.php?soldier=' . $army_management->data[$div_id][$j]['abc_user_id'] . '" title="Edit soldier">' . $army_management->data[$div_id][$j]['rank_short'] . ' ' . $army_management->data[$div_id][$j]['username'] . '</a></td>
                                </tr>';
                            }
                            echo '</table>
                        </div>';
                    } ?>
                    </form>
                	<?php } 
				} else { ?>
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