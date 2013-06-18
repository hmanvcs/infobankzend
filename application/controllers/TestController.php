<?php

class TestController extends IndexController  {
	
    function seasonAction(){
    	$session = SessionWrapper::getInstance(); 
    	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$season = new Season();
		$season->getTable()->findByid(2)->delete();
		
		$formvalues = $this->_getAllParams();
		// $formvalues['id'] = 1;
		$data = array(
			"id" => "2",
			"farmid" => "1",
			"farmerid" => "41",
			"ref" => "S001-1012",
			"startday" => "30",
			"startmonth" => "10",
			"startyear" => "2012",
			"createdby" => "1",
			"seasondetail" => array(
				array("type"=>"1","farmid"=>1,"commodityid"=>"6")
			)
		);
		
    	$season = new Season();
    	// $season->populate($formvalues['id']);
    	$formvalues = $data;
    	$season->processPost($formvalues);
    	debugMessage("season error is ".$season->getErrorStackAsString());
    	// debugMessage($season->toArray());
    	$season->save();
    	
    	$sid = $season->getID();
    	// debugMessage('id is '.$sid);
    	
    	$inputdata = array(
			"id" => "",
			"farmid" => "1",
			"seasonid" => $sid,
			"ref" => "Input/001/25-Oct-12",
			"activityname" => "testname",
			"startdate" => "2012-06-25",
			"total" => "150000",
    		"status" => "1",
			"createdby" => "1",
			"inputdetails" => array(
				array("type"=>"1","name"=>"line name 1", "inputdate"=>"2012-06-26","amount"=>"50000")
			)
		);
		
		$seasoninput = new SeasonInput();
		$seasoninput->processPost($inputdata);
		debugMessage("input error is ".$seasoninput->getErrorStackAsString());
    	// debugMessage($seasoninput->toArray());
    	$seasoninput->save();
    	
    	$tillagedata = array(
			"id" => "",
			"farmid" => "1",
			"seasonid" => $sid,
			"ref" => "Tillage/001/25-Oct-12",
			"activityname" => "test till",
			"startdate" => "2012-07-25",
    		"method" => "5",
			"totalexpenses" => "150000",
    		"status" => "1",
			"createdby" => "1"
		);
		
		$seasontill = new SeasonTillage();
		$seasontill->processPost($tillagedata);
		debugMessage("till error is ".$seasontill->getErrorStackAsString());
    	// debugMessage($seasontill->toArray());
    	$seasontill->save();
    	
		$plantdata = array(
			"id" => "",
			"farmid" => "1",
			"seasonid" => $sid,
			"cropid" => 5,
			"ref" => "Plant/001/25-Oct-12",
			"activityname" => "test till",
			"startdate" => "2012-09-25",
    		"method" => "2",
			"totalexpenses" => "150000",
    		"status" => "1",
			"createdby" => "1"
		);
		
		$seasonplant = new SeasonPlanting();
		$seasonplant->processPost($plantdata);
		debugMessage("plant error is ".$seasonplant->getErrorStackAsString());
    	// debugMessage($seasonplant->toArray());
    	$seasonplant->save();
    	
    	$pid = $seasonplant->getID();
    	// debugMessage('id is '.$sid);
    	
    	$trackdata = array(
			"id" => "",
			"farmid" => "1",
			"seasonid" => $sid,
			"plantingid" => $pid,
			"ref" => "Plant/001/25-Oct-12",
			"activityitem" => "test till",
			"startdate" => "2012-09-25",
    		"method" => "2",
			"totalexpenses" => "150000",
    		"status" => "1",
			"createdby" => "1"
		);
		
		$seasontrack = new SeasonTracking();
		$seasontrack->processPost($trackdata);
		debugMessage("plant error is ".$seasontrack->getErrorStackAsString());
    	debugMessage($seasontrack->toArray());
    	$seasontrack->save();
    	$ttid = $seasontrack->getID();
    	
    	$seasontrack = new SeasonTracking();
    	$test = $seasontrack->getTable()->findByid($ttid);
    	debugMessage($test->toArray());
    	
    }
    
    function smsAction(){
    	// disable rendering of the view and layout so that we can just echo the AJAX output 
	    $this->_helper->layout->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(TRUE);
	    
	    $session = SessionWrapper::getInstance(); 
	    $formvalues = $this->_getAllParams();
	    // debugMessage($formvalues);
	    $phone = SMS_TEST_NUMBER;
	    $username = SMS_USERNAME;
	    $password = SMS_PASSWORD;
	    // $port = SMS_PORT;
	    $message = 'helloworld. this is a dev test from farmis '.date('Y-m-d H:i:s');
	    
	    $client = new Zend_Http_Client(SMS_SERVER);
	    // the GET Parameters
		$client->setParameterGet(array(
			'username'  => $username,
			'password'  => $password,
			'type'	=>	0,
			'dlr'	=>	0,
			'source'=>	'TBD',
			'destination' => $phone,
			'message' => $message
		));
		
		// debugMessage($client); // exit();
    	try {
    	    $response = $client->request();
    	    $body = $response->getBody();
    	    // debugMessage($body);
    	    
	    } catch (Exception $e) {
	        # error handling
	        debugMessage("Error is ".$e->getMessage());
	    }
    }
    
	function pricesAction(){
    	// disable rendering of the view and layout so that we can just echo the AJAX output 
	    $this->_helper->layout->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(TRUE);
	    
	    $session = SessionWrapper::getInstance(); 
	    $formvalues = $this->_getAllParams();
	    
		if (!function_exists('curl_init')){
	        die('Sorry cURL is not installed!');
	    }
	    //$Url = PRICES_SERVER."/feed/prices/commodity/maize/market/owino";
	    $Url = "http://mis.infotradeuganda.com/feed/prices/range/all";
	    // debugMessage($Url);
	    
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $Url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
	    $output = curl_exec($ch);
	    // debugMessage(curl_getinfo($ch, CURLINFO_HTTP_CODE));
    	curl_close($ch);
    	
    	// echo $output;
    	$data = xmlstr_to_array($output);
		debugMessage($data);
    }
    
    function testmailAction(){
    	$this->_helper->layout->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(TRUE);
	    
    	sendTestMessage('hman test','farmis email testing');
    }
    
    function commentAction(){
    	$this->_helper->layout->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(TRUE);
	    
	    $formvalues = $this->_getAllParams();
	   
	    $id = 32;
		$communityforumcomment = new CommunityForumComment();
		$communityforumcomment->populate($id);	
		debugMessage($communityforumcomment->toArray());
		$communityforumcomment->afterSave();
		
    }
    
    function vgroupAction(){
    	$this->_helper->layout->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(TRUE);
	    
	    $formvalues = $this->_getAllParams();
	    $id = 49;
	    $farmgrp = new FarmGroup();
	    $farmgrp->populate($id);
	    debugMessage($farmgrp->toArray());
	    $farmgrp->refNoExists();
    }
    
    function cleanfarmersAction(){
    	$this->_helper->layout->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(TRUE);
	    
	    $f = new Farmer();
	    $allfarmers = $f->getAllFarmers();
	    // debugMessage($allfarmers->toArray());
	    debugMessage('count is '.$allfarmers->count());
    }
    
	function emailAction(){
    	$this->_helper->layout->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(TRUE);
	    
	    sendTestMessage('test farmis email','this is a test message for farmis please ignore - '.APPLICATION_ENV);
    }
}

