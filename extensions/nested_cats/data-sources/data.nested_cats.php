<?php

	Class datasourceNested_Cats extends Datasource{

		function __construct(&$parent){
			parent::__construct($parent);
		}

		function example(){
			return '	<nested-cats>
		<item id="7" parent-id="1" level="1" handle="fruits">Fruits</item>
		<item id="8" parent-id="7" level="2" handle="apples">Apples</item>
		<item id="9" parent-id="7" level="2" handle="bananas">Bananas</item>
		<item id="10" parent-id="1" level="1" handle="animals">Animals</item>
		<item id="11" parent-id="10" level="2" handle="giraffes">Giraffes</item>
		<item id="12" parent-id="10" level="2" handle="pandas">Pandas</item>
	</nested-cats>
    
	Possible Usage:
    
	<ul>
		<xsl:apply-templates select="nested-cats/item[@level = 1]"/>
	</ul>
	
	<xsl:template match="nested-cats/item">
		<xsl:variable name="id" select="@id"/>
		<li>
			<a href="{$root}/test/{@handle}"><xsl:value-of select="."/></a>
			<xsl:if test="/data/nested-cats/item[@parent-id = $id]">
				<ul>
					<xsl:apply-templates select="/data/nested-cats/item[@parent-id = $id]"/>
				</ul>
			</xsl:if>
		</li>
	</xsl:template>


';
		}

		function about(){

			return array(
				"name" => "Nested Cats",
				"description" => "Nested Cats",
				"author" => array("name" => "Andrey Lubinov",
					"website" => "http://las.kiev.ua",
					"email" => "andrey.lubinov@gmail.com"),
				"version" => "1.0",
				"release-date" => "2009-03-28",
			);
		}

		function grab(){
			
			include_once(EXTENSIONS . '/nested_cats/extension.driver.php');
        		$driver = $this->_Parent->ExtensionManager->create('nested_cats');

			$xml = new XMLElement('nested-cats');

			$data = $driver->getTree('lft',0);
			
			$right = array($data[0]['rgt']);
			array_shift($data);

			if (!$data) {
				$error = new XMLElement('error', 'No data received.');
				$xml->appendChild($error);
				return $xml;
			}
			
			foreach($data as $d) {
			
				if(count($right)>0) {
					while ($right[count($right)-1]<$d['rgt']) {
						array_pop($right);
					}
				}
			
				$item = new XMLElement('item', $d['title']);
					$item->setAttribute('id', $d['id']);
					$item->setAttribute('parent-id', $d['parent']);
					$item->setAttribute('level', count($right));
					$item->setAttribute('handle', $d['handle']);
				$xml->appendChild($item);
				
				$right[] = $d['rgt'];
			
			}

			return $xml;

		}
	}

?>