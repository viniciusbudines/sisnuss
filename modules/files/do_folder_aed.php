<?php /* $Id: do_folder_aed.php 1711 2011-03-01 06:21:38Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version2.4/modules/files/do_folder_aed.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$delete = (int) w2PgetParam($_POST, 'del', 0);

$controller = new w2p_Controllers_Base(
                    new CFileFolder(), $delete, 'File Folder', 'm=files', 'm=files&a=addedit_folder'
                  );

$AppUI = $controller->process($AppUI, $_POST);
$AppUI->redirect($controller->resultPath);
