<?php /* $Id: vw_logs.php 1849 2011-04-30 21:42:29Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version2.4/modules/projects/vw_logs.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI, $project_id, $df, $canEdit, $m, $tab;

$company_id = CProject::getCompany($project_id);

$task_log_costcodes =  array(0 => '(all)') + CProject::getBillingCodes($company_id, true);

$users = w2PgetUsers();

$cost_code = w2PgetParam($_GET, 'cost_code', 0);

if (isset($_GET['user_id'])) {
	$AppUI->setState('ProjectsTaskLogsUserFilter', w2PgetParam($_GET, 'user_id', 0));
}
$user_id = $AppUI->getState('ProjectsTaskLogsUserFilter') ? $AppUI->getState('ProjectsTaskLogsUserFilter') : 0;

if (isset($_GET['hide_inactive'])) {
	$AppUI->setState('ProjectsTaskLogsHideArchived', true);
} else {
	$AppUI->setState('ProjectsTaskLogsHideArchived', false);
}
$hide_inactive = $AppUI->getState('ProjectsTaskLogsHideArchived');

if (isset($_GET['hide_complete'])) {
	$AppUI->setState('ProjectsTaskLogsHideComplete', true);
} else {
	$AppUI->setState('ProjectsTaskLogsHideComplete', false);
}
$hide_complete = $AppUI->getState('ProjectsTaskLogsHideComplete');

?>
<script language="javascript" type="text/javascript">
<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($canEdit) {
?>
function delIt2(id) {
	if (confirm( '<?php echo $AppUI->_('doDelete', UI_OUTPUT_JS) . ' ' . $AppUI->_('Task Log', UI_OUTPUT_JS) . '?'; ?>' )) {
		document.frmDelete2.task_log_id.value = id;
		document.frmDelete2.submit();
	}
}
<?php } ?>
</script>
<table border="0" cellpadding="2" cellspacing="1" width="100%" class="std">
<form name="frmFilter" action="./index.php" method="get" accept-charset="utf-8">
<tr>
	<td width="98%">&nbsp;</td>
	<td width="1%" nowrap="nowrap"><input type="checkbox" name="hide_inactive" id="hide_inactive" <?php echo $hide_inactive ? 'checked="checked"' : '' ?> onchange="document.frmFilter.submit()" /></td><td width="1%" nowrap="nowrap"><label for="hide_inactive"><?php echo $AppUI->_('Hide Inactive') ?></label></td>
	<td width="1%" nowrap="nowrap"><input type="checkbox" name="hide_complete" id="hide_complete" <?php echo $hide_complete ? 'checked="checked"' : '' ?> onchange="document.frmFilter.submit()" /></td><td width="1%" nowrap="nowrap"><label for="hide_complete"><?php echo $AppUI->_('Hide 100% Complete') ?></label></td>
	<!--
	disabled this filter for now... something is wrong with the userId portion...
	<td width="1%" nowrap="nowrap"><?php echo $AppUI->_('User Filter') ?></td>
	<td width="1%"><?php echo arraySelect($users, 'user_id', 'size="1" class="text" id="medium" onchange="document.frmFilter.submit()"', $user_id) ?></td>
	-->
	<td width="1%" nowrap="nowrap"><?php echo $AppUI->_('Cost Code Filter') ?></td>
	<td width="1%"><?php echo arraySelect($task_log_costcodes, 'cost_code', 'size="1" class="text" onchange="document.frmFilter.submit()"', $cost_code) ?></td>
</tr>
<input type="hidden" name="m" value="projects"/>
<input type="hidden" name="a" value="view"/>
<input type="hidden" name="project_id" value="<?php echo $project_id ?>"/>
<input type="hidden" name="tab" value="<?php echo $tab ?>"/>
</form>
</table>
<table border="0" cellpadding="2" cellspacing="1" width="100%" class="tbl">
<form name="frmDelete2" action="./index.php?m=tasks" method="post" accept-charset="utf-8">
	<input type="hidden" name="dosql" value="do_updatetask" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="task_log_id" value="0" />
</form>
<tr>
	<th></th>
	<th><?php echo $AppUI->_('Date'); ?></th>
	<th width="100"><?php echo $AppUI->_('Summary'); ?></th>
	<th width="100"><?php echo $AppUI->_('User'); ?></th>
	<th width="100"><?php echo $AppUI->_('Hours'); ?></th>
	<th width="100"><?php echo $AppUI->_('Cost Code'); ?></th>
	<th width="100%"><?php echo $AppUI->_('Comments'); ?></th>
	<th></th>
</tr>
<?php
// Winnow out the tasks we are not allowed to view.
$perms = &$AppUI->acl();
$canDelete = canDelete('task_log');

// Pull the task comments
$project = new CProject;
$logs = $project->getTaskLogs($AppUI, $project_id, $user_id, $hide_inactive, $hide_complete, $cost_code);

$s = '';
$hrs = 0;
$canEdit = canEdit('task_log');
$sf = $df;
$df .= ' ' . $AppUI->getPref('TIMEFORMAT');
foreach ($logs as $row) {
	$task_log_date = intval($row['task_log_date']) ? new w2p_Utilities_Date($row['task_log_date']) : null;

	$s .= '<tr bgcolor="white" valign="top"><td>';
	if ($canEdit) {
		$s .= '<a href="?m=tasks&a=view&task_id=' . $row['task_id'] . '&tab=1&task_log_id=' . $row['task_log_id'] . '">' . w2PshowImage('icons/stock_edit-16.png', 16, 16, '') . "\n\t\t</a>";
	}
	$s .= '</td>';
	$s .= '<td nowrap="nowrap">' . ($task_log_date ? $task_log_date->format($sf) : '-') . '<br /><br />';
    $task_log_updated = intval($row['task_log_updated']) ? new w2p_Utilities_Date($row['task_log_updated']) : null;
    $s .= '(' . $AppUI->_('Logged').': ' . ($task_log_updated ? $task_log_updated->format($df) : '-') . ')';
    $s .= '</td>';
	$s .= '<td width="30%"><a href="?m=tasks&a=view&task_id=' . $row['task_id'] . '&tab=0">' . $row['task_log_name'] . '</a></td>';
	$s .= '<td width="100">' . $row['real_name'] . '</td>';
	$s .= '<td width="100" align="right">' . sprintf('%.2f', $row['task_log_hours']) . '</td>';
	$s .= '<td width="100">' . $row['task_log_costcode'] . '</td><td>';

	// dylan_cuthbert: auto-transation system in-progress, leave these lines
	$transbrk = "\n[translation]\n";
	$descrip = w2p_textarea($row['task_log_description']);
	$tranpos = mb_strpos($descrip, mb_str_replace("\n", '<br />', $transbrk));
	if ($tranpos === false) {
		$s .= $descrip;
	} else {
		$descrip = mb_substr($descrip, 0, $tranpos);
		$tranpos = mb_strpos($row['task_log_description'], $transbrk);
		$transla = mb_substr($row['task_log_description'], $tranpos + mb_strlen($transbrk));
		$transla = mb_trim(mb_str_replace("'", '"', $transla));
		$s .= $descrip . '<div style="font-weight: bold; text-align: right"><a title="' . $transla . '" class="hilite">[' . $AppUI->_('translation') . ']</a></div>';
	}
	// end auto-translation code

	$s .= '</td><td>';
	if ($canDelete) {
		$s .= '<a href="javascript:delIt2(' . $row['task_log_id'] . ');" title="' . $AppUI->_('delete log') . '">' . w2PshowImage('icons/stock_delete-16.png', 16, 16, '') . '</a>';
	}
	$s .= '</td></tr>';
	$hrs += (float)$row['task_log_hours'];
}
$s .= '<tr bgcolor="white" valign="top">';
$s .= '<td colspan="4" align="right">' . $AppUI->_('Total Hours') . ' =</td>';
$s .= '<td align="right">' . sprintf('%.2f', $hrs) . '</td>';
$s .= '</tr>';
echo $s;
?>
</table>