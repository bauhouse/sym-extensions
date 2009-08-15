<?php
	
	if (!defined('__IN_SYMPHONY__')) die('<h2>Symphony Error</h2><p>You cannot directly access this file</p>');
	
	class FieldFlexDate extends Field {
		const SIMPLE = 0;
		const REGEXP = 1;
		const RANGE = 3;
		const ERROR = 4;
		
	/*-------------------------------------------------------------------------
		Definition:
	-------------------------------------------------------------------------*/
		
		public function __construct(&$parent){
			parent::__construct($parent);
			
			$this->_name = 'Flex Date';
		}
		
		public function createTable() {
			$field_id = $this->get('id');
			
			return $this->_engine->Database->query("
				CREATE TABLE IF NOT EXISTS `tbl_entries_data_{$field_id}` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`entry_id` int(11) unsigned NOT NULL,
					`value` varchar(80) default NULL,
					`local` int(11) NOT NULL,
					`gmt` int(11) NOT NULL,
					PRIMARY KEY  (`id`),
					KEY `entry_id` (`entry_id`),
					KEY `value` (`value`)
				)
			");
		}
		
		public function allowDatasourceOutputGrouping() {
			return true;
		}
		
		public function allowDatasourceParamOutput() {
			return true;
		}
		
		public function canPrePopulate() {
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
		
		public function findDefaults(&$fields) {	
			if (!isset($fields['pre_populate'])) $fields['pre_populate'] = 'yes';
		}
		
		public function displaySettingsPanel(&$wrapper) {
			parent::displaySettingsPanel($wrapper);
			
			$order = $this->get('sortorder');
			
			$label = Widget::Label();
			$input = Widget::Input("fields[{$order}][pre_populate]", 'yes', 'checkbox');
			
			if ($this->get('pre_populate') == 'yes') {
				$input->setAttribute('checked', 'checked');
			}
			
			$label->setValue($input->generate() . ' Pre-populate this field with today\'s date');
			$wrapper->appendChild($label);
			
			$this->appendShowColumnCheckbox($wrapper);
		}
		
		public function commit() {
			if (!parent::commit() or $this->get('id') === false) {
				return false;
			}
			
			$id = $this->get('id');
			$fields = array(
				'field_id'		=> $this->get('id'),
				'pre_populate'	=> ($this->get('pre_populate') ? $this->get('pre_populate') : 'no')
			);
			
			$this->_engine->Database->query("
				DELETE FROM
					`tbl_fields_flexdate`
				WHERE
					`field_id` = '$id'
				LIMIT 1
			");
			
			return $this->_engine->Database->insert($fields, 'tbl_fields_flexdate');
		}
		
	/*-------------------------------------------------------------------------
		Publish:
	-------------------------------------------------------------------------*/
		
		public function displayPublishPanel(&$wrapper, $data = null, $error = null, $prefix = null, $postfix = null) {
			$this->_engine->Page->addScriptToHead(URL . '/extensions/flexdatefield/assets/jquery.js', 8251840);
			$this->_engine->Page->addScriptToHead(URL . '/extensions/flexdatefield/assets/publish.js', 8251841);
			$this->_engine->Page->addStylesheetToHead(URL . '/extensions/flexdatefield/assets/publish.css', 'screen', 8251840);
			
			$sortorder = $this->get('sortorder');
			$handle = $this->get('element_name');
			$pre_populate = $this->get('pre_populate') == 'yes';
			$timestamp = null; $value = null;
			
			if (is_array($data) and isset($data['local'])) {
				$value = DateTimeObj::get(__SYM_DATETIME_FORMAT__, $data['local']);
				
			} else if ($pre_populate) {
				$value = DateTimeObj::get(__SYM_DATETIME_FORMAT__, null);
			}
			
			$label = Widget::Label($this->get('label') . ': ' . $value);
			$input = Widget::Input(
				"fields{$prefix}[{$handle}]{$postfix}", $value
			);
			
			$label->appendChild($input);
			
			if (!is_null($error)) {
				$label = Widget::wrapFormElementWithError($label, $error);
			}
			
			$wrapper->appendChild($label);
		}
		
	/*-------------------------------------------------------------------------
		Input:
	-------------------------------------------------------------------------*/
		
		public function checkPostFieldData($data, &$message, $entry_id = null) {
			$label = $this->get('label');
			
			if (empty($data)) return self::__OK__; 
			
			if (is_array($data)) {
				$data = implode(',', $data);
			}
			
			$data = preg_split('/\,\s*/i', $data, -1, PREG_SPLIT_NO_EMPTY);
			$data = array_map('trim', $data);
			
			foreach ($data as $value) {
				if (empty($value) or strtotime($value) === false) {
					$message = "The date specified in '{$label}' is invalid.";
					
					return self::__INVALID_FIELDS__;
				}
			}
			
			return self::__OK__;
		}
		
		public function processRawFieldData($data, &$status, $simulate = false, $entry_id = null) {
			$status = self::__OK__;
			$field_id = $this->get('id');
			$current = null;
			
			header('content-type: text/plain');
			
			if (!empty($entry_id)) (integer)$current = $this->Database->fetchVar('local', 0, "
				SELECT
					f.local
				FROM
					`tbl_entries_data_{$field_id}` AS f
				WHERE
					f.entry_id = '{$entry_id}'
			");
			
			if (is_null($current)) $current = time();
			
			if ($data == '') {
				$timestamp = $current;
				
			} else {
				$timestamp = strtotime($data, $current);
			}
			
			$results = array(
				'value'	=> DateTimeObj::get('c', $timestamp),
				'local'	=> strtotime(DateTimeObj::get('c', $timestamp)),
				'gmt'	=> strtotime(DateTimeObj::getGMT('c', $timestamp))			
			);
			
			return $results;
		}
		
	/*-------------------------------------------------------------------------
		Output:
	-------------------------------------------------------------------------*/
		
		public function appendFormattedElement(&$wrapper, $data, $encode = false) {
			$wrapper->appendChild(General::createXMLDateObject(
				$data['local'], $this->get('element_name')
			));
		}
		
		public function prepareTableValue($data, XMLElement $link = null) {
			return parent::prepareTableValue(
				array(
					'value'		=> DateTimeObj::get(__SYM_DATE_FORMAT__, $data['local'])
				),
				$link
			);
		}
		
	/*-------------------------------------------------------------------------
		Filtering:
	-------------------------------------------------------------------------*/
		
		public function buildDSRetrivalSQL($data, &$joins, &$where, $andOperation = false){
			$parsed = array();
			
			header('content-type: text/plain');
			
			foreach ($data as $string) {
				$type = $this->__parseFilter($string);
				
				if ($type == self::ERROR) return false;
				
				if (!is_array($parsed[$type])) $parsed[$type] = array();
				
				$parsed[$type][] = $string;
			}
			
			foreach ($parsed as $type => $data) {
				switch ($type) {
					case self::RANGE:
						//echo 'range';
						$this->__buildRangeFilterSQL($data, $joins, $where, $andOperation);
						break;
						
					case self::SIMPLE:
						$this->__buildSimpleFilterSQL($data, $joins, $where, $andOperation);
						//var_dump($where);
						break;
				}
			}
			//exit;
			return true;
		}
		
		protected function __buildSimpleFilterSQL($data, &$joins, &$where, $andOperation = false) {
			$field_id = $this->get('id');
			
			foreach ($data as &$value) {
				$value = DateTimeObj::get('Y-m-d', strtotime($value));
			}
			
			if ($andOperation) {
				foreach ($data as $value) {
					$this->_key++;
					$joins .= "
						LEFT JOIN
							`tbl_entries_data_{$field_id}` AS t{$field_id}_{$this->_key}
							ON (e.id = t{$field_id}_{$this->_key}.entry_id)
					";
					$where .= "
						AND DATE_FORMAT(t{$field_id}_{$this->_key}.value, '%Y-%m-%d') = '{$value}'
					";
				}
				
			} else {
				$this->_key++;
				$value = html_entity_decode(@implode("', '", $data));
				$joins .= "
					LEFT JOIN
						`tbl_entries_data_{$field_id}` AS t{$field_id}_{$this->_key}
						ON (e.id = t{$field_id}_{$this->_key}.entry_id)
				";
				$where .= "
					AND DATE_FORMAT(t{$field_id}_{$this->_key}.value, '%Y-%m-%d') IN ('{$value}')
				";
			}
		}
		
		protected function __buildRangeFilterSQL($data, &$joins, &$where, $andOperation = false) {	
			$field_id = $this->get('id');
			
			if (empty($data)) return;
			
			if ($andOperation) {
				foreach ($data as $values) {
					$start = DateTimeObj::get('Y-m-d', strtotime($values['start']));
					$end = DateTimeObj::get('Y-m-d', strtotime($values['end']));
					$joins .= "
						LEFT JOIN
							`tbl_entries_data_{$field_id}` AS t{$field_id}_{$this->key}
							ON e.id = t{$field_id}_{$this->key}.entry_id
					";
					$where .= "
						AND (
							DATE_FORMAT(t{$field_id}_{$this->key}.value, '%Y-%m-%d') >= '{$start}' 
							AND DATE_FORMAT(t{$field_id}_{$this->key}.value, '%Y-%m-%d') <= '{$end}'
						)
					";
					
					$this->key++;
				}
				
			} else {
				$tmp = array();
				
				foreach ($data as $values) {
					$start = DateTimeObj::get('Y-m-d', strtotime($values['start']));
					$end = DateTimeObj::get('Y-m-d', strtotime($values['end']));
					
					$tmp[] = "
						(
							DATE_FORMAT(t{$field_id}_{$this->key}.value, '%Y-%m-%d') >= '{$start}' 
							AND DATE_FORMAT(t{$field_id}_{$this->key}.value, '%Y-%m-%d') <= '{$end}'
						)
					";
				}
				
				$tmp = @implode(' OR ', $tmp);
				$joins .= "
					LEFT JOIN
						`tbl_entries_data_{$field_id}` AS t{$field_id}_{$this->key}
						ON e.id = t{$field_id}_{$this->key}.entry_id
				";
				$where .= " AND ({$tmp}) ";
				
				$this->key++;
			}
		}
		
		protected function __parseFilter(&$string) {
			$string = $this->__parseFilterClean($string);
			
			// Check its not a regexp
			if (preg_match('/^regexp:/i', $string)) {
				$string = str_replace('regexp:', '', $string);
				return self::REGEXP;
				
			// Look to see if its a shorthand date (year only), and convert to full date
			} elseif (preg_match('/^(1|2)\d{3}$/i', $string)) {
				$string = "$string-01-01 to $string-12-31";
				
			// Look to see if its a shorthand date (year and month), and convert to full date
			} elseif (preg_match('/^(1|2)\d{3}[-\/]\d{1,2}$/i', $string)) {
				$start = "{$string}-01";
				
				if (!$this->__parseFilterValid($start)) return self::ERROR;
				
				$string = "$start to $string-" . date('t', strtotime($start));
				
			// Match for a simple date (Y-m-d), check its ok using checkdate() and go no further
			} elseif (!preg_match('/to/i', $string)) {
				if (!$this->__parseFilterValid($string)) return self::ERROR;
				
				$string = DateTimeObj::get('Y-m-d', strtotime($string));
				
				return self::SIMPLE;
			}
			
			// Parse the full date range and return an array
			if (!$parts = preg_split('/to/', $string, 2, PREG_SPLIT_NO_EMPTY)) {
				return self::ERROR;
			}
			
			$parts = array_map(array('self', '__parseFilterClean'), $parts);
			
			list($start, $end) = $parts;
			
			if (
				!$this->__parseFilterValid($start)
				or !$this->__parseFilterValid($end)
			) {
				return self::ERROR;
			}
			
			$string = array('start' => $start, 'end' => $end);
			
			return self::RANGE;
		}
		
		protected function __parseFilterClean($string) {
			$string = trim($string);
			$string = trim($string, '-/');
			
			return $string;
		}
		
		protected function __parseFilterValid($string) {
			$string = trim($string);
			
			if (empty($string) or strtotime($string) === false) {
				return false;
			}
			
			return true;
		}
		
	/*-------------------------------------------------------------------------
		Sorting:
	-------------------------------------------------------------------------*/
		
		function buildSortingSQL(&$joins, &$where, &$sort, $order = 'ASC') {
			$field_id = $this->get('id');
			$joins .= "
				INNER JOIN
					`tbl_entries_data_{$field_id}` AS ed
					ON (e.id = ed.entry_id)
			";
			$sort = 'ORDER BY ' . (strtolower($order) == 'random' ? 'RAND()' : "ed.gmt {$order}");
		}
		
	/*-------------------------------------------------------------------------
		Grouping:
	-------------------------------------------------------------------------*/
		
		public function groupRecords($records) {
			if (!is_array($records) or empty($records)) return;
			
			header('content-type: text/plain');
			
			$groups = array('year' => array());
			
			foreach ($records as $r) {
				$data = $r->getData($this->get('id'));
				
				if (is_array($data['local'])) {
					sort($data['local'], SORT_NUMERIC);
					$data['local'] = array_shift($data['local']);
				}
				
				$info = getdate($data['local']);
				$year = $info['year'];
				$month = ($info['mon'] < 10 ? '0' . $info['mon'] : $info['mon']);
				
				if (!isset($groups['year'][$year])) {
					$groups['year'][$year] = array(
						'attr'		=> array(
							'value'		=> $year
						),
						'records'	=> array(), 
						'groups'	=> array()
					);
				}
				
				if (!isset($groups['year'][$year]['groups']['month'])) {
					$groups['year'][$year]['groups']['month'] = array();
				}
				
				if (!isset($groups['year'][$year]['groups']['month'][$month])) {
					$groups['year'][$year]['groups']['month'][$month] = array(
						'attr'		=> array(
							'value'		=> $month
						),
						'records'	=> array(), 
						'groups'	=> array()
					);
				}
				
				$groups['year'][$year]['groups']['month'][$month]['records'][] = $r;
			}
			
			return $groups;
		}
	}
	
?>