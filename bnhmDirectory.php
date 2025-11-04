<?php
/**
 * @package bnhmDirectory
 * @version 0.8
 */
/*
Plugin Name:  BNHM Directory
Plugin URI:
Description: Display the contents of the BNHM People Directory and News Feed
Author: John Deck
Version: 0.8
Author URI:
License: GPL
*/

defined('ABSPATH') or die('No script kiddies please!');

// ———————————————————————————————————————————————————————————
// Museum map kept for legacy/reference (not used for images anymore)
$bnhm_directory_museums = array(
  "EME"  => "sites/10/2016/01/bigtb_essig.gif",
  "UCBG" => "sites/10/2016/01/bigtb_garden.gif",
  "UCMP" => "sites/10/2016/01/bigtb_paleo.gif",
  "UCJEPS"=> "sites/10/2016/01/bigtb_ucjeps.gif",
  "MVZ"  => "sites/10/2016/01/bigtb_mvz.gif",
  "PHMA" => "sites/10/2016/01/bigtb_anthro.gif",
  "BNHM" => "sites/10/2015/06/bnhm_logo_150xbg.png"
);

// Shortcodes
add_shortcode('print_bnhm_directory_alphabetical','bnhm_directory_alphabetical');
add_shortcode('print_bnhm_directory_groupname','bnhm_directory_groupname');
add_shortcode('print_bnhm_news','bnhm_news');

// Admin menu + settings
add_action('admin_menu', 'bnhm_directory_plugin_menu');
add_action('admin_init','register_bnhm_directory_settings');

// Add settings link on the plugins page
function bnhm_directory_settings_link($links) {
  $settings_link = '<a href="options-general.php?page=bnhmDirectory.php">Settings</a>';
  array_unshift($links, $settings_link);
  return $links;
}
$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'bnhm_directory_settings_link');

// Admin page
function bnhm_directory_plugin_menu() {
  add_options_page('BNHM Directory Options', 'BNHM Directory Options', 'manage_options', 'bnhmDirectory.php', 'bnhm_directory_plugin_options');
}

// Register options
function register_bnhm_directory_settings() {
  register_setting('bnhm_directory-group','bnhm_directory_museum_name');
  register_setting('bnhm_directory-group','bnhm_directory_header_text');
  register_setting('bnhm_directory-group','bnhm_directory_host');
  register_setting('bnhm_directory-group','bnhm_directory_login');
  register_setting('bnhm_directory-group','bnhm_directory_password');
  register_setting('bnhm_directory-group','bnhm_directory_database');
  register_setting('bnhm_directory-group','bnhm_directory_show_logos'); // legacy (no longer used in table rendering)
}

// Options page markup
function bnhm_directory_plugin_options() {
  global $bnhm_directory_museums;

  if ( ! current_user_can('manage_options') ) {
    wp_die( esc_html__('You do not have sufficient permissions to access this page.') );
  }

  echo "<div class='wrap'>";
  echo "<h1>BNHM Directory Options</h1>";
  echo "<p>Enter connection info for your directory and configure display options.</p>";

  echo "<form method='post' action='" . esc_url( admin_url('options.php') ) . "'>";
  settings_fields('bnhm_directory-group');
  do_settings_fields('bnhm_directory-group', '');

  echo "<table class='form-table'>";

  // Museum name dropdown
  echo "<tr><th scope='row'><label for='bnhm_directory_museum_name'>Museum Code</label></th><td>";
  echo "<select id='bnhm_directory_museum_name' name='bnhm_directory_museum_name'>";
  foreach ($bnhm_directory_museums as $key => $value) {
    $sel = selected(get_option('bnhm_directory_museum_name'), $key, false);
    echo "<option value='" . esc_attr($key) . "' {$sel}>" . esc_html($key) . "</option>";
  }
  echo "</select> <span class='description'>(Your museum code; BNHM = all)</span>";
  echo "</td></tr>";

  // Header text
  echo "<tr><th scope='row'><label for='bnhm_directory_header_text'>Header Text</label></th><td>";
  echo "<textarea id='bnhm_directory_header_text' rows='4' cols='50' name='bnhm_directory_header_text'>" . esc_textarea(get_option('bnhm_directory_header_text')) . "</textarea>";
  echo " <span class='description'>(Optional header text printed above results)</span>";
  echo "</td></tr>";

  // DB connection details
  echo "<tr><th scope='row'><label for='bnhm_directory_host'>Host</label></th><td>";
  echo "<input type='text' id='bnhm_directory_host' name='bnhm_directory_host' value='" . esc_attr(get_option('bnhm_directory_host')) . "' />";
  echo " <span class='description'>(DB host)</span>";
  echo "</td></tr>";

  echo "<tr><th scope='row'><label for='bnhm_directory_login'>Login</label></th><td>";
  echo "<input type='text' id='bnhm_directory_login' name='bnhm_directory_login' value='" . esc_attr(get_option('bnhm_directory_login')) . "' />";
  echo " <span class='description'>(DB username)</span>";
  echo "</td></tr>";

  echo "<tr><th scope='row'><label for='bnhm_directory_password'>Password</label></th><td>";
  echo "<input type='password' id='bnhm_directory_password' name='bnhm_directory_password' value='" . esc_attr(get_option('bnhm_directory_password')) . "' />";
  echo " <span class='description'>(DB password)</span>";
  echo "</td></tr>";

  echo "<tr><th scope='row'><label for='bnhm_directory_database'>Database</label></th><td>";
  echo "<input type='text' id='bnhm_directory_database' name='bnhm_directory_database' value='" . esc_attr(get_option('bnhm_directory_database')) . "' />";
  echo " <span class='description'>(DB name)</span>";
  echo "</td></tr>";

  // Legacy checkbox (kept for compatibility; not used in rendering any images)
  $checked = get_option('bnhm_directory_show_logos') ? 'checked' : '';
  echo "<tr><th scope='row'><label for='bnhm_directory_show_logos'>Show logos (legacy)</label></th><td>";
  echo "<input type='checkbox' id='bnhm_directory_show_logos' name='bnhm_directory_show_logos' value='1' {$checked} />";
  echo " <span class='description'>(Not used in current table output)</span>";
  echo "</td></tr>";

  echo "</table>";

  submit_button();
  echo "</form>";
  echo "</div>";
}

// DB helper
function getDB() {
  $host = get_option('bnhm_directory_host');
  $user = get_option('bnhm_directory_login');
  $pass = get_option('bnhm_directory_password');
  $dbnm = get_option('bnhm_directory_database');

  $db = mysqli_connect($host, $user, $pass, $dbnm);
  if (!$db) {
    die("Unable to connect to database");
  }
  mysqli_set_charset($db, 'utf8');

  if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
  }
  return $db;
}

// ———————————————————————————————————————————————————————————
// Alphabetical Directory — NO IMAGES IN TABLE
function bnhm_directory_alphabetical() {
  $db = getDB();
  $text = '';

  // Optional header text
  $header = get_option('bnhm_directory_header_text');
  if (!empty($header)) {
    $text .= wp_kses_post($header);
  }

  // Build query
  $l_strSQL  = "SELECT CONCAT_WS('', a.lastname, ', ', a.firstname, ' ', a.suffix) AS name,";
  $l_strSQL .= " a.position AS position,";
  $l_strSQL .= " a.interests AS interests,";
  $l_strSQL .= " REPLACE(REPLACE(a.email,'@','&#064;'),'.edu','&#046;&#069;&#068;&#085;') AS email,";
  $l_strSQL .= " a.phone AS phone,";
  $l_strSQL .= " a.url AS url,";
  $l_strSQL .= " a.contact_reason AS contact_reason,";
  $l_strSQL .= " g.description AS groupname,";
  $l_strSQL .= " u.cal_login AS museum";
  $l_strSQL .= " FROM webcal_affiliates a, webcal_affiliates_groups g, webcal_user u";
  $l_strSQL .= " WHERE (g.name!='Not_Active' AND g.description NOT LIKE 'Former%' AND g.name!='Alumni')";
  $l_strSQL .= " AND (u.cal_login!='BNHM' AND g.name!='Directors')";
  $l_strSQL .= " AND a.groupname = g.affiliates_groups_id";
  $l_strSQL .= " AND g.cal_create_by = u.cal_login";

  if (get_option('bnhm_directory_museum_name') !== 'BNHM') {
    $museum = mysqli_real_escape_string($db, get_option('bnhm_directory_museum_name'));
    $l_strSQL .= " AND u.cal_login = '{$museum}'";
  }

  $l_strSQL .= " GROUP BY CONCAT_WS('', a.lastname, ', ', a.firstname, ' ', a.suffix), u.cal_login";
  $l_strSQL .= " ORDER BY a.lastname";

  $res = mysqli_query($db, $l_strSQL);
  mysqli_store_result($db);
  $num = $res ? mysqli_num_rows($res) : 0;

  $text .= "<table class='bnhm_dir' style='width:100%;border-collapse:collapse;'>";
  $cur = 1;

  while ($res && $num >= $cur) {
    $row = mysqli_fetch_assoc($res);

    $name  = esc_html(trim($row['name']));
    $url   = !empty($row['url']) ? esc_url( set_url_scheme($row['url'], 'https') ) : '';
    $pos   = !empty($row['position']) ? esc_html($row['position']) : '';
    $intr  = !empty($row['interests']) ? esc_html($row['interests']) : '';
    $email = strtolower($row['email']); // already obfuscated in SQL

    // Format phone
    $real_phone = preg_replace('/\D/', '', $row['phone']);
    if (strlen($real_phone) === 7) {
      $real_phone = '510' . $real_phone;
    }
    $formatted_phone = '';
    if (!empty($real_phone)) {
      $formatted_phone = '(' . substr($real_phone, 0, 3) . ') ' . substr($real_phone, 3, 3) . '-' . substr($real_phone, 6);
    }

    $text .= "<tr>";

    // Name/position/interests
    $text .= "<td class='name' style='vertical-align:top;padding:6px 8px;width:60%;'>";
    if (!empty($url)) { $text .= "<a href='{$url}'>"; }
    $text .= "<span>{$name}</span>";
    if (!empty($url)) { $text .= "</a>"; }
    if (!empty($pos))  { $text .= "<br/>" . $pos; }
    if (!empty($intr)) { $text .= "<br/><i>" . $intr . "</i>"; }
    $text .= "</td>";

    // Phone
    $tel_href = !empty($real_phone) ? "tel:+1{$real_phone}" : '';
    $text .= "<td class='phone' style='vertical-align:top;padding:6px 8px;white-space:nowrap;'>";
    if ($tel_href) {
      $text .= "<span itemprop='telephone'><a href='{$tel_href}'>" . esc_html($formatted_phone) . "</a></span>";
    }
    $text .= "</td>";

    // Email
    $text .= "<td class='email' style='vertical-align:top;padding:6px 8px;white-space:nowrap;'>";
    if (!empty($email)) {
      $text .= "<a href='mailto:{$email}'>" . $email . "</a>";
    }
    $text .= "</td>";

    $text .= "</tr>";
    $cur++;
  }

  $text .= "</table>";
  $text .= "<p>";

  return $text;
}

// ———————————————————————————————————————————————————————————
// Grouped Directory — NO IMAGES IN TABLE
function bnhm_directory_groupname($atts = [], $content = null, $tag = '') {
  $groupname = isset($atts['groupname']) ? $atts['groupname'] : '';
  $db = getDB();
  $text = '';

  // Optional header text
  $header = get_option('bnhm_directory_header_text');
  if (!empty($header)) {
    $text .= wp_kses_post($header);
  }

  // Query
  $l_strSQL  = "SELECT * FROM (";
  $l_strSQL .= " SELECT CONCAT_WS('', a.lastname, ', ', a.firstname, ' ', a.suffix) AS name,";
  $l_strSQL .= " a.position AS position,";
  $l_strSQL .= " REPLACE(REPLACE(a.email,'@','&#064;'),'.edu','&#046;&#069;&#068;&#085;') AS email,";
  $l_strSQL .= " a.phone AS phone,";
  $l_strSQL .= " a.url AS url,";
  $l_strSQL .= " a.contact_reason AS contact_reason,";
  $l_strSQL .= " g.description AS groupname,";
  $l_strSQL .= " u.cal_login AS museum";
  $l_strSQL .= " FROM webcal_affiliates a, webcal_affiliates_groups g, webcal_user u";
  $l_strSQL .= " WHERE (g.name!='Not_Active')";
  $l_strSQL .= " AND (u.cal_login!='BNHM' AND g.name!='Directors')";
  $l_strSQL .= " AND a.groupname = g.affiliates_groups_id";
  $l_strSQL .= " AND g.cal_create_by = u.cal_login";
  $l_strSQL .= " AND a.groupname != ''";
  $l_strSQL .= " AND a.groupname IS NOT NULL";

  if (!empty($groupname)) {
    $groupname_esc = mysqli_real_escape_string($db, $groupname);
    $l_strSQL .= " AND g.description LIKE '{$groupname_esc}'";
  }

  if (get_option('bnhm_directory_museum_name') !== 'BNHM') {
    $museum = mysqli_real_escape_string($db, get_option('bnhm_directory_museum_name'));
    $l_strSQL .= " AND u.cal_login = '{$museum}'";
  }

  $l_strSQL .= " GROUP BY CONCAT_WS('', a.lastname, ', ', a.firstname, ' ', a.suffix), u.cal_login";
  $l_strSQL .= " ) AS t";
  $l_strSQL .= " ORDER BY groupname";

  $res = mysqli_query($db, $l_strSQL);
  mysqli_store_result($db);
  $num = $res ? mysqli_num_rows($res) : 0;

  $thisgroupname = '';
  $cur = 1;

  while ($res && $num >= $cur) {
    $row = mysqli_fetch_assoc($res);

    if (!empty($row['groupname'])) {
      if ($row['groupname'] !== $thisgroupname) {
        if ($cur > 1) {
          $text .= "</table>";
        }
        $thisgroupname = $row['groupname'];
        $text .= "<h3>" . esc_html($thisgroupname) . "</h3>";
        $text .= "<table class='bnhm_dir' style='width:100%;border-collapse:collapse;'>";
      }

      $name  = esc_html(trim($row['name']));
      $url   = !empty($row['url']) ? esc_url( set_url_scheme($row['url'], 'https') ) : '';
      $pos   = !empty($row['position']) ? esc_html($row['position']) : '';
      $email = strtolower($row['email']); // already obfuscated

      // Phone formatting
      $real_phone = preg_replace('/\D/', '', $row['phone']);
      if (strlen($real_phone) === 7) {
        $real_phone = '510' . $real_phone;
      }
      $formatted_phone = '';
      if (!empty($real_phone)) {
        $formatted_phone = '(' . substr($real_phone, 0, 3) . ') ' . substr($real_phone, 3, 3) . '-' . substr($real_phone, 6);
      }

      $text .= "<tr>";

      // Name/position cell (no images)
      $text .= "<td class='name' style='vertical-align:top;padding:6px 8px;width:60%;'>";
      if (!empty($url)) { $text .= "<a href='{$url}'>"; }
      $text .= "<span>{$name}</span>";
      if (!empty($url)) { $text .= "</a>"; }
      if (!empty($pos)) { $text .= "<br/>" . $pos; }
      $text .= "</td>";

      // Phone
      $tel_href = !empty($real_phone) ? "tel:+1{$real_phone}" : '';
      $text .= "<td class='phone' style='vertical-align:top;padding:6px 8px;white-space:nowrap;'>";
      if ($tel_href) {
        $text .= "<span itemprop='telephone'><a href='{$tel_href}'>" . esc_html($formatted_phone) . "</a></span>";
      }
      $text .= "</td>";

      // Email
      $text .= "<td class='email' style='vertical-align:top;padding:6px 8px;white-space:nowrap;'>";
      if (!empty($email)) {
        $text .= "<a href='mailto:{$email}'>" . $email . "</a>";
      }
      $text .= "</td>";

      $text .= "</tr>";
    }

    $cur++;
  }

  if ($num > 0) {
    $text .= "</table>";
  }

  $text .= "<p>";

  return $text;
}

// ———————————————————————————————————————————————————————————
// News (image, if present, is shown OUTSIDE tables; not altered here)
function bnhm_news($atts = [], $content = null, $tag = '') {
  $limit = isset($atts['limit']) ? $atts['limit'] : 2;

  $db = getDB();
  $text = "";

  $l_strSQL  = "SELECT cal_id, cal_title, cal_url, cal_description, cal_mod_date, cal_imageurl";
  $l_strSQL .= " FROM webcal_entry";
  $l_strSQL .= " WHERE cal_eventtype='news'";
  $l_strSQL .= " AND cal_create_by='MVZ'";
  $l_strSQL .= " ORDER BY cal_mod_date DESC";
  if ($limit !== "all") {
    $limit_esc = intval($limit);
    $l_strSQL .= " LIMIT " . $limit_esc;
  }

  $res = mysqli_query($db, $l_strSQL);
  mysqli_store_result($db);
  $num = $res ? mysqli_num_rows($res) : 0;
  $cur = 1;

  while ($res && $num >= $cur) {
    $row = mysqli_fetch_assoc($res);
    $l_intDate = strtotime($row['cal_mod_date']);

    $title = esc_html($row['cal_title']);
    $url   = !empty($row['cal_url']) ? esc_url( set_url_scheme($row['cal_url'], 'https') ) : '';
    $desc  = nl2br(esc_html($row['cal_description']));
    $img   = !empty($row['cal_imageurl']) ? esc_url( set_url_scheme($row['cal_imageurl'], 'https') ) : '';

    $text .= "<h5 class='clear' style='margin:.5em 0;'>";
    $text .= "&nbsp;" . esc_html(date("M d, Y", $l_intDate)) . "&nbsp;&nbsp;";
    $text .= "</h5><span class='kindabig'>";

    if (!empty($url)) {
      $text .= "<a class='linkstylequery' href='{$url}'>" . $title . "</a>";
    } else {
      $text .= $title;
    }
    $text .= "</span>";

    // Image (if provided by news entry) — not inside any table
    if (!empty($img)) {
      $text .= "<div style='margin:.5em 0;'>";
      $text .= "<a href='{$img}' target='_blank' rel='noopener'>";
      $text .= "<img src='{$img}' alt='Photo' style='max-height:200px; height:auto; width:auto;' />";
      $text .= "</a>";
      $text .= "</div>";
    }

    $text .= "<p>{$desc}</p>";

    $cur++;
  }

  $text .= "<hr />";
  return $text;
}
