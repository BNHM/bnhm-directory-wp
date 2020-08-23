# BNHM Directory 
Tags: directory list, BNHM   
License: GPLv3 or later    
License URI: http://www.gnu.org/licenses/gpl-3.0.html    

This plugin is for Berkeley Natural History Museums member museum use only    

## Description
The BNHM Directory plugin searches names from the BNHM directory (see the https://bnhmintranet.berkeley.edu/ and click on News, Names, Outreach) and displays results in WordPress.  
Individual museums can configure this plugin to display a list of names affiliated with their museum.

There is an administration panel that you need to configure after plugin installation.  Please see the BSCIT team for connection details.

## Short Codes available for use

[print_bnhm_directory_groupname] Display all names and display group

[print_bnhm_directory_groupname groupname='Affiliated Faculty'] Display just one group (note that ampersands in group names should be replaced with 'and')

[print_bnhm_directory_alphabetical]  Display all names alphabetically

[print_bnhm_news] Display news items. Defaults to displaying 2 news items

[print_bnhm_news limit="3"] Display three news items (input any number here to limit news items)

[print_bnhm_news limit="all"] Display all news items for this museum

## Styling

Names listing for alphabetical and groups have class = 'bnhm_dir'
You can apply styling to that class to change look and feel
