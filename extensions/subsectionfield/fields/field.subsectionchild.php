<?php
	
	if (!defined('__IN_SYMPHONY__')) die('<h2>Symphony Error</h2><p>You cannot directly access this file</p>');
	
	class FieldSubSectionChild extends Field {
		protected $_driver = null;
		static $entries = array();
		static $errors = array();
		
	/*-------------------------------------------------------------------------
		Definition:
	-------------------------------------------------------------------------*/
		
		public function __construct(&$parent) {
			parent::__construct($parent);
			
			$this->_name = 'Sub Section Child';
			$this->_driver = $this->_engine->ExtensionManager->create('subsectionfield');
			
			// Set defaults:
			$this->set('show_column', 'yes');
		}
		
		public function createTable() {
			$field_id = $this->get('id');
			
			return $this->_engine->Database->query("
				CREATE TABLE IF NOT EXISTS `tbl_entries_data_{$field_id}` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`entry_id` int(11) unsigned NOT NULL,
					`parent_entry_id` int(11) unsigned default NULL,
					PRIMARY KEY (`id`),
					KEY `entry_id` (`entry_id`),
					KEY `parent_entry_id` (`parent_entry_id`)
				)
			");
		}
		
		public function canFilter() {
			return true;
		}

		public function allowDatasourceParamOutput() {
			return true;
		}
		
		public function isSortable() {
			return true;
		}
		
	/*-------------------------------------------------------------------------
		Utilities:
	-------------------------------------------------------------------------*/
		
		protected function getData() {
			$handle = $this->get('element_name');
			$data = $_POST['fields'][$handle];
			
			if (isset($_FILES['fields'])) {
				foreach ($_FILES['fields'] as $key => $entries) {
					foreach ($entries[$handle]['entry'] as $entry => $fields) {
						foreach ($fields as $field => $value) {
							$data['entry'][$entry][$field][$key] = $value;
						}
					}
				}
			}
			
			return $data;
		}
		
		protected function getFields() {
			$sectionManager = new SectionManager($this->_engine);
			$section = $sectionManager->fetch($this->get('section_id'));
			
			if (empty($section)) return null;
			
			return $section->fetchFields();
		}
		
	/*-------------------------------------------------------------------------
		Settings:
	-------------------------------------------------------------------------*/
		
		public function displaySettingsPanel(&$wrapper, $errors = null) {
			parent::displaySettingsPanel($wrapper, $errors);
			
			$this->appendShowColumnCheckbox($wrapper);
		}
		
		public function commit() {
			if (!parent::commit() or $field_id === false) return false;
			
			$field_id = $this->get('id');
			$handle = $this->handle();
			
			header('content-type: text/plain');
			
			// Find existing values:
			if (!isset($this->_fields['parent_section_id'])) {
				$data = $this->_engine->Database->fetchRow(0, "
					SELECT
						f.*
					FROM
						`tbl_fields_{$handle}` AS f
					WHERE
						f.field_id = '$field_id'
					LIMIT 1
				");
				
				$this->set('parent_section_id', $data['parent_section_id']);
				$this->set('parent_field_id', $data['parent_field_id']);
			}
			
			$fields = array(
				'field_id'			=> $this->get('id'),
				'parent_section_id'	=> $this->get('parent_section_id'),
				'parent_field_id'	=> $this->get('parent_field_id')
			);
			
			// Cleanup:
			$this->_engine->Database->query("
				DELETE FROM
					`tbl_fields_{$handle}`
				WHERE
					`field_id` = '$field_id'
				LIMIT 1
			");
			
			// Create:
			if (!$this->_engine->Database->insert($fields, "tbl_fields_{$handle}")) return false;
			
			return true;
		}
		
	/*-------------------------------------------------------------------------
		Publish:
	-------------------------------------------------------------------------*/
		
		public function displayPublishPanel(&$wrapper, $data = null, $error = null, $prefix = null, $postfix = null, $entry_id = null) {
			return null;
		}
		
		public function processRawFieldData($data, &$status, $simulate = false, $entry_id = null) {
			return $data;
		}
		
	/*-------------------------------------------------------------------------
		Output:
	-------------------------------------------------------------------------*/
		
		public function getParameterPoolValue($data) {
			if (!is_array($data['parent_entry_id'])) {
				$data['parent_entry_id'] = array($data['parent_entry_id']);
			}
			
			return implode(', ', $data['parent_entry_id']);
		}
		
		public function fetchIncludableElements() {
			return array(
				$this->get('element_name') . ': items',
				$this->get('element_name') . ': entries'
			);
		}
		
		public function appendFormattedElement(&$wrapper, $data, $encode = false, $mode = null) {
			$sectionManager = new SectionManager($this->_engine);
			$entryManager = new EntryManager($this->_engine);
			$parent_section_id = $this->get('parent_section_id');
			$section = $sectionManager->fetch($parent_section_id);
			
			if (!is_array($data['parent_entry_id'])) {
				$data['parent_entry_id'] = array($data['parent_entry_id']);
			}
			
			$list = new XMLElement($this->get('element_name'));
			$list->setAttribute('mode', $mode);
			
			// No section or relations:
			if (empty($section) or empty($data['parent_entry_id'])) return;
			
			$entries = $entryManager->fetch($data['parent_entry_id'], $parent_section_id);
			
			// List:
			if ($mode == null or $mode == 'items') {
				$field = @current($section->fetchVisibleColumns());
				
				foreach ($entries as $count => $entry) {
					if (empty($entry)) continue;
					
					$value = $field->prepareTableValue(
						$entry->getData($field->get('id'))
					);
					$handle = Lang::createHandle($value);
					
					$item = new XMLElement('item', $value);
					$item->setAttribute('id', $entry->get('id'));
					$item->setAttribute('handle', $handle);
					
					$list->appendChild($item);
					$list->setAttribute('entries', $count + 1);
				}
				
			// Full:
			} else if ($mode == 'entries') {
				$list->appendChild(new XMLElement(
					'section', $section->get('name'),
					array(
						'id'		=> $section->get('id'),
						'handle'	=> $section->get('handle')
					)
				));
				
				foreach ($entries as $count => $entry) {
					$associated = $entry->fetchAllAssociatedEntryCounts();
					$data = $entry->getData();
					
					$item = new XMLElement('entry');
					
					if (is_array($associated) and !empty($associated)) {
						foreach ($associated as $section => $count) {
							$handle = $this->_engine->Database->fetchVar('handle', 0, "
								SELECT
									s.handle
								FROM
									`tbl_sections` AS s
								WHERE
									s.id = '{$section}'
								LIMIT 1
							");
							
							$item->setAttribute($handle, (string)$count);
						}
					}
					
					// Add fields:
					foreach ($data as $field_id => $values) {
						if ($field_id == $this->get('parent_field_id')) continue;
						
						$field =& $entryManager->fieldManager->fetch($field_id);
						$field->appendFormattedElement($item, $values, false);
					}
					
					$list->appendChild($item);
					$list->setAttribute('entries', $count + 1);
				}
			}
			
			$wrapper->appendChild($list);
		}
		
		public function prepareTableValue($data, XMLElement $link = null) {
			$value = 'None';
			
			if (is_array($data)) $value = $this->findTableValue($data);
			
			if ($link) {
				$value = $link->setValue(strip_tags($value));
			}
			
			return $value;
		}
		
		protected function findTableValue($data) {
			$parent_section_id = $this->get('parent_section_id');
			$parent_entry_id = $data['parent_entry_id'];
			
			if (!empty($parent_entry_id)) {
				$sectionManager = new SectionManager($this->_engine);
				$entryManager = new EntryManager($this->_engine);
				$section = $sectionManager->fetch($parent_section_id);
				$entry = @current($entryManager->fetch($parent_entry_id, $parent_section_id));
				
				if ($entry and $section) {
					$field = @current($section->fetchVisibleColumns());
					$data = $entry->getData($field->get('id'));
					$link = Widget::Anchor(
						null, URL . "/symphony/publish/" . $section->get('handle') . "/edit/{$parent_entry_id}/"
					);
					$value = $field->prepareTableValue($data, $link);
					
					return $value;
				}
			}
			
			return 'None';
		}
		
	/*-------------------------------------------------------------------------
		Filtering:
	-------------------------------------------------------------------------*/

		public function buildDSRetrivalSQL($data, &$joins, &$where, $andOperation = false) {
			$field_id = $this->get('id');
			
			if ($andOperation) {
				foreach ($data as $value) {
					$this->_key++;
					$value = $this->cleanValue($value);
					$joins .= "
						LEFT JOIN
							`tbl_entries_data_{$field_id}` AS t{$field_id}_{$this->_key}
							ON (e.id = t{$field_id}_{$this->_key}.entry_id)
					";
					$where .= "
						AND t{$field_id}_{$this->_key}.relation_id = '{$value}'
					";
				}
				
			} else {
				if (!is_array($data)) $data = array($data);
				
				foreach ($data as &$value) {
					$value = $this->cleanValue($value);
				}
				
				$this->_key++;
				$data = implode("', '", $data);
				$joins .= "
					LEFT JOIN
						`tbl_entries_data_{$field_id}` AS t{$field_id}_{$this->_key}
						ON (e.id = t{$field_id}_{$this->_key}.entry_id)
				";
				$where .= "
					AND t{$field_id}_{$this->_key}.relation_id IN ('{$data}')
				";
			}

			return true;
		}
		
	/*-------------------------------------------------------------------------
		Sorting:
	-------------------------------------------------------------------------*/

		public function buildSortingSQL(&$joins, &$where, &$sort, $order = 'ASC') {
			$field_id = $this->get('id');
			
			$joins .= "
				INNER JOIN
					`tbl_entries_data_{$field_id}` AS ed
					ON (e.id = ed.entry_id)
			";
			$sort = 'ORDER BY ' . (strtolower($order) == 'random' ? 'RAND()' : "ed.parent_entry_id {$order}");
		}
		
	/*-------------------------------------------------------------------------
		Linking:
	-------------------------------------------------------------------------*/
		
		public function fetchAssociatedEntrySearchValue($data) {
			if (!is_array($data)) return $data;
			
			$relation_id = $this->get('relation_id');
			$handle = $this->cleanValue($data['handle']);
			
 			return $this->_engine->Database->fetchVar('entry_id', 0, "
				SELECT
					f.entry_id
				FROM
					`tbl_entries_data_{$relation_id}` AS f
				WHERE
					f.handle = '{$handle}' 
				LIMIT 1
			");
		}
		
		public function fetchAssociatedEntryCount($value) {
			$field_id = $this->get('id');
			
			return $this->_engine->Database->fetchVar('count', 0, "
				SELECT
					count(f.id) AS `count`
				FROM
					`tbl_entries_data_{$field_id}` AS f
				WHERE
					f.parent_entry_id = '$value'
			");
		}

		public function fetchAssociatedEntryIDs($value) {
			$field_id = $this->get('id');
			
			return $this->_engine->Database->fetchCol('entry_id', "
				SELECT
					f.entry_id
				FROM
					`tbl_entries_data_{$field_id}` AS f
				WHERE
					f.parent_entry_id = '{$value}'
			");
		}
	}
	
?>