<?php 
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require_once 'library/abc_start_up.php';
require_once 'library/classes/class.sign_ups.php';
$sign_ups = new Sign_ups();
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
                <div class="content-middle-box">
                <?php if($abc_user->is_admin) { ?>
                    <div class="large-heading">ABC Admins</div>
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