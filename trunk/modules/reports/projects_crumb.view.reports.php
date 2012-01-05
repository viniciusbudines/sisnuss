<?php /* $Id: projects_crumb.view.reports.php 1533 2010-12-18 08:41:46Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version2.4/modules/reports/projects_crumb.view.reports.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $titleBlock, $project_id;

$canView = canView('reports');
if ($canView) {
    $titleBlock->addCrumb('?m=reports&project_id=' . $project_id, 'reports');
}