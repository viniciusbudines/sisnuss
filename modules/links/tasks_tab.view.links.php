<?php /* $Id: tasks_tab.view.links.php 767 2009-11-16 01:39:58Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version2.4/modules/links/tasks_tab.view.links.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly');
}

global $AppUI, $m, $obj, $task_id;
$project_id = $obj->task_project;
$showProject = false;
include W2P_BASE_DIR . '/modules/links/index_table.php';