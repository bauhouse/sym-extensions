<?php

	require_once(TOOLKIT . '/class.administrationpage.php');
	
	class contentExtensionSEOManagerRules extends AdministrationPage {
		protected $_driver = null;
		protected $_editing = false;
		protected $_errors = array();
		protected $_fields = null;
		protected $_pagination = null;
		protected $_status = null;
		protected $_table_column = 'title';
		protected $_table_columns = array();
		protected $_table_direction = 'asc';
		protected $_rules = array();
		protected $_uri = null;
		protected $_valid = true;
		
		public function __construct(&$parent){
			parent::__construct($parent);
			
			$this->_uri = URL . '/symphony/extension/seomanager';
			$this->_driver = $this->_Parent->ExtensionManager->create('seomanager');
		}
		
		public function build($context) {
			if (@$context[0] == 'edit' or @$context[0] == 'new') {
				$this->_editing = $context[0] == 'edit';
				$this->_status = $context[2];
				
				if ($this->_editing) {
					$this->_fields = $this->_driver->getRule($context[1]);
					$keywords = $this->_driver->getKeywords($context[1]);
					$this->_fields['keywords'] = implode(', ', $keywords);
				}
				
			} else {
				$this->__prepareIndex();
			}
			
			parent::build($context);
		}
		
	/*-------------------------------------------------------------------------
		Edit
	-------------------------------------------------------------------------*/
		
		public function __actionNew() {
			$this->__actionEdit();
		}
		
		public function __actionEdit() {
			if (@array_key_exists('delete', $_POST['action'])) {
				$this->__actionEditDelete();
				
			} else {
				$this->__actionEditNormal();
			}
		}
		
		public function __actionEditDelete() {
			$this->_Parent->Database->delete('tbl_seomanager_rules', " `id` = '{$this->_fields['id']}'");
			$this->_Parent->Database->delete('tbl_seomanager_keywords', " `rule_id` = '{$this->_fields['id']}'");
			
			redirect($this->_uri . '/rules/');
		}
		
		public function __actionEditNormal() {
			$this->_fields = (isset($_POST['fields']) ? $_POST['fields'] : $this->_fields);
			
		// Validate -----------------------------------------------------------
			
			if (empty($this->_fields['expression'])) {
				$this->_errors['expression'] = __('Expression must not be empty.');
			}
			
			if (empty($this->_fields['title'])) {
				$this->_errors['title'] = __('Title must not be empty.');
			}
			
			if (!isset($this->_fields['method'])) {
				$this->_fields['method'] = 'normal';
			}
			
			if (!isset($this->_fields['logged'])) {
				$this->_fields['logged'] = 'no';
			}
			
			if (!empty($this->_errors)) {
				$this->_valud = false;
				return;
			}
			
		// Save ---------------------------------------------------------------
			
			$keywords = preg_split('/\s*,\s*/', $this->_fields['keywords'], 0, PREG_SPLIT_NO_EMPTY);
			
			natcasesort($keywords);
			
			unset($this->_fields['keywords']);
			
			$result = $this->_Parent->Database->insert($this->_fields, 'tbl_seomanager_rules', true);
			
			if (!$this->_editing) {
				$redirect_mode = 'created';
				$rule_id = (integer)$this->_Parent->Database->fetchVar('id', 0, "
					SELECT
						p.id
					FROM
						`tbl_seomanager_rules` AS p
					ORDER BY
						p.id DESC
					LIMIT 1
				");
				
			} else {
				$redirect_mode = 'saved';
				$rule_id = $this->_fields['id'];
			}
			
			// Delete old keywords:
			$this->_Parent->Database->delete('tbl_seomanager_keywords', " `rule_id` = '{$rule_id}'");
			
			// Insert new keywords:
			foreach ($keywords as $keyword) {
				$this->_Parent->Database->insert(array(
					'rule_id'	=> $rule_id,
					'value'		=> $keyword
				), 'tbl_seomanager_keywords');
			}
			
			redirect("{$this->_uri}/rules/edit/{$rule_id}/{$redirect_mode}/");
		}
		
		public function __viewNew() {
			self::__viewEdit();
		}
		
		public function __viewEdit() {
			$this->setPageType('form');
			$title = ($this->_editing ? $this->_fields['title'] : 'Untitled');
			$this->setTitle(__(
				'Symphony &ndash; SEO Manager &ndash; %s', array(
				$title
			)));
			$this->appendSubheading(__(
				'<a href="%s">Rules</a> &mdash; %s', array(
				$this->_uri . '/rules/', $title
			)));
			
			if (!$this->_valid) $this->pageAlert(
				__('An error occurred while processing this form. <a href="#error">See below for details.</a>'),
				Alert::ERROR
			);
			
			// Status message:
			if ($this->_status) {
				$action = null;
				
				switch($this->_status) {
					case 'saved': $action = '%1$s updated at %2$s. <a href="%3$s">Create another?</a> <a href="%4$s">View all %5$s</a>'; break;
					case 'created': $action = '%1$s created at %2$s. <a href="%3$s">Create another?</a> <a href="%4$s">View all %5$s</a>'; break;
				}
				
				if ($action) $this->pageAlert(
					__(
						$action, array(
							__('Rule'), 
							DateTimeObj::get(__SYM_TIME_FORMAT__), 
							URL . '/symphony/extension/seomanager/rules/new/', 
							URL . '/symphony/extension/seomanager/rules/',
							__('Rules')
						)
					),
					Alert::SUCCESS
				);
			}
			
		// Fields -------------------------------------------------------------
			
			$fieldset = new XMLElement('fieldset');
			$fieldset->setAttribute('class', 'settings');
			$fieldset->appendChild(new XMLElement('legend', __('Essentials')));
			
			if (!empty($this->_fields['id'])) {
				$fieldset->appendChild(Widget::Input("fields[id]", $this->_fields['id'], 'hidden'));
			}
			
		// Title --------------------------------------------------------------
			
			$label = Widget::Label(__('Title'));
			$label->appendChild(Widget::Input(
				'fields[title]',
				General::sanitize($this->_fields['title'])
			));
			
			if (isset($this->_errors['title'])) {
				$label = Widget::wrapFormElementWithError($label, $this->_errors['title']);
			}
			
			$fieldset->appendChild($label);
			
		// Description --------------------------------------------------------
			
			$group = new XMLElement('div');
			$group->setAttribute('class', 'group');
			
			$left = new XMLElement('div');
			
			$label = Widget::Label(__('Description'));
			$label->appendChild(Widget::Textarea(
				'fields[description]', 3, 10,
				General::sanitize($this->_fields['description'])
			));
			
			if (isset($this->_errors['description'])) {
				$label = Widget::wrapFormElementWithError($label, $this->_errors['description']);
			}
			
			$left->appendChild($label);
			$group->appendChild($left);
			
		// Keywords -----------------------------------------------------------
			
			$right = new XMLElement('div');
			
			$label = Widget::Label(__('Keywords'));
			$label->appendChild(Widget::Input(
				'fields[keywords]',
				General::sanitize($this->_fields['keywords'])
			));
			
			if (isset($this->_errors['keywords'])) {
				$label = Widget::wrapFormElementWithError($label, $this->_errors['keywords']);
			}
			
			$right->appendChild($label);
			
			if ($keywords = $this->_driver->getKeywords()) {
				$list = new XMLElement('ul');
				$list->setAttribute('class', 'tags');
				
				foreach ($keywords as $keyword) {
					$list->appendChild(new XMLElement('li', $keyword));
				}
				
				$right->appendChild($list);
			}
			
			$group->appendChild($right);
			$fieldset->appendChild($group);
			$this->Form->appendChild($fieldset);
			
		// Expression ---------------------------------------------------------
			
			$fieldset = new XMLElement('fieldset');
			$fieldset->setAttribute('class', 'settings');
			$fieldset->appendChild(new XMLElement('legend', __('Advanced')));
			
			$label = Widget::Label(__('Expression'));
			$label->appendChild(Widget::Input(
				'fields[expression]',
				General::sanitize($this->_fields['expression'])
			));
			
			if (isset($this->_errors['expression'])) {
				$label = Widget::wrapFormElementWithError($label, $this->_errors['expression']);
			}
			
			$fieldset->appendChild($label);
			
			$help = new XMLElement('p');
			$help->setAttribute('class', 'help');
			$help->setValue('Match against <code>$current-path</code>, use <code>*</code> as a wild-card unless regular expressions are enabled.');
			
			$fieldset->appendChild($help);
			
		// Method -------------------------------------------------------------
			
			$group = new XMLElement('div');
			$group->setAttribute('class', 'seomanager_group');
			
			$input = Widget::Input(
				'fields[method]', 'regexp', 'checkbox',
				($this->_fields['method'] == 'regexp' ? array('checked' => 'checked') : null)
			);
			
			$label = Widget::Label(__(
				'%s Use regular expressions?', array(
				$input->generate()
			)));
			
			$group->appendChild($label);
			
			$help = new XMLElement('p');
			$help->setAttribute('class', 'help');
			$help->setValue(__(
				'When checked, you can use <a href="%s">Perl compatible regular expressions</a>.', array(
				$this->_uri . '/help/pcre/'
			)));
			
			$group->appendChild($help);
			
		// Logged -------------------------------------------------------------
			
			$input = Widget::Input(
				'fields[logged]', 'yes', 'checkbox',
				($this->_fields['logged'] == 'yes' ? array('checked' => 'checked') : null)
			);
			
			$label = Widget::Label(__(
				'%s Enable logging for this rule?', array(
				$input->generate()
			)));
			
			$group->appendChild($label);
			
			$help = new XMLElement('p');
			$help->setAttribute('class', 'help');
			$help->setValue(__('With logging enabled, requests that match this expression will be recorded.'));
			
			$group->appendChild($help);
			$fieldset->appendChild($group);
			$this->Form->appendChild($fieldset);
			
		// Save ---------------------------------------------------------------
			
			$div = new XMLElement('div');
			$div->setAttribute('class', 'actions');
			$div->appendChild(
				Widget::Input(
					'action[save]', __('Save Changes'), 'submit',
					array(
						'accesskey'		=> 's'
					)
				)
			);
			
		// Delete -------------------------------------------------------------
			
			if ($this->_editing) {
				$button = new XMLElement('button', __('Delete'));
				$button->setAttributeArray(array(
					'name'		=> 'action[delete]',
					'class'		=> 'confirm delete',
					'title'		=> __('Delete this email')
				));
				$div->appendChild($button);
			}
			
			$this->Form->appendChild($div);
		}
		
	/*-------------------------------------------------------------------------
		Index
	-------------------------------------------------------------------------*/
		
		public function __prepareIndex() {
			$this->_table_columns = array(
				'title'			=> array(__('Title'), true),
				'expression'	=> array(__('Expression'), true),
				'method'		=> array(__('Method'), true),
				'logged'		=> array(__('Logged?'), true),
				'description'	=> array(__('Description'), true),
				'keywords'		=> array(__('Keywords'), false)
			);
			
			if (@$_GET['sort'] and $this->_table_columns[$_GET['sort']][1]) {
				$this->_table_column = $_GET['sort'];
			}
			
			if (@$_GET['order'] == 'desc') {
				$this->_table_direction = 'desc';
			}
			
			$this->_pagination = (object)array(
				'page'		=> (@(integer)$_GET['pg'] > 1 ? (integer)$_GET['pg'] : 1),
				'length'	=> $this->_Parent->Configuration->get('pagination_maximum_rows', 'symphony')
			);
			
			$this->_rules = $this->_driver->getRules(
				$this->_table_column,
				$this->_table_direction,
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
			$this->_pagination->total = $this->_driver->countRules();
			$this->_pagination->pages = ceil(
				$this->_pagination->total / $this->_pagination->length
			);
		}
		
		public function generateLink($values) {
			$values = array_merge(array(
				'pg'	=> $this->_pagination->page,
				'sort'	=> $this->_table_column,
				'order'	=> $this->_table_direction
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
						foreach ($checked as $rule_id) {
							$this->_Parent->Database->query("
								DELETE FROM
									`tbl_seomanager_rules`
								WHERE
									`id` = {$rule_id}
							");
							$this->_Parent->Database->query("
								DELETE FROM
									`tbl_seomanager_keywords`
								WHERE
									`rule_id` = {$rule_id}
							");
						}
						
						redirect($this->_uri . '/rules/');
						break;
				}
			}
		}
		
		public function __viewIndex() {
			$this->setPageType('table');
			$this->setTitle(__('Symphony &ndash; SEO Manager &ndash; Rules'));
			$this->appendSubheading('Rules', Widget::Anchor(
				__('Create New'), $this->_uri . '/rules/new/',
				__('Create a new rule'), 'create button'
			));
			
			$tableHead = array();
			$tableBody = array();
			
			// Columns, with sorting:
			foreach ($this->_table_columns as $column => $values) {
				if ($values[1]) {
					if ($column == $this->_table_column) {
						if ($this->_table_direction == 'desc') {
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
					
					$anchor = Widget::Anchor($values[0], $link, __("Sort by {$label} " . strtolower($values[0])));
					
					if ($column == $this->_table_column) {
						$anchor->setAttribute('class', 'active');
					}
					
					$tableHead[] = array($anchor, 'col');
					
				} else {
					$tableHead[] = array($values[0], 'col');
				}
			}
			
			if (!is_array($this->_rules) or empty($this->_rules)) {
				$tableBody = array(
					Widget::TableRow(array(Widget::TableData(__('None Found.'), 'inactive', null, count($tableHead))))
				);
				
			} else {
				foreach ($this->_rules as $rule) {
					$rule = (object)$rule;
					
					$col_title = Widget::TableData(
						Widget::Anchor(
							$this->_driver->truncateValue($rule->title),
							$this->_uri . "/rules/edit/{$rule->id}/"
						)
					);
					$col_title->appendChild(Widget::Input("items[{$rule->id}]", null, 'checkbox'));
					
					if (!empty($rule->expression)) {
						$col_expression = Widget::TableData($rule->expression);
						
					} else {
						$col_expression = Widget::TableData('None', 'inactive');
					}
					
					$col_method = Widget::TableData(ucwords($rule->method));
					
					$col_logged = Widget::TableData(ucwords($rule->logged));
					
					if (!empty($rule->description)) {
						$value = $this->_driver->truncateValue($rule->description);
						
						$col_description = Widget::TableData($value);
						
					} else {
						$col_description = Widget::TableData('None', 'inactive');
					}
					
					$keywords = $this->_driver->getKeywords($rule->id);
					
					if (!empty($keywords)) {
						$keywords = implode(', ', $keywords);
						$keywords = $this->_driver->truncateValue($keywords);
						
						$col_keywords = Widget::TableData($keywords);
						
					} else {
						$col_keywords = Widget::TableData('None', 'inactive');
					}
					
					$tableBody[] = Widget::TableRow(array(
						$col_title, $col_expression, $col_method,
						$col_logged, $col_description, $col_keywords
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
				array(null, false, __('With Selected...')),
				array('delete', false, __('Delete'))
			);
			
			$actions->appendChild(Widget::Select('with-selected', $options));
			$actions->appendChild(Widget::Input('action[apply]', __('Apply'), 'submit'));
			
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