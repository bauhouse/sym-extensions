<?php
	
	if (!defined('__IN_SYMPHONY__')) die('<h2>Symphony Error</h2><p>You cannot directly access this file</p>');
	
	require_once(EXTENSIONS . '/curlinputfield/lib/curl.php');
	
	class FieldCurlInput extends Field {
	/*-------------------------------------------------------------------------
		Field definition:
	-------------------------------------------------------------------------*/
		
		public function __construct(&$parent) {
			parent::__construct($parent);
			
			$this->_name = 'Curl Input';
			$this->_required = true;
			
			// Set defaults:
			$this->set('show_column', 'yes');
			$this->set('required', 'yes');
		}
		
		public function createTable() {
			$field_id = $this->get('id');
			
			return $this->_engine->Database->query("
				CREATE TABLE IF NOT EXISTS `tbl_entries_data_{$field_id}` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`entry_id` int(11) unsigned NOT NULL,
					`handle` varchar(255) default NULL,
					`value` varchar(255) default NULL,
					PRIMARY KEY  (`id`),
					KEY `entry_id` (`entry_id`),
					KEY `handle` (`handle`),
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

		public function allowDatasourceOutputGrouping() {
			return true;
		}
		
		public function allowDatasourceParamOutput() {
			return true;
		}
		
	/*-------------------------------------------------------------------------
		Display functions:
	-------------------------------------------------------------------------*/
		
		public function displayPublishPanel(&$wrapper, $data = null, $flagWithError = null, $fieldnamePrefix = null, $fieldnamePostfix = null) {
			$label = Widget::Label($this->get('label'));
			$name = $this->get('element_name');
			
			if ($this->get('required') != 'yes') {
				$label->appendChild(new XMLElement('i', 'Optional'));
			}
			
			$input = Widget::Input(
				"fields{$fieldnamePrefix}[$name]{$fieldnamePostfix}",
				(strlen($data['value']) != 0 ? General::sanitize($data['value']) : NULL)
			);
			
			$label->appendChild($input);
			
			if ($flagWithError != null) {
				$wrapper->appendChild(Widget::wrapFormElementWithError($label, $flagWithError));
				
			} else {
				$wrapper->appendChild($label);
			}
		}
		
		public function buildValidationSelect(&$wrapper, $selected, $title, $name, $type = 'input'){
			include(TOOLKIT . '/util.validators.php');
			
			$rules = ($type == 'upload' ? $upload : $validators);
			
			$label = Widget::Label("Validation Rule: {$title} <i>Optional</i>");
			$label->appendChild(Widget::Input($name, $selected));
			$wrapper->appendChild($label);
			
			$ul = new XMLElement('ul', null, array(
				'class'	=> 'tags singular'
			));
			
			foreach ($rules as $name => $rule) {
				$ul->appendChild(new XMLElement(
					'li', $name, array(
						'class'	=> $rule
					)
				));
			}
			
			$wrapper->appendChild($ul);
		}
		
		public function displaySettingsPanel(&$wrapper, $errors = null) {
			parent::displaySettingsPanel($wrapper);
			
			$order = $this->get('sortorder');
			
			$this->buildValidationSelect(
				$wrapper, $this->get('valid_status'),
				'Return Status', "fields[{$order}][valid_status]"
			);
			
			$this->buildValidationSelect(
				$wrapper, $this->get('valid_type'),
				'Content Type', "fields[{$order}][valid_type]"
			);
			
			$this->appendRequiredCheckbox($wrapper);
			$this->appendShowColumnCheckbox($wrapper);
		}
		
	/*-------------------------------------------------------------------------
		Data retrieval functions:
	-------------------------------------------------------------------------*/
		
		public function groupRecords($records) {
			if (!is_array($records) || empty($records)) return;
			
			$groups = array($this->get('element_name') => array());
			
			foreach ($records as $r) {
				$data = $r->getData($this->get('id'));
				
				$value = $data['value'];
				$handle = Lang::createHandle($value);
				
				if (!isset($groups[$this->get('element_name')][$handle])) {
					$groups[$this->get('element_name')][$handle] = array(
						'attr'		=> array(
							'handle'	=> $handle,
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
		
		public function buildSortingSQL(&$joins, &$where, &$sort, $order = 'ASC'){
			$field_id = $this->get('id');
			$joins .= "
				INNER JOIN `tbl_entries_data_{$field_id}` AS ed
				ON (e.id = ed.entry_id)
			";
			$sort = 'ORDER BY ' . (strtolower($order) == 'random' ? 'RAND()' : "ed.value {$order}");
		}
		
		public function buildDSRetrivalSQL($data, &$joins, &$where, $andOperation = false) {
			$field_id = $this->get('id');
			
			if (self::isFilterRegex($data[0])) {
				$pattern = str_replace('regexp:', '', $data[0]);
				$joins .= "
					LEFT JOIN
						`tbl_entries_data_{$field_id}` AS `t{$field_id}`
						ON (`e`.`id` = `t{$field_id}`.entry_id)
					";
				$where .= "
					AND `t{$field_id}`.value REGEXP '{$pattern}'
					OR `t{$field_id}`.handle REGEXP '{$pattern}'
				";
				
			} else if ($andOperation) {
				foreach ($data as $key => $bit) {
					$bit = $this->cleanValue($bit);
					
					$joins .= "
						LEFT JOIN
							`tbl_entries_data_{$field_id}` AS `t{$field_id}{$key}`
							ON (`e`.`id` = `t{$field_id}{$key}`.entry_id)
						";
					$where .= "
						AND `t{$field_id}{$key}`.value = '{$bit}'
						OR `t{$field_id}{$key}`.handle = '{$bit}'
					";
				}
				
			} else {
				array_walk_recursive(
					$data, create_function('&$v, $k, $s', '$v = $s->cleanValue($v);'), $this
				);
				
				$value = (@implode("', '", $data));
				$joins .= "
					LEFT JOIN
						`tbl_entries_data_{$field_id}` AS `t{$field_id}`
						ON (`e`.`id` = `t{$field_id}`.entry_id)
					";
				$where .= "
					AND `t{$field_id}`.value IN ('{$value}')
					OR `t{$field_id}`.handle IN ('{$value}')
				";
			}
			
			return true;
		}
		
		public function appendFormattedElement(&$wrapper, $data, $encode = false) {
			$value = $uri = trim($data['value']);
			
			if (!preg_match('/(^(https?):\/\/)/', $value)) {
				$uri = 'http://' . $uri;
			}
			
			$attributes['uri'] = $uri;
			
			if ($this->get('show_column') == 'yes') {
				$attributes['handle'] = $data['handle'];
			}
			
			$wrapper->appendChild(
				new XMLElement(
					$this->get('element_name'), (
						$encode ? General::sanitize($value) : $value
					), $attributes
				)
			);
		}
		
		public function prepareTableValue($data, XMLElement $link = null) {
			if (@empty($data['value'])) return null;
			
			$name = $file = $data['value'];
			$parts = explode('/', $name);
			
			if (count($parts) > 4) {
				$parts = explode('/', $name);
				$prefix = array_splice($parts, 0, 3);
				$suffix = array_splice($parts, -1);
				
				$name = implode('/', $prefix) . '/.../' . implode('/', $suffix);
			}
			
			if ($link) {
				$link->setValue($name);
				return $link->generate();
			} else {
				$link = Widget::Anchor($name, $file);
				return $link->generate();
			}
		}
		
	/*-------------------------------------------------------------------------
		Data processing functions:
	-------------------------------------------------------------------------*/
		
		public function checkPostFieldData($data, &$message, $entry_id = null) {
			$handle = Lang::createHandle($data);
			$message = null; $label = $this->get('label');
			
			// Empty?
			if ($this->get('required') == 'yes' and empty($data)) {
				$message = "'{$label}' is a required field.";
				
				return self::__MISSING_FIELDS__;
				
			} else if (empty($data)) {
				return self::__OK__;
			}
			
			// Valid?
			$curl = new CurlInputFieldCurl();
			$request = $curl->get($data);
			
			if ($data === false) {
				$message = "'{$label}' is not a valid URL.";
				
				return self::__MISSING_FIELDS__;
			}
			
			$status = $request->headers['Status-Code'];
			$type = strtok($request->headers['Content-Type'], "; ");
			$valid_status = $this->get('valid_status');
			$valid_type = $this->get('valid_type');
			
			// Validate status?
			if (!empty($valid_status) and !preg_match($valid_status, $status)) {
				$message = "'{$label}' returned an invalid status code: {$status}.";
				
				return self::__MISSING_FIELDS__;
			}
			
			// Validate type?
			if (!empty($valid_type) and !preg_match($valid_type, $type)) {
				$message = "'{$label}' returned an invalid content type: {$type}.";
				
				return self::__MISSING_FIELDS__;
			}
			
			return self::__OK__;
		}
		
		public function processRawFieldData($data, &$status, $simulate = false, $entry_id = null) {
			$status = self::__OK__;
			
			if (trim($data) == '') return array();
			
			$value = General::sanitize($data);
			$handle = Lang::createHandle($data);
			
			$result = array(
				'handle'	=> $handle,
				'value'		=> $value,
			);
			
			return $result;
		}
		
		public function commit() {
			if (!parent::commit()) return false;
			
			$id = $this->get('id');
			$handle = $this->handle();
			
			if ($id === false) return false;
			
			$fields = array();
			
			$fields['field_id'] = $id;
			$fields['valid_status'] = (
				$fields['valid_status'] == 'custom' ? null : $this->get('valid_status')
			);
			$fields['valid_type'] = (
				$fields['valid_type'] == 'custom' ? null : $this->get('valid_type')
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
	}
	
?>