<?php

	require_once(TOOLKIT . '/class.administrationpage.php');
	
	class contentExtensionImportManagerTemplates extends AdministrationPage {
		protected $_driver = null;
		protected $_uri = null;
		
		public function __construct(&$parent){
			parent::__construct($parent);
			
			$this->_uri = URL . '/symphony/extension/importmanager';
			$this->_driver = $this->_Parent->ExtensionManager->create('importmanager');
		}
		
		public function build($context) {
			if (@$context[0] == 'edit' or @$context[0] == 'new') {
				$this->__prepareEdit($context);
				
			} else {
				$this->__prepareIndex();
			}
			
			parent::build($context);
		}
		
	/*-------------------------------------------------------------------------
		Edit
	-------------------------------------------------------------------------*/
		
		protected $_editing = false;
		protected $_errors = array();
		protected $_fields = null;
		protected $_status = null;
		protected $_valid = true;
		
		public function __prepareEdit($context) {
			$this->_editing = $context[0] == 'edit';
			$this->_status = $context[2];
			
			if ($this->_editing) {
				//$this->_fields = $this->_driver->getRule($context[1]);
				//$keywords = $this->_driver->getKeywords($context[1]);
				//$this->_fields['keywords'] = implode(', ', $keywords);
			}
		}
		
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
				$this->_errors['expression'] = 'Expression must not be empty.';
			}
			
			if (empty($this->_fields['title'])) {
				$this->_errors['title'] = 'Title must not be empty.';
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
			$this->setTitle("Symphony &ndash; SEO Manager &ndash; {$title}");
			$this->appendSubheading("<a href=\"{$this->_uri}/rules/\">Rules</a> &mdash; {$title}");
			
			if (!$this->_valid) $this->pageAlert('
				An error occurred while processing this form.
				<a href="#error">See below for details.</a>',
				AdministrationPage::PAGE_ALERT_ERROR
			);
			
			// Status message:
			if ($this->_status) {
				$action = null;
				
				switch ($this->_status) {
					case 'saved': $action = 'updated'; break;
					case 'created': $action = 'created'; break;
				}
				
				if ($action) $this->pageAlert(
					'Rule {1} successfully. <a href="{2}/symphony/{3}">Create another?</a>',
					AdministrationPage::PAGE_ALERT_NOTICE, array(
						$action, URL, 'extension/seomanager/rules/new/'
					)
				);
			}
			
		// Fields -------------------------------------------------------------
			
			$fieldset = new XMLElement('fieldset');
			$fieldset->setAttribute('class', 'settings');
			$fieldset->appendChild(new XMLElement('legend', 'Essentials'));
			
			if (!empty($this->_fields['id'])) {
				$fieldset->appendChild(Widget::Input("fields[id]", $this->_fields['id'], 'hidden'));
			}
			
		// Title --------------------------------------------------------------
			
			$label = Widget::Label('Title');
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
			
			$label = Widget::Label('Description');
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
			
			$label = Widget::Label('Keywords');
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
			$fieldset->appendChild(new XMLElement('legend', 'Advanced'));
			
			$label = Widget::Label('Expression');
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
			$input = $input->generate();
			
			$label = Widget::Label("{$input} Use regular expressions?");
			
			$group->appendChild($label);
			
			$help = new XMLElement('p');
			$help->setAttribute('class', 'help');
			$help->setValue("
				When checked, you can use <a href=\"{$this->_uri}/help/pcre/\">Perl compatible regular expressions</a>.
			");
			
			$group->appendChild($help);
			
		// Logged -------------------------------------------------------------
			
			$input = Widget::Input(
				'fields[logged]', 'yes', 'checkbox',
				($this->_fields['logged'] == 'yes' ? array('checked' => 'checked') : null)
			);
			$input = $input->generate();
			
			$label = Widget::Label("{$input} Enable logging for this rule?");
			
			$group->appendChild($label);
			
			$help = new XMLElement('p');
			$help->setAttribute('class', 'help');
			$help->setValue('With logging enabled, requests that match this expression will be recorded.');
			
			$group->appendChild($help);
			$fieldset->appendChild($group);
			$this->Form->appendChild($fieldset);
			
		// Save ---------------------------------------------------------------
			
			$div = new XMLElement('div');
			$div->setAttribute('class', 'actions');
			$div->appendChild(
				Widget::Input(
					'action[save]', 'Save Changes', 'submit',
					array(
						'accesskey'		=> 's'
					)
				)
			);
			
		// Delete -------------------------------------------------------------
			
			if ($this->_editing) {
				$button = new XMLElement('button', 'Delete');
				$button->setAttributeArray(array(
					'name'		=> 'action[delete]',
					'class'		=> 'confirm delete',
					'title'		=> 'Delete this email'
				));
				$div->appendChild($button);
			}
			
			$this->Form->appendChild($div);
		}
		
	/*-------------------------------------------------------------------------
		Index
	-------------------------------------------------------------------------*/
		
		protected $_pagination = null;
		protected $_column = 'name';
		protected $_columns = array();
		protected $_direction = 'name';
		protected $_templates = array();
		
		public function __prepareIndex() {
			$this->_columns = array(
				'name'			=> array('Template Name', true),
				'expression'	=> array('Expression', true),
				'maps'			=> array('Maps', true)
			);
			
			if (@$_GET['sort'] and $this->_columns[$_GET['sort']][1]) {
				$this->_column = $_GET['sort'];
			}
			
			if (@$_GET['order'] == 'desc') {
				$this->_direction = 'desc';
			}
			
			$this->_pagination = (object)array(
				'page'		=> (@(integer)$_GET['pg'] > 1 ? (integer)$_GET['pg'] : 1),
				'length'	=> $this->_Parent->Configuration->get('pagination_maximum_rows', 'symphony')
			);
			
			$this->_rules = $this->_driver->getTemplates(
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
			$this->_pagination->total = $this->_driver->countTemplates();
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
						foreach ($checked as $template_id) {
							$this->_Parent->Database->query("
								DELETE FROM
									`tbl_importmanager_templates`
								WHERE
									`id` = {$template_id}
							");
							$this->_Parent->Database->query("
								DELETE FROM
									`tbl_importmanager_maps`
								WHERE
									`template_id` = {$template_id}
							");
						}
						
						redirect($this->_uri . '/rules/');
						break;
				}
			}
		}
		
		public function __viewIndex() {
			$this->setPageType('table');
			$this->setTitle('Symphony &ndash; Import Manager &ndash; Templates');
			$this->appendSubheading('Templates', Widget::Anchor(
				'Create New', $this->_uri . '/templates/new/',
				'Create a new template', 'create button'
			));
			
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
			
			if (!is_array($this->_templates) or empty($this->_templates)) {
				$tableBody = array(
					Widget::TableRow(array(Widget::TableData(__('None Found.'), 'inactive', null, count($tableHead))))
				);
				
			} else {
				foreach ($this->_templates as $template) {
					$template = (object)$template;
					
					$col_name = Widget::TableData(
						Widget::Anchor(
							$this->_driver->truncateValue($rule->title),
							$this->_uri . "/rules/edit/{$rule->id}/"
						)
					);
					$col_name->appendChild(Widget::Input("items[{$rule->id}]", null, 'checkbox'));
					
					if (!empty($rule->expression)) {
						$col_expression = Widget::TableData($rule->expression);
						
					} else {
						$col_expression = Widget::TableData('None', 'inactive');
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
						$col_name, $col_expression, $col_keywords
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