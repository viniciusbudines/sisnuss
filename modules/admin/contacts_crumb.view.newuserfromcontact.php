<?php /* $Id: contacts_crumb.view.newuserfromcontact.php 1022 2010-04-24 03:53:00Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version2.4/modules/admin/contacts_crumb.view.newuserfromcontact.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $AppUI, $titleBlock, $contact_id, $is_user;
$perms = &$AppUI->acl();
$canAddUsers = canAdd('admin');

if ($canAddUsers && $contact_id && !$is_user) {
	$titleBlock->addCrumb('?m=admin&a=addedituser&contact_id='.$contact_id, 'create a user');
}