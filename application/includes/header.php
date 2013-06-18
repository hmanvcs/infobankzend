<?php 
# whether or not the session has errors
$session = SessionWrapper::getInstance(); 
$sessionhaserror = !isEmptyString($session->getVar(ERROR_MESSAGE));

$userid = $session->getVar("userid");  
$type = $session->getVar("type");

$user = new UserAccount();
$user->populate($userid);
	
# the request object instance
$request = Zend_Controller_Front::getInstance()->getRequest();

# application config
$config = Zend_Registry::get('config');

# pagination defaults
Zend_Paginator::setDefaultScrollingStyle('Sliding');
Zend_View_Helper_PaginationControl::setDefaultViewPartial("index/pagination_control.phtml");

$hide_on_print_class = $request->getParam(PAGE_CONTENTS_ONLY) == "true" ? "hideonprint" : ""; 

// initialize the ACL for all views
$acl = getACLInstance(); 

$os = browser_detection('os');
$islinux = false;
if($os != 'nt'){
  $islinux = true;
}

$thecontroller = $request->getControllerName();
$theaction = $request->getActionName();
$publiccontrollers = array("index", "signup","aboutus","howitworks","benefits","news","help","user","tour","forum","faqs","testimonials","billing"); 
if(isEmptyString($userid)){
	$publiccontrollers[] = 'contactus';
}

$bodyclass = '';
$bodyclassonly = '';
$isInnerPage = false;
if (in_array($thecontroller, $publiccontrollers)){
	$bodyclass = ' class="publicbody"';
	$bodyclassonly = ' publicbody';
} else {
	$isInnerPage = true;
}

$menulist = getMainmenuList();