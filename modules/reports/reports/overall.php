<?php /* $Id: overall.php 2011 2011-07-24 06:01:27Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version2.4/modules/reports/reports/overall.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $AppUI, $cal_sdf;
$AppUI->loadCalendarJS();

/**
 * Generates a report of the task logs for given dates
 */
$do_report = w2PgetParam($_POST, 'do_report', 0);
$log_pdf = w2PgetParam($_POST, 'log_pdf', 0);

$log_start_date = w2PgetParam($_POST, 'log_start_date', 0);
$log_end_date = w2PgetParam($_POST, 'log_end_date', 0);
$log_all = w2PgetParam($_POST, 'log_all', 0);

// create Date objects from the datetime fields
$start_date = intval($log_start_date) ? new w2p_Utilities_Date($log_start_date) : new w2p_Utilities_Date();
$end_date = intval($log_end_date) ? new w2p_Utilities_Date($log_end_date) : new w2p_Utilities_Date();

if (!$log_start_date) {
	$start_date->subtractSpan(new Date_Span('14,0,0,0'));
}
$end_date->setTime(23, 59, 59);

$fullaccess = ($AppUI->user_type == 1);

if (function_exists('styleRenderBoxTop')) {
	echo styleRenderBoxTop();
}
?>
<form name="editFrm" action="index.php?m=reports" method="post" accept-charset="utf-8">
<input type="hidden" name="project_id" value="<?php echo $project_id; ?>" />
<input type="hidden" name="report_type" value="<?php echo $report_type; ?>" />
<table cellspacing="0" cellpadding="4" border="0" width="100%" class="std">
<tr>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_('For period'); ?>:</td>
	<td nowrap="nowrap">
		<input type="hidden" name="log_start_date" id="log_start_date" value="<?php echo $start_date ? $start_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
		<input type="text" name="start_date" id="start_date" onchange="setDate('editFrm', 'start_date');" value="<?php echo $start_date ? $start_date->format($df) : ''; ?>" class="text" />
		<a href="javascript: void(0);" onclick="return showCalendar('start_date', '<?php echo $df ?>', 'editFrm', null, true)">
			<img src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" />
		</a>
	</td>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_('to'); ?></td>
	<td nowrap="nowrap">
		<input type="hidden" name="log_end_date" id="log_end_date" value="<?php echo $end_date ? $end_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
		<input type="text" name="end_date" id="end_date" onchange="setDate('editFrm', 'end_date');" value="<?php echo $end_date ? $end_date->format($df) : ''; ?>" class="text" />
		<a href="javascript: void(0);" onclick="return showCalendar('end_date', '<?php echo $df ?>', 'editFrm', null, true)">
			<img src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" />
		</a>
	</td>

	<td nowrap="nowrap">
		<input type="checkbox" name="log_all" id="log_all" <?php if ($log_all)
	echo 'checked="checked"' ?> />
		<label for="log_all"><?php echo $AppUI->_('Log All'); ?></label>
	</td>
	<td nowrap="nowrap">
		<input type="checkbox" name="log_pdf" id="log_pdf" <?php if ($log_pdf)
	echo 'checked="checked"' ?> />
		<label for="log_pdf"><?php echo $AppUI->_('Make PDF'); ?></label>
	</td>

	<td align="right" width="50%" nowrap="nowrap">
		<input class="button" type="submit" name="do_report" value="<?php echo $AppUI->_('submit'); ?>" />
	</td>
</tr>
</table>
</form>
<?php
$allpdfdata = array();
function showcompany($company, $restricted = false) {
	global $AppUI, $allpdfdata, $log_start_date, $log_end_date, $log_all;
	$q = new w2p_Database_Query;
	$q->addTable('projects');
	$q->addQuery('project_id, project_name');
	$q->addWhere('project_company = ' . (int)$company);
	$projects = $q->loadHashList();
	$q->clear();

	$q->addTable('companies');
	$q->addQuery('company_name');
	$q->addWhere('company_id = ' . (int)$company);
	$company_name = $q->loadResult();
	$q->clear();

	$table = '<h2>Company: ' . $company_name . '</h2>
    	<table cellspacing="1" cellpadding="4" border="0" class="tbl">';
	$project_row = '
        <tr>
                <th>' . $AppUI->_('Project') . '</th>';

	$pdfth[] = $AppUI->_('Project');
	$project_row .= '<th>' . $AppUI->_('Total') . '</th></tr>';
	$pdfth[] = $AppUI->_('Total');
	$pdfdata[] = $pdfth;

	$hours = 0.0;
	$table .= $project_row;

	foreach ($projects as $project => $name) {
		$pdfproject = array();
		$pdfproject[] = $name;
		$project_hours = 0;
		$project_row = '<tr><td>' . $name . '</td>';

		$q->addTable('projects');
		$q->addTable('tasks');
		$q->addTable('task_log');
		$q->addQuery('task_log_costcode, SUM(task_log_hours) as hours');
		$q->addWhere('project_id = ' . (int)$project);
		$q->addWhere('project_active = 1');
		if (($template_status = w2PgetConfig('template_projects_status_id')) != '') {
			$q->addWhere('project_status <> ' . (int)$template_status);
		}

		if ($log_start_date != 0 && !$log_all) {
			$q->addWhere('task_log_date >=' . $log_start_date);
		}
		if ($log_end_date != 0 && !$log_all) {
			$q->addWhere('task_log_date <=' . $log_end_date);
		}
		if ($restricted) {
			$q->addWhere('task_log_creator = ' . (int)$AppUI->user_id);
		}

		$q->addWhere('project_id = task_project');
		$q->addWhere('task_id = task_log_task');
		$q->addGroup('project_id');

		$task_logs = $q->loadHashList();
		$q->clear();

		foreach ($task_logs as $task_log) {
			$project_hours += $task_log;
		}
		$project_row .= '<td style="text-align:right;">' . sprintf('%.2f', round($project_hours, 2)) . '</td></tr>';
		$pdfproject[] = round($project_hours, 2);
		$hours += $project_hours;
		if ($project_hours > 0) {
			$table .= $project_row;
			$pdfdata[] = $pdfproject;
		}
	}

	if ($hours > 0) {
		$pdfdata[] = array($AppUI->_('Total'), round($hours, 2));
		$allpdfdata[$company_name] = $pdfdata;
		echo $table;
		echo '<tr><td>' . $AppUI->_('Total') . '</td><td style="text-align:right;">' . sprintf('%.2f', round($hours, 2)) . '</td></tr></table>';
	}

	return $hours;
}

if ($do_report) {

	if (function_exists('styleRenderBoxBottom')) {
		echo styleRenderBoxBottom();
	}
	echo '<br />';
	if (function_exists('styleRenderBoxTop')) {
		echo styleRenderBoxTop();
	}
	echo '<table cellspacing="0" cellpadding="4" border="0" width="100%" class="std">
	<tr>
		<td>';

	$total = 0;

	$q = new w2p_Database_Query;
	if ($fullaccess) {
		$q->addTable('companies');
		$q->addQuery('company_id');
	} else {
		$q->addTable('companies');
		$q->addQuery('company_id');
		$q->addWhere('company_owner = ' . (int)$AppUI->user_id);
	}

	$companies = $q->loadColumn();
	$q->clear();

	if (!empty($companies)) {
		foreach ($companies as $company) {
			$total += showcompany($company);
		}
	} else {
		$q->addTable('companies');
		$q->addQuery('company_id');
		foreach ($q->loadColumn() as $company) {
			$total += showcompany($company, true);
		}
	}

	echo '<h2>' . $AppUI->_('Total Hours') . ': ';
	printf("%.2f", $total);
	echo '</h2>';
	$pdfdata[] = array($AppUI->_('Total Hours'), round($total, 2));

	if ($log_pdf) {
		// make the PDF file

		$font_dir = W2P_BASE_DIR . '/lib/ezpdf/fonts';
		$temp_dir = W2P_BASE_DIR . '/files/temp';

		require ($AppUI->getLibraryClass('ezpdf/class.ezpdf'));

		$pdf = new Cezpdf();
		$pdf->ezSetCmMargins(1, 2, 1.5, 1.5);
		$pdf->selectFont($font_dir . '/Helvetica.afm');

		$pdf->ezText(w2PgetConfig('company_name'), 12);

		if ($log_all) {
			$date = new w2p_Utilities_Date();
			$pdf->ezText("\nAll hours as of " . $date->format($df), 8);
		} else {
			$sdate = new w2p_Utilities_Date($log_start_date);
			$edate = new w2p_Utilities_Date($log_end_date);
			$pdf->ezText("\nHours from " . $sdate->format($df) . ' to ' . $edate->format($df), 8);
		}

		$pdf->selectFont($font_dir . '/Helvetica-Bold.afm');
		$pdf->ezText("\n" . $AppUI->_('Overall Report'), 12);

		foreach ($allpdfdata as $company => $data) {
			$title = $company;
			$options = array('showLines' => 1, 'showHeadings' => 0, 'fontSize' => 8, 'rowGap' => 2, 'colGap' => 5, 'xPos' => 50, 'xOrientation' => 'right', 'width' => '500', 'cols' => array(0 => array('justification' => 'left', 'width' => 250), 1 => array('justification' => 'right', 'width' => 120)));

			$pdf->ezTable($data, null, $title, $options);
		}

		$w2pReport = new CReport();
		if ($fp = fopen($temp_dir . '/'.$w2pReport->getFilename().'.pdf', 'wb')) {
			fwrite($fp, $pdf->ezOutput());
			fclose($fp);
			echo '<a href="' . W2P_BASE_URL . '/files/temp/' . $w2pReport->getFilename() . '.pdf" target="pdf">';
			echo $AppUI->_('View PDF File');
			echo '</a>';
		} else {
			echo 'Could not open file to save PDF.  ';
			if (!is_writable($temp_dir)) {
				'The files/temp directory is not writable.  Check your file system permissions.';
			}
		}
	}
	echo '</td>
</tr>
</table>';
}