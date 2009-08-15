<?php

	Class extension_datestamp_helper extends Extension{

		public $workspacePosition = NULL;

		public function about(){
			return array(
				'name' => 'Datestamp Helper',
				'version' => '1.0',
				'release-date' => '2009-07-02',
				'author' => array(
					'name' => 'Rainer Borene',
					'email' => 'rainerborene@gmail.com',
				)
			);		
		}

		public function getSubscribedDelegates(){
			return array(
				array(
					'page' => '/frontend/',
					'delegate' => 'FrontendOutputPostGenerate',
					'callback' => 'replaceParams'					
				)
			);
		}

		public function __replaceParam($matches) {
			if (!$this->workspacePosition) $this->workspacePosition = strpos($matches[2], 'workspace');

			$mtime = @filemtime(substr($matches[2], $this->workspacePosition));

			return str_replace(':datestamp', ($mtime ? '?' . $mtime : NULL), $matches[0]);
		}

		public function replaceParams(&$context){
			$context['output'] = preg_replace_callback('/(\"|\')([^\"\']+):datestamp?(\"|\')/', array(&$this, '__replaceParam'), $context['output']);
		}
	}
