<?php /* $Id: forums.class.php 2013 2011-08-07 07:03:43Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version2.4/modules/forums/forums.class.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

if (!isset($AppUI)) {
    $AppUI = new CAppUI;
}
require_once ($AppUI->getLibraryClass('PEAR/BBCodeParser'));
$bbparser = new HTML_BBCodeParser();

$filters = array('- Filters -');

if ($a == 'viewer') {
	array_push($filters, 'My Watched', 'Last 30 days');
} else {
	array_push($filters, 'My Forums', 'My Watched', 'My Projects', 'My Company', 'Inactive Projects');
}

class CForum extends w2p_Core_BaseObject {
	public $forum_id = null;
	public $forum_project = null;
	public $forum_status = null;
	public $forum_owner = null;
	public $forum_name = null;
	public $forum_create_date = null;
	public $forum_last_date = null;
	public $forum_last_id = null;
	public $forum_message_count = null;
	public $forum_description = null;
	public $forum_moderated = null;

	public function __construct() {
        parent::__construct('forums', 'forum_id');
	}

    public function check() {
        // ensure the integrity of some variables
        $errorArray = array();
        $baseErrorMsg = get_class($this) . '::store-check failed - ';

        if ('' == trim($this->forum_name)) {
            $errorArray['forum_name'] = $baseErrorMsg . 'forum name is not set';
        }
        if (0 == (int) $this->forum_owner) {
            $errorArray['forum_owner'] = $baseErrorMsg . 'forum owner is not set';
        }

        $this->_error = $errorArray;
        return $errorArray;
    }

    public function getMessages(CAppUI $AppUI, $forum_id = 0, $message_id = 0, $sortDir = 'asc') {

        $q = $this->_query;
        $q->addTable('forums');
        $q->addTable('forum_messages');
        $q->addQuery('forum_messages.*,	contact_first_name, contact_last_name, contact_email,
            contact_display_name, user_username, forum_moderated, visit_user');
        $q->addJoin('forum_visits', 'v', 'visit_user = ' . (int)$AppUI->user_id . ' AND visit_forum = ' . (int) $forum_id . ' AND visit_message = forum_messages.message_id');
        $q->addJoin('users', 'u', 'message_author = u.user_id', 'inner');
        $q->addJoin('contacts', 'con', 'contact_id = user_contact', 'inner');
        $q->addWhere('forum_id = message_forum AND (message_id = ' . (int)$message_id . ' OR message_parent = ' . (int)$message_id . ')');
        $q->addOrder('message_date ' . $sortDir);

        return $q->loadList();
    }

    public function load(CAppUI $AppUI, $forum_id) {

        $q = $this->_query;
        $q->addQuery('*');
        $q->addTable('forums');
        $q->addWhere('forum_id = ' . (int) $forum_id);
        $q->loadObject($this, true, false);
    }

    public function loadFull(CAppUI $AppUI, $forum_id) {

        $q = $this->_query;
        $q->addTable('forums');
        $q->addTable('users', 'u');
        $q->addQuery('forum_id, forum_project,	forum_description, forum_owner, forum_name,
            forum_create_date, forum_last_date, forum_message_count, forum_moderated,
            user_username, contact_first_name, contact_last_name, contact_display_name,
            project_name, project_color_identifier');
        $q->addJoin('contacts', 'con', 'contact_id = user_contact', 'inner');
        $q->addJoin('projects', 'p', 'p.project_id = forum_project', 'left');
        $q->addWhere('user_id = forum_owner');
        $q->addWhere('forum_id = ' . (int)$forum_id);

        $this->project_name = '';
        $this->project_color_identifier = '';
        $this->contact_first_name = '';
        $this->contact_last_name = '';
        $this->contact_display_name = '';
        $q->loadObject($this);
    }

    public function getAllowedForums($user_id, $company_id, $filter = -1, $orderby = 'forum_name', $orderdir = 'asc', $max_msg_length = 30)
    {
        $project = new CProject();

        $q = $this->_query;
        $q->addTable('forums');
        
        $q->addQuery('forum_id, forum_project, forum_description, forum_owner, forum_name');
        $q->addQuery('forum_moderated, forum_create_date, forum_last_date');
        $q->addQuery('sum(if(c.message_parent=-1,1,0)) as forum_topics, sum(if(c.message_parent>0,1,0)) as forum_replies');
        $q->addQuery('user_username, project_name, project_color_identifier, CONCAT(contact_first_name,\' \',contact_last_name) owner_name');
        $q->addQuery('SUBSTRING(l.message_body,1,' . $max_msg_length . ') message_body');
        $q->addQuery('LENGTH(l.message_body) message_length, watch_user, l.message_parent, l.message_id');
        $q->addQuery('count(distinct v.visit_message) as visit_count, count(distinct c.message_id) as message_count');

        $q->addJoin('users', 'u', 'u.user_id = forum_owner');
        $q->addJoin('projects', 'pr', 'pr.project_id = forum_project');
        $q->addJoin('forum_messages', 'l', 'l.message_id = forum_last_id');
        $q->addJoin('forum_messages', 'c', 'c.message_forum = forum_id');
        $q->addJoin('forum_watch', 'w', 'watch_user = ' . $user_id . ' AND watch_forum = forum_id');
        $q->addJoin('forum_visits', 'v', 'visit_user = ' . $user_id . ' AND visit_forum = forum_id and visit_message = c.message_id');
        $q->addJoin('contacts', 'cts', 'contact_id = u.user_contact');

        $project->setAllowedSQL($user_id, $q, null, 'pr');
        $this->setAllowedSQL($user_id, $q);

        switch ($filter) {
            case 1:
                $q->addWhere('(project_active = 1 OR forum_project = 0) AND forum_owner = ' . $user_id);
                break;
            case 2:
                $q->addWhere('(project_active = 1 OR forum_project = 0) AND watch_user IS NOT NULL');
                break;
            case 3:
                $q->addWhere('(project_active = 1 OR forum_project = 0) AND project_owner = ' . $user_id);
                break;
            case 4:
                $q->addWhere('(project_active = 1 OR forum_project = 0) AND project_company = ' . $company_id);
                break;
            case 5:
                $q->addWhere('(project_active = 0 OR forum_project = 0)');
                break;
            default:
                $q->addWhere('(project_active = 1 OR forum_project = 0)');
                break;
        }

        $q->addGroup('forum_id');
        $q->addOrder($orderby . ' ' . $orderdir);

        return $q->loadList();
    }

	public function store(CAppUI $AppUI) {
        $perms = $AppUI->acl();
        $stored = false;

        $this->_error = $this->check();

        if (count($this->_error)) {
            return $this->_error;
        }

        if ($this->forum_id && $perms->checkModuleItem('forums', 'edit', $this->forum_id)) {
            if (($msg = parent::store())) {
                return $msg;
            }
            $stored = true;
        }
        if (0 == $this->forum_id && $perms->checkModuleItem('forums', 'add')) {
            $this->forum_create_date = $AppUI->convertToSystemTZ($this->forum_create_date);
            if (($msg = parent::store())) {
                return $msg;
            }
            $stored = true;
        }
        return $stored;
	}

	public function delete(CAppUI $AppUI = null) {
        global $AppUI;
        $perms = $AppUI->acl();
        $this->_error = array();

        if ($perms->checkModuleItem('forums', 'delete', $this->forum_id)) {
            $q = $this->_query;
            $q->setDelete('forum_visits');
            $q->addWhere('visit_forum = ' . (int)$this->forum_id);
            $q->exec(); // No error if this fails, it is not important.

            $q->clear();
            $q->setDelete('forum_messages');
            $q->addWhere('message_forum = ' . (int)$this->forum_id);
            if (!$q->exec()) {
                return db_error();
            }

            if ($msg = parent::delete()) {
                return $msg;
            }
            return true;
        }
		return false;
	}

	public function getAllowedRecords($uid, $fields = '*', $orderby = '', $index = null, $extra = null) {
		global $AppUI;
		$oPrj = new CProject();

		$aPrjs = $oPrj->getAllowedRecords($uid, 'projects.project_id, project_name', '', null, null, 'projects');
		if (count($aPrjs)) {
			$buffer = '(forum_project IN (' . implode(',', array_keys($aPrjs)) . ') OR forum_project IS NULL OR forum_project = \'\' OR forum_project = 0)';

			if ($extra['where'] != '') {
				$extra['where'] = $extra['where'] . ' AND ' . $buffer;
			} else {
				$extra['where'] = $buffer;
			}
		} else {
			// There are no allowed projects, so only allow forums with no project associated.
			if ($extra['where'] != '') {
				$extra['where'] = $extra['where'] . ' AND (forum_project IS NULL OR forum_project = \'\' OR forum_project = 0) ';
			} else {
				$extra['where'] = '(forum_project IS NULL OR forum_project = \'\' OR forum_project = 0)';
			}
		}
		return parent::getAllowedRecords($uid, $fields, $orderby, $index, $extra);
	}

    public function hook_search() {
        $search['table'] = 'forums';
        $search['table_module'] = 'forums';
        $search['table_key'] = 'forum_id';
        $search['table_link'] = 'index.php?m=forums&a=viewer&forum_id='; // first part of link
        $search['table_title'] = 'Forums';
        $search['table_orderby'] = 'forum_name';
        $search['search_fields'] = array('forum_name', 'forum_description');
        $search['display_fields'] = $search['search_fields'];

        return $search;
    }
}