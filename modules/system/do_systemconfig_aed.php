<?php /* $Id: do_systemconfig_aed.php 1595 2011-01-17 07:37:10Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version2.4/modules/system/do_systemconfig_aed.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$perms = &$AppUI->acl();
if (!canEdit('system')) {
	$AppUI->redirect('m=public&a=access_denied');
}

$obj = new CConfig();

// set all checkboxes to false
// overwrite the true/enabled/checked checkboxes later
$q = new w2p_Database_Query;
$q->addTable('config');
$q->addUpdate('config_value', 'false');
$q->addWhere("config_type = 'checkbox'");
$rs = $q->loadResult();
$q->clear();

foreach ($_POST['w2Pcfg'] as $name => $value) {
    $obj->config_name = $name;
	$obj->config_value = $value;

	// grab the appropriate id for the object in order to ensure
	// that the db is updated well (config_name must be unique)
	$obj->config_id = $_POST['w2PcfgId'][$name];
    $update = false;

    // This is really kludgy, but it works.. suggestions?
    if (strpos($name, '_pass') !== false) {
        if (1 == $_POST[$name.'_mod']) {
            $update = true;
        }
    } else {
        $update = true;
    }

    if ($update) {
        // prepare (and translate) the module name ready for the suffix
        $AppUI->setMsg('System Configuration');
        if (($msg = $obj->store($AppUI))) {
            $AppUI->setMsg($msg, UI_MSG_ERROR);
        } else {
            $AppUI->setMsg('updated', UI_MSG_OK, true);
        }
    }
}
$AppUI->redirect();