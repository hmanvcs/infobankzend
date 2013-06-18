<?php

/**
 * Model for a position 
 *
 */
class Position extends BaseRecord {
	
	public function setTableDefinition() {
		#add the table definitions from the parent table
		parent::setTableDefinition();
		$this->setTableName('position');
		
		$this->hasColumn('name', 'string', 255, array('notnull' => true, 'notblank' => true));
		$this->hasColumn('alias', 'string', 500);
		$this->hasColumn('styleid', 'string', 50);
		$this->hasColumn('styleclass', 'string', 50);
		$this->hasColumn('description', 'string', 500);
		$this->hasColumn('level', 'integer', null, array('default' => 0));
		$this->hasColumn('parentid', 'integer', null, array('default' => NULL));
		$this->hasColumn('sortorder', 'integer', null, array('default' => NULL));
	}
	/**
	 * Contructor method for custom functionality - add the fields to be marked as dates
	 */
	public function construct() {
		parent::construct();
		// set the custom error messages
       	$this->addCustomErrorMessages(array(
       									"name.notblank" => $this->translate->_("global_name_error"),	
       									"name.length" => $this->translate->_("global_name_length_error")						
       	       						));
	}
	/*
	 * Pre process model data
	 */
	function processPost($formvalues) {
		$session = SessionWrapper::getInstance(); 
		// trim spaces from the name field
		if(isArrayKeyAnEmptyString('parentid', $formvalues)){
			unset($formvalues['parentid']); 
		}
		if(isArrayKeyAnEmptyString('level', $formvalues)){
			unset($formvalues['level']); 
		}
		if(isArrayKeyAnEmptyString('sortorder', $formvalues)){
			unset($formvalues['sortorder']); 
		}
		
		// debugMessage($formvalues); exit();
		parent::processPost($formvalues);
	}
}

?>