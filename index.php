<?php  
/* 
 * Plugin Name: wpCJ Testimonials
 * Plugin URI: http://www.wpcj.com/plugins/testimonials
 * Description: Manage a list of testimonials that you can place anywhere you want using shortcodes, php calls, widgets, smoke signals... you name it.
 * Version: 1.0.4
 * Author: Williams Castillo
 * Author URI: http://www.wpcj.com/
 *  
*/
/*  Copyright 2009  Williams Castillo  (email : eduven@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

$wpcjt_version = get_option( "wpcjt_version" );

if ($wp_version < "2.7") {
	define("WPCJT_URL", "edit.php?page=wpcjTestimonials/index.php");
}
else {
	define("WPCJT_URL", "options-general.php?page=wpcjt");
}
define(WPCJT, "WPCJT");
define(WPCJT_DB_VERSION, "4");		// change it when a database update is needed.

$plugin_dir = basename(dirname(__FILE__));
load_plugin_textdomain( WPCJT, 'wp-content/plugins/' . $plugin_dir, $plugin_dir );

if ( is_admin() ){ // admin actions
	add_action('admin_menu','wpcjt_menu');
	add_action('admin_init', 'wpcjt_register_settings' );

	register_activation_hook(__FILE__,'wpcjt_activate');
	add_action('plugins_loaded', 'wpcjt_activate');
	
	if ( function_exists('register_uninstall_hook') )
		register_uninstall_hook(__FILE__,'wpcjt_delete_settings');	
		
	//***** Add Item to Favorites Menu *****
	add_filter('favorite_actions', 'wpcjt_add_menu_favorite');

}
add_shortcode('wpcjt', 'wpcjt_shortcodes');
add_action("plugins_loaded", "wpcjt_widget_init");

function wpcjt_activate() {		
	return wpcjt_init_settings();
}

function wpcjt_register_settings() {
	register_setting( 'wpcjt-group', 'wpcjt_template' );	
	register_setting( 'wpcjt-group', 'wpcjt_delete_me' );
	register_setting( 'wpcjt-group', 'wpcjt_nofollow' );
	register_setting( 'wpcjt-group', 'wpcjt_size' );
}

function wpcjt_menu() {
	add_options_page('wpCJ Testimonials', 'wpCJ Testimonials', 8, 'wpcjt', 'wpcjt_handle_input');
}

function wpcjt_handle_input() {
	if ( $_REQUEST['bulkaction'] == 'delete' ) {
		$_REQUEST['action'] = 'delete';
	}
	$action = $_REQUEST['action'];
	switch ($action) {
		case 'usermanual':
			wpcjt_show_user_manual();
		break;
		
		case 'delete':
			if ($_REQUEST['tid']) {
				$tids = explode(',',$_REQUEST['tid']);
				if ($_REQUEST['delete'] == 1) {
					foreach ($tids as $key => $tid) {
						wpcjt_delete_testimonial($tid);
					}
					wpcjt_show_testimonials_form();
				} else {
					echo '
					<div class="wrap">
					<div id="icon-options-general" class="icon32"><br /></div>
					<h2>'.__('wpCJ Testimonials - Delete a testimonial',WPCJT).'</h2>
					';
					echo __('Do you really want to delete this testimonial?',WPCJT);
					echo '<br/><br/><a style="color: red" href="'.WPCJT_URL.'&amp;action=delete&delete=1&tid='.$tids[0].'">'.__('YES! Delete it!',WPCJT).'</a>&nbsp;&nbsp;&nbsp;<a href="'.WPCJT_URL.'&amp;action=testimonials">'.__('No, sorry. I changed my mind.',WPCJT).'</a>';
					echo '</div>';
					echo '<div style="clear:both;"></div><br/><br/>';
					
					echo '<div style="border: 1px solid #000; float: left; width:250px;">';
					wpcjt_show_testimonial($tids[0]);
					echo '</div>';
				}
			} elseif ( $_REQUEST['chk'] ) {
				$chk = $_REQUEST['chk'];
				$tids = implode(',',$chk);
				echo '
				<div class="wrap">
				<div id="icon-options-general" class="icon32"><br /></div>
				<h2>'.__('wpCJ Testimonials - Delete testimonials',WPCJT).'</h2>
				';
				
				echo __('Do you really want to delete these testimonials?',WPCJT);
				echo '<br/><br/><a style="color: red" href="'.WPCJT_URL.'&amp;action=delete&delete=1&tid='.$tids.'">'.__('YES! Delete them all!',WPCJT).'</a>&nbsp;&nbsp;&nbsp;<a href="'.WPCJT_URL.'&amp;action=testimonials">'.__('No, sorry. I changed my mind.',WPCJT).'</a>';
				echo '<div style="clear:both;"></div><br/><br/>';
				
				foreach ($chk as $key => $tid) {
					echo '<div style="border: 1px solid #000;float: left; width:250px; margin-right: 10px;">';
					wpcjt_show_testimonial($tid);
					echo '</div>';					
				}
				
				echo '</div>';
				
			}
		break;
		
		case 'settings':
			wpcjt_show_settings_form();
		break;
		
		default:
		case 'testimonials':
			if ( get_option('wpcjt_template') == '' ) {
				wpcjt_show_settings_form();
			} else {
				wpcjt_show_testimonials_form();
			}
		break;
	}
	
}

function wpcjt_show_settings_form() {
$nofollow 		= '';
$follow 		= '';

$zapme 			= get_option('wpcjt_delete_me');
$checked		= '';
$warning		= '';
if ($zapme) {
	$checked = 'checked="checked"';
	$warning = '<strong>&nbsp; OK, the dice has been thrown so <span style="color:#f00;">WATCH OUT!</span> The next time you deactivate this plugin you will lose everything.</strong>';
}
if (get_option('wpcjt_nofollow') == 'nofollow') {
	$nofollow = 'selected="selected"';
} else {
	$follow = 'selected="selected"';
}

$save 			= __('Save Changes',WPCJT);
$wpcjt_template = get_option('wpcjt_template');

$wpcjt_size		= get_option('wpcjt_size');

if ($wpcjt_template == '') {
	$wpcjt_template = '<blockquote>
[IMAGE_TAG][TESTIMONIAL]<br/>
<p align="right">-- [LCOMPANY]</p></blockquote>
<br/>';
	$lazy = __('<br/>(by the way, we have added a basic template, just in case you feel lazy today --you still need to click on \'Save Changes\', ok?)',WPCJT);
}
?>
	<div class="wrap">
	<div id="icon-options-general" class="icon32"><br /></div>
	<h2><?php _e('wpCJ Testimonials - Manage Settings',WPCJT); ?></h2>
	
	<div style="float:left; width: 70%;">
	<form method="post" action="options.php">
	<?php settings_fields( 'wpcjt-group' ); ?>	
	<table class="form-table" style="width: 100%;">
	
	<tr valign="top">
	<th scope="row"><?php _e('Template',WPCJT); ?></th>
	<td>
		<?php _e('Specify the HTML layout you would like the testimonials to have.<br/><br/>Make sure it includes the following pseudo-tags:<br/>[CLIENT] [COMPANY] [LCOMPANY] [WEBSITE] [TESTIMONIAL]<br/><br/>... they will be replaced at runtime.',WPCJT); ?>
		<?php _e('If a testimonial has a website defined, LCOMPANY will show the company as a link to the website.<br>',WPCJT); ?>
		<?php _e('[IMAGE_URL] will return the url of the image related to the testimonial. [IMAGE_TAG] will return the image tag. I recommend to use this because it does some error checking before sending the result to the browser. By the way, the css class of the image is wpcjt_picture so you should defined it somewhere.<br>',WPCJT); ?>
		<?php echo $lazy; ?>
	<br/><textarea rows="8" cols="50" name="wpcjt_template"><?php echo $wpcjt_template; ?></textarea></td>
	</tr>
	 
	<tr valign="top">
	<th scope="row"><?php _e('Link Attribute to use:',WPCJT); ?></th>
	<td><select name="wpcjt_nofollow"><option <?php echo $follow; ?> value="follow">Follow</option><option <?php echo $nofollow; ?> value="nofollow">No Follow</option>
	</select></td>
	</tr>	 

	<tr valign="top">
	<th scope="row"><?php _e('Max. size for images:',WPCJT); ?></th>
	<td><select name="wpcjt_size">
		<option <?php echo ($wpcjt_size== 50?'selected="selected"':''); ?> value="50">50KB</option>
		<option <?php echo ($wpcjt_size==100?'selected="selected"':''); ?> value="100">100KB</option>
		<option <?php echo ($wpcjt_size==250?'selected="selected"':''); ?> value="250">250KB</option>
		<option <?php echo ($wpcjt_size==500?'selected="selected"':''); ?> value="500">500KB</option>
	</select></td>
	</tr>	 
	
	<tr valign="top">
	<th scope="row"><?php _e('Zap me!',WPCJT); ?></th>
	<td><?php _e('WARNING: If you click on the following checkbox, you will be preparing this plugin for its complete deletion: Testimonial, Options, tables... all will be gone FOREVER... when you finally delete it<br/>Leave it blank if you are not sure about it.',WPCJT); ?><br/>
	<input type="checkbox" <? echo $checked; ?> name="wpcjt_delete_me" value="1" /><? echo $warning; ?></td>
	</tr>

	</table>
	
	<p class="submit">
	<input type="submit" class="button-primary" value="<? echo $save; ?>" />
	</p>
	
	</form>
	</div>
	<?php wpcjt_show_usermenu('settings'); ?>
	</div>
<?php

echo $retval;
}

function wpcjt_show_testimonials_form() {
global $wpdb;

	$tid 			= $wpdb->escape($_REQUEST['tid']);
	$client 		= $wpdb->escape($_REQUEST['client']);
	$company 		= $wpdb->escape($_REQUEST['company']);
	$website 		= $wpdb->escape($_REQUEST['website']);
	$product 		= $wpdb->escape($_REQUEST['product']);
	$testimonial 	= $wpdb->escape($_REQUEST['testimonial']);
	
	if ( isset($_REQUEST['submit']) ) {
		if ($client != '' && $testimonial != '') { 
			if ($tid != '') {
				$sql = "UPDATE ".$wpdb->prefix."wpcj_testimonials SET client='$client',company='$company',website='$website',testimonial='$testimonial',product='$product' WHERE id = $tid";
				$result = $wpdb->query($sql);
			} else {
				$sql = "INSERT INTO ".$wpdb->prefix."wpcj_testimonials (client,company,website,testimonial,product) values ('$client','$company','$website','$testimonial','$product')";
				$result = $wpdb->query($sql);
			}
			if ( $wpdb->last_error == '' ) {
				if ($tid == '' && $wpdb->last_error == '') {
					$tid = $wpdb->insert_id;
				}
				if ($tid != '' && $_FILES['picture']['tmp_name'] != '' && $wpdb->last_error == '') {
					//echo nl2br(print_r($_FILES,true));
					
					$picture = '';
					$new_path = wpcjt_get_plugin_url('path').'/pictures';
					if (!is_dir($new_path)) {
						mkdir($new_path,0755);
					}
					if ($new_path != '' && is_dir($new_path)) {
						$tmp_data = pathinfo($_FILES['picture']['name']);
						
						$tmp_data['extension'] = strtolower($tmp_data['extension']);
						if ( strpos('jpg,jpeg,gif,png',$tmp_data['extension']) !== false ) {
							$filename = $tid .'.'. $tmp_data['extension'];
							$new_path .= '/'.$filename;
							
							if(move_uploaded_file($_FILES['picture']['tmp_name'], $new_path)) {
								$picture = $filename;
							} else {
								echo 
									'<div id="message" class="error fade">
										<p>'.__('The testimonial was saved, but we found an error while trying to upload the pìcture. Please, verify its extension (jpg, gif or png), and that it is no larger than 100KB.',WPCJT).'</p>
									</div>
									';    
							}
						} else {
							echo 
								'<div id="message" class="error fade">
									<p>'.__('The testimonial was saved, but the file has a wrong file extension. It must be jpg, gif or png.',WPCJT).'</p>
								</div>
								';    
						}
					} else {
						echo 
							'<div id="message" class="error fade">
								<p>'.__('The testimonial was saved, but we can\'t create the folder for the picture. Please verify',WPCJT).'</p>
							</div>
							';    
					}
					if ($picture != '') {
						$sql = "UPDATE ".$wpdb->prefix."wpcj_testimonials SET picture='$picture' WHERE id = $tid";
						$result = $wpdb->query($sql);
					}
				}

				$tid 			= '';
				$client 		= '';
				$company 		= '';
				$website 		= '';
				$product 		= '';
				$testimonial 	= '';
				
				$success 		= 1;
			} else {
				$success 		= 0;
			}
		} else {
			$success 		= 0;
		}
	}

	$tid 			= $_REQUEST['tid'];
	$client 		= $_REQUEST['client'];
	$company 		= $_REQUEST['company'];
	$website 		= $_REQUEST['website'];
	$product 		= $_REQUEST['product'];
	$testimonial 	= $_REQUEST['testimonial'];
	
?>
	<div class="wrap">
	<div id="icon-options-general" class="icon32"><br /></div>
	<h2><?php _e('wpCJ Testimonials - Manage Testimonials',WPCJT); ?></h2>
<?php
	if ($success) {
		echo '
		<div id="message" class="updated fade">
			<p>'.__('Testimonial saved.',WPCJT).'</p>
		</div>
		';
	} elseif (isset($_REQUEST['submit'])) {
		echo '
		<div id="message" class="error fade">
			<p>'.__('Somehow, the testimonial couldn\'t be saved. Please, try again (remember: Client and Testimonial are mandatories).',WPCJT);
		if ($wpdb->lasterror != '') {
			echo '<br/>Database return: '.$wpdb->lasterror;
		}
		echo '</p>
		</div>
		';
	}
?>
	<?php wpcjt_show_usermenu('testimonials'); ?>
	<div style="float:left; width: 70%;">
	<form id="testimonial_form" name="testimonial_form" method="post" action="<?php echo WPCJT_URL; ?>&amp;action=testimonials" enctype="multipart/form-data" >	
	
	<table class="form-table" width="50%">
	
	<tr valign="top">
	<th scope="row"><?php _e('Client Name',WPCJT); ?>:</th>
	<td><input type="text" class="regular-text" name="client" value="<?php echo stripslashes($client); ?>" /></td>
	</tr>
	 
	<tr valign="top">
	<th scope="row"><?php _e('Company',WPCJT); ?>:</th>
	<td><input type="text" class="regular-text" name="company" value="<?php echo stripslashes($company); ?>" /></td>
	</tr>
	
	<tr valign="top">
	<th scope="row"><?php _e('Website',WPCJT); ?>:</th>
	<td><input type="text" class="regular-text" name="website" value="<?php echo $website; ?>" /></td>
	</tr>

	<tr valign="top">
	<th scope="row"><?php _e('Related product',WPCJT); ?>:</th>
	<td><input type="text" class="regular-text" name="product" value="<?php echo stripslashes($product); ?>" /></td>
	</tr>

	<tr valign="top">
	<th scope="row"><?php _e('Image',WPCJT); ?>:</th>
	<td><input type="file" name="picture" /></td>
	</tr>

	<tr valign="top">
	<th scope="row"><?php _e('Testimonial',WPCJT); ?>:</th>
	<td><textarea rows="7" cols="50" name="testimonial"><?php echo stripslashes($testimonial); ?></textarea></td>
	</tr>
	 
	</table>
	
	<input type="hidden" name="action" value="update" />
	<input type="hidden" name="tid" value="<?php echo $tid; ?>" />
	
	<p class="submit">
	<input type="submit" class="button-primary" name="submit" value="<?php _e('Save Changes',WPCJT) ?>" />&nbsp;&nbsp;&nbsp;
	<input class="button-secondary" type="reset" name="reset" value="<?php _e('Blank fields',WPCJT); ?>"/>
	</p>
	
	</form>
	</div>
	
	</div>
	<div style="clear:both;"></div>
<?php

	srand();
	$rand = rand(26,36);
	if ( $rand == 31 ) wpcjt_donate();  //Why 31? It's my lucky number!
	
	wpcjt_list_testimonials();
}

function wpcjt_show_user_manual() {	
?>
	<div class="wrap">
	<div id="icon-options-general" class="icon32"><br /></div>
	<h2><?php _e('wpCJ Testimonials - User Manual',WPCJT); ?></h2>	
	<?php wpcjt_show_usermenu('usermanual'); ?>
	To use wpCJ Testimonials is, hopefully, very very simple.<br/><br/>
	
	This plugin has three pages that you can be accessed from the top right corner of its pages: Manage Settings, Manage Testimonials and User Manual (this one).<br/><br/>	
	
	Page: <strong>Manage Settings</strong>
	<ul>
	<li>Template: This setting is used to specify the "layout" of a testimonial. You can use a pure HTML template or, much better, a CSS powered template. It's up to you.</li>
	<li>Link Attribute: You can specify if the links that the plugin create from the website of the testimonials, will have the attribute rel="nofollow" or not. If you don't have a clue about what it is, leave it "follow" or google it.</li>
	<li>Max. Size for images: This is the maximum KB that an image can weight. Default: 100KB</li>
	<li>Zap me!: The data this plugin uses is persistent: It is in your database even if you deactivate the plugin. However, if you are really mad about it and you want to get rid of it once and for all, click on this field and the proceed to the plugin module and delete it (man! I'm so sorry to hear this plugin caused you so much problems!)</li>
	</ul>	
	<br/><br/>
	Page: <strong>Manage Testimonials</strong><br/>
	There is nothing strange here. It just shows a form where you can add or edit you testimonials.<br/>
	The only thing I think deserves an explanation it's the field named "Product":
	<blockquote>
	If you are using this plugin to store testimonials for just one thing (your website, your company, yourself), then it would probably be useless for you.<br/>rHowever, if you would like to collect different testimonials for different stuff (your Service A and your Product B), then it could be a life saver (well... not that much): You just need to specify two different "products" when you add those testimonials. Then, when you need to display your testimonials you just need to specify which product you are willing to show in that particular place. Cool, isn't it?</li>
	</blockquote>
	<br/><br/>OK Will.. This plugin is great but... <strong><em>How in heavens I can show the world the hundreds of wonderful testimonials I've inserted in my database?</em></strong><br/><br/>
	Easy man! You have several options:<blockquote>
	<ul>
	<li><u>Using Widgets:</u> You can add as many wpCJ Testimonials widgets as you need. You just need to specify the "Product" and how many testimonials you would like to show (well... and the title of the widget).</li>
	<li><u>Using PHP calls:</u> If you need to add testimonials to your template, you can do so by inserting a simple php call like this:<br/>
	<textarea onclick="this.focus();this.select()" readonly="readonly" rows="2" cols="80">
&lt;?php if ( function_exists('wpcjt') ) wpcjt($limit,$product,$id = ''); ?>
</textarea><br/>Remember to replace the $limit and $product variables for actual values! If you specify an ID, it will override the other parameters. I.e.- wpcjt(10,'ABC',15) will show Testimonial Number 15 no matter what product it belongs to... and, obviously, it won't be showed 10 times!</li>
	<li><u>Using shortcodes:</u> And finally, if you need to add your testimonials to any post or page, you can do it by inserting a shortcode in your post:<br/><strong>[wpcjt limit="4" product="Service A" id="15"]<br/></strong>(once again, remember to change the 4 and Service A for actual values -both are optional by the way, or just specify which testimonial you want to show by entering its ID)</li>
	</ul></blockquote><br/><br/>
	There is a special <em>ID</em>: <strong>last</strong>. By using id='last' you will get the latest $limit testimonials.
	<br/><br/>
	Did I mentioned that you can get the ID of any testimonial directly from the list of testimonials?	
	<br/><br/>
	I think that's all for now. I hope you find it useful and easy to use. That's what I had in mind while I was coding it.
	<br/><br/>
	All best,<br/>Will<br/><br/><em>
<?php
	wpcjt_donate(false);
?>
	</div>
<?php
}

function wpcjt_donate($clickable = true) { ?>
<!-- BEGIN DONATE BOX -->
<div id="donate" style="border-color:#CC0000;margin:5px 15px 15px;background-color:#FFFBCC;border-style:solid;border-width:1px;padding:0 0.6em;">

	<div style="float:right;">
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
			<input type="hidden" name="cmd" value="_s-xclick">
			<input type="hidden" name="hosted_button_id" value="7122511">
			<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
			<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
		</form>
	</div>
	
	Heavens! So much work dried my throat... I'm feeling thristy... I would like to drink a couple beers but unfortunelly I ran out of money. If there were only a caritative soul that could pay me a beer or two! (hint! hint! ==================></em>
<?php if ($clickable) { ?>
	<br/><a href="#" style="color:#f00" onclick="javascript:document.getElementById('donate').style.visibility='hidden'; return false;">I DON'T CARE! Just leave me alone right now!</a>
<?php }
	echo '
</div>
<div style="clear:both;"></div>
<!-- END DONATE BOX -->
';
}

function wpcjt_delete_settings() {
global $wpdb;
	delete_option('wpcjt_version');
	if( !defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') )
		exit();
	
	$zapme = get_option('wpcjt_delete_me');
	if ($zapme) {
		delete_option('wpcjt_template');
		delete_option('wpcjt_nofollow');
		delete_option('wpcjt_delete_me');
		delete_option('wpcjt_size');
		
		unlink(wpcjt_get_plugin_url('path').'/pictures/*');
		rmdir(wpcjt_get_plugin_url('path').'/pictures');
				
		$wpdb->query('DROP TABLE '.$wpdb->prefix.'wpcj_testimonials');	
	}
}

function wpcjt_create_tables() {
global $wpdb;

	$table_name = $wpdb->prefix.'wpcj_testimonials';
	$version = get_option('wpcjt_version');
	
	if($wpdb->get_var("show tables like '$table_name'") != $table_name || $version < WPCJT_DB_VERSION) {
		$sql = "CREATE TABLE $table_name (
	              id int(11) NOT NULL auto_increment,  
	              client varchar(60) NOT NULL,
	              company varchar(100) NULL,
	              website varchar(255) NULL,
	              product varchar(100) NULL,
	              testimonial text,
	              picture varchar(100) NULL,
              PRIMARY KEY  (id)
            	);
				";		
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		return true;
	}
	return false;
}

function wpcjt_init_settings() {
	$version = get_option('wpcjt_version');
	if ($version < WPCJT_DB_VERSION) {
		if (wpcjt_create_tables()) {
			update_option('wpcjt_version',WPCJT_DB_VERSION);
		}
	}
}

function wpcjt_widget_init() {  		
// Link Widget
	$prefix = 'wpcjt_'; // $id prefix
	$name = __('wpCJ Testimonials',WPCJT);
	$widget_ops = array('classname' => 'wpcjt_multi', 'description' => __('Add testimonials to your sidebars.',WPCJT));
	$control_ops = array('width' => 200, 'height' => 200, 'id_base' => $prefix);
	
	$options = get_option('wpcjt_multi');
	if(isset($options[0])) unset($options[0]);
	
	if(!empty($options)){
		foreach(array_keys($options) as $widget_number){
			wp_register_sidebar_widget($prefix.'-'.$widget_number, $name, 'wpcjt_widget', $widget_ops, array( 'number' => $widget_number ));
			wp_register_widget_control($prefix.'-'.$widget_number, $name, 'wpcjt_widget_control', $control_ops, array( 'number' => $widget_number ));
		}
	} else{
		$options = array();
		$widget_number = 1;
		wp_register_sidebar_widget($prefix.'-'.$widget_number, $name, 'wpcjt_widget', $widget_ops, array( 'number' => $widget_number ));
		wp_register_widget_control($prefix.'-'.$widget_number, $name, 'wpcjt_widget_control', $control_ops, array( 'number' => $widget_number ));
	}
	
}  

function wpcjt_widget_control($args) {
global $wpdb;

	$prefix = 'wpcjt_'; // $id prefix
 
	$options = get_option('wpcjt_multi');
	if(empty($options)) $options = array();
	if(isset($options[0])) unset($options[0]);
 
	// update options array
	if(!empty($_POST[$prefix]) && is_array($_POST)){
		foreach($_POST[$prefix] as $widget_number => $values){
			if(empty($values) && isset($options[$widget_number])) // user clicked cancel
				continue;
 
			if(!isset($options[$widget_number]) && $args['number'] == -1){
				$args['number'] = $widget_number;
				$options['last_number'] = $widget_number;
			}
			$options[$widget_number] = $values;
		}
 
		// update number
		if($args['number'] == -1 && !empty($options['last_number'])){
			$args['number'] = $options['last_number'];
		}
 
		// clear unused options and update options in DB. return actual options array
		$options = bf_smart_multiwidget_update($prefix, $options, $_POST[$prefix], $_POST['sidebar'], 'wpcjt_multi');
	}
 
	// $number - is dynamic number for multi widget, gived by WP
	// by default $number = -1 (if no widgets activated). In this case we should use %i% for inputs
	//   to allow WP generate number automatically
	$number = ($args['number'] == -1)? '%i%' : $args['number'];
 
	// now we can output control
	$opts = @$options[$number];
 
	$title 	= @$opts['title'];
	$limit	= @$opts['limit'];
	$product= @$opts['product'];
	$id		= @$opts['id'];
 	echo __('Title',WPCJT).':<br />
		<input type="text" name="'.$prefix.'['.$number.'][title]" value="'.$title.'" /><br/><br/>
	';
 	echo __('Number of testimonials',WPCJT).':<br />
 		<input type="text" name="'.$prefix.'['.$number.'][limit]" value="'.$limit.'" /><br/><br/>
 	';
 	echo __('Product',WPCJT).':<br />
 		<input type="text" name="'.$prefix.'['.$number.'][product]" value="'.$product.'" /><br/><br/>
 	';
 	echo __('ID',WPCJT).':<br />
 		<input type="text" name="'.$prefix.'['.$number.'][id]" value="'.$id.'" /><br/><br/>
 	';
}

function wpcjt_widget($args,$instance) {
    extract($args);	    
	if ( is_numeric($widget_args) )
		$widget_args = array( 'number' => $widget_args );
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	extract($widget_args, EXTR_SKIP);
	
	$options = get_option('wpcjt_multi');

	if ( !isset($options[$instance['number']]) )
		return;

	$title  = $options[$instance['number']]['title'];
	$limit	= $options[$instance['number']]['limit'];
	$product= $options[$instance['number']]['product'];
	$id		= $options[$instance['number']]['id'];
	
	echo $before_widget; 
	if ($title != '') {	
		echo $before_title; 
	    echo $title;
	    echo $after_title;
	}
	
    wpcjt($limit,$product,$id);
    
    echo $after_widget;
}

if(!function_exists('bf_smart_multiwidget_update')){
	// This function is obsolete in WP 2.8+ but it is here for backward compatibility.
	function bf_smart_multiwidget_update($id_prefix, $options, $post, $sidebar, $option_name = ''){
		global $wp_registered_widgets;
		static $updated = false;
 
		// get active sidebar
		$sidebars_widgets = wp_get_sidebars_widgets();
		if ( isset($sidebars_widgets[$sidebar]) )
			$this_sidebar =& $sidebars_widgets[$sidebar];
		else
			$this_sidebar = array();
 
		// search unused options
		foreach ( $this_sidebar as $_widget_id ) {
			if(preg_match('/'.$id_prefix.'-([0-9]+)/i', $_widget_id, $match)){
				$widget_number = $match[1];
 
				// $_POST['widget-id'] contain current widgets set for current sidebar
				// $this_sidebar is not updated yet, so we can determine which was deleted
				if(!in_array($match[0], $_POST['widget-id'])){
					unset($options[$widget_number]);
				}
			}
		}
 
		// update database
		if(!empty($option_name)){
			update_option($option_name, $options);
			$updated = true;
		}
 
		// return updated array
		return $options;
	}
}

function wpcjt($limit, $product = '', $id = '', $echo=true) {
global $wpdb;
	$order = 'ORDER BY RAND()';
	if (strtolower($id) == 'last') {
		$order = 'ORDER BY id desc';
	} elseif ($id != '') {
		$id = ' AND id = '.$id;
	}
	$sql = 'SELECT client,company,website,testimonial,picture
			FROM '.$wpdb->prefix.'wpcj_testimonials
			WHERE product=\''.$wpdb->escape($product).'\'
			'.$id.'
			'.$order.'
			LIMIT 0,'.$wpdb->escape($limit);
	
	//echo $sql;die();
	$testimonials = $wpdb->get_results($sql);
	
	$template = get_option('wpcjt_template');
	if ( get_option('wpcjt_nofollow') == 'nofollow' ) {
		$nofollow = ' rel="nofollow" ';
	}
	foreach ((array) $testimonials as $t) {
		$current = $template;
		
		if ($t->website != '' && $t->company != '') {
			$current = str_replace('[LCOMPANY]','<a href="[WEBSITE]" '.$nofollow.' target="_blank" title="[COMPANY]">[CLIENT],<br/>[COMPANY]</a>',$current);
		} elseif ($t->website != '') {
			$current = str_replace('[LCOMPANY]','<a href="[WEBSITE]" '.$nofollow.' target="_blank" title="[WEBSITE]">[CLIENT],<br/>[WEBSITE]</a>',$current);
		} elseif ($t->company != '') {
			$current = str_replace('[LCOMPANY]','[CLIENT],<br/>[COMPANY]',$current);
		} else {
			$current = str_replace('[LCOMPANY]','[CLIENT]',$current);
		}
		
		$current = str_replace('[CLIENT]',stripslashes($t->client),$current);
		$current = str_replace('[COMPANY]',stripslashes($t->company),$current);
		$current = str_replace('[WEBSITE]',$t->website,$current);
		$current = str_replace('[TESTIMONIAL]',stripslashes($t->testimonial),$current);
		$current = str_replace('[IMAGE_URL]',wpcjt_get_plugin_url().'/pictures/'.$t->picture,$current);
		
		if ($t->picture != '') {
			if ( file_exists( wpcjt_get_plugin_url('path').'/pictures/'.$t->picture ) ) {
				if ($t->client != '' && $t->company != '') {
					$alt = stripslashes($t->client) .', '. stripslashes($t->company);
				} elseif ($t->client != '') {
					$alt = stripslashes($t->client);
				} elseif ($t->company != '') {
					$alt = stripslashes($t->company);
				} else {
					$alt = '';
				}
				
				$picture_tag = '<img src="'.wpcjt_get_plugin_url().'/pictures/'.$t->picture.'" class="wpcjt_picture" alt="'.$alt.'" title="'.$alt.'" />';
				$current = str_replace('[IMAGE_TAG]',$picture_tag,$current);
			}
		} else {
			$current = str_replace('[IMAGE_TAG]','',$current);
		}
		
		$retval .= $current;		
	}
	
	if ( $echo ) {
		echo $retval;
	} else {
		return $retval;
	}	
}

function wpcjt_shortcodes($atts) {
	extract(shortcode_atts(array(
		'limit' => '',
		'product' => '',
		'id' => ''
	), $atts));	
	
	if ($limit == '') {		
		$limit = 1;
	}
	
	$retval = wpcjt($limit,$product,$id,false);
	
	return $retval;
}

function wpcjt_show_usermenu($current = 'testimonials',$echo = true) {
	$settings 		= '<a title="Manage Settings" href="'.WPCJT_URL.'&amp;action=settings">'.__('Manage settings',WPCJT).'</a><br/>';
	$testimonials 	= '<a title="Manage Testimonials" href="'.WPCJT_URL.'&amp;action=testimonials">'.__('Manage Testimonials',WPCJT).'</a><br/>';
	$usermanual 	= '<a title="Online Manual" href="'.WPCJT_URL.'&amp;action=usermanual">'.__('Online Manual',WPCJT).'</a><br/>';
	$wpcj 			= '<br/>Sponsor: <a title="wpCJ - WordPress & Commission Junction Working Together!" target="_blank" href="http://www.wpcj.com/features/">http://www.wpcj.com</a><br/>';
	
	switch ($current) {
		case 'settings':
			$settings = '<strong>Manage Settings</strong><br/>';
		break;
		
		case 'usermanual':
			$usermanual = '<strong>Online Manual</strong><br/>';
		break;
		
		default:
		case 'testimonials':
			$testimonials = '<strong>Manage Testimonials</strong><br/>';
			break;
	}
	
	$retvalue = '<div style="float:right;width: 30%;"><div style="float:right; width: 200px; background: #ff9; border:1px solid black;"><center>'.$settings.$testimonials.$usermanual.$wpcj.'</center></div></div>';
	
	if ( $echo ) {
		echo $retvalue;
	} else {
		return $retvalue;
	}	
}

function wpcjt_list_testimonials() {
global $wpdb;

	$query = "SELECT id,client,company,website,product,testimonial FROM ".$wpdb->prefix."wpcj_testimonials ORDER BY company,client";
	
	$paged = $_REQUEST['paged'];
	if ($paged == '') {
		$paged = 1;
	}
	$query .= ' LIMIT ' . ( ($paged-1) * 25 ) . ',25';
	
	$testimonials = $wpdb->get_results($query);
	$rows 		= '';
	$rowclass 	= '';
	$p 			= 0;
	foreach ( (array) $testimonials as $t ) {
		if ($rowclass == '') {
			$rowclass = 'class="alternate" ';
		} else {
			$rowclass = '';
		}
		
		$tid 		= $t->id;
		$product 	= stripslashes($t->product);
		$client 	= stripslashes($t->client);
		$company 	= stripslashes($t->company);
		$website 	= $t->website;
		$testimonial= stripslashes($t->testimonial);
		
		if ($website != '') {
			if (substr($website,0,7) != 'http://' ) {
				$website = "http://$website";
			}
			$website = '<a target="_blank" href="'.$website.'">'.$website.'</a>';
		}
		
		$rows .= '
		<tr id="row_'.$t->id.'" valign="middle"  '.$rowclass.'>
			<th scope="row" class="check-column"><input type="checkbox" name="chk[]" value="'.$tid.'" /></th>
				<td class="column-client" style="text-align: left;">'.$client.'<br/>ID: '.$tid.($product!=''?' | (made for: '.$product.')':'');
		$rows .= '
				<div class="row-actions">
					<span class="edit"><a href="javascript:fill_form(\''.$tid.'\',\''.addslashes($client).'\',\''.addslashes($company).'\',\''.$t->website.'\',\''.addslashes($product).'\',\''.htmlentities(addslashes($testimonial),ENT_QUOTES,get_option('blog_charset')).'\');">'.__('Edit',WPCJT).'</a></span>
				</div>';
		$rows .= '
			</td>
			<td class="column-company" style="text-align: left;">'.$company.'</td>
			<td class="column-webside" style="text-align: left;">'.$website.'</td>
			<td class="column-product" style="text-align: left;">'.$testimonial.'
				<div class="row-actions">
					<span class="edit"><a style="color: red" href="'.WPCJT_URL.'&amp;action=delete&tid='.$t->id.'">'.__('Delete',WPCJT).'</a></span>
				</div>
			</td>
		</tr>
		';
		$p++;
	}
	
	if ($rows != '') {
		echo '<h3>'.__('Current testimonials',WPCJT).'</h3>';
		
		echo '<form name="list_form" action="/wp-admin/options-general.php">';	
		echo '<input type="hidden" name="page" value="'.$_REQUEST['page'].'" />
		<script languege="javscript">
			function fill_form(id,client,company,website,product,testimonial)
			{
				document.testimonial_form[\'tid\'].value = id;
				document.testimonial_form[\'client\'].value = client;
				document.testimonial_form[\'company\'].value =company;
				document.testimonial_form[\'website\'].value = website;
				document.testimonial_form[\'product\'].value = product;
				document.testimonial_form[\'testimonial\'].value = testimonial;
			}		
		</script>
		';
		echo '<div class="tablenav">
				<div class="alignleft actions">
					<select name="bulkaction">
					<option selected="selected" value="">'.__('Bulk Actions',WPCJT).'</option>
					<option value="delete">'.__('Delete testimonials',WPCJT).'</option>
					</select>
					<input id="doaction" class="button-secondary action" type="submit" name="doaction" value="'.__('Apply',WPCJT).'"/>
				</div>
			</div>
			
			<table class="widefat fixed" cellspacing="0">
				<thead>
					<tr>
						<th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox" /></th>
						<th scope="col" id="client" class="manage-column column-client" style="">'.__('Client',WPCJT).'</th>
						<th scope="col" id="company" class="manage-column column-company" style="">'.__('Company',WPCJT).'</th>
						<th scope="col" id="website" class="manage-column column-website" style="">'.__('Website',WPCJT).'</th>
						<th scope="col" id="testimonial" class="manage-column column-testimonial" style="">'.__('Testimonial',WPCJT).'</th>
					</tr>
				</thead>
			
				<tfoot>
					<tr>
						<th scope="col" class="manage-column column-cb check-column" style=""><input type="checkbox" /></th>
						<th scope="col" class="manage-column column-client" style="">'.__('Client',WPCJT).'</th>
						<th scope="col" class="manage-column column-company" style="">'.__('Company',WPCJT).'</th>
						<th scope="col" class="manage-column column-website" style="">'.__('Website',WPCJT).'</th>
						<th scope="col" class="manage-column column-testimonial" style="">'.__('Testimonial',WPCJT).'</th>
					</tr>
				</tfoot>	
				<tbody>
			';	
		echo $rows;
		echo '
				</tbody>
			</table>
		</form>
		';	
	}
}

function wpcjt_show_testimonial($tid,$echo = true) {
global $wpdb;
	$sql = 'SELECT client,company,website,testimonial,picture
			FROM '.$wpdb->prefix.'wpcj_testimonials
			WHERE id='.$tid;
	
	$testimonials = $wpdb->get_results($sql);
		
	$template = get_option('wpcjt_template');
	if ( get_option('wpcjt_nofollow') == 'nofollow' ) {
		$nofollow = ' rel="nofollow" ';
	}
	foreach ((array) $testimonials as $t) {
		$current = $template;
		
		if ($t->website != '' && $t->company != '') {
			$current = str_replace('[LCOMPANY]','<a href="[WEBSITE]" '.$nofollow.' target="_blank" title="[COMPANY]">[CLIENT],<br/>[COMPANY]</a>',$current);
		} elseif ($t->website != '') {
			$current = str_replace('[LCOMPANY]','<a href="[WEBSITE]" '.$nofollow.' target="_blank" title="[WEBSITE]">[CLIENT],<br/>[WEBSITE]</a>',$current);
		} elseif ($t->company != '') {
			$current = str_replace('[LCOMPANY]','[CLIENT],<br/>[COMPANY]',$current);
		} else {
			$current = str_replace('[LCOMPANY]','[CLIENT]',$current);
		}
				
		$current = str_replace('[CLIENT]',stripslashes($t->client),$current);
		$current = str_replace('[COMPANY]',stripslashes($t->company),$current);
		$current = str_replace('[WEBSITE]',$t->website,$current);
		$current = str_replace('[TESTIMONIAL]',stripslashes($t->testimonial),$current);
		$current = str_replace('[IMAGE_URL]',wpcjt_get_plugin_url().'/pictures/'.$t->picture);
		
		if ($t->picture != '') {
			if ( file_exists( wpcjt_get_plugin_url('path').'/pictures/'.$t->picture ) ) {
				if ($t->client != '' && $t->company != '') {
					$alt = stripslashes($t->client) .', '. stripslashes($t->company);
				} elseif ($t->client != '') {
					$alt = stripslashes($t->client);
				} elseif ($t->company != '') {
					$alt = stripslashes($t->company);
				} else {
					$alt = '';
				}
				
				$picture_tag = '<img src="'.wpcjt_get_plugin_url().'/pictures/'.$t->picture.'" class="wpcjt_picture" alt="'.$alt.'" />';
				$current = str_replace('[IMAGE_TAG]',$picture_tag);
			}
		}		
		$retval .= $current;		
	}
	
	if ( $echo ) {
		echo $retval;
	} else {
		return $retval;
	}	
}

function wpcjt_delete_testimonial($tid) {
global $wpdb;
	$sql = 'DELETE FROM '.$wpdb->prefix.'wpcj_testimonials
			WHERE id='.$tid;
	return $wpdb->query($sql);
}

function wpcjt_add_menu_favorite($actions) {
	$actions[WPCJT_URL] 		= array('wpCJ Testimonials', 'manage_options');		
	return $actions;
}
		
function wpcjt_get_plugin_url($type='url') {
	if ( !defined('WP_CONTENT_URL') )
		define( 'WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
	if ( !defined('WP_CONTENT_DIR') )
		define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
	if ($type=='path') { return WP_CONTENT_DIR.'/plugins/'.plugin_basename(dirname(__FILE__)); }
	else { return WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__)); }
}

?>