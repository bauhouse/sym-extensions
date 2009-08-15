<?php
	
	class Extension_SEOManager extends Extension {
	/*-------------------------------------------------------------------------
		Extension definition
	-------------------------------------------------------------------------*/
		
		public static $rule = null;
		
		public function about() {
			return array(
				'name'			=> 'SEO Manager',
				'version'		=> '1.002',
				'release-date'	=> '2008-12-11',
				'author'		=> array(
					'name'			=> 'Rowan Lewis',
					'website'		=> 'http://pixelcarnage.com/',
					'email'			=> 'rowan@pixelcarnage.com'
				),
				'description' => 'Manage page titles, descriptions, keywords and even headers.'
			);
		}
		
		public function uninstall() {
			$this->_Parent->Database->query("DROP TABLE `tbl_seomanager_rules`");
			$this->_Parent->Database->query("DROP TABLE `tbl_seomanager_keywords`");
			$this->_Parent->Database->query("DROP TABLE `tbl_seomanager_logs`");
		}
		
		public function install() {
			$this->_Parent->Database->query("
				CREATE TABLE IF NOT EXISTS `tbl_seomanager_rules` (
					`id` int(11) NOT NULL auto_increment,
					`sortorder` int(11) NOT NULL,
					`title` varchar(255) default NULL,
					`description` text default NULL,
					`expression` varchar(255) default NULL,
					`method` enum('normal', 'regexp') default 'normal',
					`logged` enum('yes', 'no') default 'no',
					PRIMARY KEY  (`id`),
					KEY `sortorder` (`sortorder`)
				)
			");
			
			$this->_Parent->Database->query("
				CREATE TABLE IF NOT EXISTS `tbl_seomanager_keywords` (
					`id` int(11) NOT NULL auto_increment,
					`rule_id` int(11) NOT NULL,
					`value` varchar(255) default NULL,
					PRIMARY KEY  (`id`),
					KEY `rule_id` (`rule_id`),
					KEY `value` (`value`)
				)
			");
			
			$this->_Parent->Database->query("
				CREATE TABLE IF NOT EXISTS `tbl_seomanager_logs` (
					`id` int(11) NOT NULL auto_increment,
					`rule_id` int(11) NOT NULL,
					`server_name` varchar(255) default NULL,
					`server_addr` varchar(255) default NULL,
					`server_port` int(11) default NULL,
					`remote_addr` varchar(255) default NULL,
					`remote_port` int(11) default NULL,
					`request_time` int(11) default NULL,
					`request_uri` text default NULL,
					`request_method` enum('get', 'post') NOT NULL,
					`request_args` text default NULL,
					PRIMARY KEY  (`id`),
					KEY `rule_id` (`rule_id`),
					KEY `request_time` (`request_time`)
				)
			");
			
			return true;
		}
		
		public function getSubscribedDelegates() {
			return array(
				array(
					'page'		=> '/backend/',
					'delegate'	=> 'InitaliseAdminPageHead',
					'callback'	=> 'initializeAdmin'
				),
				array(
					'page'		=> '/blueprints/events/edit/',
					'delegate'	=> 'AppendEventFilter',
					'callback'	=> 'addFilterToEventEditor'
				),
				array(
					'page'		=> '/blueprints/events/new/',
					'delegate'	=> 'AppendEventFilter',
					'callback'	=> 'addFilterToEventEditor'
				),
				array(
					'page'		=> '/frontend/',
					'delegate'	=> 'EventFinalSaveFilter',
					'callback'	=> 'eventFinalSaveFilter'
				),
				/* ManipulatePageParameters is non-standard */
				array(
					'page'		=> '/frontend/',
					'delegate'	=> 'ManipulatePageParameters',
					'callback'	=> 'initializeRules'
				),
				array(
					'page'		=> '/frontend/',
					'delegate'	=> 'FrontendParamsResolve',
					'callback'	=> 'initializeRules'
				)
			);
		}
		
		public function fetchNavigation() {
			return array(
				array(
					'location'	=> 240,
					'name'		=> 'SEO Manager',
					'children'	=> array(
						array(
							'name'		=> 'Rules',
							'link'		=> '/rules/'
						),
						array(
							'name'		=> 'Logs',
							'link'		=> '/logs/'
						)
					)
				)
			);
		}
		
		public function initializeRules(&$context) {
			$matches = array();
			$rules = $this->getRules();
			$path = $context['params']['current-path'];
			$path = preg_replace('/[\?&]debug$/', '', $path);
			
			foreach ($rules as $rule) {
				$rule = (object)$rule;
				
				// Convert a 'normal' expression into a regexp:
				if ($rule->method == 'normal') {
					$rule->expression = preg_quote($rule->expression, '/');
					$rule->expression = str_replace('\\*', '.*?', $rule->expression);
					$rule->expression = "/^{$rule->expression}$/i";
				}
				
				if (@preg_match($rule->expression, $path, $match)) {
					$matches[$match[0]] = $rule;
				}
			}
			
			// Find best match:
			if (!empty($matches)) {
				if (!function_exists('__compare_rules')) {
					function __compare_rules($a, $b) {
						return strlen($b) - strlen($a);
					}
				}
				
				uksort($matches, '__compare_rules');
				
				$rule = array_shift($matches);
				$rule->keywords = $this->getKeywords($rule->id);
				
				// Log this:
				if ($rule->logged == 'yes') {
					$this->_Parent->Database->insert(array(
						'rule_id'			=> $rule->id,
						'server_name'		=> $_SERVER['SERVER_NAME'],
						'server_addr'		=> $_SERVER['SERVER_ADDR'],
						'server_port'		=> $_SERVER['SERVER_PORT'],
						'remote_addr'		=> $_SERVER['REMOTE_ADDR'],
						'remote_port'		=> $_SERVER['REMOTE_PORT'],
						'request_time'		=> time(),
						'request_uri'		=> $_SERVER['REQUEST_URI'],
						'request_method'	=> strtolower($_SERVER['REQUEST_METHOD']),
						'request_args'		=> serialize(array(
							'get'	=> $_GET,
							'post'	=> $_POST
						))
					), 'tbl_seomanager_logs');
				}
				
				self::$rule = $rule;
			}
		}
		
		public function initializeAdmin($context) {
			$page = $context['parent']->Page;
			
			$page->addStylesheetToHead(URL . '/extensions/seomanager/assets/form.css', 'screen');
		}
		
	/*-------------------------------------------------------------------------
		Utilities:
	-------------------------------------------------------------------------*/
		
		public function getCurrentRule() {
			return self::$rule;
		}
		
		public function truncateValue($value) {
			$max_length = $this->_Parent->Configuration->get('cell_truncation_length', 'symphony');
			$max_length = ($max_length ? $max_length : 75);
			
			$value = General::sanitize($value);
			$value = (strlen($value) <= $max_length ? $value : substr($value, 0, $max_length) . '...');
			
			return $value;
		}
		
	/*-------------------------------------------------------------------------
		Data:
	-------------------------------------------------------------------------*/
		
		public function countLogs() {
			return (integer)$this->_Parent->Database->fetchVar('total', 0, "
				SELECT
					COUNT(l.id) AS `total`
				FROM
					`tbl_seomanager_logs` AS l
			");
		}
		
		public function getLogs($rule_id, $column, $direction, $page, $length) {
			$rule_id = (integer)$rule_id;
			$start = ($page - 1) * $length;
			
			return $this->_Parent->Database->fetch("
				SELECT
					l.*
				FROM
					`tbl_seomanager_logs` AS l
				WHERE
					l.rule_id = '{$rule_id}'
					OR '{$rule_id}' = '0'
				ORDER BY
					l.{$column} {$direction},
					l.request_time DESC
				LIMIT {$start}, {$length}
			");
		}
		
		public function getLog($log_id) {
			$log_id = (integer)$log_id;
			
			return $this->_Parent->Database->fetchRow(0, "
				SELECT
					l.*
				FROM
					`tbl_seomanager_logs` AS l
				WHERE
					l.id = '{$log_id}'
				LIMIT 1
			");
		}
		
		public function countRules() {
			return (integer)$this->_Parent->Database->fetchVar('total', 0, "
				SELECT
					COUNT(r.id) AS `total`
				FROM
					`tbl_seomanager_rules` AS r
			");
		}
		
		public function getRules($column = 'title', $direction = 'asc', $page = 1, $length = 10000) {
			$start = ($page - 1) * $length;
			
			return $this->_Parent->Database->fetch("
				SELECT
					r.*
				FROM
					`tbl_seomanager_rules` AS r
				ORDER BY
					r.{$column} {$direction},
					r.title ASC
				LIMIT {$start}, {$length}
			");
		}
		
		public function getRule($rule_id) {
			$rule_id = (integer)$rule_id;
			
			return $this->_Parent->Database->fetchRow(0, "
				SELECT
					r.*
				FROM
					`tbl_seomanager_rules` AS r
				WHERE
					r.id = {$rule_id}
			");
		}
		
		public function getKeywords($rule_id = 0) {
			$rule_id = (integer)$rule_id;
			
			return $this->_Parent->Database->fetchCol('value', "
				SELECT DISTINCT
					k.value
				FROM
					`tbl_seomanager_keywords` AS k
				WHERE
					k.rule_id = '{$rule_id}'
					OR '{$rule_id}' = '0'
			");
		}
	}
	
?>