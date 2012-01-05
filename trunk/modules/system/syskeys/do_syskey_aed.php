<?php /* $Id: do_syskey_aed.php 1022 2010-04-24 03:53:00Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version2.4/modules/system/syskeys/do_syskey_aed.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

// check permissions
$perms = &$AppUI->acl();
if (!canEdit('system')) {
	$AppUI->redirect('m=public&a=access_denied');
}

$del = (int) w2PgetParam($_POST, 'del', 0);

$obj = new CSysKey();

if (!$obj->bind($_POST)) {
	$AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
	$AppUI->redirect();
}

$AppUI->setMsg('System Lookup Keys', UI_MSG_ALERT);
if ($del) {
	if (($msg = $obj->delete())) {
		$AppUI->setMsg($msg, UI_MSG_ERROR);
	} else {
		$AppUI->setMsg('deleted', UI_MSG_ALERT, true);
	}
} else {
	if (($msg = $obj->store())) {
		$AppUI->setMsg($msg, UI_MSG_ERROR);
	} else {
		$AppUI->setMsg($_POST['syskey_id'] ? 'updated' : 'inserted', UI_MSG_OK, true);
	}
}
$AppUI->redirect('m=system&u=syskeys&a=keys');