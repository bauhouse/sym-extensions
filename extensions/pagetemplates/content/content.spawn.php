<?php

	require_once(CONTENT . '/content.blueprintspages.php');
	require_once(EXTENSIONS . '/pagetemplates/lib/class.templateform.php');

	class contentExtensionPageTemplatesSpawn extends contentBlueprintsPages {

		function view(){
			templateForm::render('Create Page', URL . '/symphony/blueprints/pages/new/');
		}
	} 
