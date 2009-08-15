<?php
	
	if (!defined('__IN_SYMPHONY__')) die('<h2>Symphony Error</h2><p>You cannot directly access this file</p>');
	
	require_once(TOOLKIT . '/class.xsltprocess.php');
	
	class FieldTextBox extends Field {
		const DISABLE_PROPOGATION = 1;
		
		protected $_sizes = array();
		protected $_driver = null;
		
	/*-------------------------------------------------------------------------
		Definition:
	-------------------------------------------------------------------------*/
		
		public function __construct(&$parent) {
			parent::__construct($parent);
			
			$this->_name = 'Text Box';
			$this->_required = true;
			$this->_driver = $this->_engine->ExtensionManager->create('textboxfield');
			
			// Set defaults:
			$this->set('show_column', 'yes');
			$this->set('size', 'medium');
			$this->set('required', 'yes');
			
			$this->_sizes = array(
				array('single', false, __('Single Line')),
				array('small', false, __('Small Box')),
				array('medium', false, __('Medium Box')),
				array('large', false, __('Large Box')),
				array('huge', false, __('Huge Box'))
			);
		}
		
		public function createTable() {
			$field_id = $this->get('id');
			
			return $this->_engine->Database->query("
				CREATE TABLE IF NOT EXISTS `tbl_entries_data_{$field_id}` (
					`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
					`entry_id` INT(11) UNSIGNED NOT NULL,
					`handle` VARCHAR(255) DEFAULT NULL,
					`value` TEXT DEFAULT NULL,
					`value_formatted` TEXT DEFAULT NULL,
					`word_count` INT(11) UNSIGNED DEFAULT NULL,
					PRIMARY KEY (`id`),
					KEY `entry_id` (`entry_id`),
					FULLTEXT KEY `value` (`value`),
					FULLTEXT KEY `value_formatted` (`value_formatted`)
				)
			");
		}
		
		public function allowDatasourceOutputGrouping() {
			return true;
		}
		
		public function allowDatasourceParamOutput() {
			return true;
		}
		
		public function canFilter() {
			return true;
		}
		
		public function canPrePopulate() {
			return true;
		}
		
		public function isSortable() {
			return true;
		}
		
		public function createHandle($value) {
			return Lang::createHandle(strip_tags(html_entity_decode($value)));
		}
		
	/*-------------------------------------------------------------------------
		Settings:
	-------------------------------------------------------------------------*/
		
		public function displaySettingsPanel(&$wrapper, $errors = null, $append_before = null, $append_after = null) {
			parent::displaySettingsPanel($wrapper, $errors);
			
			$order = $this->get('sortorder');
			
		/*---------------------------------------------------------------------
			Append before
		---------------------------------------------------------------------*/
			
			if (!is_null($append_before)) $wrapper->appendChild($append_before);
			
		/*---------------------------------------------------------------------
			Expression
		---------------------------------------------------------------------*/
			
			$group = new XMLElement('div');
			$group->setAttribute('class', 'group');
			
			$values = $this->_sizes;
			
			foreach ($values as &$value) {
				$value[1] = $value[0] == $this->get('size');
			}
			
			$label = Widget::Label('Size');
			$label->appendChild(Widget::Select(
				"fields[{$order}][size]", $values
			));
			
			$group->appendChild($label);
			
		/*---------------------------------------------------------------------
			Text Formatter
		---------------------------------------------------------------------*/
			
			$group->appendChild($this->buildFormatterSelect(
				$this->get('formatter'),
				"fields[{$order}][formatter]",
				'Text Formatter'
			));
			$wrapper->appendChild($group);
			
		/*---------------------------------------------------------------------
			Validator
		---------------------------------------------------------------------*/
			
			$this->buildValidationSelect(
				$wrapper, $this->get('validator'), "fields[{$order}][validator]"
			);
			
		/*---------------------------------------------------------------------
			Append after
		---------------------------------------------------------------------*/
			
			if (!is_null($append_after)) $wrapper->appendChild($append_after);
			
			$this->appendRequiredCheckbox($wrapper);
			$this->appendShowColumnCheckbox($wrapper);
		}
		
		public function commit($propogate = null) {
			if (!parent::commit()) return false;
			
			if ($propogate == self::DISABLE_PROPOGATION) return true;
			
			$id = $this->get('id');
			$handle = $this->handle();
			
			if ($id === false) return false;
			
			$fields = array(
				'field_id'			=> $id,
				'formatter'			=> $this->get('formatter'),
				'size'				=> $this->get('size'),
				'validator'			=> $this->get('validator')
			);
			
			$this->Database->query("
				DELETE FROM
					`tbl_fields_{$handle}`
				WHERE
					`field_id` = '{$id}'
				LIMIT 1
			");
			
			return $this->Database->insert($fields, "tbl_fields_{$handle}");
		}
		
	/*-------------------------------------------------------------------------
		Publish:
	-------------------------------------------------------------------------*/
		
		public function displayPublishPanel(&$wrapper, $data = null, $error = null, $prefix = null, $postfix = null) {
			$this->_driver->addHeaders($this->_engine->Page);
			
			$sortorder = $this->get('sortorder');
			$element_name = $this->get('element_name');
			$classes = array();
			
			$label = Widget::Label($this->get('label'));
			
			if ($this->get('required') != 'yes') {
				$label->appendChild(new XMLElement('i', __('Optional')));
			}
			
			// Input box:
			if ($this->get('size') == 'single') {
				$input = Widget::Input(
					"fields{$prefix}[$element_name]{$postfix}", General::sanitize($data['value'])
				);
				
				###
				# Delegate: ModifyTextBoxInlineFieldPublishWidget
				# Description: Allows developers modify the textbox before it is rendered in the publish forms
				$delegate = 'ModifyTextBoxInlineFieldPublishWidget';
			}
			
			// Text Box:
			else {
				$input = Widget::Textarea(
					"fields{$prefix}[$element_name]{$postfix}", '20', '50', General::sanitize($data['value'])
				);
				
				###
				# Delegate: ModifyTextBoxFullFieldPublishWidget
				# Description: Allows developers modify the textbox before it is rendered in the publish forms
				$delegate = 'ModifyTextBoxFullFieldPublishWidget';
			}
			
			// Add classes:
			$classes[] = 'size-' . $this->get('size');
			
			if ($this->get('formatter') != 'none') {
				$classes[] = $this->get('formatter');
			}
			
			$input->setAttribute('class', implode(' ', $classes));
			
			$this->_engine->ExtensionManager->notifyMembers(
				$delegate, '/backend/',
				array(
					'field'		=> &$this,
					'label'		=> &$label,
					'textarea'	=> &$input
				)
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
			if ($this->get('formatter') != 'none') {
				if (isset($this->_ParentCatalogue['entrymanager'])) {
					$tfm = $this->_ParentCatalogue['entrymanager']->formatterManager;
					
				} else {
					$tfm = new TextformatterManager($this->_engine);
				}
				
				$formatter = $tfm->create($this->get('formatter'));
				$formatted = $formatter->run($data);
			 	$formatted = preg_replace('/&(?![a-z]{0,4}\w{2,3};|#[x0-9a-f]{2,6};)/i', '&amp;', $formatted);
			 	
			 	return $formatted;
			}
			
			return General::sanitize($data);	
		}
		
		public function applyValidationRules($data) {			
			$rule = $this->get('validator');
			
			return ($rule ? General::validateString($data, $rule) : true);
		}
		
		public function checkPostFieldData($data, &$message, $entry_id = null) {
			$message = null;
			
			if ($this->get('required') == 'yes' and strlen(trim($data)) == 0) {
				$message = __(
					"'%s' is a required field.", array(
						$this->get('label')
					)
				);
				
				return self::__MISSING_FIELDS__;
			}	
			
			if (empty($data)) self::__OK__;
			
			if (!$this->applyValidationRules($data)) {
				$message = __(
					"'%s' contains invalid data. Please check the contents.", array(
						$this->get('label')
					)
				);
				
				return self::__INVALID_FIELDS__;	
			}
			
			if (!General::validateXML($this->applyFormatting($data), $errors, false, new XsltProcess)) {
				$message = __(
					"'%1\$s' contains invalid XML. The following error was returned: <code>%2\$s</code>", array(
						$this->get('label'),
						$errors[0]['message']
					)
				);
				
				return self::__INVALID_FIELDS__;
			}
			
			return self::__OK__;
		}
		
		public function processRawFieldData($data, &$status, $simulate = false, $entry_id = null) {
			$status = self::__OK__;
			
			$result = array(
				'handle'			=> $this->createHandle($data),
				'value'				=> $data,
				'value_formatted'	=> $this->applyFormatting($data),
				'word_count'		=> General::countWords($data)
			);
			
			return $result;
		}
		
	/*-------------------------------------------------------------------------
		Output:
	-------------------------------------------------------------------------*/
		
		public function fetchIncludableElements() {
			return array(
				$this->get('element_name'),
				$this->get('element_name') . ': raw'
			);
		}
		
		public function appendFormattedElement(&$wrapper, $data, $encode = false, $mode = null) {
			if ($mode == 'raw') {
				$value = trim($data['value']);
			}
			
			else {
				$mode = 'normal';
				$value = trim($data['value_formatted']);
			}
			
			$attributes = array(
				'mode'			=> $mode,
				'handle'		=> $data['handle'],
				'word-count'	=> $data['word_count']
			);
			
			$wrapper->appendChild(
				new XMLElement(
					$this->get('element_name'), (
						$encode ? General::sanitize($value) : $value
					), $attributes
				)
			);
		}
		
		public function prepareTableValue($data, XMLElement $link = null) {
			if (empty($data) or strlen(trim($data['value'])) == 0) return;
			
			return parent::prepareTableValue(
				array(
					'value'		=> General::sanitize(strip_tags($data['value']))
				), $link
			);
		}
		
		public function getParameterPoolValue($data) {
			return $data['handle'];
		}
		
	/*-------------------------------------------------------------------------
		Filtering:
	-------------------------------------------------------------------------*/
		
		public function displayDatasourceFilterPanel(&$wrapper, $data = null, $errors = null, $prefix = null, $postfix = null) {
			$field_id = $this->get('id');
			
			$wrapper->appendChild(new XMLElement(
				'h4', sprintf(
					__('%s <i>%s</i>'),
					$this->get('label'),
					$this->name()
				)
			));
			
			$prefix = ($prefix ? "[{$prefix}]" : '');
			$postfix = ($postfix ? "[{$postfix}]" : '');
			
			$label = Widget::Label('Value');
			$label->appendChild(Widget::Input(
				"fields[filter]{$prefix}[{$field_id}]{$postfix}",
				($data ? General::sanitize($data) : null)
			));	
			$wrapper->appendChild($label);
			
			$help = new XMLElement('p');
			$help->setAttribute('class', 'help');
			$help->setValue(__('Accepted filter methods: <code>regexp</code>, <code>not-regexp</code>, <code>boolean</code> and <code>not-boolean</code>.'));
			
			$wrapper->appendChild($help);
		}
		
		public function buildDSRetrivalSQL($data, &$joins, &$where, $andOperation = false) {
			$field_id = $this->get('id');
			
			if (preg_match('/^(not-)?regexp:\s*/', $data[0], $matches)) {
				$data = trim(array_pop(explode(':', $data[0], 2)));
				$negate = ($matches[1] == '' ? '' : 'NOT');
				
				$data = $this->cleanValue($data);
				$this->_key++;
				$joins .= "
					LEFT JOIN
						`tbl_entries_data_{$field_id}` AS t{$field_id}_{$this->_key}
						ON (e.id = t{$field_id}_{$this->_key}.entry_id)
				";
				$where .= "
					AND {$negate}(
						t{$field_id}_{$this->_key}.handle REGEXP '{$data}'
						OR t{$field_id}_{$this->_key}.value REGEXP '{$data}'
					)
				";
				
			} else if (preg_match('/^(not-)?boolean:\s*/', $data[0], $matches)) {
				$data = trim(array_pop(explode(':', implode(' + ', $data), 2)));
				$negate = ($matches[1] == '' ? '' : 'NOT');
				
				if ($data == '') return true;
				
				// Negative match?
				if (preg_match('/^not(\W)/i', $data)) {
					$mode = '-';
					
				} else {
					$mode = '+';
				}
				
				// Replace ' and ' with ' +':
				$data = preg_replace('/(\W)and(\W)/i', '\\1+\\2', $data);
				$data = preg_replace('/(^)and(\W)|(\W)and($)/i', '\\2\\3', $data);
				$data = preg_replace('/(\W)not(\W)/i', '\\1-\\2', $data);
				$data = preg_replace('/(^)not(\W)|(\W)not($)/i', '\\2\\3', $data);
				$data = preg_replace('/([\+\-])\s*/', '\\1', $mode . $data);
				
				$data = $this->cleanValue($data);
				$this->_key++;
				$joins .= "
					LEFT JOIN
						`tbl_entries_data_{$field_id}` AS t{$field_id}_{$this->_key}
						ON (e.id = t{$field_id}_{$this->_key}.entry_id)
				";
				$where .= "
					AND {$negate}(MATCH (t{$field_id}_{$this->_key}.value) AGAINST ('{$data}' IN BOOLEAN MODE))
				";
				
			} else if (preg_match('/^(not-)?((starts|ends)-with|contains):\s*/', $data[0], $matches)) {
				$data = trim(array_pop(explode(':', $data[0], 2)));
				$negate = ($matches[1] == '' ? '' : 'NOT');
				$data = $this->cleanValue($data);
				
				if ($matches[2] == 'ends-with') $data = "%{$data}";
				if ($matches[2] == 'starts-with') $data = "{$data}%";
				if ($matches[2] == 'contains') $data = "%{$data}%";
				
				$this->_key++;
				$joins .= "
					LEFT JOIN
						`tbl_entries_data_{$field_id}` AS t{$field_id}_{$this->_key}
						ON (e.id = t{$field_id}_{$this->_key}.entry_id)
				";
				$where .= "
					AND {$negate}(
						t{$field_id}_{$this->_key}.handle LIKE '{$data}'
						OR t{$field_id}_{$this->_key}.value LIKE '{$data}'
					)
				";
				
			} else if ($andOperation) {
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
							t{$field_id}_{$this->_key}.handle = '{$value}'
							OR t{$field_id}_{$this->_key}.value = '{$value}'
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
						t{$field_id}_{$this->_key}.handle IN ('{$data}')
						OR t{$field_id}_{$this->_key}.value IN ('{$data}')
					)
				";
			}
			
			return true;
		}
		
	/*-------------------------------------------------------------------------
		Sorting:
	-------------------------------------------------------------------------*/
		
		public function buildSortingSQL(&$joins, &$where, &$sort, $order = 'ASC') {
			$field_id = $this->get('id');
			
			$joins .= "INNER JOIN `tbl_entries_data_{$field_id}` AS ed ON (e.id = ed.entry_id) ";
			$sort = 'ORDER BY ' . (strtolower($order) == 'random' ? 'RAND()' : "ed.value {$order}");
		}
		
	/*-------------------------------------------------------------------------
		Grouping:
	-------------------------------------------------------------------------*/
		
		public function groupRecords($records) {
			if (!is_array($records) or empty($records)) return;
			
			$groups = array(
				$this->get('element_name') => array()
			);
			
			foreach ($records as $record) {
				$data = $record->getData($this->get('id'));
				
				$value = $data['value_formatted'];
				$handle = $data['handle'];
				$element = $this->get('element_name');
				
				if (!isset($groups[$element][$handle])) {
					$groups[$element][$handle] = array(
						'attr'		=> array(
							'handle'	=> $handle
						),
						'records'	=> array(),
						'groups'	=> array()
					);
				}
				
				$groups[$element][$handle]['records'][] = $record;
			}
			
			return $groups;
		}
	}
	
?>