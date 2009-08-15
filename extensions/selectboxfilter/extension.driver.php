<?php
	
	class Extension_SelectBoxFilter extends Extension {
	/*-------------------------------------------------------------------------
		Definition:
	-------------------------------------------------------------------------*/
		
		public function about() {
			return array(
				'name'			=> 'Select Box Filter',
				'version'		=> '1.0.0',
				'release-date'	=> '2009-03-23',
				'author'		=> array(
					'name'			=> 'Rowan Lewis',
					'website'		=> 'http://pixelcarnage.com/',
					'email'			=> 'rowan@pixelcarnage.com'
				),
				'description'	=> 'Automatically add a filter to select boxes.'
			);
		}
		
		public function getSubscribedDelegates() {
			return array(
				array(
					'page'		=> '/backend/',
					'delegate'	=> 'InitaliseAdminPageHead',
					'callback'	=> 'initaliseAdminPageHead'
				),
				array(
					'page'		=> '/backend/',
					'delegate'	=> 'AppendElementBelowView',
					'callback'	=> 'appendOrderFieldId'
				)
			);
		}
		
		public function initaliseAdminPageHead($context) {
			$page = $context['parent']->Page;
			
			$page->addScriptToHead(URL . '/extensions/selectboxfilter/assets/jquery.js', 3257230);
			$page->addScriptToHead(URL . '/extensions/selectboxfilter/assets/publish.js', 3257231);
			$page->addStylesheetToHead(URL . '/extensions/selectboxfilter/assets/publish.css', 'screen', 3257231);
		}
	}
		
?>