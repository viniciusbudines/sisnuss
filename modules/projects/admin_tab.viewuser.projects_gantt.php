<?php /* $Id: admin_tab.viewuser.projects_gantt.php 501 2009-07-09 04:41:41Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version2.4/modules/projects/admin_tab.viewuser.projects_gantt.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $company_id, $dept_ids, $department, $min_view, $m, $a, $user_id, $tab;

// reset the department and company filter info
// which is not used here
$company_id = $department = 0;

require (W2P_BASE_DIR . '/modules/projects/viewgantt.php');