<?php /* $Id: setup.php 501 2009-07-09 04:41:41Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version2.4/modules/projectdesigner/setup.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

// MODULE CONFIGURATION DEFINITION
$config = array();
$config['mod_name'] = 'ProjectDesigner';
$config['mod_version'] = '1.0';
$config['mod_directory'] = 'projectdesigner';
$config['mod_setup_class'] = 'projectDesigner';
$config['mod_type'] = 'user';
$config['mod_ui_name'] = 'ProjectDesigner';
$config['mod_ui_icon'] = 'projectdesigner.jpg';
$config['mod_description'] = 'A module to design projects';
$config['mod_config'] = true;

if ($a == 'setup') {
	echo dPshowModuleConfig($config);
}

class projectDesigner {

	public function install() {
		$success = 1;

		$bulk_sql[] = '
                  CREATE TABLE project_designer_options (
                    pd_option_id INT(11) NOT NULL auto_increment,
                    pd_option_user INT(11) NOT NULL default 0 UNIQUE,
                    pd_option_view_project INT(1) NOT NULL default 1,
                    pd_option_view_gantt INT(1) NOT NULL default 1,
                    pd_option_view_tasks INT(1) NOT NULL default 1,
                    pd_option_view_actions INT(1) NOT NULL default 1,
                    pd_option_view_addtasks INT(1) NOT NULL default 1,
                    pd_option_view_files INT(1) NOT NULL default 1,
                    PRIMARY KEY (pd_option_id) 
                  ) TYPE=MyISAM;';
		foreach ($bulk_sql as $s) {
			db_exec($s);

			if (db_error()) {
				$success = 0;
			}
		}
		return $success;
	}

	public function remove() {
		$success = 1;

		$bulk_sql[] = 'DROP TABLE project_designer_options';
		foreach ($bulk_sql as $s) {
			db_exec($s);
			if (db_error()) {
				$success = 0;
			}
		}
		return $success;
	}

	public function upgrade() {
		return null;
	}

	public function configure() {
		global $AppUI;

		$AppUI->redirect('m=projectdesigner&a=configure');

		return true;
	}

}