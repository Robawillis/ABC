<?php 
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require_once 'library/abc_start_up.php';
require_once 'library/functions/functions.output_options.php';
$army_to_manage = ($abc_user->is_admin && isset($_REQUEST['army'])) ? (int)$_REQUEST['army'] : $abc_user->army_ptr;
$rank_to_edit = (isset($_REQUEST['rank'])) ? (int)$_REQUEST['rank'] : 0;
$msg_head = '';
$msg_body = '';

if($rank_to_edit) {
	$query = "SELECT 
		rank_id, 
		rank_phpbb_id, 
		army_id, 
		rank_name, 
		rank_short, 
		rank_order, 
		rank_is_officer, 
		rank_img, 
		rank_tag, 
		rank_time_stamp 
		FROM abc_ranks 
		WHERE rank_id = $rank_to_edit";
	if($result = $mysqli->query($query)) {
		while($row = $result->fetch_assoc()) {
			$rank = new Rank($row, -1);
			$rank->load_num_soldiers();
		}
	}
}

if(isset($_POST['action'])) {
	switch($_POST['action']) {
		case 'del':
			if($rank->delete()) {
				$msg_head = 'Error!';
				$msg_body = 'There was a problem deleting the rank. Please check the database connection.';
			} else {
				$msg_head = 'Rank Deleted';
				$msg_body = 'The rank has been successfully deleted.';
				unset($rank);
			}
			break;
		
		case 'edit':
			$was_officer = $rank->is_officer;
			$rank->name = $_POST['rank_name'];
			$rank->short = $_POST['rank_short'];
			$rank->tag = $_POST['rank_tag'];
			$rank->order = $_POST['rank_order'];
			$rank->is_officer = isset($_POST['rank_is_officer']) ? 1 : 0;
			foreach($_FILES as $file) {
				if($file['error'] != 4) {
					$dir = '/kunden/homepages/1/d168861977/htdocs/www/abc/images/cache/ranks/' . $campaign->id . '/' . $armies[$army_to_manage]['army']->id . '/'; //REMOVE . "phpbb/" ON LIVE SERVER
					$php_dir = str_replace(array("/abc","/cache"), "", $dir);
					if(!is_dir($dir))
						mkdir($dir);
					if(!is_dir($php_dir))
						mkdir($php_dir);
					if(file_exists('/kunden/homepages/1/d168861977/htdocs/www/cache/data_ranks.php'));
						unlink('/kunden/homepages/1/d168861977/htdocs/www/cache/data_ranks.php');
					if($file['error']) {
						$msg_head = 'Error!';
						switch($file['error']) {
							default:
								$msg_body = 'There was a problem uploading your file. Please try again.';
								break;
							case 1:
							case 2:
								//1 - file size exceeds limit in php.ini, 2 - file size exceeds limit set by form.
								$msg_body = 'The file you uploaded was too big. Please try a smaller image.';
								break;
							case 3:
								//Partial upload
								$msg_body = 'The file did not completely upload. Please try again.';
							case 4:
								//No file uploaded
								break;
							case 6:
								//No temp folder
								$msg_body = 'An error occured during the file upload process. Please contact an admin quoting file upload error 6.';
								break;
							case 7:
								//Failed to write to disk
								$msg_body = 'An error occured during the file upload process. Please contact an admin quoting file upload error 7.';
								break;
						}
					} else {
						if($file['type'] == "image/gif" || $file['type'] == "image/jpeg" || $file['type'] == "image/pjpeg" || $file['type'] == "image/png") {
							if(file_exists($dir . $file['name']))
								unlink($dir . $file['name']);
							if(!move_uploaded_file($file['tmp_name'], $dir . $file['name'])) {
								$msg_head = 'Error!';
								$msg_body = 'Unable to upload file to the images/cache directory. Please check the folder permissions.';
							} elseif(!copy($dir . $file['name'], $php_dir . $file['name'])) {
								$msg_head = 'Error!';
								$msg_body = 'Unable to upload file to the phpbb images directory. Please check the folder permissions.';
							} else 
								$rank->img = str_replace($_SERVER['DOCUMENT_ROOT'] . '/abc/', '', $dir) . $file['name'];
						} else {
							$msg_head = 'Error!';
							$msg_body = 'You tried to upload an invalid file type. You may only upload a jpg, gif or png image file type.';
						}
					}
				}
			}
			if(!$msg_body) {
				if($rank->save()) {
					$msg_head = 'Error!';
					$msg_body = 'There was a problem whilst trying to save your changes. Please check the database connection.';
				} else {
					$msg_head = 'Rank Updated';
					$msg_body = 'The rank has been successfully updated.';
					if($was_officer && !$rank->is_officer) {
						//Remove all users with this rank from officer group
						$users = array();
						$groups = array();
						$query = "SELECT user_id FROM abc_users WHERE rank_id = " . $rank->id;
						$result = $mysqli->query($query);
						if($result->num_rows) {
							while($row = $result->fetch_row())
								$users[] = $row[0];
						}
						for($i = 0; $i < $campaign->num_armies; $i++) {
							if($armies[$i]['army']->id == $rank->army_id)
								$groups[] = $armies[$i]['army']->officer_forum_group;
						}
						if(count($users) && count($groups)) {
							if($phpbb_interaction->group_remove_users($users, $groups)) {
								$msg_head = 'Error!';
								$msg_body = 'There was a problem whilst trying to remove soldiers from the officers group. You will have to manually remove them from the group.';
							}							
						}
					} elseif(!$was_officer && $rank->is_officer) {
						//Add all users with this rank to officer group
						$users = array();
						$groups = array();
						$colour = "";
						$query = "SELECT user_id FROM abc_users WHERE rank_id = " . $rank->id;
						$result = $mysqli->query($query);
						if($result->num_rows) {
							while($row = $result->fetch_row())
								$users[] = $row[0];
						}
						for($i = 0; $i < $campaign->num_armies; $i++) {
							if($armies[$i]['army']->id == $rank->army_id) {
								$groups[] = $armies[$i]['army']->officer_forum_group;
								$colour = $armies[$i]['army']->colour;
							}
						}
						if(count($users) && count($groups)) {
							if($phpbb_interaction->group_add_users($users, $groups, $colour)) {
								$msg_head = 'Error!';
								$msg_body = 'There was a problem whilst trying to add soldiers to the officers group. You will have to manually add them to the group.';
							}							
						}
					}
				}
			}
			break;
		
		case 'new':
			$rank = new rank(array(), -1);
			$rank->name = $_POST['rank_name'];
			$rank->army_id = $armies[$army_to_manage]['army']->id;
			$rank->short = $_POST['rank_short'];
			$rank->tag = $_POST['rank_tag'];
			$rank->order = $_POST['rank_order'];
			$rank->is_officer = isset($_POST['rank_is_officer']) ? 1 : 0;
			foreach($_FILES as $file) {
				$dir = $_SERVER['DOCUMENT_ROOT'] . '/abc/images/cache/ranks/' . $campaign->id . '/' . $armies[$army_to_manage]['army']->id . '/'; //REMOVE . "phpbb/" ON LIVE SERVER
				$php_dir = str_replace(array("/abc","/cache"), "", $dir);
				if(!is_dir($dir))
					mkdir($dir);
				if(!is_dir($php_dir))
					mkdir($php_dir);
				if($file['error']) {
					$msg_head = 'Error!';
					switch($file['error']) {
						default:
							$msg_body = 'There was a problem uploading your file. Please try again.';
							break;
						case 1:
						case 2:
							//1 - file size exceeds limit in php.ini, 2 - file size exceeds limit set by form.
							$msg_body = 'The file you uploaded was too big. Please try a smaller image.';
							break;
						case 3:
							//Partial upload
							$msg_body = 'The file did not completely upload. Please try again.';
						case 4:
							//No file uploaded
							break;
						case 6:
							//No temp folder
							$msg_body = 'An error occured during the file upload process. Please contact an admin quoting file upload error 6.';
							break;
						case 7:
							//Failed to write to disk
							$msg_body = 'An error occured during the file upload process. Please contact an admin quoting file upload error 7.';
							break;
					}
				} else {
					if($file['type'] == "image/gif" || $file['type'] == "image/jpeg" || $file['type'] == "image/pjpeg" || $file['type'] == "image/png") {
						if(file_exists($dir . $file['name']))
							unlink($dir . $file['name']);
						if(!move_uploaded_file($file['tmp_name'], $dir . $file['name'])) {
							$msg_head = 'Error!';
							$msg_body = 'Unable to upload file to the images/cache directory. Please check the folder permissions.';
						} elseif(!copy($dir . $file['name'], $php_dir . $file['name'])) {
							$msg_head = 'Error!';
							$msg_body = 'Unable to upload file to the phpbb images directory. Please check the folder permissions.';
						} else 
							$rank->img = str_replace($_SERVER['DOCUMENT_ROOT'] . '/abc/', '', $dir) . $file['name'];
					} else {
						$msg_head = 'Error!';
						$msg_body = 'You tried to upload an invalid file type. You may only upload a jpg, gif or png image file type.';
					}
				}
			}
			if($rank->create()) {
				$msg_head = 'Error!';
				$msg_body = 'There was an error creating your new rank. Please check the database connection.';
				unset($rank);
			} else {
				$msg_head = 'Rank Created';
				$msg_body = 'The rank has been successfully created.';
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
                $(window).attr("location", "army_ranks.php?army=" + $(this).val());
            });
			<?php } ?>
			<?php if($msg_body) { ?>
			setTimeout(function() {
				$('#msg-box').slideUp(500);
			}, 5000);
			<?php } ?>
        });
		
		function add() {
			<?php if(isset($rank)) { ?>
			$('.amr-edit').slideUp(500);
			<?php } ?>
			$('.amr-new').slideDown(500);
		}
		$('#rank_del_btn').live("click", function(e) {
			<?php if(isset($rank) && $rank->num_soldiers) { ?>
			alert("You cannot delete a rank whilst there are still soldiers assigned this rank and there are <?php echo $rank->num_soldiers; ?> soldier(s) assigned it.\nPlease change their rank and then come back and delete this rank.");
			<?php } else { ?>
			if(confirm("Once deleted you cannot undo this action. Are you sure you wish to continue?")) {
				$('#amr-action').val('del');
				$('#amr-rank').submit();
			}
			<?php } ?>
        });
		function check_order(val) {
			if(isNaN(val) || val > 98) {
				$('#rank_order').val('98');
			} else if(val < 2) {
				$('#rank_order').val('2');
			}
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
				<?php if($msg_body) { ?>
                <div class="content-middle-box" id="msg-box">
                	<div class="large-heading"><?php echo $msg_head; ?></div>
                    <?php echo $msg_body; ?>
                </div>
                <?php } ?>
                <div class="content-middle-box">
                <?php if($abc_user->is_hc || $abc_user->is_admin) { ?>
                    <div class="large-heading">
                        Army Ranks
                        <?php if($abc_user->is_admin) {
                            echo '<select name="army-picker" id="army-picker">' . PHP_EOL;
                            for($i = 0; $i < $campaign->num_armies; $i++) {
                                echo '<option value="' . $i . '"' . ($army_to_manage == $i ? ' selected="selected"' : '') . '>' . $armies[$i]['army']->name . '</option>' . PHP_EOL;
                            }
                            echo '</select>' . PHP_EOL;
                        } ?>
                    </div>
                    <div class="am-control-box">
                    <form name="amr" method="POST">
                        <label for="rank">Rank: </label>
                        <select name="rank" class="am-cb-select">
                        <?php oo_ranks($army_to_manage, $rank_to_edit, TRUE); ?>
                        </select>
                        <input type="submit" name="submit" value="Go" />
                        <input type="button" name="new" value="New" onclick="add();" />
                    </form>
                    </div>
                	<?php if(isset($rank)) { ?>
                    <br />
                    <div class="amr-edit">
                    <form name="amr-rank" id="amr-rank" method="POST" enctype="multipart/form-data">
                    	<input type="hidden" name="action" id="amr-action" value="edit" />
                        <input type="hidden" name="rank" value="<?php echo $rank->id; ?>" />
                    	<label for="rank_name">Name: </label>
                        <input type="text" name="rank_name" id="rank_name" value="<?php echo $rank->name; ?>" required="required" />
                    	<label for="rank_short">Short Name: </label>
                        <input type="text" name="rank_short" id="rank_short" value="<?php echo $rank->short; ?>" required="required" maxlength="14" />
                        <label for="rank_tag">Tag: </label>
                        <input type="text" name="rank_tag" id="rank_tag" value="<?php echo $rank->tag; ?>" required="required" maxlength="3" />
                        <br clear="all" /><br />
                        <label for="rank_order">Order: </label>
                        <input type="text" name="rank_order" id="rank_order" value="<?php echo $rank->order; ?>" required="required" maxlength="2"<?php if($rank->order == 1 || $rank->order == 99) echo ' readonly="readonly"';?> onblur="check_order(this.value);" />
                        <div class="left">&nbsp;&nbsp; NOTE: must be between 2 and 98, 1 is reserved for General and 99 for Recruit.</div>
                        <br clear="all" /><br />
                        <label for="rank_is_officer">Officer Rank: </label> 
                        <input type="checkbox" name="rank_is_officer" id="rank_is_officer" value="1"<?php if($rank->is_officer) echo ' checked="checked"'; ?> />
                        <br clear="all" /><br />
                        <img src="<?php echo $rank->img; ?>" />
                        <label for="rank_img">New Image: </label>
                        <input type="file" name="rank_img" id="rank_img" />
                        <br clear="all" /><br />
                        <input type="button" name="delete" id="rank_del_btn" value="Delete" <?php if($rank->order == 1 || $rank->order == 99) echo ' disabled="disabled"';?> />
                        <input type="submit" name="submit-edit" value="Save" />
                    </form>
                    <div class="clear"></div>
                    </div>
                    <?php } ?>
                    <br />
                    <div class="amr-new">
                    <form name="amr-new" id="amr-new" method="POST" enctype="multipart/form-data">
                    	<input type="hidden" name="action" id="amn-action" value="new" />
                    	<label for="nrank_name">Name: </label>
                        <input type="text" name="rank_name" id="nrank_name" required="required" />
                    	<label for="nrank_short">Short Name: </label>
                        <input type="text" name="rank_short" id="nrank_short" required="required" maxlength="14" />
                        <label for="nrank_tag">Tag: </label>
                        <input type="text" name="rank_tag" id="nrank_tag" required="required" maxlength="3" />
                        <br clear="all" /><br />
                        <label for="nrank_order">Order: </label>
                        <input type="text" name="rank_order" id="nrank_order" value="2" required="required" maxlength="2" onblur="check_order(this.value);" />
                        <span>&nbsp;&nbsp; NOTE: must be between 2 and 98, 1 is reserved for General and 99 for Recruit.</span>
                        <br clear="all" /><br />
                        <label for="nrank_is_officer">Officer Rank: </label>
                        <input type="checkbox" name="rank_is_officer" id="nrank_is_officer" value="1" />
                        <br clear="all" /><br />
                        <label for="nrank_img">New Image: </label>
                        <input type="file" name="rank_img" id="nrank_img" />
                        <br clear="all" /><br />
                        <input type="submit" name="submit-new" value="Create" />
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