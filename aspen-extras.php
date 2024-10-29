<?php
/*
Plugin Name: Aspen Theme Extras
Plugin URI: http://weavertheme.com
Description: Aspen Theme Extras - This add-on to the Aspen Theme adds several new features: extra Save/Restore options, uploading and activation of add-on subthemes, switch subthemes on a per page basis, clear Aspen settings.
Author: Bruce Wampler
Author URI: http://weavertheme.com
Version: 1.1
License: GPL

Aspen Theme Extras
Copyright (C) 2013, Bruce E. Wampler - aspen@aspenthemeworks.com

GPL License: http://www.opensource.org/licenses/gpl-license.php

This program is distributed in the hope that it will be useful, but
WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

define ('ASPEN_X_VERSION','1.1');
define ('ASPEN_X_MINIFY', '');

function aspen_extras_installed() {
    return true;
}

function aspen_extras_submitted($submit_name) {
    // do a nonce check for each form submit button
    // pairs 1:1 with aspen_extras_nonce
    $nonce_act = $submit_name.'_act';
    $nonce_name = $submit_name.'_nonce';

    if (isset($_POST[$submit_name])) {
	if (isset($_POST[$nonce_name]) && wp_verify_nonce($_POST[$nonce_name],$nonce_act)) {
	    return true;
	} else {
	    die("WARNING: invalid form submit detected ($submit_name). Probably caused by session time-out, or, rarely, a failed security check.");
	}
    } else {
	return false;
    }
}

function aspen_extras_nonce($submit_name) {
    // pairs 1:1 with aspen_extras_sumbitted
    // will be one for each form submit button
    wp_nonce_field($submit_name.'_act',$submit_name.'_nonce');
}

function aspen_extras_save_msg($msg) {
    echo '<div id="message" class="updated fade" style="width:80%;"><p><strong>' . $msg .
	    '</strong></p></div>';
}
function aspen_extras_error_msg($msg) {
    echo '<div id="message" class="updated fade" style="background:#F88; width:80%;"><p><strong>' . $msg .
	    '</strong></p></div>';
}

//==============================================================
// process actions

function aspen_extras_process() {
    // add a nonced form for each needed action

    if (aspen_extras_submitted('atw_extras_action')) {
	if (isset($_POST['savethemename']) && $_POST['savethemename'] != '') {
	    $name = esc_html(strtolower(str_replace(' ','-',$_POST['savethemename'])));
	    $aspen_opts = get_option('aspen_settings',array());

	    $themes = get_option('aspen_switch_themes',array());
	    $themes[$name]['aspen_base'] = $aspen_opts;

	    update_option('aspen_switch_themes',$themes);

	    aspen_extras_save_msg('Current Aspen Theme Settings saved as subtheme "' . $name . '"');
	    unset($_POST['atw_extras_action']);
	} else {
	    aspen_extras_error_msg('No name provided to save theme.');
	}
    }

    if (aspen_extras_submitted('atw_extras_deleteall')) {
	delete_option('aspen_switch_themes');
	aspen_extras_save_msg('All Themes deleted from saved theme list.');
    }

    if (aspen_extras_submitted('atw_extras_delete')) {
	if (isset($_POST['deletename']) && $_POST['deletename'] != '') {
	    $to_delete = $_POST['deletename'];
	    $themes = get_option('aspen_switch_themes',array());
	    if (isset($themes[$to_delete]))
		unset($themes[$to_delete]);
	    update_option('aspen_switch_themes',$themes);
	    aspen_extras_save_msg('Theme deleted from saved theme list: ' . $_POST['deletename']);
	} else {
	    aspen_extras_error_msg('No name provided to delete.');
	}
    }
    if (aspen_extras_submitted('clear_aspen_main')) {
	delete_option(apply_filters('aspen_options','aspen_settings'));
	aspen_extras_save_msg("Main Aspen Theme Settings Cleared");
    }

    if (aspen_extras_submitted('clear_aspen_all')) {
	delete_option(apply_filters('aspen_options','aspen_settings'));
	delete_option( 'aspen_settings_backup' );
	delete_option('aspen_switch_themes');
	aspen_extras_save_msg("All Aspen Theme Settings Cleared");
    }

}

function aspen_extras_admin() {
    if ( !current_user_can( 'manage_options' ) )  {
	wp_die('You do not have sufficient permissions to access this page.');
    }

    // process commands
    aspen_extras_process();

    // display forms
?>
    <div class="atw-wrap">
	<div id="icon-themes" class="icon32"></div>
	<div style="float:left;padding-top:8px;"><h2>Aspen Extras - Version <?php echo ASPEN_X_VERSION;?></h2></div>
    <div style="clear:both;"></div>
	<h3><u>Extra Options for the Aspen Theme</u></h3>
	<ul style="list-style:disc inside;">
		<li><strong><em>Upload and Manage Add-on Subthemes</em></strong> - <em style="color:blue;">Adds options to the Aspen
		"Aspen Subthemes" tab</em> that allow you to easily upload and manage new free and premium add-on subthemes and child
		themes you can download from AspenTheme.com.</li>
		<li><strong><em>Update Aspen Theme Versions</em></strong> - <em style="color:blue;">Adds options to the Aspen
		"Save/Restore" tab</em> that allows you to update Aspen with the latest versions from AspenTheme.com.</li>
		<li><strong><em>Per Page Theme Switcher</em></strong> - You can save subthemes and use them on a Per Page basis. The
		admin options for this feature are included below.</li>
		<li><strong><em>Clear Aspen Setttings</em></strong> - Clear Aspen's settings from the database</li>
	</ul>
    <hr />
    <div id="tabwrap_plus" style="padding-left:5px;">
	<div id="tab-container-plus" class='yetii'>
	<ul id="tab-container-plus-nav" class='yetii'>

	<li><a href="#switcher" title="Theme Switcher"><?php echo(aspen_x_t_('Theme Switcher' /*a*/ )); ?></a></li>
	<li><a href="#clear_set" title="Clear Settings"><?php echo(aspen_x_t_('Clear Aspen Settings' /*a*/ )); ?></a></li>
	</ul>

	<div id="switcher" class="tab_plus" > <!-- Theme Switcher -->

<?php 	aspen_extras_admin_switcher(); ?>

	</div>

	<div id="clear_set" class="tab_plus" > <!-- CLEAR SETTINGS -->
<?php
	aspen_extras_clean();
?>
	</div>

	</div>
    </div>
    <script type="text/javascript">
	var tabber2 = new Yetii({
	id: 'tab-container-plus',
	tabclass: 'tab_plus',
	persist: true
	});
</script>

<?php
    // Check post-actions
}

//========================================================================
// theme switcher settings

function aspen_extras_admin_switcher() {
?>
	<h3 style="color:blue;">Theme Switcher Administration</h3>
	<p>The Aspen Extras Theme Switcher is an advanced option that will be really useful to some
	Aspen Theme users. It allows you to save a copy of your current settings in the WordPress Database
	using a name of your choosing. That name can then be used set an alternative Per Page subtheme for individual
	static pages, or dynamically by adding <code>?atw_theme=subtheme-name</code> to the link to any page on the site.</p>

	<p>If you use the link parameter method to switch themes (e.g., http://example.com/altpage/?atw_theme=subtheme-name),
	then a cookie will be set, and all pages on the site will be displayed with the alternate subtheme for 20 minutes.
	using ?atw_theme=default will delete the cookie and change back to the default theme.</p>

	<p>Create your alternative subtheme using the following instructions, and save it.
	Then, you can define a Per Page Custom Field for any
	static page. Set the <em>Name</em> of the Custom Field to <code>aspen_switch_theme</code>. Set the <em>Value></em>
	to the name you specify in the "Save Current Theme is Subtheme DB List" below (exactly as it appears in the "Select Theme to Delete"
	list box). You can set as many pages as you want
	to use the same subtheme, or you can even use several different saved subthemes.
	This feature only works for regular static pages or page with posts - no archive-like pages. It also works
	if you are logged in as an admin, although the alternate theme will not be used for the Page Editor.
	The link parameter <code>?atw_theme=name</code> version will work on any page.
	</p>
	<h3>Instructions - Saving Alternative Subthemes</h3>
	<ol>
	    <li>Create a subtheme with ALL options set as desired.</li>
	    <li>Enter a name for your theme - You can be descriptive, but should be short. Name will be normalized
	    to all lower case with blanks converted to dashes (-).</li>
	    <li>Click the "Save Current Theme as Named" button.</li>
	    <li>Repeat for each theme you want to define.</li>
	    <li>Use a saved subtheme on a per page basis:
        <br />Add a Custom Field called <em>aspen_switch_theme</em>
	    to the specific static page from the Pages editor.</li>
        <li>Use a saved subtheme dynamically:
        <br />Specify <code>?atw_theme=name</code>
	    at the end of a link to a page: <code>http://example.com/?atw_theme=blue</code></li>
	</ol>
	<h3>Important - Use Save/Restore to save your alternate subthemes</h3>
	<p>There is no way to retrieve alternative subthemes settings saved from this plugin. You should be sure to use
	the Save/Restore tab to save a copy of the subthemes used for this plugin in an alternative location such
	as downloading it to your own computer.</p>
	<hr />
	<h3>Save Current Theme in Subtheme DB List</h3>
	<form id="wpatw_switcher" name="wpatw_switcher" action="<?php echo $_SERVER["REQUEST_URI"]; ?>" method="post">
	    Name for saved theme: <input type="text" name="savethemename" size="30" />&nbsp;<small>Please provide a short name the saved theme, then click the "Save Current Theme as Named" button.</small>

	    <p class="submit">
	    <input type="submit" name="atw_extras_action" value="Save Current Theme as Named" />
	    </p>
<?php aspen_extras_nonce('atw_extras_action'); ?>
	</form>
<?php
    $themes = get_option('aspen_switch_themes',array());
?>
	<strong>Currently saved subthemes:&nbsp;&nbsp; </strong>
<?php
    foreach ($themes as $theme => $val) {
	echo '<em>' . $theme . '</em>&nbsp;&nbsp;&nbsp;';
    }
?>
	<hr />
	<h3>Delete Saved Theme</h3>

	<form id="wpatw_switcher2" name="wpatw_switcher2" action="<?php echo $_SERVER["REQUEST_URI"]; ?>" method="post">
	    <p>This drop-down list shows all the subthemes you've saved. Click the Delete button to delete selected sub-theme.</p>
	    <select name="deletename" > <option value=""><?php echo esc_html('Select Theme to Delete'); ?></option>
<?php
    foreach ($themes as $theme => $val) {
	echo ('<option value="' . $theme . '">' . esc_attr($theme) . "</option>\n");
    }
?>
	    </select>
	    <p class="submit">
	    <input type="submit" name="atw_extras_delete" value="Delete Selected Saved Theme" />
	    </p>
<?php aspen_extras_nonce('atw_extras_delete'); ?>
	</form>
	<hr />
	<form id="wpatw_switcher3" name="wpatw_switcher3" action="<?php echo $_SERVER["REQUEST_URI"]; ?>" method="post"
	onSubmit="return confirm('Are you sure you want to all saved themes?');">
	    <p>Use this option to delete all the saved themes from the database.</p>
	    <p class="submit">
	    <input type="submit" name="atw_extras_deleteall" value="Delete All Saved Themes" />
	    </p>
<?php 	aspen_extras_nonce('atw_extras_deleteall'); ?>
	</form>
	<hr />
<?php
}

function aspen_extras_load_styles() {
    // include any style sheet needed
    wp_enqueue_style('atw_x_Stylesheet', aspen_x_plugins_url('/atw-admin-style', ASPEN_X_MINIFY . '.css'));

    wp_enqueue_script('atw_Yetii', aspen_x_plugins_url('/js/yetii/yetii',ASPEN_X_MINIFY.'.js'));

}

function aspen_extras_add_page() {
    // the 'aspen_switcher' is the ?page= name for forms - use different if not add_theme_page
    $page = add_theme_page(
	'Aspen Extras','&nbsp;<span style="color:orange;">&spades;</span>Aspen Extras','manage_options','aspen_extras', 'aspen_extras_admin');
    add_action('admin_print_styles-' . $page, 'aspen_extras_load_styles');
}

add_action('admin_menu', 'aspen_extras_add_page',6);

function aspen_ts_pp_switch() {
    global $aspen_cur_page_ID;

    global $post;
    $aspen_cur_page_ID = 0;	// need this for 404 page when this is not valid
    if (is_object($post))
	$aspen_cur_page_ID = get_the_ID();	// we're on a page now, so set the post id for the rest of the session
    $name = '';
    $set_cookie = false;

    if (isset($_GET['atw_theme'])) {
	$name = wp_kses_data($_GET['atw_theme']);
	$set_cookie = true;
    }

    if ($name == '' || strlen($name) > 24) {
	$name = aspen_get_per_page_value('aspen_switch_theme');
	$set_cookie = false;	// don't set cookie on a per page theme
    }

    if ($name == ''
	&& isset($_COOKIE['atw_switch_theme'])
    ) {		// okay, could be a cookie set

	$name = $_COOKIE['atw_switch_theme'];
	$set_cookie = false;
    }

    if ($name == 'default') {
	$url = parse_url(home_url( '/' ));
	$domain = $url['host'];
	$path = '/';
	setcookie('atw_switch_theme',$name,time()-3600,$path,$domain);	// 20 minutes (60*20)
    } else if ($name  != '') {	// see if there is an alternate per page theme
	// echo '<!-- Per Page Theme:' . $name . '  -->' . "\n";
	// okay - have an alternate theme now - reset basic and pro options
	$themes = get_option('aspen_switch_themes',array());
	if (isset($themes[$name]['aspen_base'])) {
	    // successful switch...

	    $opts = $themes[$name]['aspen_base'];
	    aspen_opt_cache($opts);

	    if ($set_cookie) {		// don't reset cookie
		$url = parse_url(home_url( '/' ));
		$domain = $url['host'];
		$path = '/';
		setcookie('atw_switch_theme',$name,time()+1200,$path,$domain);	// 20 minutes (60*20)
	    }
	}
    }

}

function aspen_x_t_($m) {
    return $m;
}

function aspen_x_plugins_url($file,$ext) {
    return plugins_url($file,__FILE__) . $ext;
}

//========================================================================
// Clean Settings

function aspen_extras_clean() {

?>
    <h2>Clean Aspen Theme Settings</h2>

	<p>If you will not be using the Aspen Theme any longer, this Aspen Extras features allows you
	to remove Aspen settings from the WordPress database. The options will be removed, and can't
	be recovered once deleted.
	<br /><br />
	<strong style="color:red;">IMPORTANT WARNING!</strong> - clearing settings will remove your current settings.
	You should use the Aspen Save/Restore to save you settings to your computer first!
	</p>

	<form id="aspx_clean" name="aspx_clean" action="<?php echo $_SERVER["REQUEST_URI"]; ?>" method="post"
	    onSubmit="return confirm('Are you sure you want to reset main Aspen Theme related settings?');" >
		<span class="submit"><input type="submit" name="clear_aspen_main" value="Clear Aspen Settings"/></span>
		-- This will clear just the main Aspen settings.
		<?php aspen_extras_nonce('clear_aspen_main'); ?>
	</form>
	<br />
	<br />
	<form id="aspx_clean_all" name="aspx_clean_all" action="<?php echo $_SERVER["REQUEST_URI"]; ?>" method="post"
	    onSubmit="return confirm('Are you sure you want to reset ALL Aspen Theme related settings?');" >
		<span class="submit"><input type="submit" name="clear_aspen_all" value="Clear ALL Aspen Settings"/></span>
		-- This will clear Aspen related settings: main theme settings, saved theme settings, all alternate switch themes.
		<em>Use this option with caution!</em>
		<?php aspen_extras_nonce('clear_aspen_all'); ?>
	</form>
	<hr />

<?php
}

// =======================================================================
// Save/Restore extras
// Add-on Subthemes

define ('ASPEN_MARKET',true);

if (ASPEN_MARKET)
    add_filter('aspen_child_extrathemes','aspen_child_extrathemes_filter');
function aspen_child_extrathemes_filter($msg) {
    return '';
}

//===============================

if (ASPEN_MARKET)
    add_action('aspen_child_show_extrathemes','aspen_child_show_extrathemes_action');

function aspen_child_show_extrathemes_action() {
    echo '<h3 class="atw-option-subheader">Select an Add-on Subtheme You Have Uploaded</h3>';
    $addon_dir = aspen_f_uploads_base_dir() . 'aspen-subthemes/addon-subthemes/';
    $addon_url = aspen_f_uploads_base_url() . 'aspen-subthemes/addon-subthemes/';

    $addon_list = array();
    if($media_dir = @opendir($addon_dir)){	    // build the list of themes from directory
	while ($m_file = readdir($media_dir)) {
	    $len = strlen($m_file);
	    $base = substr($m_file,0,$len-4);
	    $ext = $len > 4 ? substr($m_file,$len-4,4) : '';
	    if($ext == '.ath' ) {
		$addon_list[] = $base;
	    }
	}
    }

    if (!empty($addon_list)) {
	natcasesort($addon_list);

	$cur_addon = aspen_getopt('aspopt_addon_name');
	if ($cur_addon)
	    echo '<h3>Currently selected Add-on Subtheme: ' . ucwords(str_replace('-',' ',$cur_addon)) . '</h3>';

?>
<form enctype="multipart/form-data" name='pick_added_theme' action="<?php echo $_SERVER["REQUEST_URI"]; ?>" method='post'>

 <h4>Select an add-on subtheme: </h4>

<?php
    foreach ($addon_list as $addon) {
	$name = ucwords(str_replace('-',' ',$addon));
?>
	<div style="float:left; width:200px;">
	    <label><input type="radio" name="aspopt_addon_name"
<?php	    echo 'value="' . $addon . '"' . (aspen_getopt('aspopt_addon_name') == $addon ? 'checked' : '') .
		'/> <strong>' . $name . '</strong><br />
		<img style="border: 1px solid gray; margin: 5px 0px 10px 0px;" src="' . $addon_url . $addon . '.jpg" width="150px" height="113px" /><label></div>' . "\n";

    }
?>
    <div style="clear:both;"></div>
    <br /><span class='submit'><input name="set_added_subtheme" type="submit" value="Set to Selected Add-on Subtheme" /></span>&nbsp;
    <span style="color:#b00;"><strong>Note:</strong> Selecting a new subtheme will change only theme related settings. Most Advanced Options will be retained.
    You can use the Save/Restore tab to save a copy of all your current settings first.</span>

	<?php aspen_nonce_field('set_added_subtheme'); ?>

	<br /><br /><span class='atw-small-submit' style="margin-left:100px;"><input name="delete_added_subtheme" type="submit" value="Delete Selected Add-on Subtheme" /></span> &nbsp;<small>This will delete the selected Add-on Subtheme from the Add-on directory</small>
	<?php aspen_nonce_field('delete_added_subtheme'); ?>
    </form>
<?php
    } else {
?>
	<p>No Add-on Subthemes available.</p>
<?php
    }
echo '<h3 class="atw-option-subheader">Upload an Add-on Subtheme From Your Computer</h3>';
?>
<form name='form_added_theme' enctype="multipart/form-data" action="<?php echo $_SERVER["REQUEST_URI"]; ?>" method="POST">
    <table>
	<tr valign="top">
	    <td><strong>Select Add-on Subtheme .zip file to upload:</strong>
		<input name="uploaded_addon" type="file" />
		<input type="hidden" name="uploadaddon" value="yes" />
            </td>
	</tr>
        <tr><td>
	    <span style="margin-left:50px;" class='submit'>
		<input name="upload_addon" type="submit" value="Upload Add-on Subtheme" />
	    </span>&nbsp;<small><strong>Upload and Save</strong> an Add-on Subtheme or Subtheme collection from .zip file on your computer. Will be saved on your site's filesystem.</small>
	</td></tr>
    </table>
    <?php aspen_nonce_field('upload_addon'); ?>
</form>

<?php
}

add_action('aspen_child_process_options','aspen_child_process_options');
function aspen_child_process_options() {

    if (aspen_submitted('set_added_subtheme') ) {	// Set to selected addon - theme
	if (isset($_POST['aspopt_addon_name']))
	{
	    $name = $_POST['aspopt_addon_name'];

	    $openname = aspen_f_uploads_base_dir() . 'aspen-subthemes/addon-subthemes/' . $name . '.ath';
	    $contents = file_get_contents($openname);

            if (!aspen_ex_set_current_to_serialized_values($contents,'ASPEN_CONVERT_WIIit:'.$openname)) {
                echo '<div id="message" class="updated fade"><p><strong><em style="color:red;">Sorry,
		there was a problem uploading your add on theme. The name you picked did not have a valid
		Aspen theme file in  the /wevaerii-subthemes/addon-subthemes directory.</em></strong></p></div>';
	    } else {
                aspen_save_msg('Aspen theme options reset to ' .
		    ucwords(str_replace('-',' ',$name )) . ' add-on subtheme.');
		aspen_setopt('aspopt_addon_name',$name);
            }
	}
    }

    else if (aspen_submitted('delete_added_subtheme') ) {	// Delete selected addon theme
	if (isset($_POST['aspopt_addon_name']))
	{
	    $name = $_POST['aspopt_addon_name'];
	    @unlink(aspen_f_uploads_base_dir() . 'aspen-subthemes/addon-subthemes/' . $name . '.ath');
	    @unlink(aspen_f_uploads_base_dir() . 'aspen-subthemes/addon-subthemes/' . $name . '.jpg');
	    aspen_save_msg('Deleted ' .
		    ucwords(str_replace('-',' ',$name )) . ' add-on subtheme.');
	}
    }

    else if (aspen_submitted('upload_addon')
	&& isset($_POST['uploadaddon'])
	&& $_POST['uploadaddon'] == 'yes') {
	// upload theme from users computer
	// they've supplied and uploaded a file
	$ok = aspen_unpackzip('uploaded_addon', aspen_f_uploads_base_dir() . 'aspen-subthemes/addon-subthemes/');
    }

    else if (aspen_submitted('upload_theme')) {
	// upload theme from users computer
	// they've supplied and uploaded a file

	if (isset($_FILES['uploaded_theme']['name']))	// uploaded_theme
	    $filename = $_FILES['uploaded_theme']['name'];
	else
	    $filename = "";

	$to = aspen_f_themes_dir();

	if (strpos($filename,'aspen-') === false && strpos($filename, 'aspen-') === false)
	{
?>
	    <div id="message" class="updated fade"><p><strong><em style="color:red;">ERROR</em></strong></p>
		<p>You did not select an Aspen theme .zip file: "<?php echo $filename;?>".
		The theme file name must start with 'aspen-'. Please use a file you downloaded from AspenTheme.com .</p>
	    </div>
<?php
	    return;
	}
	$ok = aspen_unpackzip('uploaded_theme',$to);
	if ($ok)
	    aspen_save_msg('Your Aspen Theme has been successfully updated. Please click "Clear Messages"
button right now to refresh the scrren and start using the updated version.');
    }

}

function aspen_unpackzip($uploaded, $to_dir) {
    // upload theme from users computer
    // they've supplied and uploaded a file
    // This version and the one in Aspen Plus must be identical...

    $ok = true;     // no errors so far

    if (isset($_FILES[$uploaded]['name']))	// uploaded_addon
        $filename = $_FILES[$uploaded]['name'];
    else
        $filename = "";

    if (isset($_FILES[$uploaded]['tmp_name'])) {
        $openname = $_FILES[$uploaded]['tmp_name'];
    } else {
        $openname = "";
    }

    //Check the file extension
    $check_file = strtolower($filename);
    $per = '.';
    $end = explode($per, $check_file);		// workaround for PHP strict standards warning
    $ext_check = end($end);

    if (false && !aspen_f_file_access_available()) {
	$errors[] = "Sorry - Aspen Theme unable to access files.<br />";
	$ok = false;
    }

    if ($filename == "") {
	$errors[] = "You didn't select a file to upload.<br />";
	$ok = false;
    }

    if ($ok && $ext_check != 'zip'){
	$errors[] = "Uploaded files must have <em>.zip</em> extension.<br />";
	$ok = false;
    }

    if ($ok) {
        if (!aspen_f_exists($openname)) {
            $errors[] = '<strong><em style="color:red;">
Sorry, there was a problem uploading your file. You may need to check your folder permissions
or other server settings.</em></strong><br />' . "(Trying to use file '$openname')";
            $ok = false;
        }
    }

    if ($ok) {
	// should be ready to go, but check out WP_Filesystem
	if (! WP_Filesystem()) {
	    function aspen_ex_return_direct() { return 'direct'; }
	    add_filter('filesystem_method', 'aspen_ex_return_direct');
	    $try2 = WP_Filesystem();
	    remove_filter('filesystem_method', 'aspen_ex_return_direct');
	    if (!$try2) {
		$errors[] = 'Sorry, there\'s a problem trying to use the WordPress unzip function. Please
see the FAQ at weavertheme.com support for more information.';
		$ok = false;
	    }
	}
    }
    if ($ok) {
	// $openname has uploaded .zip file to use
	// $filename has name of file uploaded
	$is_error = unzip_file( $openname, $to_dir );
	if ( !is_wp_error( $is_error ) ) {
	    aspen_save_msg('File ' . $filename . ' successfully uploaded and unpacked to: <br />' . $to_dir);
	    @unlink($openname);	// delete temp file...
	} else {
	    $errors[] = "Sorry, unpacking the .zip you selected file failed. You may have a corrupt .zip file, or there many a file permissions problem on your WordPress installation.";
	    $errors[] = $is_error->get_error_message();
	    $ok = false;
	}
    }
    if (!$ok) {
	echo '<div id="message" class="updated fade"><p><strong><em style="color:red;">ERROR</em></strong></p><p>';
	foreach($errors as $error){
	    echo $error.'<br />';
	}
	echo '</p></div>';
    }
    return $ok;
}

if (ASPEN_MARKET)
    add_action('aspen_child_saverestore','aspen_child_saverestore_action');
function aspen_child_saverestore_action() {
    echo '<h3 class="atw-option-subheader" style="font-style:italic">Use the <em>Aspen Subthemes</em>
 tab to upload Add-on Subthemes.</h3><p>You can upload extra add-on subthemes you\'ve downloaded using the
 Aspen Subthemes tab. Note: the Save and Restore options on this page are for the custom settings you
 have created. These save/restore options are not related to Add-on Subthemes, although you can
 modify an Add-on Subtheme, and save your changes here.</p>';
}

add_action('aspen_child_update','aspen_child_update_action');

function aspen_child_update_action() {
    echo '<h3 class="atw-option-subheader">*** Update Aspen theme from .zip file on your computer. ***</h3>';
    if ((!is_multisite() && current_user_can('install_themes')) || (is_multisite() && current_user_can('manage_network_themes')))
     {
?>
<form  enctype="multipart/form-data" action="<?php echo $_SERVER["REQUEST_URI"]; ?>" method="POST">
<p><strong></strong>This action will update the version of Aspen you are using right now with a version you've
downloaded from <em>AspenTheme.com</em>.</strong> Normally you can update Aspen using the standard WordPress
theme update process. This process will make it easy to update Aspen with the latest version from AspenTheme.com
before it becomes available on WordPress.org.</p>
    <table>
	<tr valign="top">
	    <td>
		<input name="uploaded_theme" type="file" />
		&nbsp;<strong>Select Aspen .zip file with version to update.</strong>
            </td>
	</tr>
        <tr><td>
	    <span class='submit'>
		<input name="upload_theme" type="submit" value="Update Aspen Theme" />
	    </span>&nbsp;<strong>Update Aspen</strong> -- Upload 'aspen' .zip file and upgrade theme.
	</td></tr>
    </table>
<?php
    $max_upload = (int)(ini_get('upload_max_filesize'));
    $max_post = (int)(ini_get('post_max_size'));
    $memory_limit = (int)(ini_get('memory_limit'));
    $upload_mb = min($max_upload, $max_post, $memory_limit);
    if ($upload_mb < 2) {
	echo '<p><strong style="color:red">WARNING! -- It appears your system upload
file size limit is less than 2Mb, which is too small for the Aspen theme .zip file. The upload
is likely to hang and fail if you continue. If your system limit is indeed less than 2Mb, you will need to have
it raised before you proceed. This may involve contacting your hosting company.</strong></p>';
    }
    aspen_nonce_field('upload_theme');
?>
</form>
<br />
<p>Note - using this Aspen theme update tool will directly update your /wp-content/themes/aspen/
directory with the new version. Any files you may have added to one of that
directory, such as a new language translation file, will not be removed. Thus, this provides an easy way to
update Aspen while retaining any new files you may have added.</p>
<?php
     } else {
	echo '<p>You must be an Admin or Super-Admin to update Aspen.</p>';
     }
}

?>
