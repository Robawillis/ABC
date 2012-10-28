<?php 
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require_once 'library/abc_start_up.php';
require_once 'library/classes/class.sign_ups.php';
$sign_ups = new Sign_ups();

$invalid_user = FALSE;
$msg_head = '';
$msg_body = '';
if(isset($_REQUEST['username'])) {
	$username = $_REQUEST['username'];
	$user_id = $phpbb_interaction->convert_user($username);
	if(!$user_id)
		$invalid_user = TRUE;
	else
		$admin_user = new Abc_user($user_id);
} elseif(isset($_REQUEST['id'])) {
	$user_id = (int)$_REQUEST['id'];
	$username = $phpbb_interaction->convert_user(FALSE, $user_id);
	if($user_id)
		$admin_user = new Abc_user($user_id);
}

if(isset($_POST['update_user'])) {
	$array = array(
		'abc_user_id'	=> FILTER_VALIDATE_INT,
		'army_id'		=> FILTER_VALIDATE_INT,
		'division_id'	=> FILTER_VALIDATE_INT,
		'rank_id'		=> FILTER_VALIDATE_INT
	);
	$input = filter_input_array(INPUT_POST, $array);
	if($abc_user->save($input)) {
		$msg_head = 'Error';
		$msg_body = 'There was an error saving the changes. Please check the database connection.';
	} else {
		$msg_head = 'Soldier Updated';
		$msg_body = 'The soldier has been successfully updated.';
	}
	if(isset($admin_user))
		unset($admin_user);
	$user_id = isset($_POST['user_id']) ? filter_input(INPUT_POST, "user_id", FILTER_VALIDATE_INT) : (isset($user_id) ? $user_id : 0);
	if($user_id) {
		$username = $phpbb_interaction->convert_user(FALSE, $user_id);
		$admin_user = new Abc_user($user_id);
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
		
		<?php if(isset($admin_user)) { ?>
		//Load all divisions and ranks into arrays for dynamic updating
		var cur_army = <?php echo $admin_user->army_id; ?>;
		var cur_div = <?php echo $admin_user->division_id; ?>;
		var cur_rank = <?php echo $admin_user->rank_id; ?>;
		var divs = new Array();
		divs[0] = new Array();
		divs[0][0] = new Array();
		divs[0][0]["id"] = 0;
		divs[0][0]["name"] = "None";
		var ranks = new Array();
		ranks[0] = new Array();
		ranks[0][0] = new Array();
		ranks[0][0]["id"] = 0;
		ranks[0][0]["name"] = "None";
		<?php 
		for($i = 0; $i < $campaign->num_armies; $i++) {
			echo 'divs[' . $armies[$i]['army']->id . '] = new Array();
			ranks[' . $armies[$i]['army']->id . '] = new Array();';
			$j = 0;
			$k = 0;
			foreach($armies[$i]['divisions'] as $d) {
				echo 'divs[' . $armies[$i]['army']->id . '][' . $j . '] = new Array();
				divs[' . $armies[$i]['army']->id . '][' . $j . ']["id"] = ' . $d->id . ';
				divs[' . $armies[$i]['army']->id . '][' . $j . ']["name"] = "' . $d->name . '";' . PHP_EOL;
				$j++;
			}
			foreach($armies[$i]['ranks'] as $r) {
				echo 'ranks[' . $armies[$i]['army']->id . '][' . $k . '] = new Array();
				ranks[' . $armies[$i]['army']->id . '][' . $k . ']["id"] = ' . $r->id . ';
				ranks[' . $armies[$i]['army']->id . '][' . $k . ']["name"] = "' . $r->name . '";' . PHP_EOL;
				$k++;
			}
		} ?>
		
		function army_update(a) {
			var dopts = '';
			var ropts = '';
			$.each(divs[a], function() {
				dopts += '<option value="' + this["id"] + '"';
				if(cur_army == a && cur_div == this["id"])
					dopts += ' selected="selected"';
				dopts += '>' + this["name"] + '</option>';
			});
			$.each(ranks[a], function() {
				ropts += '<option value="' + this["id"] + '"';
				if(cur_army == a && cur_rank == this["id"])
					ropts += ' selected="selected"';
				ropts += '>' + this["name"] + '</option>';
			});
			$('#au-eu-div').html(dopts);
			$('#au-eu-rank').html(ropts);
		}
		<?php } ?>
		$(document).ready(function(e) {
			<?php if($msg_body) { ?>
			setTimeout(function() {
				$('#msg-box').slideUp(500);
			}, 5000);
			<?php } ?>
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
            <?php if($sign_ups->num && $campaign->state > 4) { ?>
                <div class="content-middle-box">
                    <div class="large-heading">ATTENTION!</div>
                    There <?php echo $sign_ups->num > 1 ? 'are' : 'is'; ?> currently <?php echo $sign_ups->num . ' soldier' . ($sign_ups->num > 1 ? 's' : ''); ?> awaiting assignment. Please <a href="admin_sign_ups.php">assign them now</a>.
                </div>
            <?php } ?>
            <?php if($msg_body) { ?>
                <div class="content-middle-box" id="msg-box">
                    <div class="large-heading"><?php echo $msg_head; ?></div>
                    <?php echo $msg_body; ?>
                </div>
            <?php } ?>
                <div class="content-middle-box">
                <?php if($abc_user->is_admin) { ?>
                    <div class="large-heading">Admin Soldier Management</div>
                    <div class="au-user-search">
                    <form name="au-user-search" method="POST">
                        <label for="au_us_username">Search for a soldier:</label>
                        <input type="text" name="username" id="au_us_username" />
                        <div class="au-us-text"><?php $phpbb_interaction->user_search("au-user-search", "au_us_username"); ?></div>
                        <input type="submit" name="submit" id="au-us-submit" value="Search" />
                    </form>
                    </div>
                    UNDER CONSTRUCTION AGAIN! IF YOU USE THIS PAGE USERS WILL NOT BE ADDED TO FORUM GROUPS!!!<br /><br />
                    <?php if(isset($admin_user)) { ?>
                    <br />
                    <div class="small-heading">Editing User: <?php echo $username; ?></div>
                    <form name="au-edit-user" method="POST">
                        <label for="army_id">Army: </label>
                        <select name="army_id" id="au-eu-army" onchange="army_update(this.value);">
                            <option value="0">None</option>
                            <?php for($i = 0; $i < $campaign->num_armies; $i++) {
                                echo '<option value="' . $armies[$i]['army']->id . '"' . ($admin_user->army_ptr == $i ? ' selected="selected"' : '') . '>' . $armies[$i]['army']->name . '</option>' . PHP_EOL;
                            } ?>
                        </select>
                        <label for="division_id">Division: </label>
                        <select name="division_id" id="au-eu-div">
                        <?php if($admin_user->division_id > 0) {
                            for($i = 0; $i < $armies[$admin_user->army_ptr]['army']->num_divs; $i++) {
                                echo '<option value="' . $armies[$admin_user->army_ptr]['divisions'][$i]->id . '"' . ($admin_user->division_ptr == $i ? ' selected="selected"' : '') . '>' . $armies[$admin_user->army_ptr]['divisions'][$i]->name . '</option>' . PHP_EOL;
                            } ?>
                        <?php } else { ?>
                            <option value="0">None</option>
                        <?php } ?>
                        </select>
                        <label for="rank_id">Rank: </label>
                        <select name="rank_id" id="au-eu-rank">
                        <?php if($admin_user->division_id > 0) {
                            for($i = 0; $i < $armies[$admin_user->army_ptr]['army']->num_ranks; $i++) { 
                                echo '<option value="' . $armies[$admin_user->army_ptr]['ranks'][$i]->id . '"' . ($admin_user->rank_ptr == $i ? ' selected="selected"' : '') . '>' . $armies[$admin_user->army_ptr]['ranks'][$i]->name . '</option>' . PHP_EOL;
                            } ?>
                        <?php } else { ?>
                            <option value="0">None</option>
                        <?php } ?>
                        </select>
                        <input type="hidden" name="abc_user_id" value="<?php echo $admin_user->id; ?>" />
                        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
                        <input type="submit" name="update_user" value="Submit" />
                    <?php } elseif($invalid_user) { ?>
                    No user could be found matching that name. Please use the <?php $phpbb_interaction->user_search("au-user-search", "au_us_username"); ?> link and try again.
                    <?php } ?>
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