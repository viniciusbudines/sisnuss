<?php /* $Id: do_preference_aed.php 1998 2011-07-18 07:33:16Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version2.4/modules/system/do_preference_aed.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$del = (int) w2PgetParam($_POST, 'del', 0);
$pref_user = (int) w2PgetParam($_POST, 'pref_user', 0);

$perms = &$AppUI->acl();
if (!canEdit('system') && !$pref_user) {
	$AppUI->redirect('m=public&a=access_denied');
}

if ((!($AppUI->user_id == $pref_user) && !canEdit('admin')) && $pref_user) {
	$AppUI->redirect('m=public&a=access_denied');
}
$emails = (isset($_POST['tl_assign'])) ? 1 : 0;
$emails += (isset($_POST['tl_task'])) ? 2 : 0;
$emails += (isset($_POST['tl_proj'])) ? 4 : 0;
$_POST['pref_name']['TASKLOGEMAIL'] = $emails;

$obj = new CPreferences();
$obj->pref_user = $pref_user;
foreach ($_POST['pref_name'] as $name => $value) {
	$obj->pref_name = $name;
	$obj->pref_value = $value;
	// prepare (and translate) the module name ready for the suffix
	$AppUI->setMsg('Preferences');
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
			if ($obj->pref_user) {
				// if user preferences, reload them now
				$AppUI->loadPrefs($AppUI->user_id);
				$AppUI->setUserLocale();
				include_once W2P_BASE_DIR . ('/locales/' . $AppUI->user_locale . '/locales.php');
				include W2P_BASE_DIR . ('/locales/core.php');
				$AppUI->setMsg('Preferences');
			}
			$AppUI->setMsg('updated', UI_MSG_OK, true);
		}
	}
}
$returnPath = ($pref_user) ? 'm=admin&a=viewuser&user_id='.$pref_user : 'm=system';
$AppUI->redirect($returnPath);