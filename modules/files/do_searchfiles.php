<?php /* $Id: do_searchfiles.php 667 2009-09-22 16:42:52Z pedroix $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version2.4/modules/files/do_searchfiles.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

if (empty($s) || mb_strlen(mb_trim($s)) == 0) {
	$a = 'index';
	$AppUI->setMsg('Please enter a search value');
}