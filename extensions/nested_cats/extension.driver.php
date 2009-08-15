<?php

	Class extension_nested_cats extends Extension{
	
		public function about(){
			return array('name' => 'Nested Cats',
						 'version' => '1.0',
						 'release-date' => '2009-03-28',
						 'author' => array('name' => 'Andrey Lubinov',
								   'website' => 'http://las.kiev.ua',
								   'email' => 'andrey.lubinov@gmail.com')
				 		);
		}
		
		public function uninstall(){
			$this->_Parent->Database->query("DROP TABLE `tbl_fields_nested_cats`");
			$this->_Parent->Database->query("DROP TABLE `tbl_ext_nested_cats`");
			$this->_Parent->Database->query("DELETE FROM `tbl_fields` WHERE `type` = 'nested_cats'");
		}
		

		public function install(){

			$this->_Parent->Database->query("CREATE TABLE `tbl_ext_nested_cats` (
				`id` int(11) NOT NULL auto_increment,
				`parent` int(11) NOT NULL,
				`title` varchar(255) NOT NULL,
				`handle` varchar(55) NOT NULL,
				`lft` int(11) NOT NULL,
				`rgt` int(11) NOT NULL,
				PRIMARY KEY  (`id`),
				KEY `lft` (`lft`),
				KEY `rgt` (`rgt`)
				) ENGINE=MyISAM 
			");

			$this->_Parent->Database->query("INSERT INTO `tbl_ext_nested_cats` (`parent`, `title`, `handle`, `lft`, `rgt`) 
				VALUES (0, 'Root', 'root', 0, 1)
			");

			$this->_Parent->Database->query("CREATE TABLE `tbl_fields_nested_cats` (
				`id` int(11) unsigned NOT NULL auto_increment,
				`field_id` int(11) unsigned NOT NULL,
				`related_field_id` VARCHAR(255) NOT NULL,
				PRIMARY KEY  (`id`),
				KEY `field_id` (`field_id`)
			)");

			return true;

		}
		
		public function fetchNavigation(){ 
			return array(
				array(
					'location' => 400,
					'name' => 'Categories',
					'children' => array(
						
						array(
							'name' => 'Overview',
							'link' => '/overview/'
						)
					)
				)
			);
		}
		
		#### Fetching Tree by $field, starting from a $start node, excluding $exclude branch
		function getTree($field,$start,$exclude=NULL) {

			if(!$root = $this->_Parent->Database->fetchRow(0, "SELECT `lft`, `rgt` FROM `tbl_ext_nested_cats` WHERE `$field` = '$start' LIMIT 1")) 
			return false;

			if($exclude) $exclude = join(',', range($exclude['lft'], $exclude['rgt']));

			if(!$result = $this->_Parent->Database->fetch("
						SELECT * FROM `tbl_ext_nested_cats` 
						WHERE (lft BETWEEN {$root['lft']} AND {$root['rgt']}) 
						".($exclude ? ' AND (`lft` NOT IN('.$exclude.')) AND (`rgt` NOT IN('.$exclude.'))' : NULL)."
						ORDER BY lft ASC 
			")) return false;

			return $result;
		}
		
		#### Nowhere used yet
		function getPath($field, $val) {

			if(!$cat = $this->_Parent->Database->fetchRow(0,"SELECT lft, rgt FROM `tbl_ext_nested_cats` 
						WHERE `$field` = $val 
						LIMIT 1
			")) return false;

			if(!$path = $this->_Parent->Database->fetch("SELECT id,title,handle FROM `tbl_ext_nested_cats` 
						WHERE lft <= {$cat['lft']} AND rgt >= {$cat['rgt']} 
						ORDER BY lft ASC 
			")) return false;
			
			return $path;
		}
		
		function getCat($id) { 

			if(!$result = $this->_Parent->Database->fetchRow(0,"SELECT * FROM `tbl_ext_nested_cats` 
				WHERE id=$id 
				LIMIT 1
			")) return false;

			return $result;

		}
		
		function buildSelectField($field, $start, $current, $parent=NULL, $element_name, $fieldnamePrefix=NULL, $fieldnamePostfix=NULL, $exclude=NULL, $settingsPannel=NULL) {

			if(!$tree = $this->getTree($field,$start,$exclude)) return Widget::Select(NULL, NULL, array('disabled' => 'true'));

			$right = array($tree[0]['rgt']);
			
			if(!$settingsPannel) {
				$options = array(array(NULL,NULL,'None'));
			} elseif ($settingsPannel && count($tree) == 1) {
			
				return new XMLElement('p', __('It looks like youre trying to create a field. Perhaps you want categories first? <br/><a href="%s">Click here to create some.</a>', array(URL . '/symphony/extension/nested_cats/overview/new/')));

			} else {
				$options = array(array($tree[0]['id'], NULL,'Full Tree'));
			}
			
			array_shift($tree);

			$selected = isset($parent) ? $parent : $current;
			
			foreach ($tree as $o){

				while ($right[count($right)-1]<$o['rgt']) { 
					array_pop($right); 
				}

				$options[] = array(
					$o['id'],
					$o['id'] == $selected,
					str_repeat('- ',count($right)-1) . $o['title']
				);
				
				$right[] = $o['rgt']; 
			}

			$select = Widget::Select(
				'fields'.$fieldnamePrefix.'['.$element_name.']'.$fieldnamePostfix, 
				$options, count($tree)>0 ? NULL : array('disabled' => 'true'));
			
			return $select;

		}
		
		function AddCat($fields) { 

			if(empty($fields['parent'])){

				$n = $this->_Parent->Database->fetchRow(0, "SELECT id, rgt FROM `tbl_ext_nested_cats` 
						WHERE lft=0
				");

			} else {

				$n = $this->_Parent->Database->fetchRow(0, "SELECT rgt FROM `tbl_ext_nested_cats` 
						WHERE id = {$fields['parent']}
				");

			}

			$parent = empty($fields['parent']) ? $n['id'] : $fields['parent'];

			$this->_Parent->Database->query("UPDATE `tbl_ext_nested_cats` SET lft=lft+2 WHERE lft>{$n['rgt']}");
			$this->_Parent->Database->query("UPDATE `tbl_ext_nested_cats` SET rgt=rgt+2 WHERE rgt>={$n['rgt']}");

			$title = $this->makeTitle($fields['title']);
			$handle = $this->makeUniqueHandle($fields['title']);

			$this->_Parent->Database->query("INSERT INTO `tbl_ext_nested_cats` SET parent='$parent', lft={$n['rgt']}, rgt={$n['rgt']}+1, title='$title', handle='$handle' 
				");

			return true;

		}
		
		function removeCat($id) {

			### Check if category wasn't already deleted before as someones' child
			if($this->_Parent->Database->fetchVar("count", 0, "SELECT count(*) as `count` FROM `tbl_ext_nested_cats` 
					WHERE `id` = '$id' LIMIT 1")) {

				$child = $this->getTree('id', $id);
	
				foreach ($child as $c) {
					$ids[] = $c['id'];
				}
	
				$this->_Parent->Database->delete('tbl_ext_nested_cats', " `id` IN ('".implode("', '", $ids)."')");


				### Removing related entries from fields table
				if($fieldIds = $this->_Parent->Database->fetchCol('field_id',"SELECT `field_id` FROM `tbl_fields_nested_cats` WHERE  `related_field_id` IN ('".implode("', '", $ids)."')")){

					$this->_Parent->Database->delete('tbl_fields_nested_cats', " `related_field_id` IN ('".implode("', '", $ids)."')");

/* ### Not So easy =)
					foreach($fieldIds as $fieldId){
						$this->_Parent->Database->delete('tbl_entries_data_'.$fieldId, " `relation_id` IN ('".@implode("', '", $ids)."')");
					}
*/
				}
	
				$root = $this->_Parent->Database->fetchVar('id', 0, "SELECT `id` FROM `tbl_ext_nested_cats` WHERE `lft` = 0 LIMIT 1");
	
				$this->rebuildTree($root,0);
			
			}
			
			return true;
		}
		
		function rebuildTree($parent, $left) {

			$right = $left+1;

			$result = $this->_Parent->Database->fetch('SELECT id FROM `tbl_ext_nested_cats` '.
				'WHERE parent="'.$parent.'" ORDER BY lft ASC;');

			foreach ($result as $r) {
				$right = $this->rebuildTree($r['id'], $right);
			}

			$this->_Parent->Database->query('UPDATE `tbl_ext_nested_cats` SET lft='.$left.', rgt='.$right.' WHERE id="'.$parent.'";');

			return $right+1;
		}

		function updateCat($fields) { 

			$title = $this->makeTitle($fields['title']);
			$handle = $this->makeUniqueHandle($fields['title'], $fields['id']);

			if(empty($fields['parent']) || $fields['parent'] !== $this->_Parent->Database->fetchVar('parent', 0, "SELECT parent FROM `tbl_ext_nested_cats` WHERE id = {$fields['id']}")) {

				$root = $this->_Parent->Database->fetchVar('id', 0, "SELECT `id` FROM `tbl_ext_nested_cats` WHERE `lft` = 0 LIMIT 1");

				$newParent = $this->_Parent->Database->fetchRow(0, "SELECT `id`, `lft`, `rgt` FROM `tbl_ext_nested_cats` 
					WHERE `id` = '".(empty($fields['parent']) ? $root : $fields['parent'] )."'"
				);

				if(!$this->_Parent->Database->query("UPDATE `tbl_ext_nested_cats` SET title = '$title', handle = '$handle', parent = {$newParent['id']}, lft = {$newParent['rgt']} 
						WHERE id = {$fields['id']}
					")) return false;

				$this->RebuildTree($root,0);

			} else {

				if(!$this->_Parent->Database->query("UPDATE `tbl_ext_nested_cats` SET title='$title', handle='$handle' WHERE `id` ='$fields[id]'")) return false;
			}

			return true;

		}
		
		
		function makeUniqueHandle($title, $id=NULL){
		
			$handle = Lang::createHandle($title);

			### if handle is unique
			if(!$this->_Parent->Database->fetchVar("count", 0, "SELECT count(*) as `count` FROM `tbl_ext_nested_cats` 
					WHERE 
					".(!is_null($id) ? " `id` != $id AND " : NULL)."
					`handle` = '$handle' LIMIT 1")
			) {return $handle;} 
			
			### handle is not unique
			else {
			
				$count = $this->_Parent->Database->fetchVar("count", 0, "SELECT count(*) as `count` FROM `tbl_ext_nested_cats` 
					WHERE `handle` LIKE '" . $handle . "%' LIMIT 1");
				
				return $handle .= '-' . ($count+1);
			
			}
		
		}
		
		function makeTitle($title){
		
			return General::sanitize(
					function_exists('mysql_real_escape_string') ? mysql_real_escape_string(trim($title)) : addslashes(trim($title))
				);
		}
		
	}

