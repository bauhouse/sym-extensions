<?php 

require_once(TOOLKIT . '/class.administrationpage.php');

define_safe('BASE_URL', URL . '/symphony/extension/nested_cats/overview');

Class contentExtensionNested_CatsOverview extends AdministrationPage{

	private $_driver;
	private $_page;
	private $_id;
	private $_flag;
	private $_errors;

    function __construct(&$parent){
        parent::__construct($parent);
		
        $this->_driver = $this->_Parent->ExtensionManager->create('nested_cats');

    }
	
	function view(){			
		$this->__switchboard();	
	}
	
	function action(){			
		$this->__switchboard('action');		
	}

	function __switchboard($type='view'){

		$this->_page = $this->_context['0'];
		$this->_id = $this->_context['1'];
		$this->_flag = $this->_context['2'];
	
		$function = ($type == 'action' ? '__action' : '__view') . (isset($this->_page) ? ucfirst($this->_page) : 'Index') ;

		if(!method_exists($this, $function)) {
			
			## If there is no action function, just return without doing anything
			if($type == 'action') return;
			
			$this->_Parent->errorPageNotFound();
			
		}
		
		$this->$function();

	}
	
	function __viewIndex(){		
		
		$this->addStylesheetToHead(URL . '/extensions/nested_cats/assets/nested_cats.css', 'screen', 120);
		$this->addScriptToHead(URL . '/extensions/nested_cats/assets/nested_cats.js', 200);
        	$this->setTitle('Symphony &ndash; Nested Cats &ndash; Overview');
        	$this->setPageType('table');

		$this->appendSubheading('Overview', Widget::Anchor('Create New', URL . '/symphony/extension/nested_cats/overview/new/', 'Create a new category', 'create button'));

		$aTableHead = array(array('Title', 'col'));

		$tree = $this->_driver->getTree('lft', 0);

		$right = array($tree[0]['rgt']);
		array_shift($tree);

		if(!is_array($tree) || empty($tree)) {
		
			$aTableBody = array(Widget::TableRow(array(Widget::TableData('None found.', 'inactive', NULL, count($aTableHead)))));

		} else {

			$tableData = array();

			foreach($tree as $branch) {

				while ($right[count($right)-1]<$branch['rgt']) { 
					 array_pop($right); 
				}

				$isBranch = ($branch['rgt'] != $branch['lft'] + 1) ? true : false;

				$c = count($right)-1;
				$class = $isBranch ? 'n'.$c.' is_branch' : 'n'.$c;
				
				$tableData[] = Widget::TableData(
							Widget::Anchor(
								$branch['title'], 
								$this->_Parent->getCurrentPageURL() . 'edit/' . $branch['id'] . '/', 'Edit Category: '.$branch['title'], $class
							)
						);

				###### With-Selected Input
				$tableData[count($tableData) - 1]->appendChild(Widget::Input('items['.$branch['id'].']', NULL, 'checkbox'));

				$aTableBody[] = Widget::TableRow($tableData, ($bEven ? 'even' : NULL));
				$bEven = !$bEven;
					
				unset($tableData);		

				$right[] = $branch['rgt']; 
			}
		}

		$table = Widget::Table(Widget::TableHead($aTableHead), NULL, Widget::TableBody($aTableBody));

		$this->Form->appendChild($table);

		$tableActions = new XMLElement('div');
		$tableActions->setAttribute('class', 'actions');

		$options = array(
			array(NULL, false, 'With Selected...'),
			array('delete', false, 'Delete')									
		);

		$wrapDiv = new XMLElement('div');
		$wrapDiv->appendChild(Widget::Select('with-selected', $options, array('id' => 'sel')));
		$wrapDiv->appendChild(Widget::Input('action[apply]', 'Apply', 'submit'));
		$tableActions->appendChild($wrapDiv);

// 		$tableActions->appendChild(Widget::Select('with-selected', $options, array('id' => 'sel')));
// 		$tableActions->appendChild(Widget::Input('action[apply]', 'Apply', 'submit'));
		
			$notice = new XMLElement('p', 'All categories that are decsendants of selected will be also deleted.');
			$notice->setAttribute('id', 'note');
			$notice->setAttribute('class', 'hidden');
		
		$tableActions->appendChild($notice);
		
	        $this->Form->appendChild($tableActions); 
	}
	
	function __viewNew(){
	
		$select = $this->_driver->buildSelectField('lft', 0, $_POST['fields']['parent'], NULL,'parent');
	
	        $this->setTitle('Symphony &ndash; Nested Cats &ndash; Add Category');
        	$this->setPageType('form');
		$this->appendSubheading('Add Category');

			$fieldset = new XMLElement('fieldset');
			$fieldset->setAttribute('class', 'primary');
	
				$label = Widget::Label('Title');
				$label->appendChild(Widget::Input('fields[title]', $_POST['fields']['title'], 'text'));
				
				if($this->_errors['title']){
					$label = Widget::wrapFormElementWithError($label, "This is a required field.");
				}
	
			$fieldset->appendChild($label);

		$this->Form->appendChild($fieldset);

			$fieldset = new XMLElement('fieldset');
			$fieldset->setAttribute('class', 'secondary');
				
				$label = Widget::Label('Parent');
				$label->appendChild($select);

			$fieldset->appendChild($label);
	
		$this->Form->appendChild($fieldset);
		
			$div = new XMLElement('div');
			$div->setAttribute('class', 'actions');
			$div->appendChild(Widget::Input('action[save]', 'Create', 'submit', array('accesskey' => 's')));

		$this->Form->appendChild($div);
	}
	
	function __viewEdit(){
	
		if(!$this->_id || !$cat = $this->_driver->getCat($this->_id)) $this->_Parent->errorPageNotFound();

		$parent = $cat['parent'];
		$exclude = array('lft' => $cat['lft'], 'rgt' => $cat['rgt']);

		$select = $this->_driver->buildSelectField('lft', 0, $cat['id'], $cat['parent'],'parent', NULL, NULL,$exclude);

		$this->setTitle('Symphony &ndash; Nested Cats &ndash; Category: '.$cat['title']);
		$this->setPageType('form');
		$this->appendSubheading('Category: '.$cat['title']);

			$fieldset = new XMLElement('fieldset');
			$fieldset->setAttribute('class', 'primary');
				
				$label = Widget::Label('Title');
				$label->appendChild(Widget::Input('fields[title]', $cat['title'], 'text'));
				$label->appendChild(Widget::Input('fields[id]', $cat['id'], 'hidden'));
				
				if($this->_errors['title']){
					$label = Widget::wrapFormElementWithError($label, "This is a required field.");
				}
				
			$fieldset->appendChild($label);

		$this->Form->appendChild($fieldset);
		
			$fieldset = new XMLElement('fieldset');
			$fieldset->setAttribute('class', 'secondary');
				
				$label = Widget::Label('Parent');
				$label->appendChild($select);
			
			$fieldset->appendChild($label);
		
		$this->Form->appendChild($fieldset);

		$div = new XMLElement('div');
		$div->setAttribute('class', 'actions');
		$div->appendChild(Widget::Input('action[save]', 'Save', 'submit', array('accesskey' => 's')));

/*
			$button = new XMLElement('button', __('Delete'));
			$button->setAttributeArray(array('name' => 'action[delete]', 'class' => 'confirm delete', 'title' => __('Delete this category')));
			
		$div->appendChild($button);
*/

		$this->Form->appendChild($div);

		if(isset($this->_flag)) {
		
			$action = null;
		
			switch($this->_flag){

				
				case 'saved': $action = '%1$s updated at %2$s. <a href="%3$s">Create another?</a> <a href="%4$s">View all %5$s</a>'; break;
				case 'created': $action = '%1$s created at %2$s. <a href="%3$s">Create another?</a> <a href="%4$s">View all %5$s</a>'; break;
			}
			
			if ($action) $this->pageAlert(
					__(
						$action, array(
							__('Category'), 
							DateTimeObj::get(__SYM_TIME_FORMAT__), 
							URL . '/symphony/extension/nested_cats/overview/new/', 
							URL . '/symphony/extension/nested_cats/overview/',
							__('Categories')
						)
					),
					Alert::SUCCESS
				);
				
		}
	}

	function __actionIndex(){			
		$checked = @array_keys($_POST['items']);

		if(is_array($checked) && !empty($checked))
		{
			
			if($_POST['with-selected'] == 'delete')
			{
				foreach($checked as $lft)
				{
					$this->_driver->removeCat($lft);
				}

				redirect($_SERVER['REQUEST_URI']);
			}
		}
	}

	function __actionNew(){

		if(empty($_POST['fields']['title'])) {
			$this->_errors = 'title';
				$this->pageAlert(__('%1$s', array(__('Title must not be empty'))), Alert::ERROR);
				
			return;
		}

		if(!$this->_driver->addCat($_POST['fields'])) {
		
			define_safe('__SYM_DB_INSERT_FAILED__', true);
			$this->pageAlert(NULL, AdministrationPage::PAGE_ALERT_ERROR);
		
		} else {
  		    redirect(BASE_URL . '/edit/' . $this->_Parent->Database->getInsertID() . '/created/');
		}

	}

	function __actionDelete(){		
		
		if(!$this->_driver->removeCat($_POST['fields']['id'])) {
		
			define_safe('__SYM_DB_INSERT_FAILED__', true);
			$this->pageAlert(NULL, AdministrationPage::PAGE_ALERT_ERROR);

		} else {
  		    redirect(BASE_URL);
		}

	}

	
	function __actionEdit(){

		if(empty($_POST['fields']['title'])) {
			$this->_errors = 'title';
				$this->pageAlert(__('%1$s', array(__('Title must not be empty'))), Alert::ERROR);
				
			return;
		}

		if(!$this->_driver->updateCat($_POST['fields'])) {
		
			define_safe('__SYM_DB_INSERT_FAILED__', true);
			$this->pageAlert(NULL, AdministrationPage::PAGE_ALERT_ERROR);
			
		} else {
  		    redirect(BASE_URL . '/edit/' . $this->_id . '/saved/');
		}

	}


}
?>