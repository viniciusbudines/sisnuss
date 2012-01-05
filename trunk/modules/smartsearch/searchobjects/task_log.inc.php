<?php /* $Id: task_log.inc.php 501 2009-07-09 04:41:41Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version2.4/modules/smartsearch/searchobjects/task_log.inc.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

/**
 * task_log Class
 */
class task_log extends smartsearch {
	public $table = 'task_log';
	public $table_module = 'tasks';
	public $table_key = 'task_log_task';
	public $table_key2 = 'task_log_id';
	public $table_extra = 'task_log_task <> 0';
	public $table_link = 'index.php?m=tasks&a=view&task_id=';
	public $table_link2 = '&tab=1&task_log_id=';
	public $table_title = 'Task logs';
	public $table_orderby = 'task_log_name';
	public $search_fields = array('task_log_name', 'task_log_description', 'task_log_task');
	public $display_fields = array('task_log_name', 'task_log_description', 'task_log_task');

	public function ctask_log() {
		return new task_log();
	}
}