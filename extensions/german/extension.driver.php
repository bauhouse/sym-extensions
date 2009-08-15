<?php

	Class extension_german extends Extension{

		public function about(){
			
			return array(
			
				'name' => 'Deutsche Übersetzung',
				'version' => '1.0',
				'release-date' => '2009-01-23',
				'author' => array(
					'name' => 'Nils Hörrmann',
					'website' => 'http://nilshoerrmann.de',
					'email' => 'post@nilshoerrmann.de'
				)
			
			);
			
		}
		
		public function getSubscribedDelegates(){

			return array(

				array(
					'page' => '/administration/',
					'delegate' => 'NavigationPreRender',
					'callback' => '__translateNavigation'
				),
							
				array(
					'page' => '/backend/',
					'delegate' => 'InitaliseAdminPageHead',
					'callback' => '__addStyles'
				),				

			);

		}

		public function __translateNavigation($context) {
		
			if (!is_array($context['navigation'])) return;

			for ($i = 0; $i < count($context['navigation']); $i++) {
				$context['navigation'][$i]['name'] = @__($context['navigation'][$i]['name']);
				if (is_array($context['navigation'][$i]['children'])) {
					for ($c = 0; $c < count($context['navigation'][$i]['children']); $c++) {
						$context['navigation'][$i]['children'][$c]['name'] = @__($context['navigation'][$i]['children'][$c]['name']);
					}
				}
			}
		
		}
		
		public function __addStyles($context){

			$context['parent']->Page->addStylesheetToHead(URL . '/extensions/german/assets/admin.de.css', 'screen', 1000);

		}
		
		public function enable() {
		
			$this->_Parent->Configuration->set('lang', 'de', 'symphony');
			$this->_Parent->Configuration->set('date_format', 'd.m.Y', 'region');
			return $this->_Parent->saveConfig();		
		
		}
		
		public function disable() {
		
			$this->_Parent->Configuration->set('lang', 'en', 'symphony');
			$this->_Parent->Configuration->set('date_format', 'Y/m/d', 'region');
			return $this->_Parent->saveConfig();		
		
		}
		
		public function uninstall() {
		
			$this->_Parent->Configuration->set('lang', 'en', 'symphony');
			return $this->_Parent->saveConfig();		
		
		}
		
	}