<?php /* $Id: projects_tab.view.links.php 767 2009-11-16 01:39:58Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version2.4/modules/links/projects_tab.view.links.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI, $project_id, $deny, $canRead, $canEdit, $w2Pconfig;

$showProject = false;
require W2P_BASE_DIR . '/modules/links/index_table.php';