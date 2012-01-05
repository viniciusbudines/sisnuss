<?php /* $Id: tasks_dosql.addedit.php 1967 2011-07-03 22:39:16Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version2.4/modules/resources/tasks_dosql.addedit.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

// Set the pre and post save functions
global $pre_save, $post_save, $other_resources;

$pre_save[] = 'resource_presave';
$post_save[] = 'resource_postsave';
$other_resources = null;
