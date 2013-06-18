<?php

class ContactController extends SecureController {
	
	/**
	 * Get the name of the resource being accessed 
	 *
	 * @return String 
	 */
	public function getResourceForACL(){
        //return "Contact"; 
		return "User Account"; 
    }
    
    public function getActionforACL() {
        $action = strtolower($this->getRequest()->getActionName()); 
		if($action == "add" || $action == "processadd") {
			return ACTION_CREATE; 
		}
    	if($action == "search" || $action == "addsuccess" || $action == "delete" ||  $action == "temp"
    		
    	) {
			return ACTION_VIEW; 
		}
		return parent::getActionforACL(); 
    }
	
	public function addAction(){}

	public function addsuccessAction(){
		$session = SessionWrapper::getInstance(); 
     	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);

		$session->setVar(SUCCESS_MESSAGE, $this->_translate->translate("contact_add_success"));
    }
    function tempAction(){
    	$session = SessionWrapper::getInstance(); 
     	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$formvalues = $this->_getAllParams();
		debugMessage($formvalues);
		
		$contact = new Contact();
		//$contact->populate(1);
		debugMessage($contact->toArray());
		$data = array(
			"id" => "",
			"contacttype" => "2",
			"orgname" => "test",
			"locationid" => "50",
			"phone" => "6515665",
			"address" => "dfgd e gdf gd",
			"categoryid" => "3",
			"createdby" => "1"
		);
		$formvalues = array_merge_maintain_keys($formvalues, $data);
		$contact->processPost($formvalues);
		debugMessage($contact->toArray());
		debugMessage($contact->getErrorStackAsString());
		$contact->save();
    }
}