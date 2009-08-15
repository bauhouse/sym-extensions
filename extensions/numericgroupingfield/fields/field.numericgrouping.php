<?php
	
	if (!defined('__IN_SYMPHONY__')) die('<h2>Symphony Error</h2><p>You cannot directly access this file</p>');
	
	class FieldNumericGrouping extends Field {
	/*-------------------------------------------------------------------------
		Definition:
	-------------------------------------------------------------------------*/
		
		public function __construct(&$parent) {
			parent::__construct($parent);
			
			$this->_name = 'Numeric Grouping';
			
			$this->set('show_column', 'no');
		}
		
		/*
		public function createTable() {
			$field_id = $this->get('id');
			
			return $this->_engine->Database->query("
				CREATE TABLE IF NOT EXISTS `tbl_entries_data_{$field_id}` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`entry_id` int(11) unsigned NOT NULL,
					`file` varchar(255) default NULL,
					`size` int(11) unsigned NOT NULL,
					`mimetype` varchar(50) NOT NULL,
					`meta` varchar(255) default NULL,
					PRIMARY KEY (`id`),
					KEY `entry_id` (`entry_id`),
					KEY `file` (`file`),
					KEY `mimetype` (`mimetype`)
				)
			");
		}
		*/
		
		public function allowDatasourceOutputGrouping() {
			return true;
		}
		
		public function getExampleFormMarkup() {
			return null;
		}
		
		public function fetchIncludableElements() {
			return null;
		}
		
	/*-------------------------------------------------------------------------
		Settings:
	-------------------------------------------------------------------------*/
		
		public function displaySettingsPanel(&$wrapper, $errors = null) {
			parent::displaySettingsPanel($wrapper, $errors);
			
			$order = $this->get('sortorder');
			
		// Group Size ---------------------------------------------------------
			
			$ignore = array(
				'events',
				'data-sources',
				'text-formatters',
				'pages',
				'utilities'
			);
			$directories = General::listDirStructure(WORKSPACE, true, 'asc', DOCROOT, $ignore);	   	
			
			$label = Widget::Label('Group Size');
			$input = Widget::Input(
				"fields[{$order}][nth_entry]", $this->get('nth_entry')
			);
			
			$label->appendChild($input);
			$wrapper->appendChild($label);
		}
		
		public function commit() {
			if (!parent::commit() or $field_id === false) return false;
			
			$field_id = $this->get('id');
			$handle = $this->handle();
			
			$fields = array(
				'field_id'		=> $field_id,
				'nth_entry'		=> (integer)$this->get('nth_entry')
			);
			
			$this->_engine->Database->query("
				DELETE FROM
					`tbl_fields_{$handle}`
				WHERE
					`field_id` = '{$field_id}'
				LIMIT 1
			");
			
			return $this->_engine->Database->insert($fields, "tbl_fields_{$handle}");
		}
		
	/*-------------------------------------------------------------------------
		Grouping:
	-------------------------------------------------------------------------*/
		
		public function groupRecords($records) {
			if (!is_array($records) or empty($records)) return;
			
			$name = $this->get('element_name');
			$groups = array($name => array());
			$nth_entry = (integer)$this->get('nth_entry');
			$count = 0; $value = 0;
			
			foreach ($records as $record) {
				// New group:
				if ($count % $nth_entry == 0) $value++; $count++;
				
				if (!isset($groups[$name][$value])) {
					$groups[$name][$value] = array(
						'attr'		=> array(
							'from'		=> $count,
							'to'		=> $count - 1
						),
						'records'	=> array(),
						'groups'	=> array()
					);
				}
				
				$groups[$name][$value]['records'][] = $record;
				$groups[$name][$value]['attr']['to']++;
			}
			
			return $groups;
		}
	}
	
?>