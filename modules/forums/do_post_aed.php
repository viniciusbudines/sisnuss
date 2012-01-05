<?php /* $Id: do_post_aed.php 1711 2011-03-01 06:21:38Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version2.4/modules/forums/do_post_aed.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$delete = (int) w2PgetParam($_POST, 'del', 0);

$message_forum = (int) w2PgetParam($_POST, 'message_forum', 0);
$message_parent = (int) w2PgetParam($_POST, 'message_parent', 0);
$successPath = ($delete) ? 'm=forums' : 'm=forums&a=viewer&forum_id='.
        $message_forum.'&message_parent='.$message_parent;
$errorPath = 'm=forums&a=viewer&forum_id='.$message_forum.'&message_parent='.
        $message_parent.'&post_message=1';

$controller = new w2p_Controllers_Base(
                    new CForumMessage(), $delete, 'Message', $successPath, $errorPath
                  );

$AppUI = $controller->process($AppUI, $_POST);
$AppUI->redirect($controller->resultPath);
