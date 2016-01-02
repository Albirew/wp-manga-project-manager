<?php



/**
 * Display Administrative Menu for Releases.
 * @return menu
 */
function wpmanga_dataRelease() {
	global $wpdb;

	// Action Variable
	if (isset($_GET['action']))
		$action = strtolower($_GET['action']);
	else
		$action = 'add';

	// Sanity Check on EDIT and DEL
	if (!isset($_GET['id']) && $action != 'add')
		$action = 'add';

	if ( isset( $_POST['wpmanga_nonce'] ) ) {
		if ( !wp_verify_nonce( $_POST['wpmanga_nonce'], plugin_basename( plugin_sDIR() . '/wpmanga.php' ) ) ) {
			echo '<div class="error"><p>Error: Security Verification Failed.</p></div>';
		} else {
			$_POST = array_map('trim', $_POST);
			$_POST = array_map('stripslashes', $_POST);

			if ( $_POST['project_id'] ) {
				// UNIXTIME Fix
				if ($_POST['unixtime'] != 0) {
					if ($_POST['revision'] == 0) {
						$_POST['unixtime_mod'] = $_POST['unixtime'];
						$_POST['revision'] = 1;
					} else {
						$edit = get_sRelease($_GET['id']);
						$_POST['unixtime_mod'] = $_POST['unixtime'];
						$_POST['unixtime'] = $edit->unixtime;
					}
				} else {
					$_POST['unixtime'] = time();
					$_POST['unixtime_mod'] = $_POST['unixtime'];
				}

				if (preg_match("/(edit)/i", $action)) {
					$edit = get_sRelease($_GET['id']);
					if ($_POST['revision'] > $edit->revision) $_POST['unixtime_mod'] = time();
				}

				$data = array('project_id' => $_POST['project_id'], 'unixtime' => $_POST['unixtime'], 'unixtime_mod' => $_POST['unixtime_mod'], 'volume' => $_POST['volume'], 'chapter' => $_POST['chapter'], 'subchapter' => $_POST['subchapter'], 'revision' => $_POST['revision'], 'type' => $_POST['type'], 'title' => $_POST['title'], 'download_megaupload' => $_POST['download_megaupload'], 'download_mediafire' => $_POST['download_mediafire'], 'download_depositfiles' => $_POST['download_depositfiles'], 'download_fileserve' => $_POST['download_fileserve'], 'download_filesonic' => $_POST['download_filesonic'], 'download_pdf' => $_POST['download_pdf'], 'download_irc' => $_POST['download_irc'], 'link_reader' => $_POST['link_reader'], 'language' => $_POST['language'], 'status' => $_POST['status']);

				switch ($action) {
					case 'edit':
						$status = $wpdb->update($wpdb->prefix . 'projects_releases', $data, array('id' => $_GET['id']));

						if ($status)
							echo '<div class="updated"><p>Updated Release Information.</p></div>';
						else
							echo '<div class="error"><p>Error: Failed to update information.</p></div>';
						break;

					case 'delete':
						$status = $wpdb->query($wpdb->prepare("DELETE FROM `{$wpdb->prefix}projects_releases` WHERE `id` = '%d'", $_GET['id']));

						if ($status)
							echo '<div class="updated"><p>Deleted Release Information.</p></div>';
						else
							echo '<div class="error"><p>Error: Failed to delete information.</p></div>';
						break;

					default:
						$wpdb->insert($wpdb->prefix . 'projects_releases', $data);

						if ( $wpdb->insert_id )
							echo '<div class="updated"><p>Added Release for Projects. <a href="admin.php?page=manga/release&action=edit&id=' . $wpdb->insert_id . '">Edit Release</a></p></div>';
						else
							echo '<div class="error"><p>Error: Failed to add new release.</p></div>';
				}
			} else {
				echo '<div class="error"><p>Error: Please fill in the required fields.</p></div>';
			}
		}
	}

	$projects = $wpdb->get_results("SELECT `id`, `title` FROM `" . $wpdb->prefix . "projects` ORDER BY `title` ASC");

	if ( $projects ) {
		if (preg_match("/(edit|delete)/i", $action))
			$release = $wpdb->get_row($wpdb->prepare("SELECT * FROM `{$wpdb->prefix}projects_releases` WHERE `id` = '%d' LIMIT 1", $_GET['id']));
?>
		<div class="wrap">
			<?php screen_icon('edit'); ?>
			<h2><?php echo ucfirst($action); ?> Release</h2>

			<p>Add a new release for a project.</p>
<?php
			switch ($action) {
				case 'edit':
					echo '<form method="post" action="admin.php?page=manga/release&action=edit&id=' . $_GET['id'] . '">';
					break;

				case 'delete':
					echo '<form method="post" action="admin.php?page=manga/release&action=delete&id=' . $_GET['id'] . '">';
					break;

				default:
					echo '<form method="post" action="admin.php?page=manga/release">';
			}
?>
				<table class="form-table">
					<tr class="form-field">
						<th scope="row"><label for="project_id">Project</label></th>
						<td>
							<select name="project_id" id="project_id" style="width: 460px">
								<?php
									foreach ($projects as $project) {
										if (preg_match("/(edit|delete)/i", $action)) {
											if ($project->id == $release->project_id)
												echo "<option value='{$project->id}' selected='selected'>{$project->title}</option>";
											else
												echo "<option value='{$project->id}'>{$project->title}</option>";
										} else {
											echo "<option value='{$project->id}'>{$project->title}</option>";
										}
									}
								?>
							</select>
						</td>
					</tr>

					<tr class="form-field">
						<th scope="row"><label for="volume">Volume</label></th>
						<td><input name="volume" id="volume" type="number" value="<?php if (isset($release)) echo $release->volume; else echo '0'; ?>"<?php if ($action == 'delete') echo ' readonly="readonly"'; ?> autofocus></td>
					</tr>

					<tr class="form-field">
						<th scope="row"><label for="chapter">Chapter</label></th>
						<td><input name="chapter" id="chapter" type="number" value="<?php if (isset($release)) echo $release->chapter; else echo '0'; ?>"<?php if ($action == 'delete') echo ' readonly="readonly"'; ?>></td>
					</tr>

					<tr class="form-field">
						<th scope="row"><label for="subchapter">Sub-Chapter</label></th>
						<td><input name="subchapter" id="subchapter" type="number" value="<?php if (isset($release)) echo $release->subchapter; else echo '0'; ?>"<?php if ($action == 'delete') echo ' readonly="readonly"'; ?>></td>
					</tr>

					<?php if (preg_match("/(edit|delete)/i", $action)) { ?>
					<tr class="form-field">
						<th scope="row"><label for="revision">Revision</label></th>
						<td><input name="revision" id="revision" type="number" value="<?php if (isset($release)) echo $release->revision; ?>"<?php if ($action == 'delete') echo ' readonly="readonly"'; ?>></td>
					</tr>
					<?php } else { ?>
					<input type="hidden" name="revision" id="revision" value="0">
					<?php } ?>

					<tr class="form">
						<th scope="row"><label>Type de sortie</label></th>
						<td>
							<input name="type" id="normal" type="radio" value="0"<?php if (!preg_match("/(edit|delete)/i", $action)) echo ' checked="checked"'; ?><?php if (isset($release) && $release->type == 0) echo ' checked="checked"'; ?><?php if ($action == 'delete') echo ' disabled="disabled"'; ?>> <label for="normal">Chapitre</label> &nbsp;
							<input name="type" id="volumet" type="radio" value="5"<?php if (isset($release) && $release->type == 5) echo ' checked="checked"'; ?><?php if ($action == 'delete') echo ' disabled="disabled"'; ?>> <label for="volumet">Volume (repack)</label> &nbsp;
							<input name="type" id="special" type="radio" value="10"<?php if (isset($release) && $release->type == 10) echo ' checked="checked"'; ?><?php if ($action == 'delete') echo ' disabled="disabled"'; ?>> <label for="special">Spécial</label> &nbsp;
							<input name="type" id="oneshot" type="radio" value="20"<?php if (isset($release) && $release->type == 20) echo ' checked="checked"'; ?><?php if ($action == 'delete') echo ' disabled="disabled"'; ?>> <label for="oneshot">Oneshot</label> &nbsp;
						</td>
					</tr>

					<tr class="form-field">
						<th scope="row"><label for="title">Titre</label></th>
						<td><input name="title" id="title" type="text" value="<?php if (isset($release)) echo $release->title; ?>"<?php if ($action == 'delete') echo ' readonly="readonly"'; ?>></td>
					</tr>

					<tr class="form-field">
						<th scope="row"><label for="download_depositfiles">Liens de téléchargement</label></th>
						<td>
							<input name="download_depositfiles" id="download_depositfiles" type="<?php if (wpmanga_get('wpmanga_disable_depositfiles', 0)) echo 'hidden'; else 'url'; ?>" placeholder="Entrez le lien Deposit Files ici." style="width:90%;" value="<?php if (isset($release)) echo $release->download_depositfiles; ?>"<?php if ($action == 'delete') echo ' readonly="readonly"'; ?>>
							<label for="download_depositfiles"><?php if (!wpmanga_get('wpmanga_disable_depositfiles', 0)) echo '<img src="' . plugin_sURL(). 'images/download-icon-depositfiles-24.png" width="24px" style="vertical-align: middle; padding-bottom: 2px"><br>'; ?></label>

							<input name="download_fileserve" id="download_fileserve" type="<?php if (wpmanga_get('wpmanga_disable_fileserve', 0)) echo 'hidden'; else 'url'; ?>" placeholder="Entrez le lien FileServe ici." style="width:90%;" value="<?php if (isset($release)) echo $release->download_fileserve; ?>"<?php if ($action == 'delete') echo ' readonly="readonly"'; ?>>
							<label for="download_fileserve"><?php if (!wpmanga_get('wpmanga_disable_fileserve', 0)) echo '<img src="' . plugin_sURL(). 'images/download-icon-fileserve-24.png" width="24px" style="vertical-align: middle; padding-bottom: 2px"><br>'; ?></label>

							<input name="download_filesonic" id="download_filesonic" type="<?php if (wpmanga_get('wpmanga_disable_filesonic', 0)) echo 'hidden'; else 'url'; ?>" placeholder="Entrez le lien FileSonic ici." style="width:90%;" value="<?php if (isset($release)) echo $release->download_filesonic; ?>"<?php if ($action == 'delete') echo ' readonly="readonly"'; ?>>
							<label for="download_filesonic"><?php if (!wpmanga_get('wpmanga_disable_filesonic', 0)) echo '<img src="' . plugin_sURL(). 'images/download-icon-filesonic-24.png" width="24px" style="vertical-align: middle; padding-bottom: 2px"><br>'; ?></label>

							<input name="download_mediafire" id="download_mediafire" type="<?php if (wpmanga_get('wpmanga_disable_mediafire', 0)) echo 'hidden'; else 'url'; ?>" placeholder="Entrez le lien MediaFire ici." style="width:90%;" value="<?php if (isset($release)) echo $release->download_mediafire; ?>"<?php if ($action == 'delete') echo ' readonly="readonly"'; ?>>
							<label for="download_mediafire"><?php if (!wpmanga_get('wpmanga_disable_mediafire', 0)) echo '<img src="' . plugin_sURL(). 'images/download-icon-mediafire-24.png" width="24px" style="vertical-align: middle; padding-bottom: 2px"><br>'; ?></label>

							<input name="download_megaupload" id="download_megaupload" type="<?php if (wpmanga_get('wpmanga_disable_megaupload', 0)) echo 'hidden'; else 'url'; ?>" placeholder="Entrez le lien MEGA ici." style="width:90%;" value="<?php if (isset($release)) echo $release->download_megaupload; ?>"<?php if ($action == 'delete') echo ' readonly="readonly"'; ?>>
							<label for="download_megaupload"><?php if (!wpmanga_get('wpmanga_disable_megaupload', 0)) echo '<img src="' . plugin_sURL(). 'images/download-icon-megaupload-24.png" width="24px" style="vertical-align: middle; padding-bottom: 2px"><br>'; ?></label>

							<input name="download_pdf" id="download_pdf" type="<?php if (wpmanga_get('wpmanga_disable_pdf', 0)) echo 'hidden'; else 'url'; ?>" placeholder="Entrez le lien pour le fichier PDF ici." style="width:90%;" value="<?php if (isset($release)) echo $release->download_pdf; ?>"<?php if ($action == 'delete') echo ' readonly="readonly"'; ?>>
							<label for="download_pdf"><?php if (!wpmanga_get('wpmanga_disable_pdf', 0)) echo '<img src="' . plugin_sURL(). 'images/download-icon-pdf-24.png" width="24px" style="vertical-align: middle; padding-bottom: 2px"><br>'; ?></label>
						</td>
					</tr>

					<?php if (!wpmanga_get('wpmanga_reader',1) == 1) { ?>
					<tr class="form-field">
						<th scope="row"><label for="link_reader">Chapter Links</label></th>
						<td>
							<input name="link_reader" id="link_reader" type="url" placeholder="Enter chapter link here." style="width:90%;" value="<?php if (isset($release)) echo $release->link_reader; ?>"<?php if ($action == 'delete') echo ' readonly="readonly"'; ?>>
							<label for="link_reader"><img src=" <?php echo plugin_sURL(); ?>images/download-icon-onlinereader-24.png" width="24px" style="vertical-align: middle; padding-bottom: 2px"><br></label>
						</td>
					</tr>
					<?php } ?>
					<tr class="form-field">
						<th scope="row"><label for="language">Langue</label></th>
						<td>
							<select name="language" id="language" style="width:460px" <?php if ($action == 'delete') echo ' readonly="readonly"'; ?>>
								<?php
								foreach (wpmanga_listLanguages() as $language => $description) {
									if (preg_match("/(edit|delete)/i", $action)) {
										if ($release->language == $language)
											echo "<option value='{$language}' selected='selected'>{$description}</option>";
										else
											echo "<option value='{$language}'>{$description}</option>";
									} else {
										if ('en' == $language)
											echo "<option value='{$language}' selected='selected'>{$description}</option>";
										else
											echo "<option value='{$language}'>{$description}</option>";
									}
								}
								?>
							</select>
						</td>
					</tr>

<?php // end add by busaway ?>

					<tr class="form-field">
						<th scope="row"><label for="download_irc">Commande de téléchargement IRC</label></th>
						<td>
							<input name="download_irc" id="download_irc" type="text" placeholder="/MSG BOTNAME XDCC SEND #1  ou  !TRIGGER1" style="width:91%;" value="<?php if (isset($release)) echo $release->download_irc; ?>"<?php if ($action == 'delete') echo ' readonly="readonly"'; ?>>
							<label for="download_irc"><img src="<?php echo plugin_sURL(); ?>images/download-icon-irc.png" width="24px" style="vertical-align: middle; padding-bottom: 2px"></label>
						</td>
					</tr>

					<tr class="form-field">
						<th scope="row"><label for="unixtime">Date/Heure de sortie</label></th>
						<td>
							<input name="unixtime_datetime" id="unixtime_datetime" type="text">
							<input name="unixtime" id="unixtime" type="hidden" value="0">
							<script type="text/javascript">
								jQuery('#unixtime_datetime').datetimepicker();
								<?php if (!preg_match("/(edit|delete)/i", $action)) { ?>
								jQuery('#unixtime_datetime').datetimepicker('setDate', (new Date()));
								<?php } else { ?>
								jQuery('#unixtime_datetime').datetimepicker('setDate', (new Date(<?php echo $release->unixtime; ?> * 1000)));
								<?php } ?>

								datetime = Date.parse(jQuery('#unixtime_datetime').val()) / 1000;
								jQuery('#unixtime').val(datetime);

								jQuery(document).click(function() {
									datetime = Date.parse(jQuery('#unixtime_datetime').val()) / 1000;
									jQuery('#unixtime').val(datetime);
								});
							</script>
						</td>
					</tr>
					<?php if (wpmanga_get('wpmanga_release_statuspublished',0) != 0) { ?>
					<tr class="form-field">
						<th scope="row"><label for="status">Status</label></th>
						<td><input name="status" id="status" type="number" value="<?php if (isset($release)) echo $release->status; else echo '0'; ?>"<?php if ($action == 'delete') echo ' readonly="readonly"'; ?>></td>
					</tr>
					<?php } ?>

				</table>

				<p class="submit">
					<input type="submit" class="button-primary" name="save" value="<?php echo ucfirst($action); ?> Release">
					<input type="hidden" name="wpmanga_nonce" value="<?php echo wp_create_nonce( plugin_basename( plugin_sDIR() . '/wpmanga.php' ) ); ?>">
				</p>
			</form>
		</div>
<?php
	} else {
?>
		<script type="text/javascript">
			location.replace("admin.php?page=manga/project")
		</script>
<?php
	}
}

/* EOF: admin/data_release.php */