<?php /* $Id: access_denied.php 40 2008-02-11 12:11:44Z pedroix $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version2.4/modules/public/access_denied.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
$titleBlock = new CTitleBlock('Access Denied', 'error.png', $m, "$m.$a");
$titleBlock->show();
?>
<table class="std" width="100%" border="0" cellpadding="5" cellspacing="0">
<tr valign="top">
	<td width="50%"><?php echo $AppUI->_('accessDeniedMsg'); ?></td>
	<td width="50%">&nbsp;</td>
</tr>
</table>