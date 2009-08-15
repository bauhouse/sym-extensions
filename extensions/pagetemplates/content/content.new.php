<?php

	require_once(CONTENT . '/content.blueprintspages.php');
	require_once(EXTENSIONS . '/pagetemplates/lib/class.templateform.php');

	class contentExtensionPageTemplatesNew extends contentBlueprintsPages {

		function view(){
			templateForm::render();
		}
		
		function action(){
			if(@array_key_exists('save', $_POST['action'])){

				$fields = $_POST['fields'];
				
				$this->_errors = array();

				if(!isset($fields['body']) || trim($fields['body']) == '') $this->_errors['body'] = __('Body is a required field');
				elseif(!General::validateXML($fields['body'], $errors, false, new XSLTProcess())) $this->_errors['body'] = __('This document is not well formed. The following error was returned: <code>%s</code>', array($errors[0]['message']));
			
				if(!isset($fields['title']) || trim($fields['title']) == '') $this->_errors['title'] = __('Title is a required field');

				if(trim($fields['type']) != '' && preg_match('/(index|404|403)/i', $fields['type'])){
					
					$haystack = strtolower($fields['type']);
					
					if(preg_match('/\bindex\b/i', $haystack, $matches) && $row = $this->_Parent->Database->fetchRow(0, "SELECT * FROM `tbl_pages_types` WHERE `type` = 'index' LIMIT 1")){					
						$this->_errors['type'] = __('An index type page already exists.');
					}
					
					elseif(preg_match('/\b404\b/i', $haystack, $matches) && $row = $this->_Parent->Database->fetchRow(0, "SELECT * FROM `tbl_pages_types` WHERE `type` = '404' LIMIT 1")){	
						$this->_errors['type'] = __('A 404 type page already exists.');
					}	
					
					elseif(preg_match('/\b403\b/i', $haystack, $matches) && $row = $this->_Parent->Database->fetchRow(0, "SELECT * FROM `tbl_pages_types` WHERE `type` = '403' LIMIT 1")){	
						$this->_errors['type'] = __('A 403 type page already exists.');
					}
										
				}			

				if(empty($this->_errors)){

					## Manipulate some fields
					$fields['sortorder'] = $this->_Parent->Database->fetchVar('next', 0, "SELECT MAX(sortorder) + 1 as `next` FROM `tbl_pages_templates` LIMIT 1");

					if(empty($fields['sortorder']) || !is_numeric($fields['sortorder'])) $fields['sortorder'] = 1;
										
					$filename = Lang::createHandle($fields['title']);	

					if($fields['params']) $fields['params'] = trim(preg_replace('@\/{2,}@', '/', $fields['params']), '/'); //trim($fields['params'], '/');
					
					## Clean up type list
					$types = preg_split('/,\s*/', $fields['type'], -1, PREG_SPLIT_NO_EMPTY);
					$types = @array_map('trim', $types);
					unset($fields['type']);
					
					//if(trim($fields['type'])) $fields['type'] = preg_replace('/\s*,\s*/i', ', ', $fields['type']);
					//else $fields['type'] = NULL;			

					## Manipulate some fields
					$fields['parent'] = ($fields['parent'] != 'None' ? $fields['parent'] : NULL);			
					
					$fields['data_sources'] = @implode(',', $fields['data_sources']);			
					$fields['events'] = @implode(',', $fields['events']);	
					
					$fields['path'] = NULL;
					if($fields['parent']) $fields['path'] = $this->_Parent->resolvePagePath(intval($fields['parent']));
					
					## Duplicate
					if($this->_Parent->Database->fetchRow(0, "SELECT * FROM `tbl_pages_templates` 
										 WHERE `title` = '" . $fields['title'] . "' 
										 AND `path` ".($fields['path'] ? " = '".$fields['path']."'" : ' IS NULL')." 
										 LIMIT 1")){
											
						$this->_errors['title'] = __('A template with that title already exists');

					}
					
					else{	

						## Write the file
						if(!is_dir(PAGES . '/templates')) {
							mkdir(PAGES . '/templates', 0775);
						}
						
						if(!$write = General::writeFile(PAGES . "/templates/$filename.xsl" , $fields['body'], $this->_Parent->Configuration->get('write_mode', 'file')))
							$this->pageAlert(__('Page could not be written to disk. Please check permissions on <code>/workspace/pages/templates</code>.'), Alert::ERROR);

						## Write Successful, add record to the database
						else{

							## No longer need the body text
							unset($fields['body']);

							## Insert the new data
							if(!$this->_Parent->Database->insert($fields, 'tbl_pages_templates')) $this->pageAlert(__('Unknown errors occurred while attempting to save. Please check your <a href="%s">activity log</a>.', array(URL.'/symphony/system/log/')), Alert::ERROR);

							else{
								
								$page_id = $this->_Parent->Database->getInsertID();

								if(is_array($types) && !empty($types)){
									foreach($types as $type) $this->_Parent->Database->insert(array('page_id' => $page_id, 'type' => $type), 'tbl_pages_types');
								}

								## TODO: Fix Me
								###
								# Delegate: Create
								# Description: After saving the Page. The Page's database ID is provided.
								//$ExtensionManager->notifyMembers('Create', getCurrentPage(), array('page_id' => $page_id));

			                    redirect(URL . "/symphony/extension/pagetemplates/edit/$page_id/created/");

							}
						}
					}
				}
				
				if(is_array($this->_errors) && !empty($this->_errors)) $this->pageAlert(__('An error occurred while processing this form. <a href="#error">See below for details.</a>'), Alert::ERROR);				
			}			
		}
		
	}
