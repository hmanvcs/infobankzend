<?php

class SignupController extends IndexController   {	
	
	function processstep1Action() {
		// the group to which the user is to be added
		$formvalues = $this->_getAllParams();
		// debugMessage($this->_getAllParams()); // exit();	
			
		$this->_setParam("isactive", 0); 
		$this->_setParam('entityname', 'UserAccount');
		$this->_setParam(URL_FAILURE, encode($this->view->baseUrl("signup")));
		$this->_setParam(URL_SUCCESS, encode($this->view->baseUrl("signup/confirm")));
		$this->_setParam("action", ACTION_CREATE); 
		
		// set parent's gender from person type
		$post = array(
			"createdby" => "1",
			"usergroups_groupid" => array(2),
			"type" => $this->_getParam('type')
		);
		
		$this->_setParam('firstname', ucfirst($formvalues['firstname']));
		$this->_setParam('lastname', ucfirst($formvalues['lastname']));
		$this->_setParam('createdby', $post['createdby']);
		$this->_setParam('usergroups_groupid', $post['usergroups_groupid']);
		
		// debugMessage($this->_getAllParams());
		// exit();
		parent::createAction();
	}
	
	function processinviteAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$formvalues = $this->_getAllParams();
		$session = SessionWrapper::getInstance(); 
		$formvalues['id'] = $formvalues['farmerid'];
		// debugMessage($formvalues);
		
		$this->_setParam('entityname', 'Farmer');
		$this->_setParam(URL_FAILURE, encode($this->view->baseUrl('signup/index/profile/'.encode($formvalues['id'])."/")));
		$this->_setParam(URL_SUCCESS, encode($this->view->baseUrl("signup/inviteconfirmation")));
		$this->_setParam("action", ACTION_EDIT);
		$this->_setParam('createdby', 1);
		
		$farmer = new Farmer(); 
		$farmer->populate($formvalues['id']);
		
		# determine if is a farmer or farmgroup clerk
		$isgroupclerk = false;
		$isfarmer = true;
		if($farmer->getFarmGroup()->getManagerID() == $farmer->getID()){
			$isgroupclerk = true;
			$isfarmer = false;
		}
		
		if(isEmptyString($formvalues['birthday'])){
			$formvalues['birthday'] = NULL;
		}
		if(isEmptyString($formvalues['birthmonth'])){
			$formvalues['birthmonth'] = NULL;
		}
		if(isEmptyString($formvalues['birthmonth'])){
			$formvalues['birthyear'] = NULL;
		}
		if(!isEmptyString($formvalues['birthday']) && !isEmptyString($formvalues['birthmonth']) && !isEmptyString($formvalues['birthyear'])){
			$formvalues['dateofbirth'] = $formvalues['birthyear']."-".$formvalues['birthmonth']."-".$formvalues['birthday'];
		} else {
			if(!isEmptyString($formvalues['dateofbirth'])){
				$formvalues['dateofbirth'] = changeDateFromPageToMySQLFormat($formvalues['dateofbirth']); 
			} else {
				$formvalues['dateofbirth'] = NULL;
			}
		}
		
		$formvalues['gtype'] = 2;
		if($farmer->isFarmGroupManager()){
			$formvalues['gtype'] = 3;
			$formvalues['membershipplanid'] = 4;
		}
		
		// user account data
		$userarray = array(
				"id" => $formvalues['userid'],
				"farmerid" => $formvalues['farmerid'],
				"gender" => $formvalues['gender'],
				"type" => $formvalues['gtype'],
				"firstname" => $formvalues['firstname'],
			    "lastname" => $formvalues['lastname'],
		 		"username" => $formvalues['username'],
			    "email" => $formvalues['email'],
				"phone" => $formvalues['phone'],
			    "password" => sha1($formvalues['password']),
			    "agreedtoterms" => $formvalues['agreedtoterms'], 
			    "membershipplanid" => $formvalues['membershipplanid'],
				"isactive" => 1,
				"activationkey" => NULL,
  				"activationdate" => date("Y-m-d"),
				"usergroups" => array(
					array("groupid" => $formvalues['type'])
				),
				"createdby" => "1"
			);

		$formvalues['user'] = $userarray;
		$formvalues['hasacceptedinvite'] = '1';
		// debugMessage($formvalues);
		// exit(); 
		$farmer->processPost($formvalues);
		/*debugMessage($farmer->toArray()); 
		debugMessage('process errors are '.$farmer->getErrorStackAsString()); exit(); */
		// check for processing errors
		if($farmer->hasError()) {
			// debugMessage('process errors are '.$farmer->getErrorStackAsString()); exit();
			$session->setVar(FORM_VALUES, $this->_getAllParams());
    		$session->setVar(ERROR_MESSAGE, $farmer->getErrorStackAsString()); 
			$this->_helper->redirector->gotoUrl(decode($this->_getParam(URL_FAILURE)));
		} else {
			try {
				if($farmer->transactionInviteUpdate()){
					# set subscription period for user
					$farmer->setNewSubscription();
					$this->_helper->redirector->gotoUrl(decode($this->_getParam(URL_SUCCESS))); 
				}
			} catch (Exception $e) {
				$session->setVar(FORM_VALUES, $this->_getAllParams());
    			$session->setVar(ERROR_MESSAGE, $e->getMessage()); 
    			// debugMessage('save errors are '.$e->getMessage());
				$this->_helper->redirector->gotoUrl(decode($this->_getParam(URL_FAILURE)));
			}
		}
		
		// parent::createAction();
		return false;
	}
	
	function activateAction() {
		$session = SessionWrapper::getInstance(); 
		$formvalues = $this->_getAllParams();
		if(!isArrayKeyAnEmptyString('id', $formvalues)){
			// debugMessage($formvalues);
			$isphoneactivation = false;
			if(!isArrayKeyAnEmptyString('act_byphone', $formvalues)){
				// debugMessage('activated via phone');
				$isphoneactivation = true;
			}
			$user = new UserAccount(); 
			$user->populate(decode($formvalues['id']));
			// debugMessage($user->toArray());
			
			if ($user->isUserActive() && isEmptyString($user->getActivationKey())) {
				// account already activated 
	    		$session->setVar(ERROR_MESSAGE, 'Account is already activated. Please login.'); 
				$this->_helper->redirector->gotoUrl($this->view->baseUrl("user/login"));
			}
			
			$this->_setParam(URL_FAILURE, encode($this->view->baseUrl("signup/confirm/id/".encode($user->getID()))));
			$key = $this->_getParam('actkey');
			
			$this->view->result = $user->activateAccount($key, $isphoneactivation);
			// exit();
			if (!$this->view->result) {
				// activation failed
				$this->view->message = $user->getErrorStackAsString();
				$session->setVar(FORM_VALUES, $this->_getAllParams());
	    		$session->setVar(ERROR_MESSAGE, $user->getErrorStackAsString()); 
				$this->_helper->redirector->gotoUrl(decode($this->_getParam(URL_FAILURE)));
			}
		}
	}
	
	function activateaccountAction() {
		$session = SessionWrapper::getInstance(); 
		// replace the decoded id with an undecoded value which will be used during processPost() 
		$id = decode($this->_getParam('id')); 
		$this->_setParam('id', $id); 
		
		$user = new UserAccount(); 
		$user->populate($id);
		$user->processPost($this->_getAllParams());
		
		if ($user->hasError()) {
			$session->setVar(FORM_VALUES, $this->_getAllParams());
    		$session->setVar(ERROR_MESSAGE, $user->getErrorStackAsString()); 
			$this->_helper->redirector->gotoUrl(decode($this->_getParam(URL_FAILURE)));
		}
		
		$result = $user->activateAccount($this->_getParam('activationkey'));
		if ($result) {
			// go to sucess page 
			$this->_helper->redirector->gotoUrl(decode($this->_getParam(URL_SUCCESS))); 
		} else {
			$session->setVar(FORM_VALUES, $this->_getAllParams());
    		$session->setVar(ERROR_MESSAGE, $user->getErrorStackAsString()); 
			$this->_helper->redirector->gotoUrl(decode($this->_getParam(URL_FAILURE)));
		}
	}
	
	function mobileactivateAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$formvalues = $this->_getAllParams();
		// $formvalues['phone'] = '0756412300';
		$session = SessionWrapper::getInstance();
		// debugMessage($formvalues);
		$key = trim($formvalues['actkey']);
		$user = new UserAccount();
		
		$useraccount = $user->populateByPhone(getFullPhone($formvalues['phone']), $key);
		// debugMessage($useraccount->toArray());
		
		# check if user with specified phone exists
		if(!isEmptyString($useraccount->getID())){
			# now validate user's activation code specified
			if($useraccount->getActivationKey() == $key){
				$useraccount->getPhones()->get(0)->setIsPrimary(1);
				$useraccount->getPhones()->get(0)->activate();
				$this->_helper->redirector->gotoUrl($this->view->baseUrl("signup/index/profile/".encode($useraccount->getFarmerID()).'/actkey/valid'));
			} else {
				$this->_helper->redirector->gotoUrl($this->view->baseUrl("signup/activate/acterror/1"));
			}
		} else {
			$this->_helper->redirector->gotoUrl($this->view->baseUrl("signup/activate/acterror/1"));
		}
		// exit();
	}
	
	function confirmAction() {
		
	}
	
	function activationerrorAction() {
		
	}
	
	function activationconfirmationAction() {
		
	}
	
	function inviteconfirmationAction() {
		
	}
	
	function getcaptchaAction(){
		$this->view->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
		$session = SessionWrapper::getInstance(); 
		
		$string = '';
		for ($i = 0; $i < 5; $i++) {
			$string .= chr(rand(97, 122));
		}
		$session->setVar('random_number', $string);
		// $_SESSION['random_number'] = $string;

		$dir = $this->view->baseUrl("images/fonts/");
		//$dir = APPLICATION_PATH."/../public/images/fonts";
		// debugMessage($dir);
		$image = imagecreatetruecolor(165, 50);

		// random number 1 or 2
		
		echo $session->getVar('random_number');
	}
	function captchaAction() {
		$this->view->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
		$session = SessionWrapper::getInstance();
		//include('dbcon.php');
		// debugMessage('scre is '.strtolower($this->_getParam('code')));
		// debugMessage('rand is '.strtolower($session->getVar('random_number')));
		if(strtolower($this->_getParam('code')) == strtolower($session->getVar('random_number'))){
			echo 1;// submitted 
		} else {
			echo 0; // invalid code
		}
	}
	
	function testAction() {
		$this->_helper->layout->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
		$user = new UserAccount();
		$user->populate(decode($this->_getParam('id')));
		$user->transactionSave();
		//debugMessage($user->toArray());
		//debugMessage($user->getFarmer()->getNextRegNo());
	}
	
	function checkusernameAction(){
		$this->_helper->layout->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
	    
		$formvalues = $this->_getAllParams();
		$username = trim($formvalues['username']);
		// debugMessage($formvalues);
		$user = new UserAccount();
		if(!isArrayKeyAnEmptyString('userid', $formvalues)){
			$user->populate($formvalues['userid']);
		}
		if($user->usernameExists($username)){
			echo '1';
		} else {
			echo '0';
		}
		
	}
	
	function checkemailAction(){
		$this->_helper->layout->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
	    
		$formvalues = $this->_getAllParams();
		$email = trim($formvalues['email']);
		// debugMessage($formvalues);
		$user = new UserAccount();
		if(!isArrayKeyAnEmptyString('userid', $formvalues)){
			$user->populate($formvalues['userid']);
		}
		if($user->emailExists($email)){
			echo '1';
		} else {
			echo '0';
		}
	}
	
	function checkphoneAction(){
		$this->_helper->layout->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
	    
		$formvalues = $this->_getAllParams();
		$phone = trim($formvalues['phone']);
		// debugMessage($formvalues);
		$user = new UserAccount();
		if(!isArrayKeyAnEmptyString('userid', $formvalues)){
			$user->populate($formvalues['userid']);
		}
		if($user->phoneExists(getFullPhone($phone))){
			echo '1';
		} else {
			echo '0';
		}
	}
	
	function pricingAction(){
		
	}
}
