<?php
	
	require_once(TOOLKIT . '/class.sectionmanager.php');
	require_once(TOOLKIT . '/class.fieldmanager.php');
	require_once(TOOLKIT . '/class.entrymanager.php');
	
	class Extension_FrontendMemberManager extends Extension {
		protected $initialized = false;
		protected $sessions = array();
		
	/*-------------------------------------------------------------------------
		Definition:
	-------------------------------------------------------------------------*/
		
		public function about() {
			return array(
				'name'			=> 'Frontend Member Manager',
				'version'		=> '1.008',
				'release-date'	=> '2009-03-18',
				'author'		=> array(
					'name'			=> 'Rowan Lewis',
					'website'		=> 'http://pixelcarnage.com/',
					'email'			=> 'rowan@pixelcarnage.com'
				),
				'description' => 'Allows you to manage a member driven community.'
			);
		}
		
		public function uninstall() {
			$this->_Parent->Database->query("DROP TABLE `tbl_fields_groupname`");
			$this->_Parent->Database->query("DROP TABLE `tbl_fields_grouptype`");
			$this->_Parent->Database->query("DROP TABLE `tbl_fields_membergroup`");
			$this->_Parent->Database->query("DROP TABLE `tbl_fields_membername`");
			$this->_Parent->Database->query("DROP TABLE `tbl_fields_memberpassword`");
			$this->_Parent->Database->query("DROP TABLE `tbl_fields_memberstatus`");
			$this->_Parent->Database->query("DROP TABLE `tbl_fmm_tracking`");
		}
		
		public function install() {
			$this->_Parent->Database->query("
				CREATE TABLE IF NOT EXISTS `tbl_fields_groupname` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`field_id` int(11) unsigned NOT NULL,
					`formatter` varchar(255) default NULL,
					`validator` varchar(255) default NULL,
					PRIMARY KEY (`id`),
					KEY `field_id` (`field_id`)
				)
			");
			
			$this->_Parent->Database->query("
				CREATE TABLE IF NOT EXISTS `tbl_fields_grouppermissions` (
					`id` INT(11) UNSIGNED NOT NULL auto_increment,
					`field_id` INT(11) UNSIGNED NOT NULL,
					`create` ENUM('yes', 'no') DEFAULT 'no',
					`update` ENUM('yes', 'no') DEFAULT 'no',
					`update_own` ENUM('yes', 'no') DEFAULT 'no',
					`delete` ENUM('yes', 'no') DEFAULT 'no',
					`delete_own` ENUM('yes', 'no') DEFAULT 'no',
					PRIMARY KEY (`id`),
					KEY `field_id` (`field_id`)
				)
			");
			
			$this->_Parent->Database->query("
				CREATE TABLE IF NOT EXISTS `tbl_fields_membergroup` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`field_id` int(11) unsigned NOT NULL,
					`parent_section_id` int(11) unsigned default NULL,
					`parent_field_id` int(11) unsigned default NULL,
					PRIMARY KEY (`id`),
					KEY `parent_section_id` (`parent_section_id`),
					KEY `parent_field_id` (`parent_field_id`)
				)
			");
			
			$this->_Parent->Database->query("
				CREATE TABLE IF NOT EXISTS `tbl_fields_membername` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`field_id` int(11) unsigned NOT NULL,
					`formatter` varchar(255) default NULL,
					`validator` varchar(255) default NULL,
					PRIMARY KEY (`id`),
					KEY `field_id` (`field_id`)
				)
			");
			
			$this->_Parent->Database->query("
				CREATE TABLE IF NOT EXISTS `tbl_fields_memberpassword` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`field_id` int(11) unsigned NOT NULL,
					`length` int(11) unsigned NOT NULL,
					`strength` enum('weak', 'good', 'strong') NOT NULL default 'good',
					`salt` varchar(255) default NULL,
					PRIMARY KEY (`id`),
					KEY `field_id` (`field_id`)
				)
			");
			
			$this->_Parent->Database->query("
				CREATE TABLE IF NOT EXISTS `tbl_fields_memberstatus` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`field_id` int(11) unsigned NOT NULL,
					PRIMARY KEY (`id`)
				)
			");
			
			$this->_Parent->Database->query("
				CREATE TABLE IF NOT EXISTS `tbl_fmm_tracking` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`entry_id` int(11) unsigned default NULL,
					`access_id` varchar(32) NOT NULL,
					`client_id` varchar(32) NOT NULL,
					`first_seen` datetime NOT NULL,
					`last_seen` datetime NOT NULL,
					PRIMARY KEY (`id`),
					UNIQUE KEY `unique_id` (`access_id`,`client_id`),
					KEY `entry_id` (`entry_id`)
				)
			");
		}
		
		public function getSubscribedDelegates() {
			return array(
				array(
					'page'		=> '/frontend/',
					'delegate'	=> 'FrontendPageResolved',
					'callback'	=> 'initialize'
				),
				array(
					'page'		=> '/frontend/',
					'delegate'	=> 'FrontendParamsResolve',
					'callback'	=> 'parameters'
				)
			);
		}
		
		protected $addedPublishHeaders = false;
		
		public function addPublishHeaders($page) {
			if (!$this->addedPublishHeaders) {
				$page->addStylesheetToHead(URL . '/extensions/frontendmembermanager/assets/publish.css', 'screen', 8251840);
				
				$this->addedPublishHeaders = true;
			}
		}
		
		public function addSettingsHeaders($page) {
			if (!$this->addedSettingsHeaders) {
				$page->addStylesheetToHead(URL . '/extensions/frontendmembermanager/assets/settings.css', 'screen', 8251840);
				
				$this->addedSettingsHeaders = true;
			}
		}
		
	/*-------------------------------------------------------------------------
		Delegates:
	-------------------------------------------------------------------------*/
		
		public function initialize($context = null) {
			if (!$this->initialized) {
				$sectionManager = new SectionManager($this->_Parent);
				$sections = $this->_Parent->Database->fetchCol('id', "
					SELECT
						s.id
					FROM
						`tbl_sections` AS s
					WHERE
						3 = (
							SELECT
								count(*)
							FROM
								`tbl_fields` AS f
							WHERE
								f.parent_section = s.id
								AND f.type IN (
									'membername',
									'memberpassword',
									'memberstatus'
								)
						)
				");
				
				foreach ($sections as $section_id) {
					$this->sessions[] = new FMM_Session(
						$this, $this->_Parent, $this->_Parent->Database,
						$sectionManager->fetch($section_id)
					);
				}
				
				$this->initialized = true;
			}
			
			return true;
		}
		
		public function parameters(&$context) {
			$this->initialize();
			
			foreach ($this->sessions as $session) {
				$session->parameters($context);
			}
		}
		
	/*-------------------------------------------------------------------------
		Actions:
	-------------------------------------------------------------------------*/
		
		public function actionLogin($values,$redirect = null) {
			$result = new XMLElement('fmm-login');
			$section = @$values['section'];
			
			// Not setup yet:
			if (!$this->initialize()) {
				$result->setAttribute('status', 'not-setup');
				
				return $result;
				
			} else {
				$result->setAttribute('status', 'ok');
			}
			
			foreach ($this->sessions as $session)
			if ($section and $session->handle == $section) {
				$session->actionLogin($result, $values, $redirect);				
			}			
			
			return $result;
		}
		
		public function actionLogout() {
			$result = new XMLElement('fmm-logout');
			
			foreach ($this->sessions as $session) {
				$session->actionLogout($result);
			}
			
			//$this->updateTrackingData(FMM::TRACKING_LOGOUT);
			
			//$result->setAttribute('status', 'success');
			
			return $result;
		}
		
		public function actionStatus() {
			$result = new XMLElement('fmm-status');
			
			// Not setup yet:
			if (!$this->initialize()) {
				$result->setAttribute('status', 'not-setup');
				
				return $result;
				
			} else {
				$result->setAttribute('status', 'ok');
			}
			
			foreach ($this->sessions as $session) {
				$session->actionStatus($result);
			}
			
			return $result;
		}
	}
	
	class FMM {
		const STATUS_PENDING = 'pending';
		const STATUS_BANNED = 'banned';
		const STATUS_ACTIVE = 'active';
		
		const FIELD_MEMBERNAME = 'membername';
		const FIELD_MEMBERPASSWORD = 'memberpassword';
		const FIELD_MEMBERSTATUS = 'memberstatus';
		
		const TRACKING_NORMAL = 'normal';
		const TRACKING_LOGIN = 'login';
		const TRACKING_LOGOUT = 'logout';
	}
	
	class FMM_Session {
		protected $database = null;
		protected $driver = null;
		protected $parent = null;
		protected $section = null;
		public $handle = null;
		
		public function __construct($driver, $parent, $database, $section) {
			$this->driver = $driver;
			$this->database = $database;
			$this->parent = $parent;
			$this->section = $section;
			$this->handle = $section->get('handle');
			
			$this->setAccessId(@$_SESSION['fmm'][$this->handle]);
			
			$this->updateTrackingData();
			$this->cleanTrackingData();
		}
		
		public function parameters(&$context) {
			if ($this->getMemberId() and $this->getMemberStatus() == FMM::STATUS_ACTIVE) {
				$context['params']["fmm-{$this->handle}-id"] = $this->getMemberId();
			}
			
			else {
				$context['params']["fmm-{$this->handle}-id"] = 0;
			}
		}
		
	/*-------------------------------------------------------------------------
		Tracking:
	-------------------------------------------------------------------------*/
		
		public function getAccessId() {
			if (empty($this->accessId)) {
				$this->setAccessId(md5($this->handle . time()));
			}
			
			return $this->accessId;
		}
		
		public function setAccessId($access_id) {
			$_SESSION['fmm'][$this->handle] = $this->accessId = $access_id;
			
			return $this;
		}
		
		public function getClientId() {
			if (empty($this->clientId)) {
				$this->clientId = md5($_SERVER['HTTP_USER_AGENT']);
			}
			
			return $this->clientId;
		}
		
		public function cleanTrackingData() {
			$this->database->query("
				DELETE FROM
					`tbl_fmm_tracking`
				WHERE
					`last_seen` < NOW() - INTERVAL 1 MONTH
					AND (
						`access_id` != '{$this->getAccessId()}'
						AND `client_id` != '{$this->getClientId()}'
					)
				LIMIT 10
			");
		}
		
		public function hasTrackingData() {
			return (boolean)$this->database->fetchVar('id', 0, "
				SELECT
					t.id
				FROM
					`tbl_fmm_tracking` AS t
				WHERE
					t.access_id = '{$this->getAccessId()}'
					AND t.client_id = '{$this->getClientId()}'
				LIMIT 1
			");
		}
		
		public function getTrackingData($access_id) {
			return $this->database->fetchRow(0, "
				SELECT
					t.*
				FROM
					`tbl_fmm_tracking` AS t
				WHERE
					t.access_id = '{$this->getAccessId()}'
					AND t.client_id = '{$this->getClientId()}'
				LIMIT 1
			");
		}
		
		public function updateTrackingData($mode = FMM::TRACKING_NORMAL) {
			$current_date = DateTimeObj::get('Y-m-d H:i:s');
			$member_id = $this->getMemberId();
			
			if ($mode == FMM::TRACKING_LOGOUT) $member_id = 0;
			
			$this->database->query("
				INSERT INTO
					`tbl_fmm_tracking`
				SET
					`entry_id` = '{$member_id}',
					`access_id` = '{$this->getAccessId()}',
					`client_id` = '{$this->getClientId()}',
					`first_seen` = '{$current_date}',
					`last_seen` = '{$current_date}'
				ON DUPLICATE KEY UPDATE
					`entry_id` = '{$member_id}',
					`last_seen` = '{$current_date}'
			");
		}
		
	/*-------------------------------------------------------------------------
		Member:
	-------------------------------------------------------------------------*/
		
		public function getMember() {
			if (is_null($this->member)) {
				$em = new EntryManager($this->parent);
				
				$this->member = current($em->fetch(
					$this->getMemberId(), $this->section->get('id')
				));
			}
			
			return $this->member;
		}
		
		public function setMember($entry) {
			if ($entry instanceof Entry) {
				$this->member = $entry;
				$this->memberId = $entry->get('id');
			}
			
			return $this;
		}
		
		public function getMemberId() {
			if (empty($this->memberId)) {
				$member_id = $this->database->fetchVar('entry_id', 0, "
					SELECT
						t.entry_id
					FROM
						`tbl_fmm_tracking` AS t
					WHERE
						t.access_id = '{$this->getAccessId()}'
						AND t.client_id = '{$this->getClientId()}'
					LIMIT 1
				");
				
				$this->setMemberId($member_id);
			}
			
			return $this->memberId;
		}
		
		public function setMemberId($entry_id) {
			$this->memberId = (integer)$entry_id;
			
			return $this;
		}
		
		public function getMemberStatus() {
			if (
				$entry = $this->getMember()
				and $field = $this->getMemberField(FMM::FIELD_MEMBERSTATUS)
			) {
				$field = $this->getMemberField(FMM::FIELD_MEMBERSTATUS);
				$data = $entry->getData($field->get('id'));
				$data = $field->sanitizeData($data);
				
				return $data['value'];
			}
			
			return null;
		}
		
		public function setMemberStatus($status) {
			if (
				$entry = $this->getMember()
				and $field = $this->getMemberField(FMM::FIELD_MEMBERSTATUS)
			) {
				$return = null;
				
				// Get updated entry data:
				$data = $field->processRawFieldData(
					$status, $return, false, $entry->get('id')
				);
				
				$entry->setData($field->get('id'), $data);
				
				// Save to database:
				$entry->commit();
			}
			
			return $this;
		}
		
		public function getMemberField($type) {
			return current($this->section->fetchFields($type));
		}
		
	/*-------------------------------------------------------------------------
		Actions:
	-------------------------------------------------------------------------*/
		
		public function actionLogin($parent, $values, $redirect) {
			$em = new EntryManager($this->parent);
			$fm = new FieldManager($this->parent);
			
			$fields = array();
			$section = $this->section;
			$where = $joins = $group = null;
			$name_where = $name_joins = $name_group = null;
			
			$result = new XMLElement('section');
			$result->setAttribute('handle', $this->handle);
			$parent->appendChild($result);
			
			// Get given fields:
			foreach ($values as $key => $value) {
				$field_id = $fm->fetchFieldIDFromElementName($key, $this->section->get('id'));
				
				if (!is_null($field_id)) {
					$field = $fm->fetch($field_id, $this->section->get('id'));
					
					if (
						$field instanceof FieldMemberName
						or $field instanceof FieldMemberPassword
					) {
						$fields[] = $field;
						
						$field->buildDSRetrivalSQL($value, $joins, $where);
						
						if (!$group) $group = $field->requiresSQLGrouping();
						
						//Build SQL for determining of the username or the password was incorrrect. Only executed if login fails
						if ($field instanceof FieldMemberName) {
							$field->buildDSRetrivalSQL($value, $name_joins, $name_where);
							if (!$name_group) $name_group = $field->requiresSQLGrouping();
						}
					}
				}
			}
			
			// Find matching entries:
			$entries = $em->fetch(
				null, $this->section->get('id'), 1, null,
				$where, $joins, $group, true
			);
			
			// Invalid credentials, woot!
			if (!$entry = @current($entries)) {
				$result->setAttribute('status', 'failed');
				
				//determine reason for login failure. This should not normally be shown to the user as it can lead to account cracking attempts.
				$name_entries = $em->fetch(
					null, $this->section->get('id'), 1, null,
					$name_where, $name_joins, $name_group, true
				);

				if ($name_entry = @current($name_entries)) {
					$result->setAttribute('reason', 'incorrect-password');
				} else {
					$result->setAttribute('reason', 'incorrect-username');
				}
				
				return false;
			}
			
			$this->setMember($entry);
			$field = $this->getMemberField(FMM::FIELD_MEMBERSTATUS);
			$data = $entry->getData($field->get('id'));
			$status = @current($data['value']);
			
			// The member is banned:
			if ($status == FMM::STATUS_BANNED) {
				$result->setAttribute('status', 'banned');
				
				return false;
			}
			
			// The member is inactive:
			if ($status == FMM::STATUS_PENDING) {
				$result->setAttribute('status', 'pending');
				
				return false;
			}
			
			$result->setAttribute('status', 'success');
			
			$this->updateTrackingData(FMM::TRACKING_LOGIN);
			
			if($redirect != null) redirect($redirect);
			
			return true;
		}
		
		public function actionLogout($parent) {
			$result = new XMLElement('section');
			$result->setAttribute('handle', $this->handle);
			
			$this->updateTrackingData(FMM::TRACKING_LOGOUT);
			
			$result->setAttribute('status', 'success');
			
			$parent->appendChild($result);
		}
		
		public function actionStatus($parent) {
			$result = new XMLElement('section');
			$result->setAttribute('handle', $this->handle);
			
			if ($this->getMemberId() and $this->getMemberStatus() == FMM::STATUS_ACTIVE) {
				$result->setAttribute('logged-in', 'yes');
				
			} else {
				$result->setAttribute('logged-in', 'no');
			}
			
			$parent->appendChild($result);
		}
	};
	
?>
