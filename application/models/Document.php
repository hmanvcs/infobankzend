<?php 

class Document extends BaseRecord {
	
    public function setTableDefinition() {
		#add the table definitions from the parent table
		parent::setTableDefinition();
		$this->setTableName('document');
		$this->hasColumn('type', 'integer', null, array('notnull' => true, 'notblank' => true, 'default' => 1)); // 1 - farmer, 2 - Farm, 3 - Season, 4 -inventory, 5-  
		$this->hasColumn('filename', 'string', 255, array('notnull' => true, 'notblank' => true));
        $this->hasColumn('title', 'string', 255);
       	$this->hasColumn('description','string', 500);
        $this->hasColumn('filepath', 'string', 255);
        $this->hasColumn('filesize', 'integer', null);
        $this->hasColumn('extension', 'string', 6);
        $this->hasColumn('mimetype', 'string', 255);
        $this->hasColumn('category', 'integer', null);
        $this->hasColumn('dateuploaded', 'date', null);
        $this->hasColumn('notes', 'string', 500);
        
        $this->hasColumn('uploadedbyid', 'integer', null);
        $this->hasColumn('farmerid', 'integer', null);
		$this->hasColumn('farmid', 'integer', null);
		$this->hasColumn('seasonid', 'integer', null);	
		$this->hasColumn('inventoryid', 'integer', null);
		
    }
	/**
    * Contructor method for custom functionality - add the fields to be marked as dates
    */
	public function construct() {
		parent::construct();
       $this->addDateFields(array("dateuploaded"));
       
       // set the custom error messages
       $this->addCustomErrorMessages(array(
       									"filename.notblank" => $this->translate->_("document_filename_error"),
       									"type.notblank" => $this->translate->_("document_type_error"),
       									"filepath.notblank" => $this->translate->_("document_filepath_error"),
       								));
    }
	
    public function setUp() {
		parent::setUp();
		
		// match the parent id
		$this->hasOne('Season as season',
							array('local' => 'seasonid',
									'foreign' => 'id'
							)
						);
		$this->hasOne('Farm as farm',
							array('local' => 'farmid',
									'foreign' => 'id'
							)
						);
		$this->hasOne('Farmer as farmer', 
							array(
								'local' => 'farmerid',
								'foreign' => 'id'
							)
						);
		$this->hasOne('UserAccount as uploadedby', 
								array(
									'local' => 'uploadedbyid',
									'foreign' => 'id'
								)
						);
		$this->hasOne('Inventory as inventory',
							array('local' => 'inventoryid',
									'foreign' => 'id'
							)
						);
	}
	/*
	 * Pre process model data
	 */
	function processPost($formvalues) {
		// trim spaces from the name field
		if(isArrayKeyAnEmptyString('categoryid', $formvalues)){
			unset($formvalues['categoryid']); 
		}
		if(isArrayKeyAnEmptyString('filesize', $formvalues)){
			unset($formvalues['filesize']); 
		}
		if(isArrayKeyAnEmptyString('farmerid', $formvalues)){
			unset($formvalues['farmerid']); 
		}
		if(isArrayKeyAnEmptyString('farmid', $formvalues)){
			unset($formvalues['farmid']); 
		}
		if(isArrayKeyAnEmptyString('seasonid', $formvalues)){
			unset($formvalues['seasonid']); 
		}
		if(isArrayKeyAnEmptyString('inventoryid', $formvalues)){
			unset($formvalues['inventoryid']); 
		}
		if(isArrayKeyAnEmptyString('uploadedbyid', $formvalues)){
			unset($formvalues['uploadedbyid']); 
		}
		// debugMessage($formvalues); exit();
		parent::processPost($formvalues);
	}
}
?>