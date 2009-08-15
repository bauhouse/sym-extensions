<?php
	
	if (!defined('__IN_SYMPHONY__')) die('<h2>Symphony Error</h2><p>You cannot directly access this file</p>');
	
	class FieldSubSection extends Field {
		protected $_driver = null;
		static $entries = array();
		static $errors = array();
		
	/*-------------------------------------------------------------------------
		Definition:
	-------------------------------------------------------------------------*/
		
		public function __construct(&$parent) {
			parent::__construct($parent);
			
			$this->_name = 'Sub Section';
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
					`child_entry_id` int(11) unsigned default NULL,
					PRIMARY KEY (`id`),
					KEY `entry_id` (`entry_id`),
					KEY `child_entry_id_id` (`child_entry_id`)
				)
			");
		}
		
		public function canFilter() {
			return true;
		}

		public function allowDatasourceParamOutput() {
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
			$section = $sectionManager->fetch($this->get('child_section_id'));
			
			if (empty($section)) return null;
			
			return $section->fetchFields();
		}
		
	/*-------------------------------------------------------------------------
		Settings:
	-------------------------------------------------------------------------*/
		
		public function findDefaults(&$fields) {
			if (!isset($fields['allow_multiple'])) $fields['allow_multiple'] = 'yes';
		}
		
		public function findOptions() {
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
						$field->get('type') == 'subsectionchild'
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
				"fields[{$order}][child_field_id]", $this->findOptions()
			));
			
			if (isset($errors['child_field_id'])) {
				$label = Widget::wrapFormElementWithError($label, $errors['child_field_id']);
			}
			
			$group->appendChild($label);
			$wrapper->appendChild($group);
			
		// Allow Multiple -----------------------------------------------------
			
			$label = Widget::Label();
			$input = Widget::Input(
				"fields[{$order}][allow_multiple]", 'yes', 'checkbox'
			);
			
			if ($this->get('allow_multiple') == 'yes') $input->setAttribute('checked', 'checked');
			
			$label->setValue($input->generate() . ' Allow multiple entries to be created');
			
			$wrapper->appendChild($label);
			$this->appendShowColumnCheckbox($wrapper);
		}
		
		public function commit() {
			if (!parent::commit() or $field_id === false) return false;
			
			$field_id = $this->get('id');
			$handle = $this->handle();
			
			$child_field_id = $this->get('child_field_id');
			$child_section_id = $this->_engine->Database->fetchVar('parent_section', 0, "
				SELECT
					f.parent_section
				FROM
					`tbl_fields` AS f
				WHERE
					f.id = {$child_field_id}
				LIMIT 1
			");
			
			$fields = array(
				'field_id'			=> $this->get('id'),
				'child_section_id'	=> $child_section_id,
				'child_field_id'	=> $child_field_id,
				'allow_multiple'	=> ($this->get('allow_multiple') ? $this->get('allow_multiple') : 'no')
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
			
			// Update child field:
			$fieldManager = new FieldManager($this->_engine);
			$field = $fieldManager->fetch($child_field_id);
			
			if ($field) {
				$field->set('parent_section_id', $this->get('parent_section'));
				$field->set('parent_field_id', $field_id);
				$field->commit();
			}
			
			return true;
		}
		
	/*-------------------------------------------------------------------------
		Publish:
	-------------------------------------------------------------------------*/
		
		public function displayPublishPanel(&$wrapper, $data = null, $error = null, $prefix = null, $postfix = null, $entry_id = null) {
			$this->_engine->Page->addStylesheetToHead(URL . '/extensions/subsectionfield/assets/form.css', 'screen');
			
			$entryManager = new EntryManager($this->_engine);
			$fields = $this->getFields();
			$handle = $this->get('element_name');
			
		// Not ready ----------------------------------------------------------
			
			if (empty($entry_id)) {
				$label = new XMLElement('p', $this->get('label'));
				
				$help = new XMLElement('p');
				$help->setAttribute('class', 'help');
				$help->setValue('You must save this entry before you can edit this sub-section.');
				
				$wrapper->appendChild($label);
				$wrapper->appendChild($help);
				
				return;
			}
			
		// Entries ------------------------------------------------------------
			
			$label = new XMLElement('h3', $this->get('label'));
			
			$div = new XMLElement('div');
			$div->setAttribute('class', 'subsectionfield-single');
			$div->appendChild($label);
			
			if ($this->get('allow_multiple') == 'yes') {
				$div->setAttribute('class', 'subsection subsectionfield-multiple');
			}
			
			$ol = new XMLElement('ol');
			
			// Editing:
			if (isset($_POST['fields'][$handle]['entry'])) {
				foreach ($_POST['fields'][$handle]['entry'] as $order => $entry_data) {
					$entry_id = $_POST['fields'][$handle]['entry_id'][$order];
					
					if (empty($entry_id)) {
						$entry = $entryManager->create();
						
					} else {
						$entry = @current($entryManager->fetch($entry_id, $this->get('child_section_id')));
					}
					
					$entry->set('child_section_id', $this->get('child_section_id'));
					$entry->setDataFromPost($entry_data, $error);
					
					$this->displayItem($ol, $order, $entry, $fields);
				}
				
			// Viewing:
			} else if (!empty($data['child_entry_id'])) {
				$entries = $entryManager->fetch($data['child_entry_id'], $this->get('child_section_id'));
				
				foreach ($entries as $order => $entry) {
					$entry_data = array();
					
					foreach ($fields as $field) {
						$field_handle = Lang::createHandle($field->get('label'));
						$entry_data[$field_handle] = $entry->getData($field->get('id'));
					}
					
					$this->displayItem($ol, $order, $entry, $fields);
				}
				
				if (empty($entries) and $this->get('allow_multiple') != 'yes') {
					$this->displayItem($ol, -1, $entryManager->create(), $fields);
				}
				
			} else if ($this->get('allow_multiple') != 'yes') {
				$this->displayItem($ol, -1, $entryManager->create(), $fields);
			}
			
			if ($this->get('allow_multiple') == 'yes') {
				$this->displayItem($ol, -1, $entryManager->create(), $fields);
			}
			
			$div->appendChild($ol);
			$wrapper->appendChild($div);
		}
		
		protected function displayItem($wrapper, $order, $entry, $fields) {
			$handle = $this->get('element_name');
			
			$item = new XMLElement('li');
			$item->appendChild(new XMLElement('h4', __('Entry')));
			
			$input = Widget::Input(
				"fields[{$handle}][entry_id][{$order}]",
				$entry->get('id')
			);
			$input->setAttribute('type', 'hidden');
			$item->appendChild($input);
			
			$group = new XMLElement('div');
			
			$left = new XMLElement('div');
			$right = new XMLElement('div');
			
			if ($order < 0) {
				$item->setAttribute('class', 'template');
			}
			
			foreach ($fields as $field) {
				$name = "[{$handle}][entry][{$order}]";
				$data = $entry->getData($field->get('id'));
				$error = self::$errors[$this->get('id')][$order][$field->get('id')];
				
				if ($this->get('location') != 'main') {
					$container = $group;
					
				} else if ($field->get('location') == 'main') {
					$container = $left;
					
				} else {
					$container = $right;
				}
				
				$field->displayPublishPanel($container, $data, $error, $name, null, $entry->get('id'));
			}
			
			if ($this->get('location') == 'main') {
				$group->setAttribute('class', 'group');
				$group->appendChild($left);
				$group->appendChild($right);
			}
			
			$item->appendChild($group);
			$wrapper->appendChild($item);
		}
		
	/*-------------------------------------------------------------------------
		Input:
	-------------------------------------------------------------------------*/
		
		public function checkPostFieldData($data, &$error = null, $entry_id = null) {
			$entryManager = new EntryManager($this->_engine);
			$fieldManager = new FieldManager($this->_engine);
			$field = $fieldManager->fetch($this->get('child_field_id'));
			$status = self::__OK__;
			
			$data = $this->getData();
			
			// Create:
			foreach ($data['entry'] as $index => $entry_data) {
				$existing_id = $data['entry_id'][$index];
				
				if (empty($existing_id)) {
					$entry = $entryManager->create();
					$entry->set('section_id', $this->get('child_section_id'));
					$entry->set('author_id', $this->_engine->Author->get('id'));
					$entry->set('creation_date', DateTimeObj::get('Y-m-d H:i:s'));
					$entry->set('creation_date_gmt', DateTimeObj::getGMT('Y-m-d H:i:s'));
					
				} else {
					$entry = @current($entryManager->fetch($existing_id, $this->get('child_section_id')));
				}
				
				// Create link:
				if ($field) $entry_data[$field->get('element_name')] = array(
					'parent_entry_id'		=> $entry_id
				);
				
				// Validate:
				if (__ENTRY_FIELD_ERROR__ == $entry->checkPostData($entry_data, $errors)) {
					if (!empty($errors)) {
						self::$errors[$this->get('id')][$index] = $errors;
					}
					
					$status = self::__INVALID_FIELDS__;
					
				} elseif (__ENTRY_OK__ != $entry->setDataFromPost($entry_data, $error)) {
					$status = self::__INVALID_FIELDS__;
				}
				
				self::$entries[$this->get('id')][] = $entry;
			}
			
			return $status;
		}
		
		public function processRawFieldData($data, &$status, $simulate = false, $entry_id = null) {
			$status = self::__OK__;
			
			if (empty($data)) return null;
			
			$result = array();
			
			// Save entries:
			if (empty(self::$errors[$this->get('id')])) {
				foreach (self::$entries[$this->get('id')] as $entry) {
					$entry->commit();
					$result['child_entry_id'][] = $entry->get('id');
				}
				
			} else {
				$status = self::__ERROR__;
			}
			
			return $result;
		}
		
	/*-------------------------------------------------------------------------
		Output:
	-------------------------------------------------------------------------*/
		
		public function getParameterPoolValue($data) {
			if (!is_array($data['child_entry_id'])) {
				$data['child_entry_id'] = array($data['child_entry_id']);
			}
			
			return implode(', ', $data['child_entry_id']);
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
			$child_section_id = $this->get('child_section_id');
			$section = $sectionManager->fetch($child_section_id);
			
			if (!is_array($data['child_entry_id'])) {
				$data['child_entry_id'] = array($data['child_entry_id']);
			}
			
			$list = new XMLElement($this->get('element_name'));
			$list->setAttribute('mode', $mode);
			
			// No section or relations:
			if (empty($section) or empty($data['child_entry_id'])) return;
			
			$entries = $entryManager->fetch($data['child_entry_id'], $child_section_id);
			
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
						if ($field_id == $this->get('child_field_id')) continue;
						
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
			$entryManager = new EntryManager($this->_engine);
			$sectionManager = new SectionManager($this->_engine);
			$child_section_id = $this->get('child_section_id');
			$section = $sectionManager->fetch($child_section_id);
			$field = @current($section->fetchVisibleColumns());
			$values = array(); $length = 0; $result = ''; $limit = 30;
			
			if (!is_array($data['child_entry_id'])) {
				$data['child_entry_id'] = array($data['child_entry_id']);
			}
			
			$entries = $entryManager->fetch($data['child_entry_id'], $child_section_id);
			
			if (is_array($entries)) {
				// Find entry names:
				foreach ($entries as $entry) {
					if (empty($entry) or empty($field)) continue;
					
					$data = $entry->getData($field->get('id'));
					$value = $field->prepareTableValue($data);
					$length += strlen($value);
					
					if ($length >= $limit) {
						$values[] = (object)array(
							'content'	=> '...',
							'link'		=> ''
						);
						
						break;
					}
					
					$values[] = (object)array(
						'content'	=> strip_tags($value),
						'link'		=> URL . '/symphony/publish/' . $section->get('handle') . '/edit/' . $entry->get('id') . '/'
					);
				}
			}
			
			// Build final value:
			foreach ($values as $index => $current) {
				if ($index) $result .= ', ';
				
				if ($current->link) {
					$value = new XMLElement('a');
					$value->setAttribute('href', $current->link);
					$value->setValue($current->content);
					
					$result .= $value->generate();
					
				} else {
					$result .= $current->content;
				}
			}
			
			if (empty($result)) {
				return parent::prepareTableValue($result, $link);
			}
			
			if ($link) {
				$link->setValue(strip_tags($result));
				
				return $link->generate();
				
			} else {
				return $result;
			}
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
						AND t{$field_id}_{$this->_key}.child_entry_id = '{$value}'
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
					AND t{$field_id}_{$this->_key}.child_entry_id IN ('{$data}')
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
			$sort = 'ORDER BY ' . (strtolower($order) == 'random' ? 'RAND()' : "`ed`.`child_entry_id` {$order}");
		}
		
	/*-------------------------------------------------------------------------
		Linking:
	-------------------------------------------------------------------------*/
		
		public function fetchAssociatedEntrySearchValue($data) {
			if (!is_array($data)) return $data;
			
			$child_field_id = $this->get('child_field_id');
			$handle = $this->cleanValue($data['handle']);
			
 			return $this->_engine->Database->fetchVar('entry_id', 0, "
				SELECT
					f.entry_id
				FROM
					`tbl_entries_data_{$child_field_id}` AS f
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
					f.child_entry_id = '$value'
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
					f.child_entry_id = '{$value}'
			");
		}	
	}
	
?>