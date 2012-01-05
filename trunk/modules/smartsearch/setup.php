<?php /* $Id: setup.php 734 2009-11-02 04:03:47Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version2.4/modules/smartsearch/setup.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

// MODULE CONFIGURATION DEFINITION
$config = array();
$config['mod_name'] = 'SmartSearch';
$config['mod_version'] = '2.0';
$config['mod_directory'] = 'smartsearch';
$config['mod_setup_class'] = 'SSearchNS';
$config['mod_type'] = 'user';
$config['mod_ui_name']     = $config['mod_name'];
$config['mod_ui_icon'] = 'kfind.png';
$config['mod_description'] = 'A module to search keywords and find the needle in the haystack';

if ($a == 'setup') {
	echo w2PshowModuleConfig($config);
}

class SSearchNS {

	public function install() {
    global $AppUI;
    
    $perms = $AppUI->acl();
    return $perms->registerModule('Smart Search', 'smartsearch');
	}

	public function remove() {
		return true;
	}

	public function upgrade() {
		return true;
	}
}