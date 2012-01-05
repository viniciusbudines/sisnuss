<?php /* $Id: phpinfo.php 1024 2010-04-24 03:53:39Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version2.4/modules/system/phpinfo.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

if (!canView('system')) { // let's see if the user has sys access
	$AppUI->redirect('m=public&a=access_denied');
}
phpinfo();