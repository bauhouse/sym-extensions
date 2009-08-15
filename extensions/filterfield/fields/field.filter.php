<?php

	Class fieldFilter extends Field {

		public function __construct(&$parent) {
			parent::__construct($parent);
			$this->_name = __('Filter');
			$this->_required = false;
			$this->_showcolumn = false;

			// Set defaults
			$this->set('show_column', 'no');
			$this->set('required', 'yes');
		}

		/*
		**	Implementation of Symphony API
		*/

		// Specify if field value can be set from entry list table, i.e., section content page
		public function canToggle() {
			return false;
		}

		// Specify if entries can be grouped by field when listed in XML
		public function allowDatasourceOutputGrouping() {
			return false;
		}

		// Specify if field requires grouping in SQL select (to avoid duplicate entry rows)
		public function requiresSQLGrouping() {
			return true;
		}

		// Specify if field can output it's value as XSLT parameter
		public function allowDatasourceParamOutput() {
			return false;
		}

		// Specify if entries can be filtered by this field
		public function canFilter() {
			return ($this->get('filter_datasource') == 'yes');
		}

		// TODO: what is this for? When data is imported? Does it have something to do with ensembles?
		public function canImport() {
			return false;
		}

		// Specify if field can be prepopulated through GET?
		public function canPrePopulate() {
			return false;
		}

		// Specify if entries can be sorted by this field
		public function isSortable() {
			return false;
		}

		// Return value to be used as XSLT parameter
		public function getParameterPoolValue($data) {
			return '';
		}

		// Return list of values that can be applied to entry on section content page
		public function getToggleStates() {
			return array();
		}

		// Apply new field value to data
		public function toggleFieldData($data, $newState) {
			return $data;
		}

		// Generate XML data containing field values
		public function appendFormattedElement(&$wrapper, $data, $encode = false) {
			return;
		}

		// Generate groups which will be used by Datasource to when generating XML.
		// Entries with the same value of this field will be wrapped field tag
		// (so it is not grouping in the same sense as SQL grouping! :).
		public function groupRecords($records) {
			return;
		}

		// Build field widget for entry edit page
		public function displayPublishPanel(&$wrapper, $data = NULL, $flagWithError = NULL, $fieldnamePrefix = NULL, $fieldnamePostfix = NULL) {
		}

		// Generate field widget used by Events when rendering example markup
		public function getExampleFormMarkup() {
			return new XMLElement('!--', ' --');
		}

		// Build "filter by field" widget for Datasource edit page
		public function displayDatasourceFilterPanel(&$wrapper, $data = NULL, $errors = NULL, $fieldnamePrefix = NULL, $fieldnamePostfix = NULL) {
			if ($data == NULL) $data = '(if value of (param_or_value_here) is (param_or_value_here))';

			$e = $this->parseExpression($data);
			// Copy content generated by parent::displayDatasourceFilterPanel(), so we can wrap it with error if needed.
			$wrapper->appendChild(new XMLElement('h4', $this->get('label') . ' <i>'.$this->Name().'</i>'));
			$label = Widget::Label(__('Value'));
			$label->appendChild(Widget::Input('fields[filter]'.($fieldnamePrefix ? '['.$fieldnamePrefix.']' : '').'['.$this->get('id').']'.($fieldnamePostfix ? '['.$fieldnamePostfix.']' : ''), ($data ? General::sanitize($data) : NULL)));
			$wrapper->appendChild((empty($e) ? Widget::wrapFormElementWithError($label, __('Invalid syntax')) : $label));

			$params = $this->listParams();
			if (empty($params)) return;

			$optionlist = new XMLElement('ul');
			$optionlist->setAttribute('class', 'tags');

			foreach ($params as $param => $value) {
				$optionlist->appendChild(new XMLElement('li', $param, array('class' => '{$'.$param.'}', 'title' => ($value ? __('Value of %s', array($value)) : __('Value found in URL path')))));
			}

			$wrapper->appendChild($optionlist);
		}

		// Render value which will be used in entry list table (on section content page)
		public function prepareTableValue($data, XMLElement $link = NULL) {
			return '';
		}

		// Prepare default values for field settings widget (used on section edit page)
		public function findDefaults(&$fields) {
			if (!isset($fields['filter_publish'])) $fields['filter_publish'] = '';
			if (!isset($fields['filter_datasource'])) $fields['filter_datasource'] = 'no';
		}

		// Build field settings widget used on section edit page
		public function displaySettingsPanel(&$wrapper, $errors = NULL) {
			parent::displaySettingsPanel($wrapper, $errors);

			// Disable/Enable publish filtering
			$label = Widget::Label(__('Prevent publishing if expression below evaluates to false'));
			$input = Widget::Input('fields['.$this->get('sortorder').'][filter_publish]', $this->get('filter_publish'));
			$label->appendChild($input);
			$wrapper->appendChild($label);

			// Disable/Enable datasource filtering
			$label = Widget::Label();
			$input = Widget::Input('fields['.$this->get('sortorder').'][filter_datasource]', 'yes', 'checkbox');
			if ($this->get('filter_datasource') == 'yes') $input->setAttribute('checked', 'checked');
			$label->setValue(__('%s Allow datasources to filter this section with expression', array($input->generate())));
			$wrapper->appendChild($label);
		}

		// Check if publish data is valid
		public function checkPostFieldData($data, &$message, $entry_id=NULL){
			if (!($expression = trim($this->get('filter_publish')))) return self::__OK__;
// TODO: Get entry fields data (backtrace? POST?) and replace {entry/field} with field value.
//		We can't Use EntryPreCreate, EntryPreEdit and EventPreSaveFilter delegates
//		because Entry* are called AFTER check (checkPostFieldData) and set (processRawFieldData).
//		Only EventPreSaveFilter is called before them, but it's used only when publishing from frontend.
			$fields = array();
			if (preg_match_all('@{([^}]+)}@i', $expression, $matches, PREG_SET_ORDER)) {
				foreach ($matches as $m) {
					if (isset($fields[$m[1]])) {
						$v = $fields[$m[1]];
						if (is_array($v)) {
							$v = implode(', ', $v);
						}
						$expression = str_replace($m[0], $v);
					}
					else $expression = str_replace($m[0], '');
				}
			}

			$message = NULL;
			if (!$this->evaluateExpression($expression)) {
				$message = __("Contains invalid data.");
				return self::__INVALID_FIELDS__;
			}

			return self::__OK__;		
		}

		// Check if field settings data is valid
		public function checkFields(&$errors, $checkForDuplicates = true) {

			$expression = trim($this->get('filter_publish'));
			if ($expression) {
				$r = $this->parseExpression($expression);
				if (empty($r)) $errors['filter_publish'] = __('Invalid expression.');
			}

			return parent::checkFields($errors, $checkForDuplicates);
		}

		// Prepare value to be saved to database
		public function processRawFieldData($data, &$status, $simulate = false, $entry_id = NULL) {
			$status = self::__OK__;
			return array();
		}

		// Prepare SQL part responsible for sorting entries by this field
		public function buildSortingSQL(&$joins, &$where, &$sort, $order = 'ASC') {
		}

		// Prepare SQL part responsible for filtering entries by this field
		public function buildDSRetrivalSQL($data, &$joins, &$where, $andOperation = false) {
			if ($this->get('filter_datasource') != 'yes') return;

			if (is_array($data)) $data = implode(($andOperation ? '+ ' : ', '), $data);

			return $this->evaluateExpression($data);
		}

		// Save field settings (edited on section edit page) to database
		public function commit() {
			if (!parent::commit()) return false;

			$id = $this->get('id');

			if ($id === false) return false;

			$fields = array();
			$fields['field_id'] = $id;
			$fields['filter_publish'] = trim($this->get('filter_publish'));
			$fields['filter_datasource'] = ($this->get('filter_datasource') ? $this->get('filter_datasource') : 'no');

			$this->Database->query("DELETE FROM `tbl_fields_".$this->handle()."` WHERE `field_id` = '{$id}'");

			return $this->Database->insert($fields, 'tbl_fields_' . $this->handle());
		}

		// Create database table which will keep field values for each entry
		public function createTable() {
			return $this->_engine->Database->query(
				'CREATE TABLE IF NOT EXISTS `tbl_entries_data_'.$this->get('id').'` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`entry_id` int(11) unsigned NOT NULL,
					PRIMARY KEY (`id`),
					KEY `entry_id` (`entry_id`)
				) TYPE=MyISAM;'
			);
		}

		/*
		**	Own stuff
		*/

		// Get list of page and datasource params
		private function listParams() {
			$params = array();

			// Get page params
			$Admin = Administration::instance();
			$pages = $Admin->Database->fetch('SELECT params FROM tbl_pages WHERE params IS NOT NULL');
			if (is_array($pages) && !empty($pages)) {
				foreach ($pages as $page => $data) {
					if (($data = trim($data['params']))) {
						foreach (explode('/', $data) as $p) {
							$params[$p] = '';
						}
					}
				}
			}

			// Get datasource generated params
			$files = General::listStructure(DATASOURCES, array('php'), false, 'asc');
			if (!empty($files['filelist'])) {
				foreach ($files['filelist'] as $file) {
					$data = file_get_contents(DATASOURCES."/{$file}");
					if (strpos($data, 'include(TOOLKIT . \'/data-sources/datasource.section.php\');') === false) continue;

					if (preg_match('/\s+public\s*\$dsParamPARAMOUTPUT\s*=\s*([\'"])([^\1]+)(?:\1)\s*;/U', $data, $m)) {
						$p = $m[2];
						if (preg_match('/\s+public\s*\$dsParamROOTELEMENT\s*=\s*([\'"])([^\1]+)(?:\1)\s*;/U', $data, $m)) {
							$params['ds-'.$m[2]] = $p;
						}
					}
				}
			}

			return $params;
		}

		// Check syntax
		private function parseExpression($e) {
			/*
				Valid expression should result in array(
					0 => whole expression,
					1 => function,
					2 => param,
					3 => operand,
					4 => param,
				);
			*/
			if (!preg_match('/\(if\s*(value of|any of|all of)\s*(\((?:[^\(\)]+|(?2))*\))\s*((?:is|are)(?: not|)(?: in|))\s*(\((?:[^\(\)]+|(?4))*\))\s*\)/', $e, $r))
				return array();

			return $r;
		}

		// Evaluate expression to boolean value
		private function evaluateExpression($e) {
			// (if value of ({$ds-value}) is (one))
			// (if value of ({$ds-value}) is not ())
			// (if any of ({$ds-value}) is in (one,two,three))
			// (if all of ({$ds-value}) are in (one,two,three))
			// (if any of ((if value of ({$ds-value}) is (one)), ({$ds-is-true})) is (false))

			if (is_array($e)) $r = $e; // Recursive call for sub expression, $e is already parsed
			else $r = $this->parseExpression($e);

			if (empty($r)) return false;

			$r[2] = substr($r[2], 1, -1); // Remove first level parenthesis
			$r[4] = substr($r[4], 1, -1); // Same here

			// Parse sub expressions
			for ($i = 2; $i <= 4; $i+=2) {
				$max = 10;
				while ($max--) {
					$s = $this->parseExpression($r[$i]);
					if (empty($s)) break;

					$r[$i] = str_replace($s[0], ($this->evaluateExpression($s) ? 'yes' : 'no'), $r[$i]);
				}
			}

			switch ($r[3]) {
				case 'is in':
				case 'are in':
					if ((!$r[2] || !$r[4]) && $r[2] != $r[4]) return false;

					$r[2] = preg_split('/,\s*/', $r[2]);
					$r[4] = preg_split('/,\s*/', $r[4]);
					$found = array_intersect($r[2], $r[4]);
					if ($r[1] == 'value of' || $r[1] == 'all of') {
						return (!empty($found) && count($r[2]) >= count($found) && count($r[2]) <= count($r[4]));
					}
					else if ($r[1] == 'any of') {
						return (!empty($found));
					}
					break;

				case 'is not in':
				case 'are not in':
					if ((!$r[2] || !$r[4]) && $r[2] != $r[4]) return true;

					$r[2] = preg_split('/,\s*/', $r[2]);
					$r[4] = preg_split('/,\s*/', $r[4]);
					$found = array_intersect($r[2], $r[4]);
					if ($r[1] == 'value of' || $r[1] == 'all of') {
						return (empty($found));
					}
					else if ($r[1] == 'any of') {
						return (empty($r[4]) || count($found) < count($r[2]));
					}
					break;

				case 'is not':
					if ($r[1] == 'value of') {
						return ($r[2] != $r[4]);
					}
					else if ($r[1] == 'any of') {
						foreach (preg_split('/,\s*/', $r[2]) as $v) {
							if ($v != $r[4]) return true;
						}
						return false;
					}
					else if ($r[1] == 'all of') {
						foreach (preg_split('/,\s*/', $r[2]) as $v) {
							if ($v == $r[4]) return false;
						}
					}
					break;

				case 'is':
					if ($r[1] == 'value of') {
						return ($r[2] == $r[4]);
					}
					else if ($r[1] == 'any of') {
						foreach (preg_split('/,\s*/', $r[2]) as $v) {
							if ($v == $r[4]) return true;
						}
						return false;
					}
					else if ($r[1] == 'all of') {
						foreach (preg_split('/,\s*/', $r[2]) as $v) {
							if ($v != $r[4]) return false;
						}
					}
					break;
			}

			return true;
		}
	}
