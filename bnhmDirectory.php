<?php
/**
 * @package bnhmDirectory 
 * @version 0.7
 */
/*
Plugin Name:  BNHM Directory
Plugin URI: 
Description: Display the contents of the BNHM People Directory and News Feed
Author: John Deck
Version: 0.7
Author URI: 
License: GPL
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Our museums
$bnhm_directory_museums = array(
  "EME" => "sites/10/2016/01/bigtb_essig.gif",
  "UCBG" => "sites/10/2016/01/bigtb_garden.gif",
  "UCMP" => "sites/10/2016/01/bigtb_paleo.gif",
  "UCJEPS" => "sites/10/2016/01/bigtb_ucjeps.gif",
  "MVZ" => "sites/10/2016/01/bigtb_mvz.gif",
  "PHMA" => "sites/10/2016/01/bigtb_anthro.gif",
  "BNHM" => "sites/10/2015/06/bnhm_logo_150xbg.png"
);

add_shortcode('print_bnhm_directory_alphabetical','bnhm_directory_alphabetical');
add_shortcode('print_bnhm_directory_groupname','bnhm_directory_groupname');
add_shortcode('print_bnhm_news','bnhm_news');

// Add a call to the plugin menu options from the admin menu (showing up under settings)
add_action('admin_menu', 'bnhm_directory_plugin_menu' );

// Initialize Settings
add_action('admin_init','register_bnhm_directory_settings');

// Add settings link on plugin page
function your_plugin_settings_link($links) { 
  $settings_link = '<a href="options-general.php?page=bnhmDirectory.php">Settings</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
}
 
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'your_plugin_settings_link' );

// Add an options page
function bnhm_directory_plugin_menu() {
	add_options_page( 'My Plugin Options', 'BNHM Directory Options', 'manage_options', 'bnhmDirectory.php', 'bnhm_directory_plugin_options' );
}

// Register all the form variables
function register_bnhm_directory_settings() {
	register_setting('bnhm_directory-group','bnhm_directory_museum_name');
	register_setting('bnhm_directory-group','bnhm_directory_header_text');
	register_setting('bnhm_directory-group','bnhm_directory_host');
	register_setting('bnhm_directory-group','bnhm_directory_login');
	register_setting('bnhm_directory-group','bnhm_directory_password');
	register_setting('bnhm_directory-group','bnhm_directory_database');
	register_setting('bnhm_directory-group','bnhm_directory_show_logos');
}

// Setup the plugin options using an input form in the admin panel
function bnhm_directory_plugin_options() {
	global $bnhm_directory_museums;
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
    
	echo "<h2>Input</h2>\n";
        echo "<p>Enter information/data to connect and configure your directory here</p>\n";

        echo "\t<form method='post' action='/bnhm2/wp-admin/options.php'>\n";
	echo "\t<table>\n";

        settings_fields('bnhm_directory-group' ); 
        do_settings_fields('bnhm_directory-group', '' );

	 // Drop-down list of museum names
	echo "\t<tr><td>Museum Name</td><td><select name='bnhm_directory_museum_name'>";
	foreach($bnhm_directory_museums as $key => $value) {
		echo "\t\t<option value='" . $key. "'";
		if (get_option('bnhm_directory_museum_name') == $key) {
			echo " SELECTED";
		}
		echo "> ". $key. "</option>\n";
	}
	echo "\t</select> (Your museum code)</td></tr>\n";

	// Header to display at top of page, if desired 
	echo "\t<tr><td>Header Text</td><td><textarea rows='4' cols='50' name='bnhm_directory_header_text'>" . get_option('bnhm_directory_header_text') ."</textarea> (Optional header text)</td></tr>\n";

	// Connection details
	echo "\t<tr><td>Host</td><td><input type='text' name='bnhm_directory_host' value='" . get_option('bnhm_directory_host') ."'/> (Host where database lives)</td></tr>\n";
	echo "\t<tr><td>Login</td><td><input type='text' name='bnhm_directory_login' value='" . get_option('bnhm_directory_login') ."'/> (DB login name)</td></tr>\n";
	echo "\t<tr><td>Password</td><td><input type='password' name='bnhm_directory_password' value='" . get_option('bnhm_directory_password') ."'/> (DB password)</td></tr>\n";
	echo "\t<tr><td>Database</td><td><input type='text' name='bnhm_directory_database' value='" . get_option('bnhm_directory_database') ."'/> (DB name)</td></tr>\n";

	// Create a check-box to show logos or not show logos 
	echo "\t<tr><td>Show logos</td><td><input type='checkbox' name='bnhm_directory_show_logos' ";
	if (get_option('bnhm_directory_show_logos')) {
		echo "CHECKED";
	} 
	echo "/> (Show BNHM logos or not)</td></tr>\n";

	echo "\t</table>\n";

	// give us a submit button for the form
	submit_button();

        echo "\t</form>\n";
    	echo "</div>\n";
}

// Get the database connection using the option parameters set in configuration file
function getDB() {
	$db = mysqli_connect(get_option('bnhm_directory_host'), get_option('bnhm_directory_login'), get_option('bnhm_directory_password'), get_option('bnhm_directory_database')) or die("Unable to connect to database");
	mysqli_set_charset($db, "utf8");

	if (mysqli_connect_errno()) {
  		echo "Failed to connect to MySQL: " . mysqli_connect_error();
  		exit();
	} 

	return $db;
}

// Display an alphabetical list of BNHM directory names
function bnhm_directory_alphabetical() {
    	global $bnhm_directory_museums;
  	$db = getDB();
	$text = "";
  	// Display the header text
  	$text .= get_option('bnhm_directory_header_text'); 

  	$text .= "<table class='bnhm_dir'>";
  	$l_strSQL = "select concat_ws('',a.lastname,', ',a.firstname,' ',a.suffix) as name,";
  	$l_strSQL .= " a.position as position,";
  	$l_strSQL .= " a.interests as interests,";
  	$l_strSQL .= " replace(replace(a.email,'@','&#064;'),'.edu','&#046;&#069;&#068;&#085;') as email,";
  	$l_strSQL .= " a.phone as phone,";
  	$l_strSQL .= " a.url as url,";
  	$l_strSQL .= " a.contact_reason as contact_reason,";
  	$l_strSQL .= " g.description as groupname,";
  	$l_strSQL .= " u.cal_login as museum";
  	$l_strSQL .= " FROM webcal_affiliates a,webcal_affiliates_groups g,webcal_user u";
  	//$l_strSQL .= " WHERE (g.name!='Not_Active' AND g.description not like \"Former%\" AND g.name !='Alumni' AND g.name != 'Emeriti')";
  	$l_strSQL .= " WHERE (g.name!='Not_Active' AND g.description not like \"Former%\" AND g.name !='Alumni')";
 	// Don't want to display directors of BNHM-- use their institutional status
  	$l_strSQL .= " and (u.cal_login != 'BNHM' and g.name != 'Directors')";
  	$l_strSQL .= " and a.groupname = g.affiliates_groups_id";
  	$l_strSQL .= " and g.cal_create_by= u.cal_login";
	// BNHM option represents ALL museums, don't use this option
	if (get_option('bnhm_directory_museum_name') != "BNHM") {
		$l_strSQL .= " and u.cal_login = '" . get_option('bnhm_directory_museum_name') . "'";
	}
  	$l_strSQL .= " GROUP BY concat_ws('',a.lastname,', ',a.firstname,' ',a.suffix),u.cal_login";
  	$l_strSQL .= " ORDER BY a.lastname";
  
  	$res=mysqli_query($db,$l_strSQL);
	mysqli_store_result($db);
  	$num=mysqli_num_rows($res);
  	$cur = 1;

  	while ($num >= $cur) {
    		$row=mysqli_fetch_array($res);
    		$email = strtolower($row['email']);
    		$text .= "<tr>";
    
    		if (get_option('bnhm_directory_show_logos')) {
    			$text .= "<td><img src='http://bnhmwp.berkeley.edu/bnhm2/wp-content/uploads/". $bnhm_directory_museums[$row['museum']] . "' width=100 border=0></td>";
    		}

    		$text .= "<td align='left' class='name'><span>";
    		if ($row['url'] != '') { $text .= "<a href='" . $row['url'] . "'>"; }
    		$text .= utf8_encode(trim($row['name'])) . "</span>";
    		if ($row['url'] != '') { $text .= "</a>"; } 
    		if ($row['position'] != '') { $text .= "<br/>" . $row['position']; }
    		if ($row['interests'] != '') { $text .= "<br/><i>" . $row['interests'] . "</i>"; }
    		$text .= "</td>";

    		//echo "<td align='left' class='group'>" . $row['groupname'] . "</td>";
    		$real_phone = preg_replace("/\D/","",$row['phone']);
    		if(strlen($real_phone) == 7) $real_phone = "510".$real_phone;
    		if(!empty($real_phone)) { 
      			$formatted_phone = "(".substr($real_phone,0,3).") ".substr($real_phone,3,3)."-".substr($real_phone,6);
    		}
    		else $formatted_phone = "";
    		$text .= "<td class='phone'><span itemprop='telephone'><a href='tel:+1".$real_phone."'>" . $formatted_phone . "</a></span></td>";
    		$text .= "<td align='left' class='email'><a href='mailto:" .$email . "'>" . $email ."</a></td>";
    		$text .= "</tr>";
    		$cur++;
  	} 
  	$text .= "</table>";
  
  	$text .= "<p>";
	return $text;
}
// Show News for all Users (limit to 25 results)
function bnhm_news($atts = [], $content = null, $tag = '') {
	$limit = 2;

	if (isset($atts['limit'])) {
		$limit = $atts['limit'];
	}

    	global $bnhm_directory_museums;
  	$db = getDB();
	$text = "";

        $l_strSQL = "select cal_id,cal_title,cal_url,cal_description,cal_mod_date,cal_imageurl";
        $l_strSQL .= " FROM webcal_entry";
        $l_strSQL .= " WHERE cal_eventtype='news'";
        $l_strSQL .= " AND cal_create_by='MVZ'";
        $l_strSQL .= " ORDER by cal_mod_date DESC";
	if ($limit != "all") {
        	$l_strSQL .= " limit " . $limit;
	}

  	$res=mysqli_query($db,$l_strSQL);
	mysqli_store_result($db);
  	$cur = 1;
  	$num=mysqli_num_rows($res);

        while ($num >= $cur) {
    		$row=mysqli_fetch_array($res);
                $l_intDate = strtotime($row['cal_mod_date']);
                $text .= "<h5 class='clear'>";
                $text .= "&nbsp;" . date("M d, Y",$l_intDate). "&nbsp;&nbsp;";
                $text .= "</h5><span class='kindabig'>";
                $text .= "<a class=\"linkstylequery\" href=\"" . $row['cal_url'] . "\">" . $row['cal_title'] . "</a>";
                $text .= "</span>";

                if ($row['cal_imageurl'] != "") {
                        $text .= "<a href='" . $row['cal_imageurl'] . "' target='_blank'>";
                        $text .= "<img src='" . $row['cal_imageurl'] . "' alt='Photo' class='photo' style='float:left; max-height:200px;' \>";
                        $text .= "</a>";
                }
                $text .= "<p>" . nl2br($row['cal_description']);
                $text .= "</p>";

                $cur++;
        }
        $text .= "<hr />";
	return $text;
}



function bnhm_directory_groupname($atts = [], $content = null, $tag = '') { 
	$groupname = '';
    	global $bnhm_directory_museums;
  	$db = getDB();
	$text = "";

	if (isset($atts['groupname'])) {
		$groupname = $atts['groupname'];
	}

  	// Display the header text
  	$text .= get_option('bnhm_directory_header_text'); 
  	$l_strSQL = "select * from (select concat_ws('',a.lastname,', ',a.firstname,' ',a.suffix) as name,";
  	$l_strSQL .= " a.position as position,";
  	$l_strSQL .= " replace(replace(a.email,'@','&#064;'),'.edu','&#046;&#069;&#068;&#085;') as email,";
  	$l_strSQL .= " a.phone as phone,";
  	$l_strSQL .= " a.url as url,";
  	$l_strSQL .= " a.contact_reason as contact_reason,";
  	$l_strSQL .= " g.description as groupname,";
  	$l_strSQL .= " u.cal_login as museum";
  	$l_strSQL .= " FROM webcal_affiliates a,webcal_affiliates_groups g,webcal_user u";
  	//$l_strSQL .= " WHERE (g.name!='Not_Active' AND g.description not like \"Former%\" AND g.name !='Alumni' AND g.name != 'Emeriti')";
  	$l_strSQL .= " WHERE (g.name!='Not_Active')";
 	// Don't want to display directors of BNHM-- use their institutional status
  	$l_strSQL .= " and (u.cal_login != 'BNHM' and g.name != 'Directors')";
  	$l_strSQL .= " and a.groupname = g.affiliates_groups_id";
  	$l_strSQL .= " and g.cal_create_by= u.cal_login";
  	$l_strSQL .= " and a.groupname != ''";
  	$l_strSQL .= " and a.groupname is not null";
	if ($groupname != '') {
  		$l_strSQL .= " and g.description like '" . $groupname ."'";
	}
	// BNHM option represents ALL museums, don't use this option
	if (get_option('bnhm_directory_museum_name') != "BNHM") {
		$l_strSQL .= " and u.cal_login = '" . get_option('bnhm_directory_museum_name') . "'";
	}
  	$l_strSQL .= " GROUP BY concat_ws('',a.lastname,', ',a.firstname,' ',a.suffix),u.cal_login) as t ";
  	$l_strSQL .= " ORDER BY groupname";

	//print $l_strSQL;
  	$res=mysqli_query($db,$l_strSQL);

	mysqli_store_result($db);
  	$cur = 1;
  	$num=mysqli_num_rows($res);
	$thisgroupname = '';

  	while ($num >= $cur ) {

    		$row=mysqli_fetch_array($res);

		if ($row["groupname"] != '') {
		if ($row["groupname"] != $thisgroupname) {
			if ($cur > 1) {
				$text .= "</table>";
			}

			$text .= "<h3>". $row['groupname'] . "</h3>";
  			$text .= "<table class='bnhm_dir'>";
			$thisgroupname = $row["groupname"];
		}

    		$email = strtolower($row['email']);
    		$text .= "<tr>";
    
    		#if (get_option('bnhm_directory_show_logos')) {
    		#	echo "<td><img src='http://bnhmwp.berkeley.edu/bnhm2/wp-content/uploads/". $bnhm_directory_museums[$row['museum']] . "' width=100 border=0></td>";
    		#}

    		$text .= "<td align='left' class='name'><span>";
    		if ($row['url'] != '') { $text .= "<a href='" . $row['url'] . "'>"; }
    		$text .= utf8_encode(trim($row['name'])) . "</span>";
    		if ($row['url'] != '') { $text .= "</a>"; } 
    		if ($row['position'] != '') { $text .= "<br/>" . $row['position']; }
    		$text .= "</td>";

    		$real_phone = preg_replace("/\D/","",$row['phone']);
    		if(strlen($real_phone) == 7) $real_phone = "510".$real_phone;
    		if(!empty($real_phone)) { 
      			$formatted_phone = "(".substr($real_phone,0,3).") ".substr($real_phone,3,3)."-".substr($real_phone,6);
    		}
    		else $formatted_phone = "";
    		$text .= "<td class='phone'><span itemprop='telephone'><a href='tel:+1".$real_phone."'>" . $formatted_phone . "</a></span></td>";
    		$text .= "<td align='left' class='email'><a href='mailto:" .$email . "'>" . $email ."</a></td>";
    		$text .= "</tr>";
		}
    		$cur++;
  	} 
  	$text .= "</table>";
  
  	$text .= "<p>";
	return $text;
}
