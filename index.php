<?php 
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require_once 'library/abc_start_up.php';
include_once 'library/functions/functions.output_options.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Army Base Camp &bull; Home</title>
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
		<?php if(!$abc_user->is_signed_up && $campaign->state <> 4) { ?>
		$(document).ready(function(e) {
			$('#soldier_sign_up_btn').click(function(e) {
                $('.sign-up-box').fadeIn(500);
            	$('.sign-up-box-inner').css("margin-top", (($('.sign-up-box').height() - $('.sign-up-box-inner').height()) / 2));
            });
			$('.close-sign-up').click(function(e) {
                $('.sign-up-box').fadeOut(500);
            });
			$('#soldier_join_army').click(function(e) {
                $('.sign-up-password-loading').show();
				$('#sign-up-password-loading-img').css("margin-top", (($('.sign-up-password').height() - $('#sign-up-password-loading-img').height()) / 2));
				$.ajax({
					type: 'POST',
					url: 'library/ajax/ajax.join_via_password.php',
					data: { army: $('#army').val(), password: $('#army_password').val() },
					success: function(data) {
						var msg = "";
						var title = "";
						var show_form = true;
						switch(data) {
							default:
							case "MISING_DATA":
								title = "Error!";
								msg = "There was a problem whilst checking the password, please try again.";
								break;
							case "WRONG_PASSWORD":
								title = "Incorrect Password";
								msg = "The password entered did not match the one on file. Check the password you were given and make sure caps lock is off as passwords are case sensitive.";
								break;
							case "ERROR_SAVING":
								title = "Database Error!";
								msg = "Your attempt to join the army was unsuccessful at this time due to a database error. Please inform an admin and ask them to check the database connection.";
								show_form = false;
								break;
							case "SUCCESS":
								title = "Success!";
								msg = "You have successfully joined the army. Please <a href=\"index.php\">click here</a> to go back to the home page.";
								show_form = false;
								break;
						}
						setTimeout(function() {
							$('.sign-up-password-loading').hide();
							$('.sign-up-password').height($('.sign-up-password-form').height());
							$('.sign-up-password-form').hide();
							$('.sign-up-password').append('<div class="sign-up-password-msg"><div class="large-heading">' + title + '</div>' + msg + '</div>');
							if(show_form) {
								$('.sign-up-password-msg').delay(5000).fadeOut(500);
								$('.sign-up-password-form').delay(5510).fadeIn(500);
							}
						}, 1000);
					}
				});
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
                    <div class="small-heading"><img src="images/icon_menu.png" align="left" />ABC SOLDIER MENU</div>
                    <ul>
						<li><a href="index.php">ABC Home</a></li>
						<li><a href="battleday_signup.php">Battle Day Sign Up</a></li>
                        <li><a href="control_panel.php">Control Panel</a></li>
						<li><a href="armies.php">Armies</a></li>
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
                	<div class="large-heading">ABC 2 LIVE!</div>
                    Welcome to ABC 2. For those of you who were around in the days of BF2 and before you will be familiar with the general functioning of this site, although it has been completely rewritten sinse the days of BF2 and looks different, so take some time to look around. It&rsquo;s not 100% completed yet so please be patient if you still see the odd page with "Under Construction" the only thing on it.<br /><br />
                    
                    For those of you who are new to Global Conflict for BF3 welcome to Army Base Camp. This addon to the forums helps make the day to day running of Global Conflict that little bit easier. In simple terms it&rsquo;s an army management site where you will be able to view your armies structure, see your rank and medals and also sign up for up and coming battle days.<br /><br />
                    
                    A lot of new features will be rolled out in the lead up to the start of C3, and don&rsquo;t be surprised if you find the odd bug or two (hey! there&rsquo;s only so much testing we can do without all you guys). If you do find any problems, or have any comments or ideas for features / ways to improve the site then please <a href="../ucp.php?i=pm&mode=compose&u=3901">send me a message</a> and let me know.<br /><br />
                    
                    For now make sure you have signed up for the up coming draft using the button below. Please fill in as much detail as possible to make it as easy as possible for your new Generals as they have a tough choice deciding who to draft.<br /><br />
                    
                    ---<br />
                    Styphon
                </div>
                <div class="content-middle-box">
                <?php if($campaign->is_running) {
                    switch($campaign->state) {
                        default:
                        case 0: ?>
                        <div class="large-heading">ABC Unavailable</div>
                        ABC is currently experiencing technical issues and is not available at this time. Please check back later.
                        <?php break;
                        
                        case 1: ?>
                        <div class="large-heading">New campaign coming soon!</div>
                        The TAs and new HCs are hard at work busy preparing for the next campaign: <?php echo $campaign->name; ?>.<br /><br />
                        Please check back later to see if the draft has opened. Don&rsquo;t forget to check the forums regularly as more information will be available there.
                        <?php break;
                        
                        case 2: ?>
                        <div class="large-heading">Draft Open!</div>
                        <?php if($abc_user->army_id) { ?>
                            You have already been assigned to an army, so you don't need to sign up for the draft. You can view the details of your army under soldier info on the left.
                        <?php } else { 
                            if($abc_user->is_signed_up) { ?>
                            Thank you for signing up for the draft. The draft is due to take place on <?php echo date('D dS M', $campaign->draft_date); ?>. The draft will be held live on TeamSpeak. You will be able to follow the draft as it happens here on ABC.
                            <?php } else { ?>
                            The draft is now open for sign ups. If you intend to play at GC this campaign, please sign up for the draft. The draft is due to take place on <?php echo date('D dS M', $campaign->draft_date); ?> at <?php echo date('H:i e', $campaign->draft_date); ?>. The draft will be held live on TeamSpeak. You will be able to follow the draft as it happens here on ABC.
                            <?php }
                        }
                        break;
						
						case 3: ?>
                        <div class="large-heading">Draft Closed!</div>
                        The draft sign up has closed as the draft will be taking place soon. Please come back on <?php echo date('D dS M', $campaign->draft_date); ?> to watch the draft happening live.<br /><br />
                        If you haven't signed up for the draft yet then don&rsquo;t worry as you will be able to sign up for the campaign after the draft has taken place. You will then be assigned an army by the TAs.
                        <?php break;
						
						case 4: ?>
                        <div class="large-heading">LIVE DRAFT!</div>
                        The live draft is currently taking place. <a href="live_draft.php">Click here</a> to watch the draft happening live.
                        <?php break;
						
						case 5: ?>
                        <div class="large-heading">Campaign in Progress</div>
                        <?php if($abc_user->army_id) { ?>
                            Welcome soldier to Army Base Camp. This will be your home for the duration of the campaign. On your left you can see information on your rank and division, along with the tags you should be wearing in TeamSpeak and BF3 on battle days.<br /><br />
                            You have been granted access to your army forums, which you should check on a regular basis. You can get to know your fellow soldiers outside of battle days, plus read any important announcements about changes to battle days. You can also get involved in tactical discussions about how to attack and defend on specific maps, and even on a campaign level. If you're not checking the foums daily, you&rsquo;re missing out on a lot.<br /><br />
							<!-- Finally, don&rsquo;t forget to check back here every week to sign up for the next battle day. All you have to do is place a check box next to the hours you plan on attending (you wont get in trouble if you sign up and don&rsquo;t show because of an emergency). By doing so you reserve priority on the server if more than 32 people show up for your army at the start of the day, plus help your armies HC and the TAs plan ahead to make sure the battle day runs as smoothly as possible.-->
                        <?php } else { 
                            if($abc_user->is_signed_up) { ?>
                            Thank you for signing up for the campaign. Your application will be reviewed by a Tournament Admin soon and they will assign you to an appropriate army. Until then keep checking the forums and feel free to join our TeamSpeak server and say hello. Check back here on Saturday, and if you haven&rsquo;t been assigned by 18:00 SBT (check <a href="http://global-conflict.org/viewtopic.php?f=168&t=17401" title="Standard Battle Time thread">the forums</a> for more info on SBT) then join TeamSpeak and ask someone for help.
                            <?php } else { ?>
                            The campaign is currently running, but don&rsquo;t worry as you haven&rsquo;t missed it. You can still take part by clicking on the sign up button to the left. Just fill in your details and a Tournament Admin will get assign you to an army shortly.
                            <?php }
                        }
                        break;
                    }
                } else { ?>
                    <div class="large-heading">There is currently no campaign running</div>
                    We are currently in between campaigns. During this time you can only use ABC to view the campaign archives. To view your personal archives go to your <a href="control_panel.php">control panel</a>. Check the forums for more information on when the next campaign will begin.
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
			<label for="location">Location (e.g. U.K.):</label>
            <input type="text" name="location" id="location" value="<?php echo $abc_user->location; ?>" maxlength="255" />
            <br clear="all" /><br />
            <label for="vehicles">Ability With Vehicles:</label>
            <input type="text" name="vehicles" id="vehicles" value="<?php echo $abc_user->vehicles; ?>" maxlength="255" />
            <br clear="all" /><br />
			<label for="vehicles">Prefered Role:</label>
            <select name="role" id="role">
			<option value="Air"> Air </option>
			<option value="Armour"> Armour </option>
			<option value="Infantry"> Infantry </option>
			</select>
            <br clear="all" /><br />
            <label for="other_notes">Other notes:</label>
            <textarea name="other_notes" id="other_notes"><?php echo $abc_user->other_notes; ?></textarea>
            <br clear="all" /><br />
            <input type="submit" name="soldier_sign_up" value="<?php echo $campaign->state < 4 ? 'Join Draft' : 'Sign Up'; ?>" class="sign_up_submit" />
        </form>
        <div class="clear"></div>
        <?php if($campaign->army_join_pw) { ?>
        <br /><br />
        <div class="sign-up-password">
            <div class="sign-up-password-form">
            <form name="join-army" method="POST">
                <div class="large-heading">Join via Password</div>
                If you have been given a specific password to join an army you can use that password here to join the army directly. If you haven't been given a password please use the form above.
                <br /><br />
                <select name="army" id="army"><?php oo_armies_with_passwords(); ?></select>
                <input type="text" name="army_password" id="army_password" maxlength="32" />
                <br clear="all" /><br />
                <input type="button" name="soldier_join_army" id="soldier_join_army" value="Join Army" class="sign_up_submit" />
            </form>
            <div class="clear"></div>
            </div>
        	<div class="sign-up-password-loading"><img src="images/loading.gif" id="sign-up-password-loading-img" /></div>
        </div>
        <?php } ?>
        </div>
    </div>
	<?php } ?>
</body>
</html>
<?php $mysqli->close(); ?>