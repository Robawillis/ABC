<?php 
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require_once 'library/abc_start_up.php';
require_once 'library/functions/functions.output_options.php';
$army_to_manage = ($abc_user->is_admin && isset($_REQUEST['army'])) ? (int)$_REQUEST['army'] : $abc_user->army_ptr;
$div_to_edit = (isset($_REQUEST['div'])) ? (int)$_REQUEST['div'] : 0;
$msg_head = '';
$msg_body = '';

if($div_to_edit) {
	$query = "SELECT 
		division_id, 
		army_id, 
		division_name, 
		division_commander, 
		division_is_default, 
		division_is_hc, 
		division_tag, 
		division_time_stamp 
		FROM abc_divisions 
		WHERE division_id = $div_to_edit";
	if($result = $mysqli->query($query)) {
		while($row = $result->fetch_assoc()) {
			$div = new Division($row, -1);
			$div->load_num_soldiers();
		}
	}
}

if(isset($_POST['action'])) {
	switch($_POST['action']) {
		case 'del':
			if($div->delete()) {
				$msg_head = 'Error!';
				$msg_body = 'There was a problem deleting the division. Please check the database connection.';
			} else {
				$msg_head = 'Division Deleted';
				$msg_body = 'The division has been successfully deleted.';
				unset($div);
			}
			break;
		
		case 'edit':
			$div->name = $_POST['division_name'];
			$div->tag = $_POST['division_tag'];
			if($div->save()) {
				$msg_head = 'Error!';
				$msg_body = 'There was a problem whilst trying to save your changes. Please check the database connection.';
			} else {
				$msg_head = 'Division Updated';
				$msg_body = 'The division has been successfully updated.';
			}
			break;
		
		case 'new':
			$div = new Division(array(), -1);
			$div->name = $_POST['division_name'];
			$div->army_id = $armies[$army_to_manage]['army']->id;
			$div->commander = 0;
			$div->tag = $_POST['division_tag'];
			if($div->create()) {
				$msg_head = 'Error!';
				$msg_body = 'There was an error creating your new division. Please check the database connection.';
			} else {
				$msg_head = 'Division Created';
				$msg_body = 'The division has been successfully created.';
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
    <link rel="stylesheet" type="text/css" href="css/jqueryui/jquery-ui.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="css/jquery.colorpicker.css" media="screen" />
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/jquery-ui.js"></script>
    <script type="text/javascript" src="js/jquery.colorpicker.js"></script>
    <script type="text/javascript" src="js/jquery.fullscreen.js"></script>
    <script type="text/javascript" language="javascript">
		/* Controls the upcoming battles movements */
		var cur_battle = 1;
		var max_battle = <?php echo (count($bat_left_bar)); ?>;
		$(document).ready(function(e) {
			$('.battle-left-window').css('height', $('.battle-left-wrapper').height());
			$('.battle-left-wrapper').css('width', ((max_battle + 1) * 211));
			if(max_battle == 1)
				$('.battle-left-next').hide();
		});
		$(document).on('click', '.battle-left-prev', function(e) {
			$('.battle-left-wrapper').animate({ left: '+=210' }, 250);
			cur_battle--;
			if(cur_battle == 1)
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
                $(window).attr("location", "army_divisions.php?army=" + $(this).val());
            });
			<?php } ?>
			<?php if($msg_body) { ?>
			setTimeout(function() {
				$('#msg-box').slideUp(500);
			}, 5000);
			<?php } 
			if(isset($div)) { ?>
			$('#del-btn').click(function(e) {
				<?php if($div->num_soldiers) { ?>
				alert("You cannot delete a divisions whilst it still has soldiers in it and this division still has <?php echo $div->num_soldiers; ?> soldier(s) in it.\nPlease remove them and then come back and delete this division.");
				<?php } else { ?>
				if(confirm("Once deleted you cannot undo this action. Are you sure you wish to continue?")) {
					$('#amd-action').val('del');
					$('#amd-div').submit();
				}
				<?php } ?>
			});
			<?php } ?>
        });
		
		function add() {
			<?php if(isset($div)) { ?>
			$('.amd-edit').slideUp(500);
			<?php } ?>
			$('.amd-new').slideDown(500);
		}
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
				<?php if($msg_body) { ?>
                <div class="content-middle-box" id="msg-box">
                	<div class="large-heading"><?php echo $msg_head; ?></div>
                    <?php echo $msg_body; ?>
                </div>
                <?php } ?>
                <div class="content-middle-box">
                <?php if($abc_user->is_hc || $abc_user->is_admin) { ?>
                    <div class="large-heading">
                        Army Divisions
                        <?php if($abc_user->is_admin) {
                            echo '<select name="army-picker" id="army-picker">' . PHP_EOL;
                            for($i = 0; $i < $campaign->num_armies; $i++) {
                                echo '<option value="' . $i . '"' . ($army_to_manage == $i ? ' selected="selected"' : '') . '>' . $armies[$i]['army']->name . '</option>' . PHP_EOL;
                            }
                            echo '</select>' . PHP_EOL;
                        } ?>
					</div>		
                    <div class="am-control-box">
                    <form name="amd" method="POST">
                        <label for="div">Division: </label>
                        <select name="div" class="am-cb-select">
                        <?php oo_divisions($army_to_manage, $div_to_edit, TRUE); ?>
                        </select>
                        <input type="submit" name="submit_amd" value="Go" />
                        <input type="button" name="new" value="New" onclick="add();" />
                    </form>
                    </div>
                	<?php if(isset($div)) { ?>
                    <br />
                    <div class="amd-edit">
                    <form name="amd-div" id="amd-div" method="POST">
                    	<input type="hidden" name="action" id="amd-action" value="edit" />
                        <input type="hidden" name="div" value="<?php echo $div->id; ?>" />
                    	<label for="division_name">Name: </label>
                        <input type="text" name="division_name" id="division_name" value="<?php echo $div->name; ?>" required="required" />
                        <label for="division_tag">Tag: </label>
                        <input type="text" name="division_tag" id="division_tag" value="<?php echo $div->tag; ?>" required="required" maxlength="3" />
                        <input type="button" name="delete" value="Delete" id="del-btn" />
                        <input type="submit" name="submit_div" value="Save" />
                    </form>
                    </div>
                    <?php } ?>
                    <br />
                    <div class="amd-new">
                    <form name="amd-new" id="amd-new" method="POST">
                    	<input type="hidden" name="action" id="amd-action" value="new" />
                    	<label for="division_name">Name: </label>
                        <input type="text" name="division_name" id="division_name" required="required" />
                        <label for="division_tag">Tag: </label>
                        <input type="text" name="division_tag" id="division_tag" required="required" maxlength="3" />
                        <input type="submit" name="submit_new" value="Create" />
                    </form>
                    </div>
				<?php } else { ?>
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