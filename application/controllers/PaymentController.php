<?php
class PaymentController extends SecureController   {

    public function getActionforACL() {
		return ACTION_VIEW; 
    }
    
    public function getResourceForACL(){
        return "Farmer"; 
    }
    
	public function editAction() {
    	$this->_setParam("action", ACTION_EDIT);
		// debugMessage($this->_getAllParams());
    	// exit();
    	$this->createAction();
    }
    
	public function subscriptionAction() {
    	
    }
    
	public function processsubscriptionAction() {
    	$this->_setParam("action", ACTION_CREATE);
		// debugMessage($this->_getAllParams());
    	// exit();
    	parent::createAction();
    }
    
	public function viewAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
    }
    
	public function viewsubscriptionAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
    }
    
	function deleteAction() {
    	$session = SessionWrapper::getInstance(); 
    	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$formvalues = $this->_getAllParams();
		$successurl = decode($formvalues['successurl']);
		$classname = $formvalues['entityname'];
		// debugMessage($successurl);
		
    	$obj = new $classname;
    	$obj->populate($formvalues['id']);
    	// debugMessage($obj->toArray());
    	// exit();
    	if($obj->delete()) {
    		$session->setVar(SUCCESS_MESSAGE, $this->_translate->translate("global_delete_success"));
    		$this->_helper->redirector->gotoUrl($successurl);
    	}
    	
    	return false;
    }
    
	public function paytestAction(){
		$session = SessionWrapper::getInstance(); 
     	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$formvalues = $this->_getAllParams();
		debugMessage($formvalues);
		
		$user = new UserAccount();
		$user->populate(2790);
		
	    $test = array(
	    	"farmgroupid" => $user->getFarmer()->getFarmGroupID(),
	    	"trxcode" => '451541',
	    	"stem" => 1,
	    	"item" => 4,
	    	"amount" => 360000,
	    	"phone" => getFullPhone($user->getPhone()),
	    	"verifier" => "herman"
	    );
	    debugMessage($test);
	    $payment = new Payment();
	    $payment->processPost($test);
	    debugMessage('error is '.$payment->getErrorStackAsString());
	    debugMessage($payment->toArray());
	    
	    $payment->save();
	    debugMessage($payment->toArray());
    }
    
    public function paynowAction(){
		
    }
    
	public function step2authAction(){
		$session = SessionWrapper::getInstance(); 
     	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$formvalues = $this->_getAllParams();
		debugMessage($formvalues);
		debugMessage(decode($this->_getParam('successurl')));
		// exit();
		$this->_helper->redirector->gotoUrl(decode($this->_getParam('successurl')));
    }
    
    public function processpaynowAction(){
		$session = SessionWrapper::getInstance(); 
     	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$formvalues = $this->_getAllParams();
		$this->_helper->redirector->gotoUrl(decode($this->_getParam(URL_SUCCESS)));
    }
}