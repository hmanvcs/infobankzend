<?php

class MenuController extends IndexController  {
	function itemAction() {
		$formvalues = $this->_getAllParams(); 
		// debugMessage($formvalues);
		
		$menuitem = new MenuItem();
		$menuitem->findByAlias($this->_getParam('alias'));
		$this->view->itemid = $menuitem->getID();
		
	}
}

