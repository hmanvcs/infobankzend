<?php

/**
 * Enables the download of uploaded files 
 * 
 */

class DownloadController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		// automatic file mime type handling
		$filename = decode($this->_getParam('filename')); 
		$full_path = APPLICATION_PATH.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR.PUBLICFOLDER.DIRECTORY_SEPARATOR."dompdf".DIRECTORY_SEPARATOR."temp".DIRECTORY_SEPARATOR.$filename;
		
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
 
	    // disable layout and view
	    $this->view->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
	}	
}
