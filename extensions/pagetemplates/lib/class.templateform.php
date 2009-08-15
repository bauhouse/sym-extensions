<?php

	require_once(CONTENT . '/content.blueprintspages.php');
	require_once(TOOLKIT . '/class.general.php');

	class templateForm {
	
		function render($submittext = 'Create Template', $formaction){
			$this->setPageType('form');
			if(isset($formaction)) $this->Form->setAttribute('action',$formaction);
						
			$fields = array();
			
			if($this->_context[0]){
				if(!$page_id = $this->_context[0]) redirect(URL . '/symphony/extension/pagetemplates/manage');
					
				if(!$existing = $this->_Parent->Database->fetchRow(0, "SELECT * FROM `tbl_pages_templates` WHERE `id` = '$page_id' LIMIT 1"))
					$this->_Parent->customError(E_USER_ERROR, __('Page not found'), __('The page you requested to edit does not exist.'), false, true, 'error', array('header' => 'HTTP/1.0 404 Not Found'));
			}
			
			if(isset($this->_context[1])){
				switch($this->_context[1]){
					
					case 'saved':
						$this->pageAlert(
							__(
								'Template updated at %1$s. <a href="%2$s">Create another?</a> <a href="%3$s">View all Templates</a>', 
								array(
									DateTimeObj::get(__SYM_TIME_FORMAT__), 
									URL . '/symphony/extension/pagetemplates/new/', 
									URL . '/symphony/extension/pagetemplates/manage/' 
								)
							), 
							Alert::SUCCESS);						
						
						break;
						
					case 'created':
						$this->pageAlert(
							__(
								'Template created at %1$s. <a href="%2$s">Create another?</a> <a href="%3$s">View all Templates</a> <a href="%4$s">Create Page from Template</a>', 
								array(
									DateTimeObj::get(__SYM_TIME_FORMAT__), 
									URL . '/symphony/extension/pagetemplates/new/', 
									URL . '/symphony/extension/pagetemplates/manage/',
									URL . '/symphony/extension/pagetemplates/spawn/' . $page_id 
								)
							), 
							Alert::SUCCESS);
						break;
					
				}
			}
			
			if(isset($_POST['fields'])){
				$fields = $_POST['fields'];
			}
			
			elseif($this->_context[0]){
				
				$fields = $existing;
				$filename = Lang::createHandle($existing['title']);

				$types = $this->_Parent->Database->fetchCol('type', "SELECT `type` FROM `tbl_pages_types` WHERE page_id = '$page_id' ORDER BY `type` ASC");		
				$fields['type'] = @implode(', ', $types);

				$fields['data_sources'] = preg_split('/,/i', $fields['data_sources'], -1, PREG_SPLIT_NO_EMPTY);
				$fields['events'] = preg_split('/,/i', $fields['events'], -1, PREG_SPLIT_NO_EMPTY);
				$fields['body'] = @file_get_contents(PAGES . '/templates/' . $filename . '.xsl');

			}
			
			else{

				$fields['body'] = '<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:output method="xml"
	doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
	doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"
	omit-xml-declaration="yes"
	encoding="UTF-8"
	indent="yes" />

<xsl:template match="/">
	
</xsl:template>
	
</xsl:stylesheet>';
	
			}
			
			$title = ($this->_context[0] ? $fields['title'] : NULL);
			if(trim($title) == '') $title = $existing['title'];
			
			$this->setTitle(__(($title ? '%1$s &ndash; %2$s &ndash; %3$s' : '%1$s &ndash; %2$s'), array(__('Symphony'), __('Page Templates'), $title)));
			$this->appendSubheading(($title ? $title : __('Untitled')));

			$div = new XMLElement('div');
			$div->setAttribute('id', 'configure');
						
			$div->appendChild(new XMLElement('h3', __('URL Settings')));
			$group = new XMLElement('div');
			$group->setAttribute('class', 'triple group');
			
			$pages = $this->_Parent->Database->fetch("SELECT * FROM `tbl_pages` " . ($this->_context[0] ? "WHERE `id` != '$page_id' " : '') . "ORDER BY `title` ASC");
			
			$label = Widget::Label(__('Parent Page'));
			
			$options = array(
				array('', false, '/')
			);
			
			if (is_array($pages) and !empty($pages)) {
				if (!function_exists('__compare_pages')) {
					function __compare_pages($a, $b) {
						return strnatcasecmp($a[2], $b[2]);
					}
				}
				
				foreach ($pages as $page) {
					$options[] = array(
						$page['id'], $fields['parent'] == $page['id'],
						'/' . $this->_Parent->resolvePagePath($page['id'])
					);
				}
				
				usort($options, '__compare_pages');
			}
			
			$label->appendChild(Widget::Select('fields[parent]', $options));		
			$group->appendChild($label);
			
			$label = Widget::Label(__('URL Handle'));
			$label->appendChild(Widget::Input('fields[handle]', $fields['handle']));
			$group->appendChild((isset($this->_errors['handle']) ? $this->wrapFormElementWithError($label, $this->_errors['handle']) : $label));
				
			$label = Widget::Label(__('URL Parameters'));
			$label->appendChild(Widget::Input('fields[params]', $fields['params']));				
			$group->appendChild($label);
			
			$div->appendChild($group);
		
			$div->appendChild(new XMLElement('h3', __('Page Metadata')));
			
			$group = new XMLElement('div');
			$group->setAttribute('class', 'triple group');

			$label = Widget::Label(__('Events'));
			
			$EventManager = new EventManager($this->_Parent);
			$events = $EventManager->listAll();
			
			$options = array();
			if(is_array($events) && !empty($events)){		
				foreach($events as $name => $about) $options[] = array($name, @in_array($name, $fields['events']), $about['name']);
			}

			$label->appendChild(Widget::Select('fields[events][]', $options, array('multiple' => 'multiple')));		
			$group->appendChild($label);

			$label = Widget::Label(__('Data Sources'));
			
			$DSManager = new DatasourceManager($this->_Parent);
			$datasources = $DSManager->listAll();
			
			$options = array();
			if(is_array($datasources) && !empty($datasources)){		
				foreach($datasources as $name => $about) $options[] = array($name, @in_array($name, $fields['data_sources']), $about['name']);
			}

			$label->appendChild(Widget::Select('fields[data_sources][]', $options, array('multiple' => 'multiple')));
			$group->appendChild($label);
			
			$div3 = new XMLElement('div');
			$label = Widget::Label(__('Page Type'));
			$label->appendChild(Widget::Input('fields[type]', $fields['type']));
			$div3->appendChild((isset($this->_errors['type']) ? $this->wrapFormElementWithError($label, $this->_errors['type']) : $label));
			
			$ul = new XMLElement('ul');
			$ul->setAttribute('class', 'tags');
			if($types = $this->__fetchAvailablePageTypes()) foreach($types as $type) $ul->appendChild(new XMLElement('li', $type));
			$div3->appendChild($ul);
			
			$group->appendChild($div3);
			$div->appendChild($group);

			$this->Form->appendChild($div);
							
			$fieldset = new XMLElement('fieldset');
			$fieldset->setAttribute('class', 'primary');
			
			$label = Widget::Label(__('Title'));		
			$label->appendChild(Widget::Input('fields[title]', General::sanitize($fields['title'])));
			$fieldset->appendChild((isset($this->_errors['title']) ? $this->wrapFormElementWithError($label, $this->_errors['title']) : $label));
			
			$label = Widget::Label(__('Body'));
			$label->appendChild(Widget::Textarea('fields[body]', 30, 80, General::sanitize($fields['body']), array('class' => 'code')));
			$fieldset->appendChild((isset($this->_errors['body']) ? $this->wrapFormElementWithError($label, $this->_errors['body']) : $label));
			
			$this->Form->appendChild($fieldset);
			
			$utilities = General::listStructure(UTILITIES, array('xsl'), false, 'asc', UTILITIES);
			$utilities = $utilities['filelist'];			
			
			if(is_array($utilities) && !empty($utilities)){
			
				$div = new XMLElement('div');
				$div->setAttribute('class', 'secondary');
				
				$h3 = new XMLElement('h3', __('Utilities'));
				$h3->setAttribute('class', 'label');
				$div->appendChild($h3);
				
				$ul = new XMLElement('ul');
				$ul->setAttribute('id', 'utilities');
			
				$i = 0;
				foreach($utilities as $util){
					$li = new XMLElement('li');

					if ($i++ % 2 != 1) {
						$li->setAttribute('class', 'odd');
					}

					$li->appendChild(Widget::Anchor($util, URL . '/symphony/blueprints/utilities/edit/' . str_replace('.xsl', '', $util) . '/', NULL));
					$ul->appendChild($li);
				}
			
				$div->appendChild($ul);
			
				$this->Form->appendChild($div);
							
			}
			
			$div = new XMLElement('div');
			$div->setAttribute('class', 'actions');
			
			$div->appendChild(Widget::Input('action[save]', ($this->_context[0] && !isset($submittext) ? __('Save Changes') : __($submittext)), 'submit', array('accesskey' => 's')));
			
			if($this->_context[0]){
				$button = new XMLElement('button', __('Delete'));
				$button->setAttributeArray(array('name' => 'action[delete]', 'class' => 'confirm delete', 'title' => __('Delete this template')));
				$div->appendChild($button);
			}
			
			$this->Form->appendChild($div);
		}
	
	}
