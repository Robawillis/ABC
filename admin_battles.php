<?php 
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require_once 'library/abc_start_up.php';
require_once 'library/functions/functions.output_options.php';
require_once 'library/classes/class.battle.php';
$battle_to_edit = (isset($_REQUEST['bat'])) ? (int)$_REQUEST['bat'] : 0;
$msg_head = '';
$msg_body = '';

if($battle_to_edit) {
	$query = "SELECT 
		battle_id, 
		campaign_id, 
		battle_name,
		battle_start,	
		battle_length, 
		battle_is_bfi, 
		battle_time_stamp 
		FROM abc_battles 
		WHERE battle_id = $battle_to_edit";
	if($result = $mysqli->query($query)) {
		while($row = $result->fetch_assoc()) {
			$bat = new Battle($row);
			$bat->load_num_battles();
		}
	}
}

if(isset($_POST['action'])) {
	switch($_POST['action']) {
		case 'del':
			if($bat->delete()) {
				$msg_head = 'Error!';
				$msg_body = 'There was a problem deleting the battle. Please check the database connection.';
			} else {
				$msg_head = 'Battle Deleted';
				$msg_body = 'The battle has been successfully deleted.';
				unset($bat);
			}
			break;
		
		case 'edit':
			$bat->name = $_POST['battle_name'];
			$bat->start = strtotime($_POST['battle_date']);
			$bat->length = $_POST['battle_length'];
			$bat->is_bfi = isset($_POST['battle_is_bfi']) ? 1 : 0;
			$bat->save();
			if($bat->save()) {
				$msg_head = 'Error!';
				$msg_body = 'There was a problem whilst trying to save your changes. Please check the database connection.';
			} else {
				$msg_head = 'Battle Updated';
				$msg_body = 'The battle has been successfully updated.';
			}
			break;
		
		case 'new':
			$bat = new Battle(array());
			$bat->name = $_POST['battle_name'];
			$bat->start = strtotime($_POST['battle_date']);
			$bat->length = $_POST['battle_length'];
			$bat->is_bfi = isset($_POST['battle_is_bfi']) ? 1 : 0;
			$bat->campaign_id = $campaign->id;
			if($bat->create()) {
				$msg_head = 'Error!';
				$msg_body = 'There was an error creating your new Battle. Please check the database connection.';
			} else {
				$msg_head = 'Battle Created';
				$msg_body = 'The battle has been successfully created.';
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
		var FullscreenrOptions = {  width: 1920, height: 1080, bgID: '#bgimg' };
		jQuery.fn.fullscreenr(FullscreenrOptions);
		$(document).ready(function(e) {
		$('#battle_date').datepicker({ dateFormat: "dd-mm-yy" });
		<?php if($msg_body) { ?>
			setTimeout(function() {
				$('#msg-box').slideUp(500);
			}, 5000);
			<?php } ?>
        });
		
		function add() {
			<?php if(isset($bat)) { ?>
			$('.amb-edit').slideUp(500);
			<?php } ?>
			$('.amb-new').slideDown(500);
		}
		
		$('#del-btn').live("click", function(e) {
			if(confirm("Once deleted you cannot undo this action. Are you sure you wish to continue?")) {
				$('#amb-action').val('del');
				$('#amb-edit').submit();
			}
			
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
			<?php if($msg_body) { ?>
                <div class="content-middle-box" id="msg-box">
                	<div class="large-heading"><?php echo $msg_head; ?></div>
                    <?php echo $msg_body; ?>
                </div>
                <?php } ?>
                <div class="content-middle-box">
                <?php if($abc_user->is_admin) { ?>
                    <div class="large-heading">Admin Battle Management</div>
                    You can add and edit any battles for the current campaign here.
					<div class="am-control-box">
                    <form name="amb" method="POST">
                        <label for="bat">Battle: </label>
                        <select name="bat" class="am-cb-select">
                        <?php oo_battles($battle_to_edit, TRUE)?>
                        </select>
                        <input type="submit" name="submit_amb" value="Go" />
                        <input type="button" name="new" value="New" onclick="add();" />
                    </form>
                    </div>
                	<?php if(isset($bat)) { ?>
                    <br />
                    <div class="amb-edit">
                    <form name="amb-edit" id="amb-edit" method="POST">
                    	<input type="hidden" name="action" id="amb-action" value="edit" />
                        <input type="hidden" name=" bat" value="<?php echo $bat->id; ?>" />
                    	<label for="battle_name">Name: </label>
                        <input type="text" name="battle_name" id="battle_name" value="<?php echo $bat->name; ?>" required="required" />
						<label for="battle_date">Battle Date:</label>
                        <input type="text" name="battle_date" id="battle_date"<?php echo ' value="' . date("d-m-y", $bat->start) . '"'; ?> />
						<label for="battle_length">Length: </label>
                        <input type="text" name="battle_length" id="battle_length" value="<?php echo $bat->length; ?>" required="required" />
						<label for="battle_is_bfi">Battle_is_BFI: </label> 
                        <input type="checkbox" name="battle_is_bfi" id="battle_is_bfi" value="1"<?php if($bat->is_bfi) echo ' checked="checked"'; ?> />
                        <input type="button" name="delete" value="Delete" id="del-btn" />
                        <input type="submit" name="submit_battle" value="Save" />
                    </form>
                    </div>
                    <?php } ?>
                    <br />
                    <div class="amb-new">
                    <form name="amb-new" id="amb-new" method="POST">
                    	<input type="hidden" name="action" id="amd-action" value="new" />
                    	<label for="battle_name">Name: </label>
                        <input type="text" name="battle_name" id="battle_name" required="required" />
                        <label for="battle_date">Battle Date:</label>
                        <input type="text" name="battle_date" id="battle_date"/>
						<label for="battle_length">Length: </label>
                        <input type="text" name="battle_length" id="battle_length" required="required" />
						<label for="battle_is_bfi">Battle_is_BFI: </label> 
                        <input type="checkbox" name="battle_is_bfi" id="battle_is_bfi"/>
						<input type="submit" name="submit_new" value="Create" />
                    </form>
                    </div>
                <?php } else { ?>
                    <div class="large-heading">Unauthorised access!</div>
                    You do not have permission to view this page.
                <?php } ?>
				<?php echo date("d-m-y", $bat->start); ?>
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