<?php
	
	require_once(TOOLKIT . '/class.fieldmanager.php');
	require_once(TOOLKIT . '/class.entrymanager.php');
	require_once(TOOLKIT . '/class.sectionmanager.php');
	
	class Importer {
		const __OK__ = 100;
		const __ERROR_PREPARING__ = 200;
		const __ERROR_VALIDATING__ = 210;
		const __ERROR_CREATING__ = 220;
		
		public $_Parent = null;
		public $_entries = array();
		public $_errors = array();
		
		public function __construct($parent) {
			$this->_Parent = $parent;
		}
		
		public function getSection() {
			return null;
		}
		
		public function getRootExpression() {
			return '';
		}
		
		public function getUniqueField() {
			return '';
		}
		
		public function canUpdate() {
			return true;
		}
		
		public function getFieldMap() {
			return array();
		}
		
		public function getEntries() {
			return $this->_entries;
		}
		
		public function getErrors() {
			return $this->_errors;
		}
		
		protected function getExpressionValue($xml, $entry, $xpath, $expression) {
			$matches = $xpath->evaluate($expression, $entry);
			
			if ($matches instanceof DOMNodeList) {
				$value = '';
				
				foreach ($matches as $match) {
					if ($match instanceof DOMAttr or $match instanceof DOMText) {
						$value .= $match->nodeValue;
						
					} else {
						$value .= $xml->saveXML($match);
					}
				}
				
				return $value;
				
			} else if (!is_null($matches)) {
				return (string)$matches;
			}
			
			return null;
		}
		
		public function validate($data) {
			if (!function_exists('handleXMLError')) {
				function handleXMLError($errno, $errstr, $errfile, $errline, $context) {
					$context['self']->_errors[] = $errstr;
				}
			}
			
			if (empty($data)) return null;
			
			$entryManager = new EntryManager($this->_Parent);
			$fieldManager = new FieldManager($this->_Parent);
			
			set_time_limit(900);
			set_error_handler('handleXMLError');
			
			$self = $this; // Fucking PHP...
			$xml = new DOMDocument();
			$xml->loadXML($data, LIBXML_COMPACT);
			
			restore_error_handler();
			
			$xpath = new DOMXPath($xml);
			$passed = true;
			
			// Invalid Markup:
			if (empty($xml)) {
				$passed = false;
				
			// Invalid Expression:
			} else if (($entries = $xpath->query($this->getRootExpression())) === false) {
				$this->_errors[] = sprintf(
					'Root expression <code>%s</code> is invalid.',
					htmlentities($this->getRootExpression(), ENT_COMPAT, 'UTF-8')
				);
				$passed = false;
				
			// No Entries:
			} else if (empty($entries)) {
				$this->_errors[] = 'No entries to import.';
				$passed = false;
				
			// Test expressions:
			} else {
				foreach ($this->getFieldMap() as $field_id => $expression) {
					if ($xpath->evaluate($expression) === false) {
						$field = $fieldManager->fetch($field_id);
						
						$this->_errors[] = sprintf(
							'\'%s\' expression <code>%s</code> is invalid.',
							$field->get('label'),
							htmlentities($expression, ENT_COMPAT, 'UTF-8')
						);
						$passed = false;
					}
				}
			}
			
			if (!$passed) return self::__ERROR_PREPARING__;
			
			// Gather data:
			foreach ($entries as $index => $entry) {
				$this->_entries[$index] = array(
					'element'	=> $entry,
					'entry'		=> null,
					'values'	=> array(),
					'errors'	=> array()
				);
				
				foreach ($this->getFieldMap() as $field_id => $expressions) {
					if (!is_array($expressions)) {
						$value = $this->getExpressionValue($xml, $entry, $xpath, $expressions, $debug);
						//var_dump(is_utf8($value));
						
					} else {
						$value = array();
						
						foreach ($expressions as $name => $expression) {
							$value[$name] = $this->getExpressionValue($xml, $entry, $xpath, $expression);
						}
					}
					
					$this->_entries[$index]['values'][$field_id] = $value;
				}
			}
			
			// Validate:
			$passed = true;
			
			foreach ($this->_entries as &$current) {
				$entry = $entryManager->create();
				$entry->set('section_id', $this->getSection());
				$entry->set('author_id', $this->_Parent->Author->get('id'));
				$entry->set('creation_date', DateTimeObj::get('Y-m-d H:i:s'));
				$entry->set('creation_date_gmt', DateTimeObj::getGMT('Y-m-d H:i:s'));
				
				$values = array();
				
				// Map values:
				foreach ($current['values'] as $field_id => $value) {
					$field = $fieldManager->fetch($field_id);
					
					// Adjust value?
					if (method_exists($field, 'prepareImportValue')) {
						$value = $field->prepareImportValue($value);
					}
					
					$values[$field->get('element_name')] = $value;
				}
				
				// Validate:
				if (__ENTRY_FIELD_ERROR__ == $entry->checkPostData($values, $current['errors'])) {
					$passed = false;
					
				} elseif (__ENTRY_OK__ != $entry->setDataFromPost($values, $error)) {
					$passed = false;
				}
				
				$current['entry'] = $entry;
			}
			
			if (!$passed) return self::__ERROR_VALIDATING__;
			
			return self::__OK__;
		}
		
		public function commit() {
			// Find existing entries:
			$existing = array();
			
			if ($this->getUniqueField() != '') {
				$entryManager = new EntryManager($this->_Parent);
				$fieldManager = new FieldManager($this->_Parent);
				$field = $fieldManager->fetch($this->getUniqueField());
				
				if (!empty($field) and $field->canImport()) {
					foreach ($this->_entries as $index => $current) {
						$entry = $current['entry'];
						$data = $entry->getData($this->getUniqueField());
						$where = $joins = $group = null;
						
						$field->buildImportRetrivalSQL($data, $joins, $where);
						
						$group = $field->requiresSQLGrouping();
						$entries = $entryManager->fetch(null, $this->getSection(), 1, null, $where, $joins, false, true);
						
						if (is_array($entries)) {
							$entry = current($entries);
							$existing[$index] = $entry->get('id');
							
						} else {
							$existing[$index] = null;
						}
					}
				}
			}
			
			// Commit entries:
			foreach ($this->_entries as $index => $current) {
				$entry = $current['entry'];
				
				// Matches an existing entry:
				if (!empty($existing[$index])) {
					// Update the entry:
					if ($this->canUpdate()) {
						$entry->set('id', $existing[$index]);
						
					// Skip this entry:
					} else {
						continue;
					}
				}
				
				$entry->commit();
			}
		}
	}
	
?>