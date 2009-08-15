<?php

	require_once(TOOLKIT . '/class.datasource.php');
	
	Class datasourcegravatars extends Datasource{
		
		/* Configuration */
		// the comments data source output parameter, providing an array of email addresses
		var $ds_comments = 'ds-comments';
		// size in pixels
		var $size = 40;
		
		var $dsParamROOTELEMENT = 'gravatars';	
		function __construct(&$parent, $env=NULL, $process_params=true){
			parent::__construct($parent, $env, $process_params);
			$this->_dependencies = array('$' . $this->ds_comments);
		}
		
		function about(){
			return array(
					 'name' => 'Gravatars',
					 'author' => array(
							'name' => 'Nick Dunn',
							'website' => 'http://airlock.com',
							'email' => 'nick.dunn@airlock.com'),
					 'version' => '1.0',
					 'release-date' => '2008-10-21T15:58:52+00:00');	
		}
		
		function grab(&$param_pool){
			
			$result = new XMLElement($this->dsParamROOTELEMENT);			

			foreach($this->_env["env"]["pool"][$this->ds_comments] as $email) {
				$gravatar = new XMLElement("gravatar");
				$gravatar->setAttribute("email", $email);
				$gravatar->setAttribute("url", "http://www.gravatar.com/avatar/" . md5(strtolower($email)) . "?s=" . $size . $this->size . "&amp;d=" . $this->_env["param"]["root"] . "/extensions/gravatar/assets/default.gif");
				$result->appendChild($gravatar);
			}
			
			return $result;
		}
		
	}

?>