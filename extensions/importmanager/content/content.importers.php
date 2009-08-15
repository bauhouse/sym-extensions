<?php

	require_once(TOOLKIT . '/class.administrationpage.php');
	
	class contentExtensionImportManagerImporters extends AdministrationPage {
		protected $_driver = null;
		protected $_uri = null;
		
		public function __construct(&$parent){
			parent::__construct($parent);
			
			$this->_uri = URL . '/symphony/extension/importmanager';
			$this->_driver = $this->_Parent->ExtensionManager->create('importmanager');
		}
		
		public function build($context) {
			if (@$context[0] == 'edit' or @$context[0] == 'new') {
				$this->__prepareEdit($context);
				
			} else {
				$this->__prepareIndex();
			}
			
			parent::build($context);
		}
		
	/*-------------------------------------------------------------------------
		Edit
	-------------------------------------------------------------------------*/
		
		public function __prepareEdit($context) {
			
		}
		
		public function __actionNew() {
			$this->__actionEdit();
		}
		
		public function __actionEdit() {
			
		}
		
		public function __viewNew() {
			self::__viewEdit();
		}
		
		public function __viewEdit() {
			
		}
		
	/*-------------------------------------------------------------------------
		Index
	-------------------------------------------------------------------------*/
		
		protected $_pagination = null;
		protected $_column = 'name';
		protected $_columns = array();
		protected $_direction = 'name';
		protected $_templates = array();
		
		public function __prepareIndex() {
			$manager = new ImportManager();
			$this->_importers = $manager->listAll();
			
			
		}
		
		public function __actionIndex() {
			
		}
		
		public function __viewIndex() {
			$this->setPageType('table');
			$this->setTitle('Symphony &ndash; Importers');
			
			$tableHead = array(
				array('Name', 'col'),
				array('Version', 'col'),
				array('Author', 'col')
			);
			$tableBody = array();
			
			if (!is_array($this->_importers) or empty($this->_importers)) {
				$tableBody = array(
					Widget::TableRow(array(Widget::TableData(__('None Found.'), 'inactive', null, count($tableHead))))
				);
				
			} else {
				foreach ($this->_importers as $importer) {
					$importer = (object)$importer;
					
					$col_name = Widget::TableData(
						Widget::Anchor(
							$this->_driver->truncateValue($importer->name),
							$this->_uri . "/importers/edit/{$importer->handle}/"
						)
					);
					$col_name->appendChild(Widget::Input("items[{$importer->id}]", null, 'checkbox'));
					
					$col_version = Widget::TableData(
						$this->_driver->truncateValue($importer->version)
					);
					
					$col_author = Widget::TableData(
						$this->_driver->truncateValue($importer->version)
					);
					
					if (
						isset($importer->author['website'])
						and preg_match('/^[^\s:\/?#]+:(?:\/{2,3})?[^\s.\/?#]+(?:\.[^\s.\/?#]+)*(?:\/[^\s?#]*\??[^\s?#]*(#[^\s#]*)?)?$/', $importer->author['website'])
					) {
						$col_author = Widget::Anchor($importer->author['name'], General::validateURL($importer->author['website']));
						
					} elseif (
						isset($importer->author['email'])
						and preg_match('/^\w(?:\.?[\w%+-]+)*@\w(?:[\w-]*\.)+?[a-z]{2,}$/i', $importer->author['email'])
					) {
						$col_author = Widget::Anchor($importer->author['name'], 'mailto:' . $importer->author['email']);	
						
					} else {
						$col_author = $importer->author['name'];
					}
					
					$col_author = Widget::TableData($col_author);
					
					$tableBody[] = Widget::TableRow(array(
						$col_name, $col_version, $col_author
					));
				}
			}
			
			$table = Widget::Table(
				Widget::TableHead($tableHead), null, 
				Widget::TableBody($tableBody)
			);
			
			$this->Form->appendChild($table);
		}
	}
	
?>