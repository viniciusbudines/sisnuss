<?php /* $Id: vw_inactive_usr.php 753 2009-11-09 03:55:29Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version2.4/modules/admin/vw_inactive_usr.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $w2Pconfig, $canEdit, $stub, $where, $orderby;

$users = w2PgetUsersList($stub, $where, $orderby);
$canLogin = false;

require W2P_BASE_DIR . '/modules/admin/vw_usr.php';