<?php /* $Id: projects_crumb.view.projectdesigner.php 501 2009-07-09 04:41:41Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version2.4/modules/projectdesigner/projects_crumb.view.projectdesigner.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $titleBlock, $project_id;

$titleBlock->addCrumb('?m=projectdesigner&project_id=' . $project_id, 'design this project');