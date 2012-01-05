<?php /* $Id: tasks_tab.files.php 1018 2010-04-24 03:52:07Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version2.4/modules/files/tasks_tab.files.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI, $m, $obj, $task_id, $w2Pconfig;
if (canView('files')) {
	if (canAdd('files')) {
		echo '<a href="./index.php?m=files&a=addedit&project_id=' . $obj->task_project . '&file_task=' . $task_id . '">' . $AppUI->_('Attach a file') . '</a>';
	}
	echo w2PshowImage('stock_attach-16.png', 16, 16, '');
	$showProject = false;
	$project_id = $obj->task_project;
	include (W2P_BASE_DIR . '/modules/files/index_table.php');
}