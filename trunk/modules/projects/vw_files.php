<?php /* $Id: vw_files.php 501 2009-07-09 04:41:41Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version2.4/modules/projects/vw_files.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI, $project_id, $deny, $canRead, $canEdit, $w2Pconfig;

$showProject = false;
require (W2P_BASE_DIR . '/modules/files/index_table.php');