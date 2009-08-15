<?php
	
	if (!defined('__IN_SYMPHONY__')) die('<h2>Symphony Error</h2><p>You cannot directly access this file</p>');
	
	class FieldMemberPassword extends Field {
		protected $_driver = null;
		protected $_strengths = array();
		protected $_strength_map = array();
		
	/*-------------------------------------------------------------------------
		Definition:
	-------------------------------------------------------------------------*/
		
		public function __construct(&$parent) {
			parent::__construct($parent);
			
			$this->_name = 'Member Password';
			$this->_driver = $this->_engine->ExtensionManager->create('frontendmembermanager');
			$this->_strengths = array(
				array('weak', false, 'Weak'),
				array('good', false, 'Good'),
				array('strong', false, 'Strong')
			);
			$this->_strength_map = array(
				0			=> 1,
				1			=> 1,
				2			=> 2,
				3			=> 3,
				4			=> 3,
				'weak'		=> 1,
				'good'		=> 2,
				'strong'	=> 3
			);
			
			// Set defaults:
			$this->set('show_column', 'yes');
			$this->set('length', '6');
			$this->set('strength', 'good');
		}
		
		public function createTable() {
			$field_id = $this->get('id');
			
			return $this->_engine->Database->query("
				CREATE TABLE IF NOT EXISTS `tbl_entries_data_{$field_id}` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`entry_id` int(11) unsigned NOT NULL,
					`password` text default NULL,
					`strength` enum(
						'weak', 'good', 'strong'
					) NOT NULL,
					`length` tinyint NOT NULL,
					PRIMARY KEY (`id`),
					KEY `entry_id` (`entry_id`),
					FULLTEXT KEY `password` (`password`),
					KEY `strength` (`strength`),
					KEY `length` (`length`)
				)
			");
		}
		
		public function canFilter() {
			return true;
		}
		
	/*-------------------------------------------------------------------------
		Utilities:
	-------------------------------------------------------------------------*/
		
		protected function checkPassword($password) {
			$strength = 0;
			$patterns = array(
				'/[a-z]/', '/[A-Z]/', '/[0-9]/',
				'/[¬!"£$%^&*()`{}\[\]:@~;\'#<>?,.\/\\-=_+\|]/'
			);
			
			foreach ($patterns as $pattern) {
				if (preg_match($pattern, $password, $matches)) {
					$strength++;
				}
			}
			
			return $strength;
		}
		
		protected function compareStrength($a, $b) {
			if ($this->_strength_map[$a] >= $this->_strength_map[$b]) return true;
			
			return false;
		}
		
		protected function encodePassword($password) {
			return md5($this->get('salt') . $password);
		}
		
		protected function getStrengthName($strength) {
			$map = array_flip($this->_strength_map);
			
			return $map[$strength];
		}
		
		protected function rememberSalt() {
			$field_id = $this->get('id');
			
			$salt = $this->_engine->Database->fetchVar('salt', 0, "
				SELECT
					f.salt
				FROM
					`tbl_fields_memberpassword` AS f
				WHERE
					f.field_id = '$field_id'
				LIMIT 1
			");
			
			if ($salt and !$this->get('salt')) {
				$this->set('salt', $salt);
			}
		}
		
		protected function rememberData($entry_id) {
			$field_id = $this->get('id');
			
			return $this->_engine->Database->fetchRow(0, "
				SELECT
					f.password, f.strength, f.length
				FROM
					`tbl_entries_data_{$field_id}` AS f
				WHERE
					f.entry_id = '{$entry_id}'
				LIMIT 1
			");
		}
		
	/*-------------------------------------------------------------------------
		Settings:
	-------------------------------------------------------------------------*/
		
		public function displaySettingsPanel(&$wrapper, $errors = null) {
			$field_id = $this->get('id');
			$order = $this->get('sortorder');
			
			$wrapper->appendChild(new XMLElement('h4', ucwords($this->name())));
			$wrapper->appendChild(Widget::Input(
				"fields[{$order}][type]", $this->handle(), 'hidden'
			));
			
			if ($field_id) $wrapper->appendChild(Widget::Input(
				"fields[{$order}][id]", $field_id, 'hidden'
			));
			
			$wrapper->appendChild($this->buildSummaryBlock($errors));
			
		// Validator ----------------------------------------------------------
			
			$group = new XMLElement('div');
			$group->setAttribute('class', 'group');
			
			$label = Widget::Label('Minimum Length');
			$label->appendChild(Widget::Input(
				"fields[{$order}][length]", $this->get('length')
			));
			
			$group->appendChild($label);
			
		// Strength -----------------------------------------------------------
			
			$values = $this->_strengths;
			
			foreach ($values as &$value) {
				$value[1] = $value[0] == $this->get('strength');
			}
			
			$label = Widget::Label('Minimum Strength');
			$label->appendChild(Widget::Select(
				"fields[{$order}][strength]", $values
			));
			
			$group->appendChild($label);
			$wrapper->appendChild($group);
			
		// Salt ---------------------------------------------------------------
			
			$label = Widget::Label('Password Salt');
			$input = Widget::Input(
				"fields[{$order}][salt]", $this->get('salt')
			);
			
			if ($this->get('salt')) {
				$input->setAttribute('disabled', 'disabled');
			}
			
			$label->appendChild($input);
			
			if (isset($errors['salt'])) {
				$label = Widget::wrapFormElementWithError($label, $errors['salt']);
			}
			
			$wrapper->appendChild($label);
			
			$this->appendShowColumnCheckbox($wrapper);						
		}
		
		public function checkFields(&$errors, $checkForDuplicates = true) {
			parent::checkFields($errors, $checkForDuplicates);
			
			$this->rememberSalt();
			
			if (trim($this->get('salt')) == '') {
				$errors['salt'] = 'This is a required field.';
			}
		}
		
		public function commit() {
			$field_id = $this->get('id');
			
			if (!parent::commit() or $this->get('id') === false) return false;
			
			$this->rememberSalt();
			
			$fields = array(
				'field_id'		=> $this->get('id'),
				'length'		=> $this->get('length'),
				'strength'		=> $this->get('strength'),
				'salt'			=> $this->get('salt')
			);
			
			$this->_engine->Database->query("
				DELETE FROM
					`tbl_fields_memberpassword`
				WHERE
					`field_id` = '$field_id'
				LIMIT 1
			");
			
			return $this->_engine->Database->insert($fields, 'tbl_fields_memberpassword');
		}
		
	/*-------------------------------------------------------------------------
		Publish:
	-------------------------------------------------------------------------*/
		
		public function displayPublishPanel(&$wrapper, $data = null, $error = null, $prefix = null, $postfix = null, $entry_id = null) {
			$this->_engine->Page->addStylesheetToHead(URL . '/extensions/frontendmembermanager/assets/publish.css', 'screen', 8251840);
			
			$field_id = $this->get('id');
			$handle = $this->get('element_name');
			$password_set = $this->Database->fetchVar('id', 0, "
				SELECT
					f.id
				FROM
					`tbl_entries_data_{$field_id}` AS f
				WHERE
					f.entry_id = '{$entry_id}'
				LIMIT 1
			");
			
			$label = new XMLElement('div', $this->get('label'));
			$label->setAttribute('class', 'label');
			
			$container = new XMLElement('div');
			$container->setAttribute('class', 'container');
			
			$group = new XMLElement('div');
			$group->setAttribute('class', 'group');
			
			// Change password:
			if ($password_set) {
				$this->displayPublishPassword(
					$group, 'New Password', "{$prefix}[{$handle}][password]{$postfix}"
				);
				$this->displayPublishPassword(
					$group, 'Confirm New Password', "{$prefix}[{$handle}][confirm]{$postfix}"
				);
				
				$group->appendChild(Widget::Input(
					"fields{$prefix}[{$handle}][optional]{$postfix}", 'yes', 'hidden'
				));
				
				$container->appendChild($group);
				
				$help = new XMLElement('p');
				$help->setAttribute('class', 'help');
				$help->setValue(__('Leave new password field blank to keep the current password'));
				
				$container->appendChild($help);
				
			// Create password:
			} else {
				$this->displayPublishPassword(
					$group, 'Password', "{$prefix}[{$handle}][password]{$postfix}"
				);
				$this->displayPublishPassword(
					$group, 'Confirm Password', "{$prefix}[{$handle}][confirm]{$postfix}"
				);
				
				$container->appendChild($group);
			}
			
			$label->appendChild($container);
			
			if ($error != null) {
				$label = Widget::wrapFormElementWithError($label, $error);
			}
			
			$wrapper->appendChild($label);
		}
		
		public function displayPublishPassword($wrapper, $title, $name) {
			$label = Widget::Label(__($title));
			$input = Widget::Input("fields{$name}");
			$input->setAttribute('type', 'password');
			
			$label->appendChild($input);
			$wrapper->appendChild($label);
		}
		
	/*-------------------------------------------------------------------------
		Input:
	-------------------------------------------------------------------------*/
		
		public function checkPostFieldData($data, &$error, $entry_id = null) {
			$label = $this->get('label');
			$error = null; $required = false;
			
			$password = trim($data['password']);
			$optional = isset($data['optional']);
			
			if (isset($data['confirm'])) {
				$confirm = trim($data['confirm']);
			}
			
			if ($optional and (strlen($password) != '' or strlen($confirm) != '')) {
				$required = true;
				
			} else if (!$optional) {
				$required = true;
			}
			
			if ($required) {
				if (strlen($password) == 0) {
					$error = "'{$label}' is a required field.";
					
					return self::__MISSING_FIELDS__;
				}
				
				if (isset($confirm) and $confirm != $password) {
					$error = "'{$label}' passwords do not match.";
					
					return self::__INVALID_FIELDS__;
				}
				
				if (strlen($password) < (integer)$this->get('length')) {
					$error = "'{$label}' is too short.";
					
					return self::__INVALID_FIELDS__;
				}
				
				if (!$this->compareStrength($this->checkPassword($password), $this->get('strength'))) {
					$error = "'{$label}' is not strong enough.";
					
					return self::__INVALID_FIELDS__;
				}
			}
			
			return self::__OK__;
		}
		
		public function processRawFieldData($data, &$status, $simulate = false, $entry_id = null) {
			$status = self::__OK__; $required = false;
			
			if ($data == '') return array();
			
			$password = trim($data['password']);
			$optional = isset($data['optional']);
			
			if (isset($data['confirm'])) {
				$confirm = trim($data['confirm']);
			}
			
			if ($optional and (strlen($password) != '' or strlen($confirm) != '')) {
				$required = true;
				
			} else if (!$optional) {
				$required = true;
			}
			
			if ($required) {
				return array(
					'password'			=> $this->encodePassword($password),
					'strength'			=> $this->checkPassword($password),
					'length'			=> strlen($password)
				);
				
			} else {
				return $this->rememberData($entry_id);
			}
		}
		
	/*-------------------------------------------------------------------------
		Output:
	-------------------------------------------------------------------------*/
		
		public function appendFormattedElement(&$wrapper, $data, $encode = false) {
			$element = new XMLElement($this->get('element_name'));
			$element->setAttribute('strength', $data['strength']);
			$element->setAttribute('length', $data['length']);
			$wrapper->appendChild($element);
		}
		
		public function prepareTableValue($data, XMLElement $link = null) {
			if (empty($data)) return;
			
			return parent::prepareTableValue(
				array(
					'value'		=> ucwords($data['strength']) . " ({$data['length']})"
				), $link
			);
		}
		
	/*-------------------------------------------------------------------------
		Filtering:
	-------------------------------------------------------------------------*/
		
		public function buildDSRetrivalSQL($data, &$joins, &$where, $andOperation = false) {
			$field_id = $this->get('id');
			
			if ($andOperation) {
				foreach ($data as $value) {
					$this->_key++;
					$value = $this->encodePassword($value);
					$joins .= "
						LEFT JOIN
							`tbl_entries_data_{$field_id}` AS t{$field_id}_{$this->_key}
							ON (e.id = t{$field_id}_{$this->_key}.entry_id)
					";
					$where .= "
						AND (
							t{$field_id}_{$this->_key}.password = '{$value}'
						)
					";
				}
				
			} else {
				if (is_array($data) and isset($data['password'])) {
					$data = array($data['password']);
					
				} else if (!is_array($data)) {
					$data = array($data);
				}
				
				foreach ($data as &$value) {
					$value = $this->encodePassword($value);
				}
				
				$this->_key++;
				$data = implode("', '", $data);
				$joins .= "
					LEFT JOIN
						`tbl_entries_data_{$field_id}` AS t{$field_id}_{$this->_key}
						ON (e.id = t{$field_id}_{$this->_key}.entry_id)
				";
				$where .= "
					AND t{$field_id}_{$this->_key}.password IN ('{$data}')
				";
			}
			
			return true;
		}
	}
	
?>