<?php 
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require_once 'library/abc_start_up.php';
require_once 'library/classes/class.sign_ups.php';
require_once 'library/functions/functions.output_options.php';
$sign_ups = new Sign_ups();

//Check to see if form below was submitted, and if so process.
if(isset($_POST['campaign_save'])) {
	$array = array(
		"campaign_name"			=> FILTER_SANITIZE_STRING,
		"campaign_state"		=> FILTER_VALIDATE_INT,
		"draftees_per_round"	=> FILTER_VALIDATE_INT,
		"is_group_draft"		=> FILTER_VALIDATE_INT,
		"draft_date"			=> FILTER_SANITIZE_STRING,
		"army_name"				=> array(
			"filter"				=> FILTER_SANITIZE_STRING,
			"flags"					=> FILTER_REQUIRE_ARRAY),
		"army_general"			=> array(
			"filter"				=> FILTER_SANITIZE_STRING,
			"flags"					=> FILTER_REQUIRE_ARRAY),
		"army_colour"			=> array(
			"filter"				=> FILTER_VALIDATE_REGEXP,
			"flags"					=> FILTER_REQUIRE_ARRAY,
			"options"				=> array("regexp" => "/^(([a-fF0-9]){6})$/i")),
		"tag"					=> array(
			"filter"				=> FILTER_SANITIZE_STRING,
			"flags"					=> FILTER_REQUIRE_ARRAY),
		"is_neutral"			=> array(
			"filter"				=> FILTER_VALIDATE_INT,
			"flags"					=> FILTER_REQUIRE_ARRAY),
		"join_pw"				=> array(
			"filter"				=> FILTER_SANITIZE_STRING,
			"flags"					=> FILTER_REQUIRE_ARRAY),
		"ts_pw"					=> array(
			"filter"				=> FILTER_SANITIZE_STRING,
			"flags"					=> FILTER_REQUIRE_ARRAY),
		"tag_str0"				=> array(
			"filter"				=> FILTER_SANITIZE_STRING,
			"flags"					=> FILTER_REQUIRE_ARRAY),
		"tag_str1"				=> array(
			"filter"				=> FILTER_SANITIZE_STRING,
			"flags"					=> FILTER_REQUIRE_ARRAY),
		"tag_str2"				=> array(
			"filter"				=> FILTER_SANITIZE_STRING,
			"flags"					=> FILTER_REQUIRE_ARRAY),
		"tag_str3"				=> array(
			"filter"				=> FILTER_SANITIZE_STRING,
			"flags"					=> FILTER_REQUIRE_ARRAY)
	);
	$input = filter_input_array(INPUT_POST, $array);
	
	//We proceed if a campaign name has been specified or if the campaign is running
	if($input['campaign_name'] || $campaign->is_running) {
		if($input['campaign_name'])
			$campaign->name = $input['campaign_name'];
		if($input['campaign_state'])
			$campaign->state = $input['campaign_state'];
		if($input['draftees_per_round'])
			$campaign->draftees_per_round = $input['draftees_per_round'];
		$campaign->is_group_draft = $input['is_group_draft'] ? 1 : 0;
		$campaign->draft_date = strtotime($input['draft_date']);
	}
	$campaign->save(); //Save the campaign, the save script works out if it can save or if it needs to create a new campaign.
	
	$n = 0;
	for($i = 0; $i < count($input['army_name']); $i++) {
		//We need to check to see if the army exists, and if not create it, plus the default divisions and ranks.
		if(isset($armies[$i])) {
			if($input['army_name'][$i])
				$armies[$i]['army']->name = $input['army_name'][$i];
			if($input['army_general'][$i]) {
				$gen_id = $phpbb_interaction->convert_user($input['army_general'][$i]);
				if($gen_id != $armies[$i]['army']->general) {
					$phpbb_interaction->replace_general($armies[$i]['army']->general, $gen_id); //THIS FUNCTION STILL NEEDS TO BE WRITTEN!
					$old_gen = new Abc_user($armies[$i]['army']->general);
					$old_gen->army_id = 0;
					$old_gen->division_id = 0;
					$old_gen->rank_id = 0;
					$old_gen->save();
					$armies[$i]['army']->general = $gen_id;
				}
			}
			if($input['army_colour'][$i])
				$armies[$i]['army']->colour = $input['army_colour'][$i];
			$armies[$i]['army']->tag = $input['tag'][$i];
			if(isset($input['is_neutral'][$n]) && $input['is_neutral'][$n] == $i) {
				$armies[$i]['army']->is_neutral = 1;
				$n++;
			} else
				$armies[$i]['army']->is_neutral = 0;
			$armies[$i]['army']->join_pw = $input['join_pw'][$i];
			$armies[$i]['army']->ts_pw = $input['ts_pw'][$i];
			$armies[$i]['army']->tag_structure = $input['tag_str0'][$i] . $input['tag_str1'][$i] . $input['tag_str2'][$i] . $input['tag_str3'][$i];
			$armies[$i]['army']->save();
		} else {
		}
	}
}

if(!$campaign->is_running) $campaign->num_armies = 3;
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
			$('#cs-draft-date').datepicker({ dateFormat: "dd-mm-yy" });
			$('.colourpicker').ColorPicker({
				onSubmit: function(hsb, hex, rgb, e) {
					$(e).val(hex);
				},
				onBeforeShow: function() {
					$(this).ColorPickerSetColor(this.value);
				},
				onShow: function(colpkr) {
					$(colpkr).fadeIn(500);
					return false;
				},
				onHide: function(colpkr) {
					$(colpkr).fadeOut(500);
					return false;
				}
			})
			.bind('keyup', function() {
				$(this).ColorPickerSetColor(this.value);
			});
			
			/* Form Validation */
			$('#cs-draftees').focusout(function(e) {
                if(isNaN(this.value) || this.value < 1)
					$(this).val(1);
				if(this.value > 99)
					$(this).val(99);
            });
			$('.army-tag').focusout(function(e) {
				$(this).val(this.value.replace(/[^a-z 0-9]+/gi, ''));
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
            <?php if($sign_ups->num && $campaign->state > 4) { ?>
                <div class="content-middle-box">
                    <div class="large-heading">ATTENTION!</div>
                    There <?php echo $sign_ups->num > 1 ? 'are' : 'is'; ?> currently <?php echo $sign_ups->num . ' soldier' . ($sign_ups->num > 1 ? 's' : ''); ?> awaiting assignment. Please <a href="admin_sign_ups.php">assign them now</a>.
                </div>
            <?php } ?>
                <div class="content-middle-box">
                <?php if($abc_user->is_admin) { ?>
                    <div class="large-heading">Admin Control Panel</div>
                    <?php if($campaign->is_running) { ?>
                    You can edit the currently running campaign by making changes to the form below. Please note that if you change any generals, the old general will be completely removed from the army.<br /><br />
                    <strong>NOTE: When changing the general, forum access is not currently changed. You must do this manually.</strong>
                    <?php } else { ?>
                    The first stage of any campaign is to set up the new armies. The new generals need to have been chosen before you can proceed with this task as you must select a general for an army. You can have any number of armies, however only one army may be neutral.<br /><br />
                    Once you create the campaign all the appropriate forum boards, groups and ranks will automatically be created. <strong>(NOT CURRENTLY ACTIVE)</strong><br /><br />
                    If you leave the army name blank then that army will not be created, regardless of any other details filled in.<br /><br />
                    <strong>WARNING: ARMIES WILL NOT CURRENTLY BE CREATED BUT THE CAMPAIGN WILL! THIS WILL BREAK ABC!!!</strong>
                    <?php } ?>
                    <br /><br />
                    <form name="campaign_settings" method="POST">
                    <div class="small-heading higher">
                    	Core Campaign Settings
                        <input type="submit" name="campaign_save" value="Save" class="button right" />
                    </div>
                    <div class="content-middle-col left">
                    	<label for="cs-camp-name">Campaign Name:</label>
                        <input type="text" name="campaign_name" id="cs-camp-name" maxlength="255" required="required"<?php if($campaign->is_running) echo ' value="' . $campaign->name . '"';?> />
                        <br clear="all" />
                        <div class="col-note"><strong>NOTE:</strong> Format for campaign name must be "BF3C[X]: [NAME]", where X is the campaign number and NAME is the name of the campaign. It is important the colon is correctly placed to ensure forums are named properly.</div>
                    </div>
                    <div class="content-middle-col right">
                    	<label for="cs-camp-state">Campaign State:</label>
                        <select name="campaign_state" id="cs-camp-state">
                        	<?php oo_states(); ?>
                        </select>
                        <br clear="all" /><br />
                        <label for="cs-draftees">Draftees per Round:</label>
                        <input type="text" name="draftees_per_round" id="cs-draftees" class="col-short-input" maxlength="2"<?php if($campaign->is_running) echo ' value="' . $campaign->draftees_per_round . '"'; ?> />
                        <label for="cs-groups">Group Draft:</label>
                        <input type="checkbox" name="is_group_draft" id="cs-groups" class="col-checkbox" value="1"<?php if($campaign->is_group_draft) echo ' checked="checked"'; ?> />
                        <br clear="all" /><br />
                        <label for="cs-draft-date">Draft Date:</label>
                        <input type="text" name="draft_date" id="cs-draft-date"<?php if($campaign->is_running) echo ' value="' . date("d-m-y", $campaign->draft_date) . '"'; ?> />
                        <br clear="all" />
						<div class="col-note" style="font-size: 10px;"><strong>NOTE:</strong> Dates must be formated in Eurpoean format: DD-MM-YYYY</div>
                    </div>
                    <div class="clear"><br /><br /></div>
                    <?php 
					for($i = 0; $i < $campaign->num_armies; $i++) { ?>
						<div class="small-heading<?php if($campaign->is_running) echo ' higher'; ?>">
							<?php echo $campaign->is_running ? $armies[$i]['army']->name . '<div class="right"><input type="button" name="delete_army' . $i . '" id="cs-del-army' . $i . '" class="button" value="Delete Army" /></div>' : 
							'New Army ' . ($i + 1); ?>
                        </div>
                        <br />
                        <div class="content-middle-col left">
                        	<label for="army<?php echo $i; ?>-name">Army Name:</label>
                            <input type="text" name="army_name[]" id="army<?php echo $i; ?>-name" maxlength="255"<?php if($campaign->is_running) echo ' value="' . $armies[$i]['army']->name . '"'; ?> />
                            <br clear="all" />
                            <div class="col-note"><strong>NOTE:</strong> If left blank when creating a new army then no army will be created, regardless of anything in any other field. If left empty for an existing army then the original name will be kept.</div>
                            <div class="army-tag-spacer"></div>
                            <label for="army<?php echo $i; ?>-tag">Army Tag:</label>
                            <input type="text" name="tag[]" id="army<?php echo $i; ?>-tag" class="army-tag col-short-input" maxlength="3"<?php if($campaign->is_running) echo ' value="' . $armies[$i]['army']->tag . '"'; ?> />
                            <label for="army<?php echo $i; ?>-neutral">Neutral Army:</label>
                            <input type="checkbox" name="is_neutral[]" id="army<?php echo $i; ?>-neutral" class="col-checkbox" value="<?php echo $i; ?>"<?php if($campaign->is_running && $armies[$i]['army']->is_neutral) echo ' checked="checked"'; ?> />
                        </div>
                        <div class="content-middle-col right">
                        	<label for="army<?php echo $i; ?>_general">Army General:</label>
                            <input type="text" name="army_general[]" id="army<?php echo $i; ?>_general"<?php if($campaign->is_running && $armies[$i]['army']->general) echo ' value="' . $phpbb_interaction->convert_user(FALSE, $armies[$i]['army']->general) . '"'; ?> />
                            <br clear="all" />
                            <div class="col-mini-note"><?php $phpbb_interaction->user_search("campaign_settings", "army{$i}_general"); ?></div>
                            <br clear="all" /><br />
                            <label for="army<?php echo $i; ?>-colour">Army Colour:</label>
                            <input type="text" name="army_colour[]" id="army<?php echo $i; ?>-colour" class="colourpicker" maxlength="6"<?php if($campaign->is_running) echo ' value="' . $armies[$i]['army']->colour . '"'; ?> />
                            <br clear="all" /><br />
                            <label for="army<?php echo $i; ?>-join">Joining Password:</label>
                            <input type="text" name="join_pw[]" id="army<?php echo $i; ?>-join" maxlength="32"<?php if($campaign->is_running) echo ' value="' . $armies[$i]['army']->join_pw . '"'; ?> />
                            <br clear="all" /><br />
                            <label for="army<?php echo $i; ?>-ts">TeamSpeak Password:</label>
                            <input type="text" name="ts_pw[]" id="army<?php echo $i; ?>-ts" maxlength="32"<?php if($campaign->is_running) echo ' value="' . $armies[$i]['army']->ts_pw . '"'; ?> />
                        </div>
                        <br clear="all" /><br />
                        <?php if($campaign->is_running)
							$curr_tags = str_split($armies[$i]['army']->tag_structure);
						else
							$curr_tags = array("A", "A", "D", "R"); ?>
                        <label for="army<?php echo $i; ?>-tagstr1" class="tag-label">Tag Structure:</label>
                        <?php for($j = 0; $j < 4; $j++) { ?>
                        <select name="tag_str<?php echo $j; ?>[]" id="army<?php echo $i; ?>-tagstr<?php echo $j; ?>" class="tag-select<?php if($j == 3) echo ' no-right'; ?>">
                        <?php oo_tags($curr_tags[$j]); ?>
                        </select>
                        <?php } ?>
                        <br clear="all" /><br /><br />
					<?php }
                       var_dump($input);					?>
                    <input type="submit" name="campaign_save" value="Save" class="button" />
                    </form>
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