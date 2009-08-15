<?php
	
	if (!defined('__IN_SYMPHONY__')) die('<h2>Symphony Error</h2><p>You cannot directly access this file</p>');
	
	class FieldMemberName extends Field {
	/*-------------------------------------------------------------------------
		Definition:
	-------------------------------------------------------------------------*/
		
		public function __construct(&$parent) {
			parent::__construct($parent);
			
			$this->_name = 'Member Name';
			
			// Set defaults:
			$this->set('show_column', 'yes');
		}
		
		public function createTable() {
			$field_id = $this->get('id');
			
			return $this->_engine->Database->query("
				CREATE TABLE IF NOT EXISTS `tbl_entries_data_{$field_id}` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`entry_id` int(11) unsigned NOT NULL,
					`handle` varchar(255) default NULL,
					`value` text,
					`value_formatted` text,
					PRIMARY KEY  (`id`),
					KEY `entry_id` (`entry_id`),
					KEY `handle` (`handle`),
					FULLTEXT KEY `value` (`value`),
					FULLTEXT KEY `value_formatted` (`value_formatted`)
				)
			");
		}
		
		public function allowDatasourceParamOutput() {
			return true;
		}
		
		public function canFilter() {
			return true;
		}
		
		public function isSortable() {
			return true;
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
			
			$group = new XMLElement('div', null, array('class' => 'group'));
			$div = new XMLElement('div');
			
			$this->buildValidationSelect($div, $this->get('validator'), "fields[{$order}][validator]");
			
			$group->appendChild($div);
			$group->appendChild(
				$this->buildFormatterSelect($this->get('formatter'),
				"fields[{$order}][formatter]", 'Text Formatter')
			);
			
			$wrapper->appendChild($group);
			
			$this->appendShowColumnCheckbox($wrapper);						
		}
		
		public function commit() {
			$field_id = $this->get('id');
			
			if (!parent::commit() or $this->get('id') === false) return false;
			
			$fields = array(
				'field_id'		=> $this->get('id'),
				'formatter'		=> $this->get('formatter'),
				'validator'		=> $this->get('validator')
			);
			
			$this->_engine->Database->query("
				DELETE FROM
					`tbl_fields_membername`
				WHERE
					`field_id` = '$field_id'
				LIMIT 1
			");
			
			return $this->_engine->Database->insert($fields, 'tbl_fields_membername');
		}
		
	/*-------------------------------------------------------------------------
		Publish:
	-------------------------------------------------------------------------*/
		
		public function displayPublishPanel(&$wrapper, $data = null, $error = null, $prefix = null, $postfix = null) {
			$label = Widget::Label($this->get('label'));
			$name = $this->get('element_name');
			
			$input = Widget::Input(
				"fields{$prefix}[$name]{$postfix}",
				(strlen($data['value']) != 0 ? General::sanitize($data['value']) : null)
			);
			
			$label->appendChild($input);
			
			if ($error != null) {
				$label = Widget::wrapFormElementWithError($label, $error);
			}
			
			$wrapper->appendChild($label);
		}
		
	/*-------------------------------------------------------------------------
		Input:
	-------------------------------------------------------------------------*/
		
		public function applyFormatting($data) {
			if ($this->get('formatter')) {
				if (isset($this->_ParentCatalogue['entrymanager'])) {
					$tfm = $this->_ParentCatalogue['entrymanager']->formatterManager;
				} else {
					$tfm = new TextformatterManager($this->_engine);
				}
				
				$formatter = $tfm->create($this->get('formatter'));
				$formatted = $formatter->run($data);
				
			 	return preg_replace('/&(?![a-z]{0,4}\w{2,3};|#[x0-9a-f]{2,6};)/i', '&amp;', $formatted);
			}
			
			return null;		
		}
		
		public function validateRule($data) {			
			$rule = $this->get('validator');
			
			return ($rule ? General::validateString($data, $rule) : true);
		}
		
		public function checkPostFieldData($data, &$error, $entry_id = null) {
			$error = null; $label = $this->get('label');
			
			$field_id = $this->get('id');
			
			if (strlen(trim($data)) == 0) {
				$error = "'{$label}' is a required field.";
				
				return self::__MISSING_FIELDS__;
			}
			
			if (!$this->validateRule($data)) {
				$error = "'{$label}' contains invalid data. Please check the contents.";
				
				return self::__INVALID_FIELDS__;	
			}
			
			$result = $this->_engine->Database->query("
				SELECT
					f.id
				FROM
					`tbl_entries_data_{$field_id}` AS f
				WHERE
					f.value = '{$data}'
					AND f.entry_id != '{$entry_id}'
				LIMIT 1
			");
			
			if ($this->_engine->Database->numOfRows() > 0) {
				$error = "'{$label}' is already in use by another member.";
				
				return self::__INVALID_FIELDS__;	
			}

			return self::__OK__;
		}
		
		public function processRawFieldData($data, &$status, $simulate = false, $entry_id = null) {
			$status = self::__OK__;
			
			if (trim($data) == '') return array();
			
			$handle = Lang::createHandle($data);
			
			if (
				$this->get('formatter') == 'none'
				or !($formatted = $this->applyFormatting($data))
			) {
				$formatted = General::sanitize($data);
			}
			
			$result = array(
				'handle'			=> $handle,
				'value'				=> $data,
				'value_formatted'	=> $formatted
			);
			
			return $result;
		}
		
	/*-------------------------------------------------------------------------
		Output:
	-------------------------------------------------------------------------*/
		
		public function appendFormattedElement(&$wrapper, $data, $encode = false) {
			$element = new XMLElement($this->get('element_name'));
			$element->setAttribute('handle', $data['handle']);
			$element->setValue($data['value_formatted']);
			$wrapper->appendChild($element);
		}
		
		public function prepareTableValue($data, XMLElement $link = null) {
			if (empty($data)) return;
			
			return parent::prepareTableValue(
				array(
					'value'		=> $data['value_formatted']
				), $link
			);
		}
		
	/*-------------------------------------------------------------------------
		Filtering:
	-------------------------------------------------------------------------*/
		
		public function buildDSRetrivalSQL($data, &$joins, &$where, $andOperation = false) {
			$field_id = $this->get('id');
			
			if (preg_match('/^regexp:(.*)/i', $data[0], $matches)) {
				$data = $this->cleanValue($matches[1]);
				$this->_key++;
				$joins .= "
					LEFT JOIN
						`tbl_entries_data_{$field_id}` AS t{$field_id}_{$this->_key}
						ON (e.id = t{$field_id}_{$this->_key}.entry_id)
				";
				$where .= "
					AND (
						t{$field_id}_{$this->_key}.value REGEXP '{$data}'
						OR t{$field_id}_{$this->_key}.handle REGEXP '{$data}'
					)
				";
				
			} elseif (preg_match('/^partial:(.*)/i', $data[0], $matches)) {
				$data = $this->cleanValue($matches[1]);
				$this->_key++;
				$joins .= "
					LEFT JOIN
						`tbl_entries_data_{$field_id}` AS t{$field_id}_{$this->_key}
						ON (e.id = t{$field_id}_{$this->_key}.entry_id)
				";
				$where .= "
					AND MATCH (t{$field_id}_{$this->_key}.value) AGAINST ('{$data}' IN BOOLEAN MODE)
				";
				
			} elseif ($andOperation) {
				foreach ($data as $value) {
					$this->_key++;
					$value = $this->cleanValue($value);
					$joins .= "
						LEFT JOIN
							`tbl_entries_data_{$field_id}` AS t{$field_id}_{$this->_key}
							ON (e.id = t{$field_id}_{$this->_key}.entry_id)
					";
					$where .= "
						AND (
							t{$field_id}_{$this->_key}.value = '{$value}'
							OR t{$field_id}_{$this->_key}.handle = '{$value}'
						)
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
					AND (
						t{$field_id}_{$this->_key}.value IN ('{$data}')
						OR t{$field_id}_{$this->_key}.handle IN ('{$data}')
					)
				";
			}
			
			return true;
		}
	}
	
?>