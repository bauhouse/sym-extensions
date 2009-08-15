<?php

	include_once(TOOLKIT . '/class.textformattermanager.php');

	Class extension_textformatter_labels extends Extension{

		public function about() {
			return array(
				'name'         => 'Textformatter Labels',
				'version'      => '1.0.1',
				'release-date' => '2009-06-25',
				'author'       => array(
					'name'    => 'Michael Eichelsdoerfer',
					'website' => 'http://www.michael-eichelsdoerfer.de',
					'email'   => 'info@michael-eichelsdoerfer.de'
				)
			);
		}

		public function getSubscribedDelegates(){
			return array(
				array(
					'page'     => '/backend/',
					'delegate' => 'InitaliseAdminPageHead',
					'callback' => 'appendStylesheet'
				),
				array(
					'page'     => '/system/preferences/',
					'delegate' => 'AddCustomPreferenceFieldsets',
					'callback' => 'appendPreferences'
				),
				array(
					'page'     => '/system/preferences/',
					'delegate' => 'Save',
					'callback' => 'uninstall'
				),
				array(
					'page'     => '/backend/',
					'delegate' => 'ModifyTextareaFieldPublishWidget',
					'callback' => 'modifyLabel'
				),

			);
		}

		public function getFormatters(){
			$TFM = new TextformatterManager($this->_Parent);
			return $TFM->listAll();			
		}

	/*-------------------------------------------------------------------------
		Delegates:
	-------------------------------------------------------------------------*/

		public function uninstall($context){
			$this->_Parent->Configuration->remove('textformatter_labels');
			$this->_Parent->saveConfig();
		}

		public function appendStylesheet($context) {
			$page = $context['parent']->Page;
			if ($page instanceof ContentPublish and $page->_context['page'] == 'edit') {
				$page->addStylesheetToHead(URL . '/extensions/textformatter_labels/assets/tfl.css', 'screen', 80);
			}
		}
		
		public function appendPreferences($context){

			$formatters = $this->getFormatters();

			$setttings = new XMLElement('fieldset');
			$setttings->setAttribute('class', 'settings');
			$setttings->appendChild(new XMLElement('legend', 'Textformatter Labels'));

			if(!empty($formatters) && is_array($formatters)){
				foreach($formatters as $handle => $about){

					$group = new XMLElement('div');
					$group->setAttribute('class', 'group');

					$label = Widget::Label($about['name'] . ': Label Text (optional override)');
					$label->appendChild(Widget::Input('settings[textformatter_labels]['.$handle.'_text]', General::Sanitize($context['parent']->Configuration->get($handle.'_text', 'textformatter_labels'))));
					$group->appendChild($label);

					$label = Widget::Label($about['name'] . ': Link URL (optional)');
					$label->appendChild(Widget::Input('settings[textformatter_labels]['.$handle.'_url]', General::Sanitize($context['parent']->Configuration->get($handle.'_url', 'textformatter_labels'))));
					$group->appendChild($label);

					$setttings->appendChild($group);
				}
			}

			$context['wrapper']->appendChild($setttings);

		}

		public function modifyLabel($context){

			// get configuration
			$config = $this->_Parent->Configuration->get();

			// get formatter handle
			if(!$handle = $context['field']->get('formatter')) return;

			// get formatter name
			$formatters = $this->getFormatters();
			if(!$formatter_name = $formatters[$handle]['name']) return;

			// get formatter configuration values
			$formatter_text = stripslashes($config['textformatter_labels'][$handle.'_text']);
			$formatter_url  = stripslashes($config['textformatter_labels'][$handle.'_url']);

			// build output string
			$tfl = ' - ' . ($formatter_url ? '<a href="' . $formatter_url . '" target="_blank">' : '') . ($formatter_text ? $formatter_text : $formatter_name) . ($formatter_url ? '</a>' : '');

			// output
			$output = new XMLElement('em', $tfl, array('class' => 'textformatter-labels'));
			$context['label']->appendChild($output);

		}

	}
