<?php /* $Id: setup.php 1841 2011-04-30 21:40:52Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version2.4/modules/history/setup.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

/*
* Name:      History
* Directory: history
* Version:   0.32
* Class:     user
* UI Name:   History
* UI Icon:
*/

// MODULE CONFIGURATION DEFINITION
$config = array();
$config['mod_name'] = 'History';
$config['mod_version'] = '0.32';
$config['mod_directory'] = 'history';
$config['mod_setup_class'] = 'CSetupHistory';
$config['mod_type'] = 'user';
$config['mod_ui_name'] = 'History';
$config['mod_ui_icon'] = '';
$config['mod_description'] = 'A module for tracking changes';

if ($a == 'setup') {
	echo w2PshowModuleConfig($config);
}

class CSetupHistory {

	public function install(CAppUI $AppUI = null) {
        global $AppUI;

		$q = new w2p_Database_Query;
		$q->createTable('history');
        $sql = ' (
			history_id int(10) unsigned NOT NULL auto_increment,
			history_date datetime NOT NULL default \'0000-00-00 00:00:00\',		  
			history_user int(10) NOT NULL default \'0\',
			history_action varchar(20) NOT NULL default \'modify\',
			history_item int(10) NOT NULL,
			history_table varchar(20) NOT NULL default \'\',
			history_project int(10) NOT NULL default \'0\',
			history_name varchar(255),
			history_changes text,
			history_description text,
			PRIMARY KEY  (history_id),
			INDEX index_history_module (history_table, history_item),
		  	INDEX index_history_item (history_item) 
			) TYPE=MyISAM';
		$q->createDefinition($sql);
		$q->exec();

        $perms = $AppUI->acl();
        return $perms->registerModule('History', 'history');
	}

	public function remove(CAppUI $AppUI = null) {
		global $AppUI;

        $q = new w2p_Database_Query;
		$q->dropTable('history');
		$q->exec();

        $perms = $AppUI->acl();
        return $perms->unregisterModule('history');
	}

	public function upgrade($old_version) {

        return true;
	}
}