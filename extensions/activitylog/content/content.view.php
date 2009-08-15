<?php

	require_once(TOOLKIT . '/class.administrationpage.php');
	
	class contentExtensionActivityLogView extends AdministrationPage {
		public function __construct(&$parent){
			parent::__construct($parent);
		}
		
		public function __viewIndex() {
			$this->addStylesheetToHead(URL . '/extensions/activitylog/assets/view.css', 'screen', 1000);
			$this->setPageType('form');
			$this->setTitle(__('Symphony &ndash; Activity Log'));
			$this->appendSubheading(__('Activity Log'));
			
			$driver = $this->_Parent->ExtensionManager->create('activitylog');
			$items = $driver->readLog();
			$table = new XMLElement('table');
			
			foreach ($items as $item) {
				$row = new XMLElement('tr');
				
				$cell = new XMLElement('td');
				$cell->setValue($item->message);
				$row->appendChild($cell);
				
				$cell = new XMLElement('td');
				$cell->setValue($this->getTime($item->timestamp));
				$row->appendChild($cell);
				
				$table->appendChild($row);
			}
			
			$this->Form->appendChild($table);
		}
		
		protected static function getTime($fromTime) {
			$toTime = time();
			$distanceInSeconds = round(abs($toTime - $fromTime));
			$distanceInMinutes = round($distanceInSeconds / 60);
			
			if ( $distanceInMinutes <= 1 ) {
				if ( $distanceInSeconds < 5 ) {
					return 'less than 5 seconds';
				}
				if ( $distanceInSeconds < 10 ) {
					return 'less than 10 seconds';
				}
				if ( $distanceInSeconds < 20 ) {
					return 'less than 20 seconds';
				}
				if ( $distanceInSeconds < 40 ) {
					return 'about half a minute';
				}
				if ( $distanceInSeconds < 60 ) {
					return 'less than a minute';
				}
				
				return '1 minute';
			}
			
			if ( $distanceInMinutes < 45 ) {
				return $distanceInMinutes . ' minutes';
			}
			
			if ( $distanceInMinutes < 90 ) {
				return 'about 1 hour';
			}
			
			if ( $distanceInMinutes < 1440 ) {
				return 'about ' . round(floatval($distanceInMinutes) / 60.0) . ' hours';
			}
			
			if ( $distanceInMinutes < 2880 ) {
				return '1 day';
			}
			
			if ( $distanceInMinutes < 43200 ) {
				return 'about ' . round(floatval($distanceInMinutes) / 1440) . ' days';
			}
			
			if ( $distanceInMinutes < 86400 ) {
				return 'about 1 month';
			}
			
			if ( $distanceInMinutes < 525600 ) {
				return round(floatval($distanceInMinutes) / 43200) . ' months';
			}
			
			if ( $distanceInMinutes < 1051199 ) {
				return 'about 1 year';
			}
			
			return 'over ' . round(floatval($distanceInMinutes) / 525600) . ' years';
		}
	}
	
?>