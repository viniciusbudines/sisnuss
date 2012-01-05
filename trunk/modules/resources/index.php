<?php /* $Id: index.php 1025 2010-04-24 03:54:03Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version2.4/modules/resources/index.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$AppUI->savePlace();

$obj = new CResource();

$perms = &$AppUI->acl();
$canEdit = canEdit('resources');

$titleBlock = new CTitleBlock('Resources', 'resources.png', $m, $m . '.' . $a);
if ($canEdit) {
	$titleBlock->addCell('<input type="submit" class="button" value="' . $AppUI->_('new resource') . '">', '', '<form action="?m=resources&a=addedit" method="post" accept-charset="utf-8">', '</form>');
}
$titleBlock->show();

if (isset($_GET['tab'])) {
	$AppUI->setState('ResourcesIdxTab', w2PgetParam($_GET, 'tab', null));
}
$resourceTab = $AppUI->getState('ResourcesIdxTab', 0);
$tabBox = new CTabBox('?m=resources', W2P_BASE_DIR . '/modules/resources/', $resourceTab);
$tabbed = $tabBox->isTabbed();
foreach ($obj->loadTypes() as $type) {
	if ($type['resource_type_id'] == 0 && !$tabbed)
		continue;
	$tabBox->add('vw_resources', $type['resource_type_name']);
}

$tabBox->show();