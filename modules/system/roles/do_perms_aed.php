<?php /* $Id: do_perms_aed.php 1022 2010-04-24 03:53:00Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version2.4/modules/system/roles/do_perms_aed.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

// check permissions
$perms = &$AppUI->acl();
if (!canEdit('system')) {
	$AppUI->redirect('m=public&a=access_denied');
}

$del = isset($_POST['del']) ? $_POST['del'] : 0;

$obj = &$AppUI->acl();

$AppUI->setMsg('Permission');
if ($del) {
	if ($obj->del_acl($_REQUEST['permission_id'])) {
		$AppUI->setMsg('deleted', UI_MSG_ALERT, true);
		$obj->removeACLPermissions(w2PgetParam($_REQUEST, 'permission_id', null));
		$AppUI->redirect();
	} else {
		$AppUI->setMsg($obj->msg(), UI_MSG_ERROR);
		$AppUI->redirect();
	}
} else {
	// No longer have update, only add.
	if ($obj->addRolePermission()) {
		$AppUI->setMsg('added', UI_MSG_OK, true);
	} else {
		$AppUI->setMsg($obj->msg(), UI_MSG_ERROR);
	}
	$AppUI->redirect();
}