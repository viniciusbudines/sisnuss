<?php /* $Id: do_company_aed.php 1579 2011-01-11 07:09:04Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version2.4/modules/companies/do_company_aed.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$delete = (int) w2PgetParam($_POST, 'del', 0);

$controller = new w2p_Controllers_Base(
                    new CCompany(), $delete, 'Company', 'm=companies', 'm=companies&a=addedit'
                  );

$AppUI = $controller->process($AppUI, $_POST);
$AppUI->redirect($controller->resultPath);