<?php 
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require_once 'library/abc_start_up.php';
include_once 'library/functions/functions.output_options.php';

$save_error = FALSE;
if(isset($_POST['soldier_sign_up'])) {
	$abc_user->bf3_name = filter_input(INPUT_POST, "bf3_name", FILTER_SANITIZE_STRING);
	$abc_user->availability = filter_input(INPUT_POST, "availability", FILTER_SANITIZE_STRING);
	$abc_user->location = filter_input(INPUT_POST, "location", FILTER_SANITIZE_STRING);
	$abc_user->vehicles = filter_input(INPUT_POST, "vehicles", FILTER_SANITIZE_STRING);
	$abc_user->other_notes = filter_input(INPUT_POST, "other_notes", FILTER_SANITIZE_STRING);
	$abc_user->is_signed_up = 1;
	$abc_user->Role = filter_input(INPUT_POST, "role", FILTER_SANITIZE_STRING);
	$save_error = $abc_user->save();
} ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Army Base Camp &bull; Sign Up</title>
    <link rel="stylesheet" type="text/css" href="css/abc_style.css" />
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/jquery.fullscreen.js"></script>
    <script type="text/javascript" language="javascript">
		var FullscreenrOptions = {  width: 1920, height: 1080, bgID: '#bgimg' };
		jQuery.fn.fullscreenr(FullscreenrOptions);
		<?php if(!$abc_user->is_signed_up && $campaign->state <> 4) { ?>
		$(document).ready(function(e) {
			$('#soldier_sign_up_btn').click(function(e) {
                $('.sign-up-box').fadeIn(500);
            	$('.sign-up-box-inner').css("margin-top", (($('.sign-up-box').height() - $('.sign-up-box-inner').height()) / 2));
            });
			$('.close-sign-up').click(function(e) {
                $('.sign-up-box').fadeOut(500);
            });
        }); 
		<?php } ?>
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
                    <div class="small-heading"><img src="images/icon_menu.png" align="left" />ABC SOLDIER MENU</div>
                    <ul>
                        <li><a href="index.php">ABC Home</a></li>
                        <li><a href="control_panel.php">Control Panel</a></li>
                    </ul>
                </div>
                <div class="content-left-box">
                    <div class="small-heading"><img src="images/icon_user.png" align="left" />SOLDIER INFO</div>
                    <?php $abc_user->output_soldier_info(); ?>
                </div>
            </div>
            <div class="content-middle">
                <div class="content-middle-box">
                <?php if(isset($_POST['soldier_sign_up']) && $save_error) { ?>
                	<div class="large-heading">Error!</div>
                    There was an error trying to update your status. Please inform an admin and ask them to check the database connection.
                <?php } elseif(isset($_POST['soldier_sign_up'])) {
                    if($campaign->state < 4) { ?>
                        <div class="large-heading">Joined Draft</div>
                        Thank you for signing up for the next campaign. You have been entered into the draft which is scheduled to take place on <?php echo date('D jS M', $campaign->draft_date); ?>. Make sure to join TeamSpeak to listen into the draft live.
                        <br  /><br />
                        If you are not automatically returned to the previous page <a href="<?php echo $_SERVER['HTTP_REFERER']; ?>">click here</a>.
                        <meta http-equiv="refresh" content="5;url=<?php echo $_SERVER['HTTP_REFERER']; ?>" />
                    <?php } else { ?>
                        <div class="large-heading">Signed Up</div>
                        Thank you for signing up for the currently running campaign. Unfortunately you missed the draft, however the tournament administrators will assign you to an army shortly. Please check back soon to see which army you have been assigned to.
                        <br  /><br />
                        If you are not automatically returned to the previous page <a href="<?php echo $_SERVER['HTTP_REFERER']; ?>">click here</a>.
                        <meta http-equiv="refresh" content="5;url=<?php echo $_SERVER['HTTP_REFERER']; ?>" />
                    <?php }
                } elseif($campaign->state <> 4) { ?>
                    <div class="large-heading"><?php echo $campaign->state < 4 ? 'Enter Draft' : 'Sign Up'; ?></div>
                    <?php echo $campaign->state < 4 ? 
                    'The campaign will be starting soon, but before it begins the draft will be held to determine the armies. If you wish to take part then you can sign up for the draft by click on the Enter Draft button on the right. This will ensure you are placed in an army for the start of the campaign.' : 
                    'The campaign has already started but that doesn&rsquo;t mean you have to miss out on it. You can still join in by clicking on the Join Campaign button on the right. This will notify the tournament administrators that you wish to take part and they will assign you to an army.';
                } else { ?>
                    <div class="large-heading">Sign Ups Closed</div>
                    The draft is currently happening live, which means sign ups are temporarily closed. Don&rsquo;t worry though as once the draft is over you will be able to sign up for the campaign and a tournament administrator will assign you to an army.
                <?php } ?>
                </div>
            </div>
            <div class="clear"></div>
        </div>
        <div class="footer">
        </div>
    </div>
    <?php if($campaign->is_running) { ?>
    <div class="sign-up-box">
    	<div class="sign-up-box-inner">
        <form name="sign-up" action="abc_soldier_sign_up.php" method="POST">
        	<div class="large-heading"><?php echo $campaign->state < 4 ? 'Draft Sign Up' : 'Campaign Sign Up'; ?><div class="close-sign-up">x</div></div>
            <label for="bf3-name">BF3 Soldier Name:</label>
            <input type="text" name="bf3_name" id="bf3-name" value="<?php echo $abc_user->bf3_name; ?>" required="required" maxlength="255" />
            <br clear="all" /><br />
            <label for="availability">Availability (e.g. All 6 hours):</label>
            <input type="text" name="availability" id="availability" value="<?php echo $abc_user->availability; ?>" maxlength="255" />
            <br clear="all" /><br />
            <label for="location">Location (e.g. U.K.):</label>
            <input type="text" name="location" id="location" value="<?php echo $abc_user->location; ?>" maxlength="255" />
            <br clear="all" /><br />
            <label for="vehicles">Ability With Vehicles:</label>
            <input type="text" name="vehicles" id="vehicles" value="<?php echo $abc_user->vehicles; ?>" maxlength="255" />
            <br clear="all" /><br />
			<select name="role" id="role">
			<option value="Air"> Air </option>
			<option value="Armour"> Armour </option>
			<option value="Infantry"> Infantry </option>
			</select>
            <label for="other_notes">Other notes:</label>
            <textarea name="other_notes" id="other_notes"><?php echo $abc_user->other_notes; ?></textarea>
            <br clear="all" /><br />
            <input type="submit" name="soldier_sign_up" value="<?php echo $campaign->state < 4 ? 'Join Draft' : 'Sign Up'; ?>" class="sign_up_submit" />
        </form>
        <div class="clear"></div>
        <?php if($campaign->army_join_pw) { ?>
        <br /><br />
        <form name="join-army" action="abc_soldier_join_army.php" method="POST">
        	<div class="large-heading">Join via Password</div>
        	If you have been given a specific password to join an army you can use that password here to join the army directly. If you haven't been given a password please use the form above.
            <br /><br />
            <select name="army"><?php oo_armies_with_passwords(); ?></select>
            <input type="text" name="army_password" id="army_password" maxlength="32" />
            <br clear="all" /><br />
            <input type="submit" name="soldier_sign_up" value="Join Army" class="sign_up_submit" />
        </form>
        <div class="clear"></div>
        <?php } ?>
        </div>
    </div>
	<?php } ?>
</body>
</html>
<?php $mysqli->close(); ?>