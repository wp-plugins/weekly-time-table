<?php
/*
Plugin Name: Weekly TimeTable WP Plugin
Plugin URI: http://blog.fifteenpeas.com/wordpress/wordpress-weekly-time-table/
Description: create weekly time tables and display them on your site.
Version: 1.0
Author: X Villamuera
Author URI: http://blog.fifteenpeas.com 
*/

/*  Copyright 2010  X.Villamuera  (email : gzav@sio4.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

//create a class to avoid plugin collisions
global $wpdb;
$dir = preg_replace("/^.*[\/\\\]/", "", dirname(__FILE__));
define ("WTT_DIR", "/wp-content/plugins/" . $dir);
define("WTT_DIR_URL",get_bloginfo('wpurl') .WTT_DIR);
define ("WTT_DIR_I18N", WTT_DIR . "/locales");
define ("WTT_TIMETABLE",$wpdb->prefix . "wtt_timetables");
define ("WTT_ENTRYTABLE",$wpdb->prefix . "wtt_ttentries");


add_action('init', 'wttPlugIn_load_translation_file');

function wttPlugIn_load_translation_file() {
	load_plugin_textdomain( 'wttPlugIn', '', WTT_DIR_I18N );
}
	 


	/*
	** Database and app Installation function
	*/
	if(!function_exists("wtt_install")){
		
		function wtt_install () {
		   global $wpdb;
		   global $wtt_db_version;
		   $wtt_db_version 		= "1.0"; 
			
			
		   	//create timetables table
		   	// Check if exists
		  	if($wpdb->get_var("SHOW TABLES LIKE '".WTT_TIMETABLE."'") != WTT_TIMETABLE) {
		   	   	$sql = "CREATE TABLE " . WTT_TIMETABLE . " (
						id INT NOT NULL AUTO_INCREMENT,
						id_entry INT NOT NULL ,
						mon VARCHAR( 25 ) NULL ,
						tue VARCHAR( 25 ) NULL ,
						wed VARCHAR( 25 ) NULL ,
						thu VARCHAR( 25 ) NULL ,
						fri VARCHAR( 25 ) NULL ,
						sat VARCHAR( 25 ) NULL ,
						sun VARCHAR( 25 ) NULL,
						UNIQUE KEY id (id)				
						);";
		   	   	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				dbDelta($sql);
				//insert data
				$sql="insert into ".WTT_TIMETABLE." (id_entry,mon, tue, wed, thu, fri, sat, sun) values(1,'from 8am to 5pm','from 8am to 5pm','from 8am to 5pm','from 8am to 5pm','from 8am to 5pm','closed','closed');";
				$results = $wpdb->query( $sql );
				
		  	 	}
			//create entries table
			// check if exists
		  	if($wpdb->get_var("SHOW TABLES LIKE '".WTT_ENTRYTABLE."'") != WTT_ENTRYTABLE) {
			    $sql = "CREATE TABLE " . WTT_ENTRYTABLE . " (
					    id INT NOT NULL AUTO_INCREMENT,
					    entryname VARCHAR( 120 ),
					    UNIQUE KEY id (id)
					    );";
			    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				dbDelta($sql);
				
				// insert data
				$sql ="insert into ".WTT_ENTRYTABLE." (entryname) values('Drugstore');";
				$results = $wpdb->query( $sql );
				
				
				//add the db version into wordpress
				add_option("wtt_db_version", $wtt_db_version);	
				
		  	 	}
				
		  	// Upgrade Section (not needed here as it is the first one)
		  	$installed_ver = get_option( "wtt_db_version" );
		  	if( $installed_ver != $wtt_db_version ) {
		  		//the new sql version (but this is fake)
		  		$sql = "CREATE TABLE " . WTT_ENTRYTABLE . " (
					    id INT NOT NULL AUTO_INCREMENT  PRIMARY KEY,
					    entryname VARCHAR( 120 )
				   		);";
				dbDelta($sql);
				//change the version option of this plugin (not for this install)
		    	update_option( "wtt_db_version", $wtt_db_version );
		  		
		  	}//endif
		  	
		  	 	
		}//end of install 
	//execute the installation upon activation	
	register_activation_hook(__FILE__,"wtt_install");
	}



	/*
	** Admin Menu Creation
	*/
	function wtt_menu() {
		 if (function_exists('add_menu_page')) 
			{
				add_menu_page('Weekly Time Table', 'Wtt Time Tables', 'administrator', 'tophor','weeklytt_home');
		  	}
	
		 if (function_exists('add_submenu_page')) 
			{	// entry menus
				add_submenu_page( 'tophor', 'Manage entries for a new time table', 'Manage entries', 'administrator', 'mng_entry', 'mng_entry');
				// timetable menus
			 	add_submenu_page( 'tophor', 'Manage Weekly time table', 'Manage WTT', 'administrator', 'mng_wtt', 'mng_wtt');
			}
	}

	/*
	** Home page of the plugin
	*/
	function weeklytt_home()
	{
			$outp = '<div class="wrap">';
			$outp.= '<h1>'.__('Weekly Time Table','wttPlugIn').'</h1>';
			$outp .= '<h2>'.__('Time Table Management for your WordPress Site','wttPlugIn').'</h2>
			<p>This plugin lets you create time tables and dispaly them into your Wordpress site</p>';
			$outp .= "<p>This is an example:</p>";
			$outp.= wttdsp(0);
			$outp .= "<h2>".__('Usage','wttPlugIn')."</h2>";
			$outp .= "<ol><li>You must first create an entry to create a Time Table</li>
			<li>Create a time table. You can input whatever you want to display in the fields. The plugin doesn't format. However, length is limited to 25 caracters.</li>
			<li>Each <em>Wtt time table</em> has an id that should be used when displaying it using the shortcode.</li>
			<li>The shortcode to display the time table is <em><strong>[wttdsp entry_id=5]</strong></em>, where 5 is an id of a <em>time table</em>.</li>
			<li>Use css to style your timetable. A time table as an <em>id='wtt'</em> attribute.<br/>
			When displayed, the entry is in a container with attribute : <em>id='wttentry'</em>
			As an example, this is the css that is used in the admin side :<br/><br/>
			<em>#wtt {padding:2px;}<br/>
			#wtt th { background-color:#DDDDDD; padding:5px;}<br/>
			#wtt tr { background-color:#EEEEEE;padding:5px;}<br/>
			#wtt td {padding:3px; } </em></li>
			<li>It's a simple plugin, feel free to adapt it at will !</li>
			</ol>";
			$outp .= '<div><table class="widefat" style="margin-top: .5em"><thead><tr valign="top">	
			<th>Fifteenpeas Weekly Time Table WP plugin</th></tr></thead><tbody><tr>	
			<td>Find me on <a href="http://blog.fifteenpeas.com" target="_blank">http://blog.fifteenpeas.com</a>.<br />	
			The plugin homepage is at <a href="http://blog.fifteenpeas.com/wordpress/wordpress-weekly-time-table/" target="_blank">http://blog.fifteenpeas.com/wordpress/wordpress-weekly-time-table/</a>. 
			Like the software? Did i help you? <a href="http://blog.fifteenpeas.com/donate/" target="_blank">Show your appreciation</a>. Thanks!</td></tr></tbody></table</div>';
			$outp .= '</div>';
			echo $outp; 
	}

	/*
	** Select list box for entries
	*/
	function list_wttentries($style)
	{		// if $style is 1 then it's a select, 2 it's an unordered list
			global $wpdb;
			$delaction = "del_entry";
			$edtfrmaction ="edt_frm";
			
			$wpdb->show_errors(true);
			if ($style == 1)
			$outp ='<select name="id_entry">';
			else $outp ='<table id="wtt">';
			$sql= "select * from ".WTT_ENTRYTABLE;
			$rows = $wpdb->get_results($sql);
			foreach($rows as $row)
			{
				if ($style == 1)
				$outp.= '<option value="'.$row->id.'">'.$row->entryname.'</option>';
				else {
					//nonces construct for _GET
					$urldel = str_replace( '%7E', '~', $_SERVER['PHP_SELF']).'?page=mng_entry&pid='.$row->id.'&act=delentry';
					$urledt = str_replace( '%7E', '~', $_SERVER['PHP_SELF']).'?page=mng_entry&pid='.$row->id.'&act=edtfrm';
					$linkdel = wp_nonce_url(  $urldel, $delaction );
					$linkedt = wp_nonce_url(  $urledt, $edtfrmaction );
					
					$outp.= '<tr><td><a href="'.$linkedt.'" title="Edit an entry">';
					$outp .= '<img src="'.WTT_DIR_URL.'/img/pencil.png" alt="edit a time table"/></a>&nbsp;&nbsp;';
					$outp .= '<a href="'.$linkdel.'" title="delete an entry">';
					$outp .= '<img src="'.WTT_DIR_URL.'/img/cross.png" alt="delete a time table"/></a></td>';
					$outp .= '<td>'.$row->id.'</td><td> '.$row->entryname.'</td></tr>'; 
				}
			}
			if ($style == 1)
			$outp .= '</select>';
			else $outp .= '</table>';
			return $outp;
	}



	/*
	** Display the timeTable list in two flavours. 0: no edition, 1: edition
	*/
	function wttdsp($edition)
	{		 
			global $wpdb;
			//nonces construct
			$delaction = "del_wtt";
			$edtfrmaction ="edt_frm";
			
							   		
			$wpdb->show_errors(true);
			$sql = "select h.id, e.entryname, mon, tue, wed, thu, fri, sat, sun from ".WTT_TIMETABLE." h, ".WTT_ENTRYTABLE." e where h.id_entry = e.id";
			$outp='<table id="wtt">';
			$oupt.='<tr>';
			if ($edition==1) $outp .= '<th></th>'; 
			$outp.='<th>'.__('Id. Entry','wttPlugIn').'</th><th>'.__('mon','wttPlugIn').'</th><th>'.__('tue','wttPlugIn').'</th><th>'.__('wed','wttPlugIn').'</th><th>'.__('thu','wttPlugIn').'</th><th>'.__('fri','wttPlugIn').'</th><th>'.__('sat','wttPlugIn').'</th><th>'.__('sun','wttPlugIn').'</th></tr>';
	
			$rows = $wpdb->get_results($sql);
			foreach($rows as $row)
			{
				//nonces construct
				$urldel = str_replace( '%7E', '~', $_SERVER['PHP_SELF']).'?page=mng_wtt&pid='.$row->id.'&act=delwtt';
				$urledt = str_replace( '%7E', '~', $_SERVER['PHP_SELF']).'?page=mng_wtt&pid='.$row->id.'&act=edtfrm';
				$linkdel = wp_nonce_url(  $urldel, $delaction );
				$linkedt = wp_nonce_url(  $urledt, $edtfrmaction );
				
				$outp.= "<tr>";
				if ($edition==1) { 
					$outp.= '<td><a href="'.$linkedt.'" title="Edit a Time Table">';
					$outp .= '<img src="'.WTT_DIR_URL.'/img/pencil.png" alt="edit a time table"/></a>&nbsp;&nbsp;';
					$outp .= '<a href="'.$linkdel.'" title="delete a time table">';
					$outp .= '<img src="'.WTT_DIR_URL.'/img/cross.png" alt="delete a time table"/></a></td>'; 
				}
				$outp.= '<td><div class="wttentry">'.$row->id.'. '.$row->entryname.'</div></td><td>'.$row->mon.'</td><td>'.$row->tue.'</td><td>'.$row->wed.'</td><td>'.$row->thu.'</td><td>'.$row->fri.'</td><td>'.$row->sat.'</td><td>'.$row->sun.'</td></tr>';
			}  
			$outp.='</table><hr/>';
	
			return $outp;																	
	
	}

	/*
	** Table header
	*/
	function tablehead($plusentry = 0)
	{
			$outp = '<table  id="wtt"><tr>';
			if ($plusentry == 1)
			$outp .= '<th></th>';
			$outp .='<th>'.__('mon','wttPlugIn').'</th><th>'.__('tue','wttPlugIn').'</th><th>'.__('wed','wttPlugIn').'</th><th>'.__('thu','wttPlugIn').'</th><th>'.__('fri','wttPlugIn').'</th><th>'.__('sat','wttPlugIn').'</th><th>'.__('sun','wttPlugIn').'</th></tr>';
			return $outp;
	}


	/*
	 * Manage entries (CRUD)
	 */
	function mng_entry() {
			global $wpdb;
			$wpdb->show_errors(true);
			if (isset($_POST['act']) && ($_POST['act'] == "addentry"))
			{
				$sql = $wpdb->prepare("insert into ".WTT_ENTRYTABLE." (entryname) values(%s)",$_POST['entryname']);
				$wpdb->get_results($sql);
			}
			
			if (isset($_GET['act']) && ($_GET['act'] == "delentry"))
			{
				check_admin_referer('del_entry');
				//deletes the entry
				$sql=$wpdb->prepare("delete from ".WTT_ENTRYTABLE." where id=%d",$_GET['pid']);
				$wpdb->query($sql);
				//deletes the assiciated time table
				$sql=$wpdb->prepare("delete from ".WTT_TIMETABLE." where id_entry = %d",$_GET['pid']);
				$wpdb->query($sql);	
			}
	
			if (isset($_POST['act']) && ($_POST['act'] == "edtentry"))
			{
				$sql= $wpdb->prepare("UPDATE ".WTT_ENTRYTABLE." set entryname=%s where id= %d",$_POST['entryname'],$_POST['pid']);
				$wpdb->query($sql);
	
			}
	
			  echo '<div class="wrap">';
			  echo '<h1>'.__('Manage entries for WTT','wttPlugIn').'</h1>
			  <p>'.__('Existing Time Tables','wttPlugIn').'</p>';
			  echo wttdsp(0,0);
			  echo '<p>'.__('Existing Entries','wttPlugIn').'</p>';
			  echo list_wttentries(2);
			  if (isset($_GET['pid'])&& ($_GET['act']=="edtfrm" && !(isset($_POST['act'])))) {
			  	  check_admin_referer('edt_frm');//nonces check for GET
			  	  $sql = "select e.entryname from ".WTT_ENTRYTABLE." e where e.id=".$_GET['pid']; 
				  $rows = $wpdb->get_row($sql);
				  $outp = '<br/><br/><img src="'.WTT_DIR_URL.'/img/pencil.png" alt="edit an entry"/>'.__('Modify this entry','wttPlugIn').'<form method="post" action="">';
				  $outp .= '<table>
				  <tr>
				  <td>'.__('Entry','wttPlugIn').'</td><td><input type="text" name="entryname" size="50" value="'.$rows->entryname.'"/></td>
				  </tr>	
				  </table>
				  <input type="hidden" name="pid" value="'.$_GET['pid'].'">
				  <input type="hidden" name="act" value="edtentry">
				  <input type="submit" value="Modify" />';
				  $outp.= '</form>';
				  echo $outp;
			    }
			  echo '<hr/>';
			  echo '<p><img src="'.WTT_DIR_URL.'/img/add.png" alt="Create an entry"/>'.__('Create an entry.','wttPlugIn').'</p>';
			  echo '<form action="'.str_replace( '%7E', '~', $_SERVER['REQUEST_URI']).'" method="POST"><br/>';
			  echo '<tr>
				  	<td>'.__('Entry','wttPlugIn').'</td><td><input type="text" name="entryname" size="50" value=""/></td>
					</tr>				  	
					</table>
					<input type="hidden" name="act" value="addentry">
					<input type="submit" value="Submit" />
					</form>';
			  echo '</div>';
	}


	/*
	** Manage time tables (CRUD)
	*/
	function mng_wtt() {
		global $wpdb;
		$wpdb->show_errors(true);
	
			if (isset($_GET['act']) && ($_GET['act'] == "delwtt"))
			{
				check_admin_referer('del_wtt');
				$sql=$wpdb->prepare("delete from ".WTT_TIMETABLE." where id=%d",$_GET['pid']);
				$wpdb->query($sql);
	
			}
	
			if (isset($_POST['act']) && ($_POST['act'] == "edtwtt"))
			{
				$sql= $wpdb->prepare("UPDATE ".WTT_TIMETABLE." set mon=%s ,tue=%s , wed=%s ,thu=%s ,fri=%s ,sat=%s ,sun=%s where id=%d",$_POST['mon'], $_POST['tue'], $_POST['wed'], $_POST['thu'], $_POST['fri'],$_POST['sat'],$_POST['sun'],$_POST['pid']);
				$wpdb->query($sql);
	
			}
			
			if (isset($_POST['act']) && ($_POST['act'] == "addwtt"))
			{
				$sql = $wpdb->prepare( "INSERT INTO ".WTT_TIMETABLE."( id_entry,mon, tue, wed, thu, fri, sat, sun ) VALUES ( %d, %s, %s, %s, %s, %s, %s, %s )", $_POST['id_entry'], $_POST['mon'], $_POST['tue'], $_POST['wed'], $_POST['thu'], $_POST['fri'],$_POST['sat'],$_POST['sun'] );
				$wpdb->get_results($sql);
			}
	
			  echo '<div class="wrap">';
			  echo '<h1>'.__('Manage Weekly Time Table','wttPlugIn').'</h1>';
			  echo '<p>'.__('Choose time table to edit','wttPlugIn').'</p>';
			  echo wttdsp(1);
			  // edit form
			  if (isset($_GET['pid']) && ($_GET['act']=="edtfrm") && (!isset($_POST['act']))) {
			  	check_admin_referer('edt_frm');
				  $sql = "select h.id, e.entryname as entryn, mon, tue, wed, thu, fri, sat, sun from ".WTT_TIMETABLE." h, ".WTT_ENTRYTABLE." e where h.id_entry = e.id and h.id=".$_GET['pid']; 
				  $rows = $wpdb->get_row($sql);
				  $outp = '<br/><br/><img src="'.WTT_DIR_URL.'/img/pencil.png" alt="edit a wtt"/>'.__('Modify this time table','wttPlugIn').'<form method="post" action="">';
				  $outp .= '<strong>'.__('Entry','wttPlugIn').' : </strong>'.$rows->entryn.'<br/><br/>';
				  $outp .= tablehead();
				  $outp .= '<tr><td>ex.15h-17h</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
				  <tr>
				  <td><input type="text" name="mon" size="10" value="'.$rows->mon.'"/></td>
				  <td><input type="text" name="tue" size="10" value="'.$rows->tue.'"/></td>
				  <td><input type="text" name="wed" size="10" value="'.$rows->wed.'"/></td>
				  <td><input type="text" name="thu" size="10" value="'.$rows->thu.'"/></td>
				  <td><input type="text" name="fri" size="10" value="'.$rows->fri.'"/></td>
				  <td><input type="text" name="sat" size="10" value="'.$rows->sat.'"/></td>
				  <td><input type="text" name="sun" size="10" value="'.$rows->sun.'"/></td>
				  </tr>	
				  </table>
				  <input type="hidden" name="pid" value="'.$_GET['pid'].'">
				  <input type="hidden" name="act" value="edtwtt">
				  <input type="submit" value="Modify" />';
				  
				  $outp.= '</form><hr/>';
				  echo $outp;
			    }
			  // add form  
			  echo '<p><img src="'.WTT_DIR_URL.'/img/add.png" alt="Create a time table"/>'.__('Create a time table.','wttPlugIn').'</p>';
			  echo '<form action="'.str_replace( '%7E', '~', $_SERVER['REQUEST_URI']).'" method="POST">
			  <br/>';
			  echo '<b>Entry</b>'.list_wttentries(1).'</br>';
			  echo tablehead();
			  echo '<tr><td>ex.15h-17h</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
			  <tr>
			  
			  <td><input type="text" name="mon" size="10" /></td>
			  <td><input type="text" name="tue" size="10" /></td>
			  <td><input type="text" name="wed" size="10" /></td>
			  <td><input type="text" name="thu" size="10" /></td>
			  <td><input type="text" name="fri" size="10" /></td>
			  <td><input type="text" name="sat" size="10" /></td>
			  <td><input type="text" name="sun" size="10" /></td>
			  </tr>	
			  </table>
			  <input type="hidden" name="act" value="addwtt">
			  <input type="submit" value="Submit" />
			  </form>';  
			  echo '</div>';
	}


	/*
	 * Create the function for the shortcode
	 */
	function shc_wttdsp($atts)
	  	{ 
		   global $wpdb;
		  
		   $wpdb->show_errors(true);
		   extract(shortcode_atts(array(
			'entry_id' => 'entry_id',
		   ), $atts));
	
		   $sql="select * from ".WTT_TIMETABLE." h, ".WTT_ENTRYTABLE." e where h.id_entry = e.id  and h.id = ".$entry_id;
		   $rows = $wpdb->get_results($sql);
		   $outp =  tablehead(1);
		   
		    foreach($rows as $row)
			{
				 $outp .= '<tr><td><span id="wttentry">'.$row->entryname.'</span></td><td>'.$row->mon.'</td><td>'.$row->tue.'</td><td>'.$row->wed.'</td><td>'.$row->thu.'</td><td>'.$row->fri.'</td><td>'.$row->sat.'</td><td>'.$row->sun.'</td></tr>';		
			}
			$outp .= '</table>';
			return $outp;
	    
		}


	/*
	 * Uninstall procedures
	 */
	function wtt_uninstall()
		{	global $wpdb;
	    	delete_option('wtt_db_version');
	    	//dropping the tables
	    	$sql = "drop table ".WTT_TIMETABLE;
	    	$wpdb->query($sql);
	    	$sql = "drop table ".WTT_ENTRYTABLE;
	    	$wpdb->query($sql);
		}
	register_uninstall_hook(__FILE__, 'wtt_uninstall');

/*
 * USE WORDPRESS LANGUAGE
 */
// Add this action into the admin header of Wordpress
wp_enqueue_style('wttPlugIn', WTT_DIR.'/css/wtt.css');
// Call the menu creation function
add_action('admin_menu','wtt_menu');
// Add the shortcode to display the table
add_shortcode('wttdsp', 'shc_wttdsp');

?>