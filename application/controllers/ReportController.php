<?php
class ReportController extends SecureController   {
	
	/**
	 * Get the name of the resource being accessed 
	 *
	 * @return String 
	 */
	function getActionforACL() {
		return ACTION_VIEW;
	}
	/**
	 * @see SecureController::getResourceForACL()
	 * 
	 * Return the Application Settings since we need to make the url more friendly 
	 *
	 * @return String
	 */
	function getResourceForACL() {
		// return "Report"; 
		return "Farmer"; 
	}

	function primarybaselineAction(){
		$farmerid = decode($this->_getParam('id'));
		
		if($this->_getParam('download') == 1 && !isEmptyString($this->_getParam('filename'))){
			// automatic file mime type handling
			$filename = decode($this->_getParam('filename')); 
			$full_path = APPLICATION_PATH.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."public".DIRECTORY_SEPARATOR."dompdf".DIRECTORY_SEPARATOR."temp".DIRECTORY_SEPARATOR.$filename;
			
			// debugMessage($full_path); exit();
			// file headers to force a download
		    header('Content-Description: File Transfer');
		    header('Content-Type: application/octet-stream');
		    // to handle spaces in the file names 
		    header("Content-Disposition: attachment; filename=\"$filename\"");
		    header('Content-Transfer-Encoding: binary');
		    header('Expires: 0');
		    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		    header('Pragma: public');
		    readfile($full_path);
		} 
	}
	
	function baselinedetailAction(){
		$farmerid = decode($this->_getParam('id'));
		
		if($this->_getParam('download') == 1 && !isEmptyString($this->_getParam('filename'))){
			// automatic file mime type handling
			$filename = decode($this->_getParam('filename')); 
			$full_path = APPLICATION_PATH.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."public".DIRECTORY_SEPARATOR."dompdf".DIRECTORY_SEPARATOR."temp".DIRECTORY_SEPARATOR.$filename;
			
			// debugMessage($full_path); exit();
			// file headers to force a download
		    header('Content-Description: File Transfer');
		    header('Content-Type: application/octet-stream');
		    // to handle spaces in the file names 
		    header("Content-Disposition: attachment; filename=\"$filename\"");
		    header('Content-Transfer-Encoding: binary');
		    header('Expires: 0');
		    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		    header('Pragma: public');
		    readfile($full_path);
		} 
	}
	
	function allfarmersAction(){
		$listcount = new LookupType();
    	$listcount->setName("LIST_ITEM_COUNT_OPTIONS");
    	$values = $listcount->getOptionValues(); 
    	asort($values, SORT_NUMERIC); 
    	$session = SessionWrapper::getInstance();
    	
    	$dropdown = new Zend_Form_Element_Select('itemcountperpage',
							array(
								'multiOptions' => $values, 
								'view' => new Zend_View(),
								'decorators' => array('ViewHelper'),
							     'class' => array('span1')
							)
						);
		if (isEmptyString($this->_getParam('itemcountperpage'))) {
			$dropdown->setValue('ALL');
			if(!isEmptyString($session->getVar('itemcountperpage'))){
				$dropdown->setValue($session->getVar('itemcountperpage'));
			}
		} else {
			$session->setVar('itemcountperpage', $this->_getParam('itemcountperpage'));
			$dropdown->setValue($session->getVar('itemcountperpage'));
		}  
	    $this->view->listcountdropdown = '<span class="pull-right">'.$this->_translate->translate("global_list_itemcount_dropdown").$dropdown->render().'</span>';
	}
	
	function allfarmerssearchAction(){
		$this->_helper->redirector->gotoSimple("allfarmers", "report", 
    											$this->getRequest()->getModuleName(),
    											array_remove_empty(array_merge_maintain_keys($this->_getAllParams(), $this->getRequest()->getQuery())));
	}
	
	function certificateAction(){
		
	}
	
	function certificatesearchAction(){
		$this->_helper->redirector->gotoSimple("certificate", "report", 
    											$this->getRequest()->getModuleName(),
    											array_remove_empty(array_merge_maintain_keys($this->_getAllParams(), $this->getRequest()->getQuery())));
	}
}
