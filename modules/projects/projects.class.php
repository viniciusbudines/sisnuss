<?php /* $Id: projects.class.php 2024 2011-08-08 04:38:24Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version2.4/modules/projects/projects.class.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

/**
 *	@package web2Project
 *	@subpackage modules
 *	@version $Revision: 2024 $
 */

// project statii
$pstatus = w2PgetSysVal('ProjectStatus');
$ptype = w2PgetSysVal('ProjectType');

$ppriority_name = w2PgetSysVal('ProjectPriority');
$ppriority_color = w2PgetSysVal('ProjectPriorityColor');

$priority = array();
foreach ($ppriority_name as $key => $val) {
	$priority[$key]['name'] = $val;
}
foreach ($ppriority_color as $key => $val) {
	$priority[$key]['color'] = $val;
}

/*
// kept for reference
$priority = array(
-1 => array(
'name' => 'low',
'color' => '#E5F7FF'
),
0 => array(
'name' => 'normal',
'color' => ''//#CCFFCA
),
1 => array(
'name' => 'high',
'color' => '#FFDCB3'
),
2 => array(
'name' => 'immediate',
'color' => '#FF887C'
)
);
*/

/**
 * The Project Class
 */
class CProject extends w2p_Core_BaseObject {
    public $project_id = null;
    public $project_company = null;
    public $project_name = null;
    public $project_short_name = null;
    public $project_owner = null;
    public $project_url = null;
    public $project_demo_url = null;
    public $project_start_date = null;
    public $project_end_date = null;
    public $project_actual_end_date = null;
    public $project_status = null;
    public $project_percent_complete = null;
    public $project_color_identifier = null;
    public $project_description = null;
    public $project_target_budget = 0;
    public $project_actual_budget = 0;
    public $project_scheduled_hours = null;
    public $project_worked_hours = null;
    public $project_task_count = null;
    public $project_creator = null;
    public $project_active = null;
    public $project_private = null;
    public $project_priority = null;
    public $project_type = null;
    public $project_parent = null;
    public $project_location = '';
	public $project_last_task = 0;

	public $project_original_parent = null;
	/*
	 * @deprecated fields, kept to make sure the bind() works properly
	 */
    public $project_departments = null;
    public $project_contacts = null;

    public function __construct() {
        parent::__construct('projects', 'project_id');
    }

    public function bind($hash, $prefix = null, $checkSlashes = true, $bindAll = false) {
        $result = parent::bind($hash, $prefix, $checkSlashes, $bindAll);
        $this->project_contacts = is_array($this->project_contacts) ? $this->project_contacts : explode(',', $this->project_contacts);

        return $result;
    }

	public function check() {
        $errorArray = array();
        $baseErrorMsg = get_class($this) . '::store-check failed - ';

        if ('' == $this->project_name) {
            $errorArray['project_name'] = $baseErrorMsg . 'project name is not set';
        }
        if ('' == $this->project_short_name) {
            $errorArray['project_short_name'] = $baseErrorMsg . 'project short name is not set';
        }
        if (0 == (int) $this->project_company) {
            $errorArray['project_company'] = $baseErrorMsg . 'project company is not set';
        }
        if (0 == (int) $this->project_owner) {
            $errorArray['project_owner'] = $baseErrorMsg . 'project owner is not set';
        }
        if (0 == (int) $this->project_creator) {
            $errorArray['project_creator'] = $baseErrorMsg . 'project creator is not set';
        }
        if (!is_int($this->project_priority) && '' == $this->project_priority) {
            $errorArray['project_priority'] = $baseErrorMsg . 'project priority is not set';
        }
        if ('' == $this->project_color_identifier) {
            $errorArray['project_color_identifier'] = $baseErrorMsg . 'project color identifier is not set';
        }
        if (!is_int($this->project_type) && '' == $this->project_type) {
            $errorArray['project_type'] = $baseErrorMsg . 'project type is not set';
        }
        if (!is_int($this->project_status) && '' == $this->project_status) {
            $errorArray['project_status'] = $baseErrorMsg . 'project status is not set';
        }
        if ('' != $this->project_url && !w2p_check_url($this->project_url)) {
            $errorArray['project_url'] = $baseErrorMsg . 'project url is not formatted properly';
        }
        if ('' != $this->project_demo_url && !w2p_check_url($this->project_demo_url)) {
            $errorArray['project_demo_url'] = $baseErrorMsg . 'project demo url is not formatted properly';
        }

        $this->_error = $errorArray;
        return $errorArray;
	}

	public function loadFull(CAppUI $AppUI, $projectId) {

        $q = $this->_query;
		$q->addTable('projects');
		$q->addQuery('company_name, CONCAT_WS(\' \',contact_first_name,contact_last_name) user_name, projects.*');
		$q->addJoin('companies', 'com', 'company_id = project_company', 'inner');
		$q->leftJoin('users', 'u', 'user_id = project_owner');
		$q->leftJoin('contacts', 'con', 'contact_id = user_contact');
		$q->addWhere('project_id = ' . (int) $projectId);
		$q->addGroup('project_id');

		$this->company_name = '';
		$this->user_name = '';
		$q->loadObject($this);
	}

	public function delete(CAppUI $AppUI = null) {
        global $AppUI;

        $perms = $AppUI->acl();
        $result = false;
        $this->_error = array();

        /*
         * TODO: This should probably use the canDelete method from above too to
         *   not only check permissions but to check dependencies... luckily the
         *   previous version didn't check it either, so we're no worse off.
         */
        if ($perms->checkModuleItem('projects', 'delete', $this->project_id)) {
            $q = $this->_query;
            $q->addTable('tasks');
            $q->addQuery('task_id');
            $q->addWhere('task_project = ' . (int)$this->project_id);
            $tasks_to_delete = $q->loadColumn();
            $q->clear();

            foreach ($tasks_to_delete as $task_id) {
                $q->setDelete('user_tasks');
                $q->addWhere('task_id =' . $task_id);
                $q->exec();
                $q->clear();

                $q->setDelete('task_dependencies');
                $q->addWhere('dependencies_req_task_id =' . (int)$task_id);
                $q->exec();
                $q->clear();
            }
            $q->setDelete('tasks');
            $q->addWhere('task_project =' . (int)$this->project_id);
            $q->exec();
            $q->clear();

            $q->addTable('files');
            $q->addQuery('file_id');
            $q->addWhere('file_project = ' . (int)$this->project_id);
            $files_to_delete = $q->loadColumn();
            $q->clear();

            foreach ($files_to_delete as $file_id) {
                $file = new CFile();
                $file->load($file_id);
                $file->delete($AppUI);
            }
            $q->setDelete('events');
            $q->addWhere('event_project =' . (int)$this->project_id);
            $q->exec();
            $q->clear();

            // remove the project-contacts and project-departments map
            $q->setDelete('project_contacts');
            $q->addWhere('project_id =' . (int)$this->project_id);
            $q->exec();
            $q->clear();

            $q->setDelete('project_departments');
            $q->addWhere('project_id =' . (int)$this->project_id);
            $q->exec();
            $q->clear();

            $q->setDelete('tasks');
            $q->addWhere('task_represents_project =' . (int)$this->project_id);
            $q->clear();

            if ($msg = parent::delete()) {
                return $msg;
            }
            return true;
        }
		return $result;
	}

	/**	Import tasks from another project
	 *
	 *	@param	int		Project ID of the tasks come from.
	 *	@return	bool
	 **/
	public function importTasks($from_project_id) {
        global $AppUI;

        $errors = array();

		// Load the original
		$origProject = new CProject();
		$origProject->load($from_project_id);
		$q = $this->_query;
		$q->addTable('tasks');
		$q->addQuery('task_id');
		$q->addWhere('task_project =' . (int)$from_project_id);
		$tasks = array_flip($q->loadColumn());
		$q->clear();

		$origDate = new w2p_Utilities_Date($origProject->project_start_date);

		$destDate = new w2p_Utilities_Date($this->project_start_date);

		$timeOffset = $origDate->dateDiff($destDate);
		if ($origDate->compare($origDate, $destDate) > 0) {
			$timeOffset = -1 * $timeOffset;
		}

		// Dependencies array
		$deps = array();

		// Copy each task into this project and get their deps
		foreach ($tasks as $orig => $void) {
			$objTask = new CTask();
            $objTask->load($orig);
            $destTask = $objTask->copy($this->project_id);
			$tasks[$orig] = $destTask;
			$deps[$orig] = $objTask->getDependencies();
		}

		// Fix record integrity
		foreach ($tasks as $old_id => $newTask) {

			// Fix parent Task
			// This task had a parent task, adjust it to new parent task_id
			if ($newTask->task_id != $newTask->task_parent) {
				$newTask->task_parent = $tasks[$newTask->task_parent]->task_id;
			}

			// Fix task start date from project start date offset
			$origDate->setDate($newTask->task_start_date);
			$origDate->addDays($timeOffset);
			$destDate = $origDate;
			$newTask->task_start_date = $destDate->format(FMT_DATETIME_MYSQL);

			// Fix task end date from start date + work duration
			if (!empty($newTask->task_end_date) && $newTask->task_end_date != '0000-00-00 00:00:00') {
				$origDate->setDate($newTask->task_end_date);
				$origDate->addDays($timeOffset);
				$destDate = $origDate;
				$newTask->task_end_date = $destDate->format(FMT_DATETIME_MYSQL);
			}

			// Dependencies
			if (!empty($deps[$old_id])) {
				$oldDeps = explode(',', $deps[$old_id]);
				// New dependencies array
				$newDeps = array();
				foreach ($oldDeps as $dep) {
					$newDeps[] = $tasks[$dep]->task_id;
				}

				// Update the new task dependencies
				$csList = implode(',', $newDeps);
				$newTask->updateDependencies($csList);
			} // end of update dependencies
            $result = $newTask->store($AppUI);
			$newTask->addReminder();
            $importedTasks[] = $newTask->task_id;

            if (is_array($result) && count($result)) {
                foreach ($result as $key => $error_msg) {
                    $errors[] = $newTask->task_name . ': ' . $error_msg;
                }
            }
        } // end Fix record integrity

        // We have errors, so rollback everything we've done so far
        if (count($errors)) {
            foreach($importedTasks as $badTask) {
                $delTask = new CTask();
                $delTask->task_id = $badTask;
                $delTask->delete($AppUI);
            }
        }
        return $errors;
    } // end of importTasks

	/**
	 **	Overload of the w2PObject::getAllowedRecords
	 **	to ensure that the allowed projects are owned by allowed companies.
	 **
	 **	@author	handco <handco@sourceforge.net>
	 **	@see	w2PObject::getAllowedRecords
	 **/

	public function getAllowedRecords($uid, $fields = '*', $orderby = '', $index = null, $extra = null, $table_alias = '') {
		$oCpy = new CCompany();

		$aCpies = $oCpy->getAllowedRecords($uid, 'company_id, company_name');
		if (count($aCpies)) {
			$buffer = '(project_company IN (' . implode(',', array_keys($aCpies)) . '))';

			if (!isset($extra['from']) && !isset($extra['join'])) {
				$extra['join'] = 'project_departments';
				$extra['on'] = 'projects.project_id = project_departments.project_id';
			} elseif ($extra['from'] != 'project_departments' && !isset($extra['join'])) {
				$extra['join'] = 'project_departments';
				$extra['on'] = 'projects.project_id = project_departments.project_id';
			}
			//Department permissions
			$oDpt = new CDepartment();
			$aDpts = $oDpt->getAllowedRecords($uid, 'dept_id, dept_name');
			if (count($aDpts)) {
				$dpt_buffer = '(department_id IN (' . implode(',', array_keys($aDpts)) . ') OR department_id IS NULL)';
			} else {
				// There are no allowed departments, so allow projects with no department.
				$dpt_buffer = '(department_id IS NULL)';
			}

			if (isset($extra['where']) && $extra['where'] != '') {
				$extra['where'] = $extra['where'] . ' AND ' . $buffer . ' AND ' . $dpt_buffer;
			} else {
				$extra['where'] = $buffer . ' AND ' . $dpt_buffer;
			}
		} else {
			// There are no allowed companies, so don't allow projects.
			if ($extra['where'] != '') {
				$extra['where'] = $extra['where'] . ' AND 1 = 0 ';
			} else {
				$extra['where'] = '1 = 0';
			}
		}
		return parent::getAllowedRecords($uid, $fields, $orderby, $index, $extra, $table_alias);

	}

	public function getAllowedSQL($uid, $index = null) {
		$oCpy = new CCompany();
		$where = $oCpy->getAllowedSQL($uid, 'project_company');

		$oDpt = new CDepartment();
		$where += $oDpt->getAllowedSQL($uid, 'dept_id');

		$project_where = parent::getAllowedSQL($uid, $index);
		return array_merge($where, $project_where);
	}

	public function setAllowedSQL($uid, &$query, $index = null, $key = 'pr') {
		$oCpy = new CCompany;
		parent::setAllowedSQL($uid, $query, $index, $key);
		$oCpy->setAllowedSQL($uid, $query, ($key ? $key . '.' : '').'project_company');
		//Department permissions
		$oDpt = new CDepartment();
		$query->leftJoin('project_departments', '', $key.'.project_id = project_departments.project_id');
		$oDpt->setAllowedSQL($uid, $query, 'project_departments.department_id');
	}

	/**
	 *	Overload of the w2PObject::getDeniedRecords
	 *	to ensure that the projects owned by denied companies are denied.
	 *
	 *	@author	handco <handco@sourceforge.net>
	 *	@see	w2PObject::getAllowedRecords
	 */
	public function getDeniedRecords($uid) {
		$aBuf1 = parent::getDeniedRecords($uid);

		$oCpy = new CCompany();
		// Retrieve which projects are allowed due to the company rules
		$aCpiesAllowed = $oCpy->getAllowedRecords($uid, 'company_id,company_name');

		//Department permissions
		$oDpt = new CDepartment();
		$aDptsAllowed = $oDpt->getAllowedRecords($uid, 'dept_id,dept_name');

		$q = $this->_query;
		$q->addTable('projects');
		$q->addQuery('projects.project_id');
		$q->addJoin('project_departments', 'pd', 'pd.project_id = projects.project_id');

		if (count($aCpiesAllowed)) {
			if ((array_search('0', $aCpiesAllowed)) === false) {
				//If 0 (All Items of a module) are not permited then just add the allowed items only
				$q->addWhere('NOT (project_company IN (' . implode(',', array_keys($aCpiesAllowed)) . '))');
			} else {
				//If 0 (All Items of a module) are permited then don't add a where clause so the user is permitted to see all
			}
		} else {
			//if the user is not allowed any company then lets shut him off
			$q->addWhere('0=1');
		}

		if (count($aDptsAllowed)) {
			if ((array_search('0', $aDptsAllowed)) === false) {
				//If 0 (All Items of a module) are not permited then just add the allowed items only
				$q->addWhere('NOT (department_id IN (' . implode(',', array_keys($aDptsAllowed)) . '))');
			} else {
				//If 0 (All Items of a module) are permited then don't add a where clause so the user is permitted to see all
				$q->addWhere('NOT (department_id IS NULL)');
			}
		} else {
			//If 0 (All Items of a module) are permited then don't add a where clause so the user is permitted to see all
			$q->addWhere('NOT (department_id IS NULL)');
		}

		$aBuf2 = $q->loadColumn();
		$q->clear();

		return array_merge($aBuf1, $aBuf2);

	}

	public function getAllowedProjectsInRows($userId) {

        $q = $this->_query;
		$q->addQuery('pr.project_id, project_status, project_name, project_description, project_short_name');
		$q->addTable('projects', 'pr');
		$q->addOrder('project_short_name');
		$this->setAllowedSQL($userId, $q, null, 'pr');
		$allowedProjectRows = $q->exec();

		return $allowedProjectRows;
	}

	/** Retrieve tasks with latest task_end_dates within given project
	 * @param int Project_id
	 * @param int SQL-limit to limit the number of returned tasks
	 * @return array List of criticalTasks
	 */
	public function getCriticalTasks($project_id = null, $limit = 1) {
		$project_id = !empty($project_id) ? $project_id : $this->project_id;

        $q = $this->_query;
		$q->addTable('tasks');
		$q->addWhere('task_project = ' . (int)$project_id . ' AND task_end_date IS NOT NULL AND task_end_date <>  \'0000-00-00 00:00:00\'');
		$q->addOrder('task_end_date DESC');
		$q->setLimit($limit);

		return $q->loadList();
	}

	public function store(CAppUI $AppUI = null) {
        global $AppUI;

        $perms = $AppUI->acl();
        $stored = false;

        $this->w2PTrimAll();

        // ensure changes of state in checkboxes is captured
        $this->project_active = (int) $this->project_active;
        $this->project_private = (int) $this->project_private;

        $this->project_target_budget = filterCurrency($this->project_target_budget);
        $this->project_actual_budget = filterCurrency($this->project_actual_budget);

        // Make sure project_short_name is the right size (issue for languages with encoded characters)
        $this->project_short_name = mb_substr($this->project_short_name, 0, 10);
        if (empty($this->project_end_date)) {
            $this->project_end_date = null;
        }

        $this->_error = $this->check();

        if (count($this->_error)) {
            return $this->_error;
        }

        $this->project_id = (int) $this->project_id;
        // convert dates to SQL format first
        if ($this->project_start_date) {
            $date = new w2p_Utilities_Date($this->project_start_date);
            $this->project_start_date = $date->format(FMT_DATETIME_MYSQL);
        }
        if ($this->project_end_date) {
            $date = new w2p_Utilities_Date($this->project_end_date);
            $date->setTime(23, 59, 59);
            $this->project_end_date = $date->format(FMT_DATETIME_MYSQL);
        }
        if ($this->project_actual_end_date) {
            $date = new w2p_Utilities_Date($this->project_actual_end_date);
            $this->project_actual_end_date = $date->format(FMT_DATETIME_MYSQL);
        }

        // check project parents and reset them to self if they do not exist
        if (!$this->project_parent) {
            $this->project_parent = $this->project_id;
            $this->project_original_parent = $this->project_id;
        } else {
            $parent_project = new CProject();
            $parent_project->load($this->project_parent);
            $this->project_original_parent = $parent_project->project_original_parent;
        }
        if (!$this->project_original_parent) {
            $this->project_original_parent = $this->project_id;
        }

        /*
         * TODO: I don't like the duplication on each of these two branches, but I
         *   don't have a good idea on how to fix it at the moment...
         */
        $q = $this->_query;
        $this->project_updated = $q->dbfnNowWithTZ();
        if ($this->project_id && $perms->checkModuleItem('projects', 'edit', $this->project_id)) {
            if (($msg = parent::store())) {
                return $msg;
            }
            $stored = true;
        }
        if (0 == $this->project_id && $perms->checkModuleItem('projects', 'add')) {
            $this->project_created = $q->dbfnNowWithTZ();
            if (($msg = parent::store())) {
                return $msg;
            }
            if (0 == $this->project_parent || 0 == $this->project_original_parent) {
                $this->project_parent = $this->project_id;
                $this->project_original_parent = $this->project_id;
                if (($msg = parent::store())) {
                    return $msg;
                }
            }
            $stored = true;
        }

		//split out related departments and store them seperatly.
		$q->setDelete('project_departments');
		$q->addWhere('project_id=' . (int)$this->project_id);
		$q->exec();
		$q->clear();
		if ($this->project_departments) {
			foreach ($this->project_departments as $department) {
				if ($department) {
                    $q->addTable('project_departments');
                    $q->addInsert('project_id', $this->project_id);
                    $q->addInsert('department_id', $department);
                    $q->exec();
                    $q->clear();
                }
			}
		}

		//split out related contacts and store them seperatly.
		$q->setDelete('project_contacts');
		$q->addWhere('project_id=' . (int)$this->project_id);
		$q->exec();
		$q->clear();
		if ($this->project_contacts) {
			foreach ($this->project_contacts as $contact) {
				if ($contact) {
					$q->addTable('project_contacts');
					$q->addInsert('project_id', $this->project_id);
					$q->addInsert('contact_id', $contact);
					$q->exec();
					$q->clear();
				}
			}
		}

        if ($stored) {
            $custom_fields = new w2p_Core_CustomFields('projects', 'addedit', $this->project_id, 'edit');
            $custom_fields->bind($_POST);
            $sql = $custom_fields->store($this->project_id); // Store Custom Fields

            CTask::storeTokenTask($AppUI, $this->project_id);
        }
		return $stored;
	}

	public function notifyOwner($isNotNew) {
		global $AppUI, $w2Pconfig, $locale_char_set;

		$mail = new w2p_Utilities_Mail;

		if (intval($isNotNew)) {
			$mail->Subject("Project Updated: $this->project_name ", $locale_char_set);
		} else {
			$mail->Subject("Project Submitted: $this->project_name ", $locale_char_set);
		}

		$user = new CUser();
		$user->loadFull($this->project_owner);

		if ($user && $mail->ValidEmail($user->user_email)) {
			if (intval($isNotNew)) {
				$body = $AppUI->_('Project') . ": $this->project_name Has Been Updated Via Project Manager. You can view the Project by clicking: ";
			} else {
				$body = $AppUI->_('Project') . ": $this->project_name Has Been Submitted Via Project Manager. You can view the Project by clicking: ";
			}
			$body .= "\n" . $AppUI->_('URL') . ':     ' . w2PgetConfig('base_url') . '/index.php?m=projects&a=view&project_id=' . $this->project_id;
			$body .= "\n\n(You are receiving this email because you are the owner to this project)";
			$body .= "\n\n" . $AppUI->_('Description') . ':' . "\n$this->project_description";
			if (intval($isNotNew)) {
				$body .= "\n\n" . $AppUI->_('Updater') . ': ' . $AppUI->user_first_name . ' ' . $AppUI->user_last_name;
			} else {
				$body .= "\n\n" . $AppUI->_('Creator') . ': ' . $AppUI->user_first_name . ' ' . $AppUI->user_last_name;
			}

			if ($this->_message == 'deleted') {
				$body .= "\n\nProject " . $this->project_name . ' was ' . $this->_message . ' by ' . $AppUI->user_first_name . ' ' . $AppUI->user_last_name;
			}

			$mail->Body($body, isset($GLOBALS['locale_char_set']) ? $GLOBALS['locale_char_set'] : '');
			$mail->To($user->user_email, true);
			$mail->Send();
		}
	}

	public function notifyContacts($isNotNew) {
		global $AppUI, $w2Pconfig, $locale_char_set;

		$subject = (intval($isNotNew)) ? "Project Updated: $this->project_name " : "Project Submitted: $this->project_name ";

		$users = CProject::getContacts($AppUI, $this->project_id);

		if (count($users)) {
			if (intval($isNotNew)) {
				$body = $AppUI->_('Project') . ": $this->project_name Has Been Updated Via Project Manager. You can view the Project by clicking: ";
			} else {
				$body = $AppUI->_('Project') . ": $this->project_name Has Been Submitted Via Project Manager. You can view the Project by clicking: ";
			}
			$body .= "\n" . $AppUI->_('URL') . ':     ' . w2PgetConfig('base_url') . '/index.php?m=projects&a=view&project_id=' . $this->project_id;
			$body .= "\n\n(You are receiving this message because you are a contact or assignee for this Project)";
			$body .= "\n\n" . $AppUI->_('Description') . ':' . "\n$this->project_description";
			if (intval($isNotNew)) {
				$body .= "\n\n" . $AppUI->_('Updater') . ': ' . $AppUI->user_first_name . ' ' . $AppUI->user_last_name;
			} else {
				$body .= "\n\n" . $AppUI->_('Creator') . ': ' . $AppUI->user_first_name . ' ' . $AppUI->user_last_name;
			}

			if ($this->_message == 'deleted') {
				$body .= "\n\nProject " . $this->project_name . ' was ' . $this->_message . ' by ' . $AppUI->user_first_name . ' ' . $AppUI->user_last_name;
			}

			foreach ($users as $row) {
				$mail = new w2p_Utilities_Mail;
				$mail->Body($body, isset($GLOBALS['locale_char_set']) ? $GLOBALS['locale_char_set'] : '');
				$mail->Subject($subject, $locale_char_set);

				if ($mail->ValidEmail($row['contact_email'])) {
					$mail->To($row['contact_email'], true);
					$mail->Send();
				}
			}
		}
		return '';
	}
	public function getAllowedProjects($userId, $activeOnly = true) {

        $q = $this->_query;
		$q->addTable('projects', 'pr');
		$q->addQuery('pr.project_id, project_color_identifier, project_name, project_start_date, project_end_date, project_company, project_parent');
		if ($activeOnly) {
			$q->addWhere('project_active = 1');
		}
		$q->addGroup('pr.project_id');
		$q->addOrder('project_name');
		$this->setAllowedSQL($userId, $q, null, 'pr');

		return $q->loadHashList('project_id');
	}

	public static function getContacts(CAppUI $AppUI = null, $projectId) {
        global $AppUI;

        $perms = $AppUI->acl();

		if ($AppUI->isActiveModule('contacts') && canView('contacts')) {
            $q = new w2p_Database_Query();
            $q->addTable('contacts', 'c');
            $q->addQuery('c.contact_id, contact_first_name, contact_last_name');
            $q->addQuery('contact_order_by, contact_email, contact_phone');

            $q->leftJoin('departments', 'd', 'd.dept_id = c.contact_department');
            $q->addQuery('dept_name');

			$q->addJoin('project_contacts', 'pc', 'pc.contact_id = c.contact_id', 'inner');
			$q->addWhere('pc.project_id = ' . (int) $projectId);

			$q->addWhere('
				(contact_private=0
					OR (contact_private=1 AND contact_owner=' . $AppUI->user_id . ')
					OR contact_owner IS NULL OR contact_owner = 0
				)');

			$department = new CDepartment;
			$department->setAllowedSQL($AppUI->user_id, $q);

			return $q->loadHashList('contact_id');
		}
	}
	public static function getDepartments(CAppUI $AppUI = null, $projectId) {
		global $AppUI;

        $perms = $AppUI->acl();
		if ($AppUI->isActiveModule('departments') && canView('departments')) {
			$q = new w2p_Database_Query();
			$q->addTable('departments', 'a');
			$q->addTable('project_departments', 'b');
			$q->addQuery('a.dept_id, a.dept_name, a.dept_phone');
			$q->addWhere('a.dept_id = b.department_id and b.project_id = ' . (int) $projectId);

			$department = new CDepartment;
			$department->setAllowedSQL($AppUI->user_id, $q);

			return $q->loadHashList('dept_id');
		}
	}
	public static function getForums(CAppUI $AppUI = null, $projectId) {
		global $AppUI;

		if ($AppUI->isActiveModule('forums') && canView('forums')) {
			$q = new w2p_Database_Query();
			$q->addTable('forums');
			$q->addQuery('forum_id, forum_project, forum_description, forum_owner, forum_name, forum_message_count,
				DATE_FORMAT(forum_last_date, "%d-%b-%Y %H:%i" ) forum_last_date,
				project_name, project_color_identifier, project_id');
			$q->addJoin('projects', 'p', 'project_id = forum_project', 'inner');
			$q->addWhere('forum_project = ' . (int) $projectId);
			$q->addOrder('forum_project, forum_name');

			return $q->loadHashList('forum_id');
		}
	}
	public static function getCompany($projectId) {

        $q = new w2p_Database_Query();
		$q->addQuery('project_company');
		$q->addTable('projects');
		$q->addWhere('project_id = ' . (int) $projectId);

		return $q->loadResult();
	}
	public static function getBillingCodes($companyId, $all = false) {

        $q = new w2p_Database_Query();
		$q->addTable('billingcode');
		$q->addQuery('billingcode_id, billingcode_name');
		$q->addOrder('billingcode_name');
		$q->addWhere('billingcode_status = 0');
		$q->addWhere('(company_id = 0 OR company_id = ' . (int) $companyId . ')');
		$task_log_costcodes = $q->loadHashList();

		if ($all) {
			$q->clear();
			$q->addTable('billingcode');
			$q->addQuery('billingcode_id, billingcode_name');
			$q->addOrder('billingcode_name');
			$q->addWhere('billingcode_status = 1');
			$q->addWhere('(company_id = 0 OR company_id = ' . (int) $companyId . ')');

			$billingCodeList = $q->loadHashList();
			foreach($billingCodeList as $id => $code) {
				$task_log_costcodes[$id] = $code;
			}
		}

		return $task_log_costcodes;
	}
	public static function getOwners() {

        $q = new w2p_Database_Query();
		$q->addTable('projects', 'p');
		$q->addQuery('user_id, contact_display_name');
		$q->leftJoin('users', 'u', 'u.user_id = p.project_owner');
		$q->leftJoin('contacts', 'c', 'c.contact_id = u.user_contact');
		$q->addOrder('contact_first_name, contact_last_name');
		$q->addWhere('user_id > 0');
		$q->addWhere('p.project_owner IS NOT NULL');

		return $q->loadHashList();
	}

	public static function updateStatus(CAppUI $AppUI = null, $projectId, $statusId) {
		global $AppUI;
		trigger_error("CProject::updateStatus has been deprecated in v2.3 and will be removed by v4.0.", E_USER_NOTICE );
        $perms = $AppUI->acl();
		if ($perms->checkModuleItem('projects', 'edit', $projectId) && $projectId > 0 && $statusId >= 0) {
			$q = new w2p_Database_Query();
			$q->addTable('projects');
			$q->addUpdate('project_status', $statusId);
			$q->addWhere('project_id   = ' . (int) $projectId);
			$q->exec();
		}
	}

	public static function updateTaskCache($project_id, $task_id,
			$project_actual_end_date, $project_task_count) {

		if ($project_id && $task_id) {
			$q = new w2p_Database_Query();
			$q->addTable('projects');
			$q->addUpdate('project_last_task',			$task_id);
			$q->addUpdate('project_actual_end_date',	$project_actual_end_date);
			$q->addUpdate('project_task_count',			$project_task_count);
			$q->addWhere('project_id   = ' . (int) $project_id);
			$q->exec();
			self::updatePercentComplete($project_id);
		}
	}

	public static function updateTaskCount($projectId, $taskCount) {

		trigger_error("CProject::updateTaskCount has been deprecated in v2.3 and will be removed by v4.0. Please use CProject::updateTaskCache instead.", E_USER_NOTICE );

		if (intval($projectId) > 0 && intval($taskCount)) {
			$q = new w2p_Database_Query();
			$q->addTable('projects');
			$q->addUpdate('project_task_count', intval($taskCount));
			$q->addWhere('project_id   = ' . (int) $projectId);
			$q->exec();
			self::updatePercentComplete($projectId);
		}
	}

	public function hasChildProjects($projectId = 0) {
		// Note that this returns the *count* of projects.  If this is zero, it
		//   is evaluated as false, otherwise it is considered true.
		$q = $this->_query;
		$q->addTable('projects');
		$q->addQuery('COUNT(project_id)');
		if ($projectId > 0) {
			$q->addWhere('project_original_parent = ' . $projectId);
		} else {
			$q->addWhere('project_original_parent = ' . (int)($this->project_original_parent ? $this->project_original_parent : $this->project_id));
		}

		// I hate how this one works... since the default project parent is
		//   itself, so this will always have at least one result.
		return ($q->loadResult()-1);
	}

	public static function hasTasks($projectId) {
        // Note that this returns the *count* of tasks.  If this is zero, it is
        //   evaluated as false, otherwise it is considered true.

        $q = new w2p_Database_Query();
        $q->addTable('tasks');
        $q->addQuery('COUNT(distinct tasks.task_id) AS total_tasks');
        $q->addWhere('task_project = ' . (int) $projectId);

        return $q->loadResult();
	}
	public static function updateHoursWorked($project_id) {

        $q = new w2p_Database_Query();
        $q->addTable('task_log');
        $q->addTable('tasks');
        $q->addQuery('ROUND(SUM(task_log_hours),2)');
        $q->addWhere('task_log_task = task_id AND task_project = ' . (int) $project_id);
        $worked_hours = 0 + $q->loadResult();
        $worked_hours = rtrim($worked_hours, '.');
        $q->clear();

        $q->addTable('projects');
        $q->addUpdate('project_worked_hours', $worked_hours);
        $q->addWhere('project_id  = ' . (int) $project_id);
        $q->exec();
        self::updatePercentComplete($project_id);
	}

    public static function updatePercentComplete($project_id) {
        $working_hours = (w2PgetConfig('daily_working_hours') ? w2PgetConfig('daily_working_hours') : 8);

        $q = new w2p_Database_Query();
        $q->addTable('projects');
        $q->addQuery('SUM(t1.task_duration * t1.task_percent_complete * IF(t1.task_duration_type = 24, ' . $working_hours . ', t1.task_duration_type)) / SUM(t1.task_duration * IF(t1.task_duration_type = 24, ' . $working_hours . ', t1.task_duration_type)) AS project_percent_complete');
        $q->addJoin('tasks', 't1', 'projects.project_id = t1.task_project', 'inner');
        $q->addWhere('project_id = ' . $project_id . ' AND t1.task_id = t1.task_parent');
        $project_percent_complete = $q->loadResult();
        $q->clear();

        $q->addTable('projects');
        $q->addUpdate('project_percent_complete', $project_percent_complete);
        $q->addWhere('project_id  = ' . (int) $project_id);
        $q->exec();

        global $AppUI;
        CTask::storeTokenTask($AppUI, $project_id);
    }
	public function getTotalHours() {
        trigger_error("CProject->getTotalHours() has been deprecated in v2.0 and will be removed in v3.0", E_USER_NOTICE );

		return $this->getTotalProjectHours();
	}
	public function getTotalProjectHours() {
		global $w2Pconfig;

		// now milestones are summed up, too, for consistence with the tasks duration sum
		// the sums have to be rounded to prevent the sum form having many (unwanted) decimals because of the mysql floating point issue
		// more info on http://www.mysql.com/doc/en/Problems_with_float.html
		$q = $this->_query;
		$q->addTable('tasks');
		$q->addQuery('ROUND(SUM(task_duration),2)');
		$q->addWhere('task_project = ' . (int) $this->project_id . ' AND task_duration_type = 24 AND task_dynamic <> 1');
		$days = $q->loadResult();
		$q->clear();

		$q->addTable('tasks');
		$q->addQuery('ROUND(SUM(task_duration),2)');
		$q->addWhere('task_project = ' . (int) $this->project_id . ' AND task_duration_type = 1 AND task_dynamic <> 1');
		$hours = $q->loadResult();

		$total_project_hours = $days * $w2Pconfig['daily_working_hours'] + $hours;

		return rtrim($total_project_hours, '.');
	}
	public function getTaskLogs(CAppUI $AppUI = null, $projectId, $user_id = 0, $hide_inactive = false, $hide_complete = false, $cost_code = 0) {
        global $AppUI;

		$q = $this->_query;
		$q->addTable('task_log');
		$q->addQuery('DISTINCT task_log.*, user_username, task_id');
		$q->addQuery("CONCAT(contact_first_name, ' ', contact_last_name) AS real_name");
		$q->addQuery('billingcode_name as task_log_costcode');
		$q->addJoin('users', 'u', 'user_id = task_log_creator');
		$q->addJoin('tasks', 't', 'task_log_task = t.task_id');
		$q->addJoin('contacts', 'ct', 'contact_id = user_contact');
		$q->addJoin('billingcode', 'b', 'task_log.task_log_costcode = billingcode_id');

		$q->addWhere('task_project = ' . (int) $projectId);
		if ($user_id > 0) {
			$q->addWhere('task_log_creator=' . $user_id);
		}
		if ($hide_inactive) {
			$q->addWhere('task_status>=0');
		}
		if ($hide_complete) {
			$q->addWhere('task_percent_complete < 100');
		}
		if ($cost_code > 0) {
			$q->addWhere("billingcode_id = $cost_code");
		}
		$q->addOrder('task_log_date');
		$q->addOrder('task_log_created');
		$this->setAllowedSQL($AppUI->user_id, $q, 'task_project');

		return $q->loadList();
	}

    public function hook_search() {
        $search['table'] = 'projects';
        $search['table_alias'] = 'p';
        $search['table_module'] = 'projects';
        $search['table_key'] = 'p.project_id'; // primary key in searched table
        $search['table_link'] = 'index.php?m=projects&a=view&project_id='; // first part of link
        $search['table_title'] = 'Projects';
        $search['table_orderby'] = 'project_name';
        $search['search_fields'] = array('p.project_id', 'p.project_name',
            'p.project_short_name', 'p.project_location', 'p.project_description',
            'p.project_url', 'p.project_demo_url');
        $search['display_fields'] = $search['search_fields'];
        $search['table_joins'] = array(array('table' => 'project_contacts',
            'alias' => 'pc', 'join' => 'p.project_id = pc.project_id'));

        return $search;
    }
}
