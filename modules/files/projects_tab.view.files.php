<?php /* $Id: projects_tab.view.files.php 753 2009-11-09 03:55:29Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version2.4/modules/files/projects_tab.view.files.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI, $project_id, $deny, $canRead, $canEdit, $w2Pconfig;

$cfObj = new CFileFolder();
global $allowed_folders_ary;
$allowed_folders_ary = $cfObj->getAllowedRecords($AppUI->user_id);
global $denied_folders_ary;
$denied_folders_ary = $cfObj->getDeniedRecords($AppUI->user_id);

$limited = (count($allowed_folders_ary) < $cfObj->countFolders()) ? true : false;

if (!$limited) {
	$canEdit = true;
} elseif ($limited and array_key_exists($folder, $allowed_folders_ary)) {
	$canEdit = true;
} else {
	$canEdit = false;
}
$showProject = false;
require (W2P_BASE_DIR . '/modules/files/folders_table.php');