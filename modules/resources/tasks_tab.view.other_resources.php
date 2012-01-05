<?php /* $Id: tasks_tab.view.other_resources.php 1595 2011-01-17 07:37:10Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version2.4/modules/resources/tasks_tab.view.other_resources.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

// Grab a list of the other resources, determine how they are allocated,
// and if there is a clash between this and other tasks.
global $AppUI, $task_id, $obj;

$resource = new CResource();

$q = new w2p_Database_Query();
$q->addQuery('a.*');
$q->addQuery('b.percent_allocated');
$q->addQuery('c.resource_type_name');
$q->addTable('resources', 'a');
$q->addJoin('resource_tasks', 'b', 'b.resource_id = a.resource_id', 'inner');
$q->addJoin('resource_types', 'c', 'c.resource_type_id = a.resource_type', 'inner');
$q->addWhere('b.task_id = ' . (int)$task_id);
$resources = $q->loadHashList('resource_id');

// Determine any other clashes.
$resource_tasks = array();

if (count($resources)) {
	$q->clear();
	$q->addQuery('b.resource_id, sum(b.percent_allocated) as total_allocated');
	$q->addTable('tasks', 'a');
	$q->addJoin('resource_tasks', 'b', 'b.task_id = a.task_id', 'inner');
	$q->addWhere('b.resource_id IN (' . implode(',', array_keys($resources)) . ')');
	$q->addWhere('task_start_date <= \'' . $obj->task_end_date . '\'');
	$q->addWhere('task_end_date >= \'' . $obj->task_start_date . '\'');
	$q->addGroup('resource_id');
	$resource_tasks = $q->loadHashList();
}

?>
<table class="std" width="100%" cellpadding="4" cellspacing="1">
<tr><th>Type</th><th>Resource</th><th>Allocation</th><th>&nbsp;</th></tr>
<?php
foreach ($resources as $res) {
	$output = '<tr><td class="hilite">' . $res['resource_type_name'] . '</td>
	<td class="hilite">' . $res['resource_name'] . '</td>
	<td class="hilite">' . $res['percent_allocated'] . '%</td><td class="warning">';
	if (isset($resource_tasks[$res['resource_id']]) && $resource_tasks[$res['resource_id']] > $res['resource_max_allocation']) {
		$output .= 'OVERALLOCATED';
	}
	$output .= '&nbsp;</td></tr>';
	echo $output;
}
?>
</table>