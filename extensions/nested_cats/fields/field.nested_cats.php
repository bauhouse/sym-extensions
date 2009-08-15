<?php

	if(!defined('__IN_SYMPHONY__')) die('<h2>Symphony Error</h2><p>You cannot directly access this file</p>');
	
	Class fieldNested_Cats extends Field{
		
		protected $_driver = null;
		
		function __construct(&$parent){
			parent::__construct($parent);
			$this->_name = 'Nested Cats';
			$this->_required = true;
			
			$this->_driver = $this->_engine->ExtensionManager->create('nested_cats');
			
			// Set default
			$this->set('show_column', 'no'); 
			$this->set('required', 'no');
		}

		function canFilter(){
			return true;
		}

		function allowDatasourceOutputGrouping(){
			return true;
		}
		
		function allowDatasourceParamOutput(){
			return true;
		}

		public function getParameterPoolValue($data){
			return $data['handle'];
		}		

		public function set($field, $value){
			if($field == 'related_field_id' && !is_array($value)){
				$value = explode(',', $value);
			}
			$this->_fields[$field] = $value;
		}
		
		public function setArray($array){
			if(empty($array) || !is_array($array)) return;
			foreach($array as $field => $value) $this->set($field, $value);
		}		

		function groupRecords($records){

			if(!is_array($records) || empty($records)) return;

			$groups = array($this->get('element_name') => array());

			foreach($records as $r){
			
				$data = $r->getData($this->get('id'));

				$value = $data['relation_id'];

				if(!isset($groups[$this->get('element_name')][$value])){
					$groups[$this->get('element_name')][$value] = array('attr' => array('link-id' => $data['relation_id'], 'link-handle' => $data['handle']));	
				}	

				$groups[$this->get('element_name')][$value]['records'][] = $r;

			}

			return $groups;

		}

		function prepareTableValue($data, XMLElement $link=NULL){
			
			if(!is_array($data) || (is_array($data) && !isset($data['relation_id']))) return parent::prepareTableValue(NULL);

			$link = Widget::Anchor($data['value'], URL . '/symphony/extension/nested_cats/overview/edit/' . $data['relation_id']);

			return $link;
		}


		function processRawFieldData($data, &$status, $simulate=false, $entry_id=NULL){

			$status = self::__OK__;

			if(empty($data)) return NULL;

			$cat = $this->_driver->getCat($data);
			
			$result = array();

			$result['relation_id'][] = $cat['id'];
			$result['handle'][] = $cat['handle'];
			$result['value'][] = $cat['title'];

			return $result;

		}

		public function appendFormattedElement(&$wrapper, $data, $encode = false) {
			if (!is_array($data) || empty($data)) return;

			$list = new XMLElement($this->get('element_name'));
			
			if (!is_array($data['relation_id'])) {
				$data['relation_id'] = array($data['relation_id']);
				$data['handle'] = array($data['handle']);
				$data['value'] = array($data['value']);
			}

			foreach ($data['relation_id'] as $k => $v) {

				$list->appendChild(new XMLElement('item', General::sanitize($data['value'][$k]), array(
					'handle'	=> $data['handle'][$k],
					'id'		=> $v,
				)));
			}
			
			$wrapper->appendChild($list);
		}
		
		function displayPublishPanel(&$wrapper, $data=NULL, $flagWithError=NULL, $fieldnamePrefix=NULL, $fieldnamePostfix=NULL){

			$current = is_array($data['relation_id']) ? array_reverse($data['relation_id']) : array($data['relation_id']);
			
			if(!$root = $this->get('related_field_id')){
			
				$select = Widget::Select(NULL, NULL, array('disabled' => 'true'));
			
			} else {
			
				$select = $this->_driver->buildSelectField('id',$root[0], $current[0], NULL, $this->get('element_name'),$fieldnamePrefix, $fieldnamePostfix);
			
			}

			$fieldname = 'fields'.$fieldnamePrefix.'['.$this->get('element_name').']'.$fieldnamePostfix;
		
			$label = Widget::Label($this->get('label'));

			$label->appendChild($select);
				
			if($flagWithError != NULL) $wrapper->appendChild(Widget::wrapFormElementWithError($label, $flagWithError));
			else $wrapper->appendChild($label); 

		}
		
		function commit(){

			if(!parent::commit()) return false;
		
			$id = $this->get('id');

			if($id === false) return false;

			$fields = array();

			$fields['field_id'] = $id;
			if($this->get('related_field_id') != '') $fields['related_field_id'] = $this->get('related_field_id');
			
			$fields['related_field_id'] = implode(',', $this->get('related_field_id'));
			
			$this->Database->query("DELETE FROM `tbl_fields_".$this->handle()."` WHERE `field_id` = '$id'");

			if(!$this->Database->insert($fields, 'tbl_fields_' . $this->handle())) return false;

			$this->removeSectionAssociation($id);

			foreach($this->get('related_field_id') as $field_id){
				$this->createSectionAssociation(NULL, $id, $field_id);
			}

			return true;
					
		}

		function buildSortingSQL(&$joins, &$where, &$sort, $order='ASC'){

			$joins .= "INNER JOIN `tbl_entries_data_".$this->get('id')."` AS `ed` ON (`e`.`id` = `ed`.`entry_id`) ";
			$sort = 'ORDER BY ' . (in_array(strtolower($order), array('random', 'rand')) ? 'RAND()' : "`ed`.`relation_id` $order");
		}

		function buildDSRetrivalSQL($data, &$joins, &$where, $andOperation=false){

			$field_id = $this->get('id');

			if($tree = $this->_driver->getTree('handle', $data[0])) {
				$cats = array();
				foreach($tree as $cat) {
					$cats[] = $cat['handle'];
				}
				
				unset($tree);
			}


			if($andOperation): NULL; ### Disabled for now
/*
				foreach($data as $key => $bit){
				
					$joins .= " LEFT JOIN `tbl_entries_data_$field_id` AS `t$field_id$key` ON (`e`.`id` = `t$field_id$key`.entry_id) ";
					//$where .= " AND `t$field_id$key`.relation_id = '$bit' ";
					$where .= " AND `t$field_id$key`.handle = '$bit' ";
				}
*/
			else:

				$joins .= " LEFT JOIN `tbl_entries_data_$field_id` AS `t$field_id` ON (`e`.`id` = `t$field_id`.entry_id) ";
				$where .= " AND `t$field_id`.handle IN ('".@implode("', '", $cats)."') ";

			endif;

			return true;

		}

		function displaySettingsPanel(&$wrapper, $errors=NULL){		

			parent::displaySettingsPanel($wrapper, $errors);

			$div = new XMLElement('div', NULL, array('class' => 'group'));
			
			$label = Widget::Label('Root');
			
			$sectionManager = new SectionManager($this->_engine);
		  	$sections = $sectionManager->fetch(NULL, 'ASC', 'name');
			$field_groups = array();

			if(is_array($sections) && !empty($sections)){
				foreach($sections as $section) $field_groups[$section->get('id')] = array('fields' => $section->fetchFields(), 'section' => $section);
			}

			$current = $this->get('related_field_id');
			
			$select = $this->_driver->buildSelectField('lft', 0, $current[0], NULL, 'related_field_id', '['.$this->get('sortorder').']', '[]', NULL, true);
			$label->appendChild($select);

			$div->appendChild($label);
						
			if(isset($errors['related_field_id'])) $wrapper->appendChild(Widget::wrapFormElementWithError($div, $errors['related_field_id']));
			else $wrapper->appendChild($div);

			$this->appendShowColumnCheckbox($wrapper);
			$this->appendRequiredCheckbox($wrapper);
						
		}


		function createTable(){

			return $this->_engine->Database->query(

				"CREATE TABLE IF NOT EXISTS `tbl_entries_data_" . $this->get('id') . "` (
				`id` int(11) unsigned NOT NULL auto_increment,
				`entry_id` int(11) unsigned NOT NULL,
				`relation_id` int(11) unsigned NOT NULL,
				`handle` varchar(50) NOT NULL,
				`value` varchar(250) NOT NULL,
				PRIMARY KEY  (`id`),
				KEY `entry_id` (`entry_id`),
				KEY `relation_id` (`relation_id`)
				) TYPE=MyISAM;"
			);
		}

		public function getExampleFormMarkup(){
			return Widget::Input('fields['.$this->get('element_name').']', '...', 'hidden');
		}			

	}
