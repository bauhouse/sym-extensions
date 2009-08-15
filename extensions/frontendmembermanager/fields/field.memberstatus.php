<?php
	
	if (!defined('__IN_SYMPHONY__')) die('<h2>Symphony Error</h2><p>You cannot directly access this file</p>');
	
	class FieldMemberStatus extends Field {
		protected $_driver = null;
		protected $_states = array();
		
	/*-------------------------------------------------------------------------
		Definition:
	-------------------------------------------------------------------------*/
		
		public function __construct(&$parent) {
			parent::__construct($parent);
			
			$this->_name = 'Member Status';
			$this->_driver = $this->_engine->ExtensionManager->create('frontendmembermanager');
			$this->_states = array(
				'pending'	=> array('pending', false, 'Pending'),
				'banned'	=> array('banned', false, 'Banned'),
				'active'	=> array('active', true, 'Active')
			);
			
			// Set defaults:
			$this->set('show_column', 'no');
		}
		
		public function createTable() {
			$field_id = $this->get('id');
			
			return $this->_engine->Database->query("
				CREATE TABLE IF NOT EXISTS `tbl_entries_data_{$field_id}` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`entry_id` int(11) unsigned NOT NULL,
					`value` enum(
						'pending', 'banned', 'active'
					) NOT NULL DEFAULT 'pending',
					`date` datetime default NULL,
					PRIMARY KEY (`id`),
					KEY `entry_id` (`entry_id`),
					KEY `value` (`value`),
					KEY `date` (`date`)
				)
			");
		}
		
		public function canFilter() {
			return true;
		}
		
		public function canPrePopulate() {
			return true;
		}
		
		public function allowDatasourceOutputGrouping() {
			return true;
		}
		
		public function allowDatasourceParamOutput() {
			return true;
		}
		
	/*-------------------------------------------------------------------------
		Utilities:
	-------------------------------------------------------------------------*/
		
		public function sanitizeData($rows) {
			if (is_array($rows)) {
				if (is_array($rows['value'])) {
					$data = array(
						'value'		=> current($rows['value']),
						'date'		=> current($rows['date'])
					);
					
				} else {
					$data = $rows;
				}
				
			} else {
				$data = array();
			}
			
			return $data;
		}
		
	/*-------------------------------------------------------------------------
		Settings:
	-------------------------------------------------------------------------*/
		
		public function displaySettingsPanel(&$wrapper, $errors = null) {
			parent::displaySettingsPanel($wrapper);
			
			$this->appendShowColumnCheckbox($wrapper);
		}
		
		public function commit() {
			$id = $this->get('id');
			$handle = $this->handle();
			
			if (!parent::commit() or $id === false) return false;
			
			$fields = array(
				'field_id'		=> $id
			);
			
			$this->_engine->Database->query("
				DELETE FROM
					`tbl_fields_{$handle}`
				WHERE
					`field_id` = '$id'
				LIMIT 1
			");
			
			return $this->_engine->Database->insert($fields, "tbl_fields_{$handle}");
		}
		
	/*-------------------------------------------------------------------------
		Publish:
	-------------------------------------------------------------------------*/
		
		public function displayPublishPanel(&$wrapper, $rows = null, $error = null, $prefix = null, $postfix = null) {
			$data = $this->sanitizeData($rows);
			$label = Widget::Label($this->get('label'));
			$name = $this->get('element_name');
			$value = $data['value'];
			$options = $this->_states;
			
			if (!is_null($value)) foreach ($options as $index => $option) {
				$options[$index][1] = $option[0] == $value;
			}
			
			$label->appendChild(Widget::Select(
				"fields{$prefix}[{$name}]{$postfix}", $options
			));
			
			if ($error != null) {
				$label = Widget::wrapFormElementWithError($label, $error);
			}
			
			$wrapper->appendChild($label);
		}
		
	/*-------------------------------------------------------------------------
		Input:
	-------------------------------------------------------------------------*/
		
		public function checkPostFieldData($data, &$message, $entry_id = null) {
			$field_id = $this->get('id');
			$entry_id = (integer)$entry_id;
			
			if (!in_array($data, array_keys($this->_states)) and !empty($data)) {
				$message = "Invalid status given.";
				
				return self::__INVALID_FIELDS__;
			}
			
			return parent::checkPostFieldData($data, $message, $entry_id);
		}
		
		public function processRawFieldData($data, &$status, $simulate = false, $entry_id = null) {
			$status = self::__OK__;
			$field_id = $this->get('id');
			$entry_id = (integer)$entry_id;
			
			$current = $this->Database->fetch("
				SELECT
					f.value, f.date
				FROM
					`tbl_entries_data_{$field_id}` AS f
				WHERE
					f.entry_id = '{$entry_id}'
				ORDER BY
					f.date DESC
				LIMIT 4
			");
			
			if (empty($current)) $data = FMM::STATUS_ACTIVE;
			
			$values = array(
				'value'		=> array($data),
				'date'		=> array(DateTimeObj::get('Y-m-d H:i:s'))
			);
			
			foreach ($current as $item) {
				$values['value'][] = $item['value'];
				$values['date'][] = $item['date'];
			}
			
			return $values;
		}
		
	/*-------------------------------------------------------------------------
		Output:
	-------------------------------------------------------------------------*/
		
		public function appendFormattedElement(&$wrapper, $rows, $encode = false) {
			$data = $this->sanitizeData($rows);
			
			$element = new XMLElement($this->get('element_name'));
			$element->setAttribute('handle', $this->_states[$data['value']][0]);
			$element->setValue($this->_states[$data['value']][2]);
			$wrapper->appendChild($element);
		}
		
		public function prepareTableValue($rows, XMLElement $link = null) {
			$data = $this->sanitizeData($rows);
			
			return parent::prepareTableValue(array(
				'value'	=> @$this->_states[$data['value']][2]
			), $link);
		}
		
	/*-------------------------------------------------------------------------
		Grouping:
	-------------------------------------------------------------------------*/
		
		public function groupRecords($records) {
			if (!is_array($records) or empty($records)) return;
			
			$groups = array($this->get('element_name') => array());
			
			foreach ($records as $r) {
				$data = $r->getData($this->get('id'));
				
				$value = $data['value'];
				$handle = Lang::createHandle($value);
				
				if (!isset($groups[$this->get('element_name')][$handle])) {
					$groups[$this->get('element_name')][$handle] = array(
						'attr'		=> array(
							'value'		=> $value
						),
						'records'	=> array(),
						'groups'	=> array()
					);
				}
				
				$groups[$this->get('element_name')][$handle]['records'][] = $r;
			}
			
			return $groups;
		}
	}
	
?>