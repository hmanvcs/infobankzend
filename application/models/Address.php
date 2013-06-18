<?php

class Address extends BaseEntity {
	
	public function setTableDefinition() {
		#add the table definitions from the parent table
		parent::setTableDefinition();
		
		$this->setTableName('address');
		$this->hasColumn('userid', 'integer', null);
		$this->hasColumn('type', 'integer', null, array('default' => '1')); // 1 farmer, 2 farm group, 3 farm, 4 organisation
		$this->hasColumn('country', 'string', 2, array('default' => 'UG'));
		$this->hasColumn('streetaddress', 'string', 255);
		$this->hasColumn('streetaddress2', 'string', 255);
		$this->hasColumn('city', 'string', 50);
		$this->hasColumn('state', 'string', 50);
		$this->hasColumn('zipcode', 'string', 15);
		$this->hasColumn('postalcode', 'string', 15);
		$this->hasColumn('isprimary', 'integer', null, array('default' => '1'));		
	}
	/**
	 * Contructor method for custom initialization
	 */
	public function construct() {
		parent::construct();
		
	}
	/**
	 * Model relationships
	 */
	public function setUp() {
		parent::setUp();
		
		$this->hasOne('UserAccount as user', 
								array(
									'local' => 'userid',
									'foreign' => 'id'
								)
						);
	}
	/**
	 * Preprocess model data
	 */
	function processPost($formvalues){
		// set default values for integers, dates, decimals
		if(isArrayKeyAnEmptyString('userid', $formvalues)){
			unset($formvalues['userid']); 
		}
		if(isArrayKeyAnEmptyString('type', $formvalues)){
			unset($formvalues['type']); 
		}
		if(isArrayKeyAnEmptyString('isprimary', $formvalues)){
			unset($formvalues['isprimary']); 
		}
		
		parent::processPost($formvalues);
	}
	/**
	 * Return the user's full physical address
	 *
	 * @return String The full physical address
	 */
	function getFullAddress() {
		$textstring = "";
		
		return $textstring;
	}
	/**
	 * Get the full name of the state from the two digit code
	 * 
	 * @return String The full name of the state 
	 */
	function getStateName() {
		if(isEmptyString($this->getState())){
			$currentstate = "";
		}
		if(strlen(getStates() == '2')) {
			if($this->getCountry() == 'US'){
				$states = getStates(); 
				$currentstate = $states[$this->getState()];
			}
		} else {
			$currentstate = $this->getState();
		}
		
		return $currentstate; 
	}
	/**
	 * Get the full name of the country from the two digit code
	 * 
	 * @return String The full name of the state 
	 */
	function getCountryName() {
		if(isEmptyString($this->getCountry())){
			return "--";
		}
		$countries = getCountries(); 
		return $countries[$this->getCountry()];
	}
}
?>