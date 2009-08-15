<?php

	require_once(CONTENT . '/content.blueprintspages.php');

	class contentExtensionPageTemplatesManage extends contentBlueprintsPages {
		public function __viewIndex() {
			$this->setPageType('table');
			$this->setTitle(__('%1$s &ndash; %2$s', array(__('Symphony'), __('Page Templates'))));

			$this->appendSubheading(__('Page Templates'), Widget::Anchor(
				__('Create New'), URL . '/symphony/extension/pagetemplates/new/',
				__('Create a new page'), 'create button'
			));
			
			$pages = $this->_Parent->Database->fetch("
				SELECT
					p.*
				FROM
					`tbl_pages_templates` AS p
				ORDER BY
					p.sortorder ASC
			");
			
			$aTableHead = array(
				array(__('Title'), 'col'),
				array(__('<acronym title="Universal Resource Locator">URL</acronym> Parameters'), 'col'),
				array(__('Type'), 'col'),
				array(__('Available Actions'), col)
			);	
			
			$aTableBody = array();
			
			if (!is_array($pages) or empty($pages)) {
				$aTableBody = array(Widget::TableRow(array(
					Widget::TableData(__('None found.'), 'inactive', null, count($aTableHead))
				), 'odd'));
				
			}
			
			else{
				
				$bOdd = true;
				
				foreach ($pages as $page) {
					$page_title = $this->resolvePageTitle($page['id']);
					$page_url = URL . '/' . $this->resolvePagePath($page['id']) . '/';
					$page_edit_url = URL . '/symphony/extension/pagetemplates/edit/' . $page['id'] . '/';
					
					$page_types = $this->_Parent->Database->fetchCol('type', "SELECT `type` FROM `tbl_pages_types` WHERE page_id = '".$page['id']."' ORDER BY `type` ASC");
					
					$col_title = Widget::TableData(Widget::Anchor(
						$page_title, $page_edit_url, $page['handle']
					));
					$col_title->appendChild(Widget::Input("items[{$page['id']}]", null, 'checkbox'));
					
					$col_action = Widget::TableData(Widget::Anchor('New "' . $page_title . '" Page', URL . '/symphony/extension/pagetemplates/spawn/' . $page['id']));
					
					if ($page['params']) {
						$col_params = Widget::TableData(trim($page['params'], '/'));
						
					} else {
						$col_params = Widget::TableData(__('None'), 'inactive');
					}
					
					if (!empty($page_types)) {
						$col_types = Widget::TableData(implode(', ', $page_types));
						
					} else {
						$col_types = Widget::TableData(__('None'), 'inactive');
					}
					
					$aTableBody[] = Widget::TableRow(array($col_title, $col_params, $col_types, $col_action), ($bOdd ? 'odd' : NULL));
					
					$bOdd = !$bOdd;
				}
			}
			
			$table = Widget::Table(
				Widget::TableHead($aTableHead), null, 
				Widget::TableBody($aTableBody), 'orderable'
			);
			
			$this->Form->appendChild($table);
			
			$tableActions = new XMLElement('div');
			$tableActions->setAttribute('class', 'actions');
			
			$options = array(
				array(null, false, __('With Selected...')),
				array('delete', false, __('Delete'))							
			);
			
			$tableActions->appendChild(Widget::Select('with-selected', $options));
			$tableActions->appendChild(Widget::Input('action[apply]', __('Apply'), 'submit'));
			
			$this->Form->appendChild($tableActions);
			
		}
		
		function __actionIndex(){

			$checked = @array_keys($_POST['items']);

			if(is_array($checked) && !empty($checked)){
				switch($_POST['with-selected']) {

					case 'delete':

						$pages = $checked;
						
						## TODO: Fix Me
						###
						# Delegate: Delete
						# Description: Prior to deletion. Provided with an array of pages for deletion that can be modified.
						//$ExtensionManager->notifyMembers('Delete', getCurrentPage(), array('pages' => &$pages));			

						$pagesList = join (', ', array_map ('intval', $pages));

						// 1. Fetch page details
						$query = 'SELECT `id`, `sortorder`, `handle`, `path`, `title` FROM tbl_pages_templates WHERE `id` IN (' . $pagesList .')';
						$details = $this->_Parent->Database->fetch($query);

						$this->_Parent->Database->delete('tbl_pages_templates', " `id` IN('".implode("','",$checked)."')");
						$this->_Parent->Database->delete('tbl_pages_types', " `page_id` IN('".implode("','",$checked)."')");	  

						foreach($details as $r){

							$filename = Lang::createHandle($r['title']);
							// echo PAGES . "/templates/" . $filename . ".xsl";

							$this->_Parent->Database->query("UPDATE tbl_pages_templates SET `sortorder` = (`sortorder` + 1) WHERE `sortorder` < '".$r['sortorder']."'");     
							General::deleteFile(PAGES . "/templates/" . $filename . ".xsl");
						}

						redirect($this->_Parent->getCurrentPageURL());	
						break;  	

				}
			}
		}	
		
		public function resolvePageTitle($page_id) {
			$path = $this->resolvePage($page_id, 'title');
			
			return @implode(': ', $path);
		}
		
		public function resolvePagePath($page_id) {
			$path = $this->resolvePage($page_id, 'handle');
			
			return @implode('/', $path);
		}

		public function resolvePage($page_id, $column) {
			header('content-type: text/plain');
			
			$page = $this->_Parent->Database->fetchRow(0, "
				SELECT
					p.{$column},
					p.parent
				FROM 
					`tbl_pages_templates` AS p
				WHERE
					p.id = '{$page_id}'
					OR p.handle = '{$page_id}'
				LIMIT 1
			");
			
			$path = array(
				$page[$column]
			);
			
			if ($page['parent'] != null) {
				$next_parent = $page['parent'];
				
				while (
					$parent = $this->_Parent->Database->fetchRow(0, "
						SELECT
							p.*
						FROM
							`tbl_pages_templates` AS p
						WHERE
							p.id = '{$next_parent}'
					")
				) {
					array_unshift($path, $parent[$column]);
					
					$next_parent = $parent['parent'];
				}
			}
			
			return $path;
		}
		
	}
