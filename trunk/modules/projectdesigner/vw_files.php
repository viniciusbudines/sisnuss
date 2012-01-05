<?php /* $Id: vw_files.php 753 2009-11-09 03:55:29Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version2.4/modules/projectdesigner/vw_files.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $AppUI, $project_id, $deny, $canRead, $canEdit, $w2Pconfig;

$showProject = false;
require (w2PgetConfig('root_dir') . '/modules/files/index_table.php');