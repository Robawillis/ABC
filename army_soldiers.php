<?php 
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require_once 'library/abc_start_up.php';
$army_to_manage = ($abc_user->is_admin && isset($_REQUEST['army'])) ? (int)$_REQUEST['army'] : $abc_user->army_ptr;
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
                $(window).attr("location", "army_ranks.php?army=" + $(this).val());
            });
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
            </div>
            <div class="content-middle">
                <div class="content-middle-box">
                <?php if($abc_user->is_hc || $abc_user->is_admin) { ?>
                    <div class="large-heading">
                        Army Soldiers
                        <?php if($abc_user->is_admin) {
                            echo '<select name="army-picker" id="army-picker">' . PHP_EOL;
                            for($i = 0; $i < $campaign->num_armies; $i++) {
                                echo '<option value="' . $i . '"' . ($army_to_manage == $i ? ' selected="selected"' : '') . '>' . $armies[$i]['army']->name . '</option>' . PHP_EOL;
                            }
                            echo '</select>' . PHP_EOL;
                        } ?>
                    </div>
                    Page under construction.
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