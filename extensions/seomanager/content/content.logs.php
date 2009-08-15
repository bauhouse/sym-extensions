<?php

	require_once(TOOLKIT . '/class.administrationpage.php');
	
	class contentExtensionSEOManagerLogs extends AdministrationPage {
		protected $_driver = null;
		protected $_column = 'request_time';
		protected $_columns = array();
		protected $_direction = 'desc';
		protected $_logs = array();
		protected $_log = array();
		protected $_pagination = null;
		protected $_uri = null;
		
		public function __construct(&$parent){
			parent::__construct($parent);
			
			$this->_uri = URL . '/symphony/extension/seomanager';
			$this->_driver = $this->_Parent->ExtensionManager->create('seomanager');
		}
		
		public function build($context) {
			if (@$context[0] == 'show') {
				$this->_log = (object)$this->_driver->getLog($context[1]);
				
			} else {
				$this->__prepareIndex();
			}
			
			parent::build($context);
		}
		
	/*-------------------------------------------------------------------------
		Show
	-------------------------------------------------------------------------*/
		
		public function __viewShow() {
			$this->setPageType('form');
			$title = DateTimeObj::get(__SYM_DATETIME_FORMAT__, $this->_log->request_time);
			$this->setTitle("Symphony &ndash; SEO Manager &ndash; {$title}");
			$this->appendSubheading("<a href=\"{$this->_uri}/logs/\">Logs</a> &mdash; {$title}");
			
			$values = unserialize($this->_log->request_args);
			
			foreach ($values as $type => $array) {
				if (!empty($array)) {
					$type = strtoupper($type);
					
					$fieldset = new XMLElement('fieldset');
					$fieldset->setAttribute('class', 'settings');
					$fieldset->appendChild(new XMLElement('legend', "{$type} Values"));
					
					$pre = new XMLElement('pre');
					$code = new XMLElement('code');
					
					ob_start();
					print_r($array);
					
					$code->setValue(General::sanitize(ob_get_clean()));
					
					$pre->appendChild($code);
					$fieldset->appendChild($pre);
					
					$this->Form->appendChild($fieldset);
				}
			}
		}
		
	/*-------------------------------------------------------------------------
		Index
	-------------------------------------------------------------------------*/
		
		public function __prepareIndex() {
			$this->_columns = array(
				'request_time'		=> array('Time', true),
				'request_uri'		=> array('URI', true),
				'request_method'	=> array('Method', true),
				'server_name'		=> array('Server Name', true),
				'server_addr'		=> array('Server Address', true),
				'server_port'		=> array('Server Port', true),
				'remote_addr'		=> array('Remote Address', true),
				'remote_port'		=> array('Remote Port', true)
			);
			
			if (@$_GET['sort'] and $this->_columns[$_GET['sort']][1]) {
				$this->_column = $_GET['sort'];
			}
			
			if (@$_GET['order'] == 'asc') {
				$this->_direction = 'asc';
			}
			
			$this->_pagination = (object)array(
				'page'		=> (@(integer)$_GET['pg'] > 1 ? (integer)$_GET['pg'] : 1),
				'length'	=> $this->_Parent->Configuration->get('pagination_maximum_rows', 'symphony')
			);
			
			$this->_logs = $this->_driver->getLogs(
				null,
				$this->_column,
				$this->_direction,
				$this->_pagination->page,
				$this->_pagination->length
			);
			
			// Calculate pagination:
			$this->_pagination->start = max(1, (($page - 1) * 17));
			$this->_pagination->end = (
				$this->_pagination->start == 1
				? $this->_pagination->length
				: $start + count($this->_rules)
			);
			$this->_pagination->total = $this->_driver->countLogs();
			$this->_pagination->pages = ceil(
				$this->_pagination->total / $this->_pagination->length
			);
		}
		
		public function generateLink($values) {
			$values = array_merge(array(
				'pg'	=> $this->_pagination->page,
				'sort'	=> $this->_column,
				'order'	=> $this->_direction
			), $values);
			
			$count = 0;
			$link = $this->_Parent->getCurrentPageURL();
			
			foreach ($values as $key => $value) {
				if ($count++ == 0) {
					$link .= '?';
				} else {
					$link .= '&amp;';
				}
				
				$link .= "{$key}={$value}";
			}
			
			return $link;
		}
		
		public function __actionIndex() {
			$checked = @array_keys($_POST['items']);
			
			if (is_array($checked) and !empty($checked)) {
				switch ($_POST['with-selected']) {
					case 'delete':
						foreach ($checked as $log_id) {
							$this->_Parent->Database->query("
								DELETE FROM
									`tbl_seomanager_logs`
								WHERE
									`id` = {$log_id}
							");
						}
						
						redirect($this->_uri . '/logs/');
						break;
				}
			}
		}
		
		public function __viewIndex() {
			$this->setPageType('table');
			$this->setTitle('Symphony &ndash; SEO Manager &ndash; Logs');
			$this->appendSubheading('Logs');
			
			$tableHead = array();
			$tableBody = array();
			
			// Columns, with sorting:
			foreach ($this->_columns as $column => $values) {
				if ($values[1]) {
					if ($column == $this->_column) {
						if ($this->_direction == 'desc') {
							$direction = 'asc';
							$label = 'ascending';
						} else {
							$direction = 'desc';
							$label = 'descending';
						}
					} else {
						$direction = 'asc';
						$label = 'ascending';
					}
					
					$link = $this->generateLink(array(
						'sort'	=> $column,
						'order'	=> $direction
					));
					
					$anchor = Widget::Anchor($values[0], $link, "Sort by {$label} " . strtolower($values[0]));
					
					if ($column == $this->_column) {
						$anchor->setAttribute('class', 'active');
					}
					
					$tableHead[] = array($anchor, 'col');
					
				} else {
					$tableHead[] = array($values[0], 'col');
				}
			}
			
			if (!is_array($this->_logs) or empty($this->_logs)) {
				$tableBody = array(
					Widget::TableRow(array(Widget::TableData(__('None Found.'), 'inactive', null, count($tableHead))))
				);
				
			} else {
				foreach ($this->_logs as $log) {
					$log = (object)$log;
					
					$col_time = Widget::TableData(
						Widget::Anchor(
							DateTimeObj::get(__SYM_DATETIME_FORMAT__, $log->request_time),
							$this->_uri . "/logs/show/{$log->id}/"
						)
					);
					$col_time->appendChild(Widget::Input("items[{$log->id}]", null, 'checkbox'));
					
					if (!empty($log->request_uri)) {
						$value = $this->_driver->truncateValue($log->request_uri);
						
						$col_uri = Widget::TableData($value);
						
					} else {
						$col_uri = Widget::TableData('None', 'inactive');
					}
					
					if (!empty($log->request_method)) {
						$col_method = Widget::TableData(ucwords($log->request_method));
						
					} else {
						$col_method = Widget::TableData('None', 'inactive');
					}
					
					if (!empty($log->server_name)) {
						$value = $this->_driver->truncateValue($log->server_name);
						
						$col_server_name = Widget::TableData($value);
						
					} else {
						$col_server_name = Widget::TableData('None', 'inactive');
					}
					
					if (!empty($log->server_addr)) {
						$value = $this->_driver->truncateValue($log->server_addr);
						
						$col_server_addr = Widget::TableData($value);
						
					} else {
						$col_server_addr = Widget::TableData('None', 'inactive');
					}
					
					if (!empty($log->server_port)) {
						$value = $this->_driver->truncateValue($log->server_port);
						
						$col_server_port = Widget::TableData($value);
						
					} else {
						$col_server_port = Widget::TableData('None', 'inactive');
					}
					
					if (!empty($log->remote_addr)) {
						$value = $this->_driver->truncateValue($log->remote_addr);
						
						$col_remote_addr = Widget::TableData($value);
						
					} else {
						$col_remote_addr = Widget::TableData('None', 'inactive');
					}
					
					if (!empty($log->remote_port)) {
						$value = $this->_driver->truncateValue($log->remote_port);
						
						$col_remote_port = Widget::TableData($value);
						
					} else {
						$col_remote_port = Widget::TableData('None', 'inactive');
					}
					
					$tableBody[] = Widget::TableRow(array(
						$col_time, $col_uri, $col_method,
						$col_server_name, $col_server_addr, $col_server_port,
						$col_remote_addr, $col_remote_port
					));
				}
			}
			
			$table = Widget::Table(
				Widget::TableHead($tableHead), null, 
				Widget::TableBody($tableBody)
			);
			
			$this->Form->appendChild($table);
			
			$actions = new XMLElement('div');
			$actions->setAttribute('class', 'actions');
			
			$options = array(
				array(null, false, 'With Selected...'),
				array('delete', false, 'Delete')
			);
			
			$actions->appendChild(Widget::Select('with-selected', $options));
			$actions->appendChild(Widget::Input('action[apply]', 'Apply', 'submit'));
			
			$this->Form->appendChild($actions);
			
			// Pagination:
			if ($this->_pagination->pages > 1) {
				$ul = new XMLElement('ul');
				$ul->setAttribute('class', 'page');
				
				## First
				$li = new XMLElement('li');
				
				if ($this->_pagination->page > 1) {
					$li->appendChild(
						Widget::Anchor('First', $this->generateLink(array(
							'pg'	=> 1
						)))
					);
					
				} else {
					$li->setValue('First');
				}
				
				$ul->appendChild($li);
				
				## Previous
				$li = new XMLElement('li');
				
				if ($this->_pagination->page > 1) {
					$li->appendChild(
						Widget::Anchor('&larr; Previous', $this->generateLink(array(
							'pg'	=> $this->_pagination->page - 1
						)))
					);
					
				} else {
					$li->setValue('&larr; Previous');
				}
				
				$ul->appendChild($li);

				## Summary
				$li = new XMLElement('li', 'Page ' . $this->_pagination->page . ' of ' . max($this->_pagination->page, $this->_pagination->pages));
				
				$li->setAttribute('title', 'Viewing ' . $this->_pagination->start . ' - ' . $this->_pagination->end . ' of ' . $this->_pagination->total . ' entries');
				
				$ul->appendChild($li);

				## Next
				$li = new XMLElement('li');
				
				if ($this->_pagination->page < $this->_pagination->pages) {
					$li->appendChild(
						Widget::Anchor('Next &rarr;', $this->generateLink(array(
							'pg'	=> $this->_pagination->page + 1
						)))
					);
					
				} else {
					$li->setValue('Next &rarr;');
				}
				
				$ul->appendChild($li);

				## Last
				$li = new XMLElement('li');
				
				if ($this->_pagination->page < $this->_pagination->pages) {
					$li->appendChild(
						Widget::Anchor('Last', $this->generateLink(array(
							'pg'	=> $this->_pagination->pages
						)))
					);
					
				} else {
					$li->setValue('Last');
				}
				
				$ul->appendChild($li);
				$this->Form->appendChild($ul);	
			}
		}
	}
	
?>