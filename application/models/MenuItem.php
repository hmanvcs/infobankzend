<?php

/**
 * Model for a menu item
 *
 */
class MenuItem extends BaseEntity {
	
	public function setTableDefinition() {
		#add the table definitions from the parent table
		parent::setTableDefinition();
		$this->setTableName('menuitem');
		
		$this->hasColumn('menuid', 'integer', null); 
		$this->hasColumn('name', 'string', 255, array('notnull' => true, 'notblank' => true));
		$this->hasColumn('description', 'string', 1000);
		$this->hasColumn('alias', 'string', 255);
		$this->hasColumn('alt', 'string', 255);
		$this->hasColumn('parentid', 'integer', null, array('default' => NULL)); 
		$this->hasColumn('type', 'integer', null, array('default' => NULL)); 
		$this->hasColumn('status', 'integer', null, array('default' => NULL));
		$this->hasColumn('path', 'string', 500);
		$this->hasColumn('link', 'string', 500);
		$this->hasColumn('styleid', 'string', 50);
		$this->hasColumn('styleclass', 'string', 50);
		$this->hasColumn('level', 'integer', null, array('default' => 0));
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
	 * Relationships for the model
	 */
	public function setUp() {
		parent::setUp();
		$this->hasOne('Menu as menu', 
								array(
									'local' => 'menuid',
									'foreign' => 'id'
								)
						); 
		$this->hasOne('MenuItem as parent', 
								array(
									'local' => 'parentid',
									'foreign' => 'id'
								)
						);
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
		if(isArrayKeyAnEmptyString('type', $formvalues)){
			unset($formvalues['type']); 
		}
		if(isArrayKeyAnEmptyString('status', $formvalues)){
			unset($formvalues['status']); 
		}
		
		// debugMessage($formvalues); exit();
		parent::processPost($formvalues);
	}
	/**
     * Overide  to save persons relationships
     *	@return true if saved, false otherwise
     */
    function afterSave(){
    	$session = SessionWrapper::getInstance();
    	$conn = Doctrine_Manager::connection();
    	$update = false;
    	
    	# save changes 
    	if($update){
    		$this->save();
    	}
    	
    	// find any duplicates and delete them
    	$duplicates = $this->getDuplicates();
		if($duplicates->count() > 0){
			$duplicates->delete();
		}
		
    	// exit();
    	return true;
    }
	# find duplicates after save
	function getDuplicates(){
		$q = Doctrine_Query::create()->from('MenuItem m')->where("m.type = '".$this->getType()."' AND m.name = '".$this->getName()."' AND m.parentid = '".$this->getParentID()."' AND m.createdby = '".$this->getCreatedBy()."' AND m.id <> '".$this->getID()."' ");
		
		$result = $q->execute();
		return $result;
	}
	# find menu by alias
	function findByAlias($alias) {
		# query active user details using email
		$q = Doctrine_Query::create()->from('MenuItem m')->where('m.alias = ?', $alias);
		$result = $q->fetchOne(); 
		
		if($result){
			$data = $result->toArray(); 
		} else {
			$data = $this->toArray(); 
		}
		
		# merge all the data including the user groups 
		$this->merge($data);
		# also assign the identifier for the object so that it can be updated
		if($result){
			$this->assignIdentifier($data['id']);
		} 
		
		return true; 
	}
}

?>