<?php
	
	if (!defined('__IN_SYMPHONY__')) die('<h2>Symphony Error</h2><p>You cannot directly access this file</p>');
	
	class FieldMemberGroup extends Field {
		protected $_driver = null;
		
	/*-------------------------------------------------------------------------
		Definition:
	-------------------------------------------------------------------------*/
		
		public function __construct(&$parent) {
			parent::__construct($parent);
			
			$this->_name = 'Member Group';
			$this->_driver = $this->_engine->ExtensionManager->create('frontendmembermanager');
			
			// Set defaults:
			$this->set('show_column', 'yes');
		}
		
		public function createTable() {
			$field_id = $this->get('id');
			
			return $this->_engine->Database->query("
				CREATE TABLE IF NOT EXISTS `tbl_entries_data_{$field_id}` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`entry_id` int(11) unsigned NOT NULL,
					`value` int(11) unsigned NOT NULL,
					PRIMARY KEY  (`id`),
					KEY `entry_id` (`entry_id`),
					KEY `value` (`value`)
				)
			");
		}
		
		public function isSortable() {
			return true;
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
		Settings:
	-------------------------------------------------------------------------*/
		
		public function findFields() {
			$sectionManager = new SectionManager($this->_engine);
		  	$sections = $sectionManager->fetch(null, 'ASC', 'name');
			$groups = $options = array();
			
			if (is_array($sections) and !empty($sections)) {
				foreach ($sections as $section) {
					$groups[$section->get('id')] = array(
						'fields'	=> $section->fetchFields(),
						'section'	=> $section
					);
				}
			}
			
			foreach ($groups as $group) {
				if (!is_array($group['fields'])) continue;
				
				$fields = array();
				
				foreach ($group['fields'] as $field) {
					if (
						$field->get('type') == 'groupname'
					) {
						$selected = $this->get('child_field_id') == $field->get('id');
						$fields[] = array(
							$field->get('id'), $selected, $field->get('label')
						);
					}
				}
				
				if (empty($fields)) continue;
				
				$options[] = array(
					'label'		=> $group['section']->get('name'),
					'options'	=> $fields
				);
			}
			
			return $options;
		}
		
		public function displaySettingsPanel(&$wrapper, $errors = null) {
			parent::displaySettingsPanel($wrapper, $errors);
			
			$field_id = $this->get('id');
			$order = $this->get('sortorder');
			
		// Relation -----------------------------------------------------------
		
			$group = new XMLElement('div');
			$group->setAttribute('class', 'group');
			
			$label = Widget::Label(__('Options'));
			
			$label->appendChild(Widget::Select(
				"fields[{$order}][parent_field_id]", $this->findFields()
			));
			
			if (isset($errors['parent_field_id'])) {
				$label = Widget::wrapFormElementWithError($label, $errors['parent_field_id']);
			}
			
			$group->appendChild($label);
			$wrapper->appendChild($group);
			$this->appendShowColumnCheckbox($wrapper);
		}
		
		public function commit() {
			$field_id = $this->get('id');
			$handle = $this->handle();
			
			if (!parent::commit() or $field_id === false) return false;
			
			$parent_field_id = $this->get('parent_field_id');
			$parent_section_id = $this->_engine->Database->fetchVar('parent_section', 0, "
				SELECT
					f.parent_section
				FROM
					`tbl_fields` AS f
				WHERE
					f.id = {$parent_field_id}
				LIMIT 1
			");
			
			$fields = array(
				'field_id'			=> $field_id,
				'parent_section_id'	=> $parent_section_id,
				'parent_field_id'	=> $parent_field_id
			);
			
			$this->_engine->Database->query("
				DELETE FROM
					`tbl_fields_{$handle}`
				WHERE
					`field_id` = '$field_id'
				LIMIT 1
			");
			
			$this->removeSectionAssociation($field_id);
			$this->createSectionAssociation(null, $field_id, $parent_field_id, $parent_section_id);
			
			return $this->_engine->Database->insert($fields, "tbl_fields_{$handle}");
		}
		
	/*-------------------------------------------------------------------------
		Publish:
	-------------------------------------------------------------------------*/
		
		public function findOptions() {
			$fieldManager = new FieldManager($this->_engine);
			$field = $fieldManager->fetch($this->get('parent_field_id'));
		  	$entryManager = new EntryManager($this->_engine);
		  	$entries = $entryManager->fetch(null, $this->get('parent_section_id'));
		  	$options = array();
		  	
		  	foreach ($entries as $entry) {
		  		$data = $entry->getData($this->get('parent_field_id'));
		  		$options[] = array(
		  			$entry->get('id'), false, General::sanitize($field->prepareTableValue($data))
		  		);
		  	}
		  	
		  	return $options;
		}
		
		public function displayPublishPanel(&$wrapper, $data = null, $flagWithError = null, $fieldnamePrefix = null, $fieldnamePostfix = null) {
			$label = Widget::Label($this->get('label'));
			$name = $this->get('element_name');
			$parent_section_id = $this->get('parent_section_id');
			$parent_field_id = $this->get('parent_field_id');
			
			$value = (integer)((integer)$data['value'] > 0 ? $data['value'] : $default_id);
			$options = $this->findOptions();
			
			foreach ($options as $index => $option) {
				$options[$index][1] = $option[0] == $value;
			}
			
			$label->appendChild(Widget::Select(
				"fields{$fieldnamePrefix}[{$name}]{$fieldnamePostfix}", $options
			));
			
			if ($flagWithError != null) {
				$wrapper->appendChild(Widget::wrapFormElementWithError($label, $flagWithError));
				
			} else {
				$wrapper->appendChild($label);
			}
		}
		
	/*-------------------------------------------------------------------------
		Input:
	-------------------------------------------------------------------------*/
		
		public function processRawFieldData($data, &$status, $simulate = false, $entry_id = null) {
			$status = self::__OK__;
			
			return array(
				'value'		=> General::sanitize($data),
			);
		}
		
	/*-------------------------------------------------------------------------
		Output:
	-------------------------------------------------------------------------*/
		
		public function prepareTableValue($data, XMLElement $link = null) {
			$section_id = $this->get('section_id');
			$entry_id = @(integer)$data['value'];
			
			$link_id = $this->Database->fetchVar('id', 0, "
				SELECT
					f.id
				FROM
					`tbl_fields` f
				WHERE
					f.type = 'groupname'
					AND f.parent_section = {$section_id}
			");
			$value = $this->Database->fetchVar('value', 0, "
				SELECT
					f.value
				FROM
					`tbl_entries_data_{$link_id}` f
				WHERE
					f.entry_id = {$entry_id}
				LIMIT 1
			");
			
			return parent::prepareTableValue(array('value' => $value), $link);
		}
		
	/*-------------------------------------------------------------------------
		Sorting:
	-------------------------------------------------------------------------*/
		
		public function buildSortingSQL(&$joins, &$where, &$sort, $order = 'ASC'){
			$field_id = $this->get('id');
			$joins .= "
				INNER JOIN `tbl_entries_data_{$field_id}` AS ed
				ON (e.id = ed.entry_id)
			";
			$sort = 'ORDER BY ' . (strtolower($order) == 'random' ? 'RAND()' : "ed.value {$order}");
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