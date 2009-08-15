<?php
	
	require_once(EXTENSIONS . '/importmanager/lib/class.importer.php');
	
	class importFeeds extends Importer {
		public function __construct(&$parent) {
			parent::__construct($parent);
		}
		
		public function about() {
			return array(
				'name'			=> 'Feeds',
				'author'		=> array(
					'name'			=> 'Rowan Lewis',
					'website'		=> 'http://pixelcarnage.com/',
					'email'			=> 'rowan@pixelcarnage.com'
				),
				'version'		=> '1.001',
				'release-date'	=> '13 January 2009'
			);	
		}
		
		public function getSection() {
			return (integer)'22';
		}
		
		public function getRootExpression() {
			return '/rss/channel/item | /feed/entry';
		}
		
		public function getUniqueField() {
			return '109';
		}
		
		public function canUpdate() {
			return true;
		}
		
		public function getFieldMap() {
			return array(
				'106' => 'title/text()',
				'107' => "link/@href | *[name() = 'link' or name() = 'guid'][1]/text()",
				'108' => "*[name() = 'pubDate' or name() = 'published'][1]/text()",
				'109' => "*[name() = 'guid' or name() = 'id' or name() = 'link'][1]/text()",
				'110' => "*[name() = 'description' or name() = 'content'][1]/text()",
				'111' => '/rss/channel/title/text() | /feed/title'
			);
		}
		
		public function allowEditorToParse() {
			return true;
		}
	}
	
?>