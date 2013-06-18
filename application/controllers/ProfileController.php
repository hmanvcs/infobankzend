<?php

class ProfileController extends SecureController  {
	
	/**
	 * @see SecureController::getResourceForACL()
	 *
	 * @return String
	 */
	function getResourceForACL() {
		return "User Account";
	}
	
	/**
	 * Override unknown actions to enable ACL checking 
	 * 
	 * @see SecureController::getActionforACL()
	 *
	 * @return String
	 */
	public function getActionforACL() {
        $action = strtolower($this->getRequest()->getActionName()); 
		if($action == "add" || $action == "other" || $action == "processother" || $action == "processadd" || 
			$action == "processvalidatephone" || $action == 'validatephone' || $action == 'changesettings') {
			return ACTION_CREATE; 
		} else {
			return ACTION_VIEW; 
		}
		return parent::getActionforACL(); 
    }
    
	public function addAction(){}

    function changepasswordAction()  {
    	
    }
    
    function processchangepasswordAction(){
    	$session = SessionWrapper::getInstance(); 
        $this->_translate = Zend_Registry::get("translate"); 
    	if(!isEmptyString($this->_getParam('password'))){
	        $user = new UserAccount(); 
	    	$user->populate(decode($this->_getParam('id')));
	    	// debugMessage($user->toArray());
	    	# Change password
	    	$user->changePassword($this->_getParam('oldpassword'), $this->_getParam('password'));
	    		// clear the session
	   			// send a link to enable the user to recover their password 
	   		$this->_redirect($this->view->baseUrl('profile/updatesuccess'));
		}
    }
    function changeusernameAction()  {
    	
    }
	function processchangeusernameAction()  {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
    	$session = SessionWrapper::getInstance(); 
        $this->_translate = Zend_Registry::get("translate");
        $formvalues = $this->_getAllParams();
        
    	if(!isArrayKeyAnEmptyString('username', $formvalues)){
	        $user = new UserAccount(); 
	    	$user->populate(decode($formvalues['id']));
	    	// debugMessage($user->toArray());
	    	
	    	if($user->usernameExists($formvalues['username'])){
	    		$session->setVar(ERROR_MESSAGE, sprintf($this->_translate->translate("useraccount_username_unique_error"), $formvalues['username']));
	    		return false;
	    	}
	    	# save new username
	    	$user->setUsername($formvalues['username']);
	    	$user->save();
	   		$this->_redirect($this->view->baseUrl('index/updatesuccess'));
		}
    }
    
	function changeemailAction()  {
		$session = SessionWrapper::getInstance(); 
		
		$formvalues = $this->_getAllParams();
		if(!isArrayKeyAnEmptyString('actkey', $formvalues) && !isArrayKeyAnEmptyString('ref', $formvalues)){
        	$newemail = decode($formvalues['ref']);
		
			$user = new UserAccount();
			$user->populate(decode($formvalues['id']));
			$oldemail = $user->getEmail();
			
			# validate the activation code
			if($formvalues['actkey'] != $user->getActivationKey()){
				$session->setVar(ERROR_MESSAGE, "Invalid code specified for email activation");
				$failureurl = $this->view->baseUrl('profile/view/id/'.encode($user->getID()).'/tab/account');
				$this->_helper->redirector->gotoUrl($failureurl);
			}
			
			$user->setActivationKey('');
			$user->setEmail($newemail);
			$user->setEmail2($oldemail);
			$user->save();
			// debugMessage($user->toArray());
			
	    	$successmessage = "Successfully updated. Please note that you can no longer login using your previous Email Address";
	    	$session->setVar(SUCCESS_MESSAGE, $successmessage);
	   		$this->_helper->redirector->gotoUrl($this->view->baseUrl('profile/view/id/'.encode($user->getID()).'/tab/account'));
        }
    }
	function processchangeemailAction()  {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
    	$session = SessionWrapper::getInstance(); 
        $this->_translate = Zend_Registry::get("translate");
        $formvalues = $this->_getAllParams();
         
        if(!isArrayKeyAnEmptyString('email', $formvalues)){
	        $user = new UserAccount(); 
	    	$user->populate(decode($formvalues['id']));
	    	// debugMessage($user->toArray());
	    	
	    	if($user->emailExists($formvalues['email'])){
	    		$session->setVar(ERROR_MESSAGE, sprintf($this->_translate->translate("useraccount_email_unique_error"), $formvalues['email']));
	    		return false;
	    	}
	    	# save new username
	    	$user->setEmail2($formvalues['email']);
	    	$user->setActivationKey($user->generateActivationKey());
	    	$user->save();
	    	
	    	//$user->sendNewEmailNotification($formvalues['email']);
    		//$user->sendOldEmailNotification($formvalues['email']);
	    	$successmessage = "A request to change your login email has been recieved. <br />To complete this process check your Inbox for a confirmation code and enter it below. Alternatively, click the activation link sent in the same email.";
	   		$this->_redirect($this->view->baseUrl('index/updatesuccess/successmessage/'.encode($successmessage)));
		}
    }
    
	function changephoneAction()  {
		$session = SessionWrapper::getInstance(); 
		
		$formvalues = $this->_getAllParams();
		if(!isArrayKeyAnEmptyString('actkey', $formvalues) && !isArrayKeyAnEmptyString('ref', $formvalues)){
        	$newphone = decode($formvalues['ref']);
		
			$user = new UserAccount();
			$user->populate(decode($formvalues['id']));
			$oldphone = $user->getPhone();
			$newprimary = $user->getPhone2();
			
			# validate the activation code
			if($formvalues['actkey'] != $user->getPhone2_ActKey()){
				$session->setVar(ERROR_MESSAGE, "Invalid code specified for phone activation");
				$failureurl = $this->view->baseUrl('profile/view/id/'.encode($user->getID()).'/tab/account');
				$this->_helper->redirector->gotoUrl($failureurl);
			}
			
			$user->setPhone($newprimary);
			$user->setPhone2($oldphone);
			$user->setPhone2_ActKey('');
			$user->setPhone2_IsActivated(1);
			$user->save();
			
	    	$successmessage = "Successfully updated. Please note that you can no longer login using your previous primary phone";
	    	$session->setVar(SUCCESS_MESSAGE, $successmessage);
	   		$this->_helper->redirector->gotoUrl($this->view->baseUrl('profile/view/id/'.encode($user->getID()).'/tab/account'));
        }
    }
	function processchangephoneAction()  {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
    	$session = SessionWrapper::getInstance(); 
        $this->_translate = Zend_Registry::get("translate");
        $formvalues = $this->_getAllParams();
         
        if(!isArrayKeyAnEmptyString('phone', $formvalues)){
	        $user = new UserAccount(); 
	    	$user->populate(decode($formvalues['id']));
	    	
			// debugMessage($formvalues);
	    	
	    	if($formvalues['phone'] == getShortPhone($user->getPhone2()) && $user->isValidated(2)){
		    	try {
		    		$user->setPhone(getFullPhone($formvalues['phone']));
		    		$user->setPhone2(getFullPhone($formvalues['oldphone']));
		    		$user->save();
		    	} catch (Exception $e) {
		    		debugMessage($e->getMessage());
		    	}
	    		
				$successmessage = "Successfully updated. Please note that you can no longer login using your previous primary phone";
		    	$session->setVar(SUCCESS_MESSAGE, $successmessage);
	    	} else {
	    		if($user->phoneExists($formvalues['phone'])){
		    		$session->setVar(ERROR_MESSAGE, sprintf($this->_translate->translate("useraccount_phone_unique_error"), $formvalues['phone']));
		    		return false;
		    	}
		    	# save new phone
		    	$user->setPhone2(getFullPhone($formvalues['phone']));
		    	$user->setPhone2_isActivated(0);
		    	$user->generatePhoneActivationCode(2);
		    	// $user->save(); 
		    	
		    	// $user->sendNewEmailNotification($formvalues['email']);
	    		// $user->sendOldEmailNotification($formvalues['email']);
		    	$successmessage = "A request to change your primary phone has been recieved. <br />To complete this process check your phone inbox for a confirmation code and enter it below.";
		   		$this->_redirect($this->view->baseUrl('index/updatesuccess/successmessage/'.encode($successmessage)));
	    	}
		}
    }
    
	function resendemailcodeAction()  {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
    	$session = SessionWrapper::getInstance(); 
        $formvalues = $this->_getAllParams();
         
        $user = new UserAccount(); 
    	$user->populate(decode($formvalues['id']));
    	// debugMessage($user->toArray());
    	
    	$session->setVar('contactuslink', "<a href='".$this->view->baseUrl('contactus')."' title='Contact Support'>Contact us</a>");
    	$user->sendNewEmailNotification($user->getEmail2());
    	$successmessage = "A new activation code has been sent to your new email address. If you are still having any problems please do contact us";
    	$session->setVar(SUCCESS_MESSAGE, $successmessage);
   		$this->_redirect($this->view->baseUrl('profile/view/id/'.encode($user->getID()).'/tab/account'));
    }
    
    function resetpasswordAction(){
    	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
	   			$session = SessionWrapper::getInstance(); 
       	$this->_translate = Zend_Registry::get("translate"); 
       		
		$user = new UserAccount(); 
		$user->populate(decode($this->_getParam('id')));
    	$user->setEmail($user->getEmail());
    	
    	if ($user->recoverPassword()) {
       		$session->setVar(SUCCESS_MESSAGE, sprintf($this->_translate->translate('useraccount_change_password_admin_confirmation'), $user->getName()));
   			// send a link to enable the user to recover their password 
   			$this->_helper->redirector->gotoUrl($this->view->baseUrl("profile/view/id/".encode($user->getID())));
    	} else {
   			$session->setVar(ERROR_MESSAGE, $user->getErrorStackAsString());
   			$session->setVar(FORM_VALUES, $this->_getAllParams());
	   		$this->_helper->redirector("view", "profile");
	   		$this->_helper->redirector->gotoUrl($this->view->baseUrl("profile/view/id/".encode($user->getID())));
    	}
   	}
   	
	public function validatephoneAction(){
    	
    }
    public function processvalidatephoneAction(){
    	$session = SessionWrapper::getInstance(); 
     	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		$formvalues = $this->_getAllParams();
		
		// debugMessage($formvalues);
		$successurl = decode($formvalues['successurl']);
		
		$user = new UserAccount();
		$user->populate($formvalues['id']);
		$type = $formvalues['phonetype'];
		
		// debugMessage($user->toArray()); 
		try {
			$user->generatePhoneActivationCode($type);
			$user->sendActivationCodeToMobile($type);
			
			$session->setVar(SUCCESS_MESSAGE, 'Validation code has been sent to the mobile phone. Please check Inbox and enter the code sent below to confirm.');
		} catch (Exception $e) {
			$txt = $e->getMessage();
			$session->setVar(ERROR_MESSAGE, 'An error occured in requesting activation for your Phone. Please contact support for resolution. '.$txt);
		}
		// exit();
    	$this->_helper->redirector->gotoUrl($successurl);
    }
	public function validatephonesuccessAction(){
		$session = SessionWrapper::getInstance(); 
     	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);

		$session->setVar(SUCCESS_MESSAGE, 'Validation code has been sent to the mobile phone. Please check Inbox and enter the code sent below to confirm.');
    }
	public function verifyphoneAction(){
		$session = SessionWrapper::getInstance(); 
     	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$formvalues = $this->_getAllParams();
		$successurl = decode($formvalues['successurl']);
		$type = $formvalues['type'];
		// debugMessage($formvalues);
		// debugMessage($successurl);
		
		$user = new UserAccount();
		$user->populate($formvalues['id']);
		// debugMessage($user->toArray());
		if($user->verifyPhone($formvalues['code'], $type)){
			$user->activatePhone($type);
			$user->sendActivationConfirmationToMobile($type);
			
			$session->setVar(SUCCESS_MESSAGE, 'Phone Number Successfully Verified and Confirmed');
			$session->setVar(ERROR_MESSAGE, '');
		} else {
			$session->setVar(SUCCESS_MESSAGE, '');
			$session->setVar(ERROR_MESSAGE, 'Invalid activation code specified. Please try again.');
		}
		
		// exit();
		// return to successpage
		$this->_helper->redirector->gotoUrl($successurl);
    }
}

