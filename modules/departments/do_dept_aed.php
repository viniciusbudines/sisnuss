<?php /* $Id: do_dept_aed.php 1579 2011-01-11 07:09:04Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version2.4/modules/departments/do_dept_aed.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$delete = (int) w2PgetParam($_POST, 'del', 0);
$company_id = (int) w2PgetParam($_POST, 'dept_company', 0);
$successPath = 'm=companies&a=view&company_id='.$company_id;

$controller = new w2p_Controllers_Base(
                    new CDepartment(), $delete, 'Department', $successPath, 'm=departments&a=addedit'
                  );

$AppUI = $controller->process($AppUI, $_POST);
$AppUI->redirect($controller->resultPath);