<?php /* $Id: index.php 1565 2010-12-31 05:34:18Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version2.4/modules/system/roles/index.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$AppUI->savePlace();

// pull all the key types
$perms = &$AppUI->acl();

// Get the permissions for this module
$canAccess = canAccess('roles');
if (!$canAccess) {
	$AppUI->redirect('m=public&a=access_denied');
}
$canRead = canView('roles');
$canAdd = canAdd('roles');
$canEdit = canEdit('roles');
$canDelete = canDelete('roles');

$crole = new CRole;
$roles = $crole->getRoles();

$role_id = (int) w2PgetParam($_GET, 'role_id', 0);

// setup the title block
$titleBlock = new CTitleBlock('Roles', 'main-settings.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=system', 'System Admin');
$titleBlock->show();

$crumbs = array();
$crumbs['?m=system'] = 'System Admin';

?>

<script language="javascript" type="text/javascript">
<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($canDelete) {
?>
function delIt(id) {
	if (confirm( 'Are you sure you want to delete this?' )) {
		f = document.roleFrm;
		f.del.value = 1;
		f.role_id.value = id;
		f.submit();
	}
}
<?php } ?>
</script>

<table border="0" cellpadding="2" cellspacing="1" width="100%" class="tbl">
<tr>
	<th>&nbsp;</th>
	<th><?php echo $AppUI->_('Role ID'); ?></th>
	<th><?php echo $AppUI->_('Description'); ?></th>
	<th>&nbsp;</th>
</tr>
<?php

// do the modules that are installed on the system
$s = '';
foreach ($roles as $row) {
	echo showRoleRow($row);
}
// add in the new key row:
if ($role_id == 0) {
	echo showRoleRow();
}
?>
</table>