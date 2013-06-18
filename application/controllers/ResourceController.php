<?php

class ResourceController extends SecureController   {

	/**
	 * Get the name of the resource being accessed 
	 *
	 * @return String 
	 */
	function getActionforACL() {
		return ACTION_VIEW;
	}
    
    public function getResourceForACL(){
        return "Farmer"; 
    }
    
    function allpricesAction() {
    	
    }
    
    function variablesAction(){
    	// parent::listAction();
    }
    
	function variablessearchAction(){
		$this->_helper->redirector->gotoSimple("variables", "resource", 
    											$this->getRequest()->getModuleName(),
    											array_remove_empty(array_merge_maintain_keys($this->_getAllParams(), $this->getRequest()->getQuery())));
	}
	
	function processvariableAction(){
		$session = SessionWrapper::getInstance(); 
     	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$formvalues = $this->_getAllParams();
		debugMessage($formvalues);
		
		$haserror = false;
		if(isArrayKeyAnEmptyString('value', $formvalues)){
			$haserror = true;
			$session->setVar(ERROR_MESSAGE, 'Error: No value specified for addition');
			$session->setVar(FORM_VALUES, $formvalues);
			$this->_helper->redirector->gotoUrl($this->view->baseUrl('resource/variables/type/'.$formvalues['lookupid']));
		}
		
		$lookupvalue = new LookupTypeValue();
		$dataarray = array('id' => $formvalues['id'],
							'lookuptypeid' => $formvalues['lookupid'], 
							'lookuptypevalue' => $formvalues['index'], 
							'lookupvaluedescription' => trim($formvalues['value']),
							'createdby' => $session->getVar('userid')
					);
		
		if(!isArrayKeyAnEmptyString('id', $formvalues)){
			$lookupvalue->populate($formvalues['id']);
		}
		// unset($dataarray['id']);
		$lookupvalue->processPost($dataarray);
		
		if($lookupvalue->hasError()){
			$haserror = true;
			$session->setVar(ERROR_MESSAGE, $lookupvalue->getErrorStackAsString());
			$session->setVar(FORM_VALUES, $formvalues);
			/*debugMessage($lookupvalue->toArray());
    		debugMessage('errors are '.$lookupvalue->getErrorStackAsString());*/
		} else {
			try {
				$lookupvalue->save();
				$session->setVar(SUCCESS_MESSAGE, "Successfully Added");
			} catch (Exception $e) {
				$session->setVar(ERROR_MESSAGE, $e->getMessage()."<br />".$lookupvalue->getErrorStackAsString());
				$session->setVar(FORM_VALUES, $formvalues);
			}
		}
		// exit();
		$this->_helper->redirector->gotoUrl($this->view->baseUrl('resource/variables/type/'.$formvalues['lookupid']));		
		
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
    
	function massmailAction(){
    	
    }
    
	function processmassmailAction(){
		$session = SessionWrapper::getInstance(); 
    	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$formvalues = $this->_getAllParams();
	}
	
	function bulksmsAction(){
    	
    }
    
	function processbulksmsAction(){
		$session = SessionWrapper::getInstance(); 
    	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		$formvalues = $this->_getAllParams();
	}
	function testAction(){
    	
    }
    function searchAction() {
    	$session = SessionWrapper::getInstance(); 
    	$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		$conn = Doctrine_Manager::connection();
		$formvalues = $this->_getAllParams();
		$userid = $session->getVar('userid');
		$farmerid = $session->getVar('farmerid');
		$farmgroupid = $session->getVar('farmgroupid');
		
		$user = new UserAccount();
		$user->populate($userid);
		
		$q = $formvalues['searchword'];
		$type = $user->getType();
		$grp = '';
		$html = '';
		$groupjoin = ' left ';
		if(!isArrayKeyAnEmptyString('grp', $formvalues)){
			$grp = $formvalues['grp'];
		}
		$typequery = '';
		if($type == 3 && !isArrayKeyAnEmptyString('grp', $formvalues)){
			$typequery = ' AND f.farmgroupid = '.$grp;
			$groupjoin = ' inner ';
		}
		$hasdata = false;
		// debugMessage($formvalues);
		
    	# search seasons. available for farmers and farm groups
		if($type == 2 || $type == 3){
			# seasons
			$farmer_filter = "s.farmerid = '".$user->getFarmerID()."' ";
			$farm_filter = "fm.farmerid = '".$user->getFarmerID()."' ";
			if($user->isFarmGroupAdmin()){
				$farmer_filter = "f.farmgroupid = '".$user->getFarmer()->getFarmGroupID()."' ";
				$farm_filter = "f.farmgroupid = '".$user->getFarmer()->getFarmGroupID()."' ";
			}	
			$query_seasons = "SELECT s.* FROM season as s inner join farmer as f on (s.farmerid = f.id)
				WHERE
				(".$farmer_filter.") AND  
				(s.activityname like '%".$q."%' or 
				s.ref like '%".$q."%') 
				GROUP BY s.id
				order by s.datecreated desc, s.activityname asc LIMIT 5 ";
			
			$result_seasons = $conn->fetchAll($query_seasons);
			// debugMessage($query_seasons);
			if(count($result_seasons) > 0){
				$hasdata = true;
				$html .= '<li class="separator"><span>Seasons</span></li>';
				foreach ($result_seasons as $row){
					$season = new Season();
					$season->populate($row['id']);
					$name= $season->getName()." - ".$season->getRef();
					$s_name='<b>'.$q.'</b>';
					$final_name = str_ireplace($q, $s_name, $name);
					$farmer_season_details = '';
					if(!isEmptyString($user->isFarmGroupAdmin())){
						$farmer_season_details = '<span class="blocked">'.$season->getFarmer()->getName().'</span><span class="blocked">'.$season->getFarm()->getName().'</span>';
					}
					$media = $season->getCrop()->getImagePath();
					$viewurl = $this->view->baseUrl('season/view/id/'.encode($row['id']));
					$html .= '
					<li style="height:55px;" class="display_box" align="left" url="'.$viewurl.'" theid="'.$row['id'].'">
						<img src="'.$media.'" style="width:60px; height:54px; float:left; margin-right:6px;" />
						<span class="name blocked">'.$final_name.'</span>
						'.$farmer_season_details.'
					</li>';
				}
			}
			
			$hasactivity = false;
			# search season inputs
			$query_activity_input = "SELECT s.* FROM seasoninput as s inner join farmer as f on (s.farmerid = f.id)
				WHERE
				(".$farmer_filter.") AND  
				(s.activityname like '%".$q."%' or 
				s.ref like '%".$q."%') 
				GROUP BY s.id
				order by s.datecreated desc, s.activityname asc LIMIT 5 ";
			$result_inputs = $conn->fetchAll($query_activity_input);
			// debugMessage($query_seasons);
			if(count($result_inputs) > 0){
				$hasdata = true;
				foreach ($result_inputs as $row){
					$hasactivity = true;
					$input = new SeasonInput();
					$input->populate($row['id']);
					$name= $input->getActivityName()." - ".$input->getRef()." [".$input->getSeason()->getRef()."]";
					$s_name='<b>'.$q.'</b>';
					$final_name = str_ireplace($q, $s_name, $name);
					$farmer_season_details = '';
					if(!isEmptyString($user->isFarmGroupAdmin())){
						$farmer_season_details = '<span class="blocked">'.$input->getFarmer()->getName().'</span><span class="blocked">'.$input->getFarm()->getName().'</span>';
					}
					$media = $input->getSeason()->getCrop()->getImagePath();
					$viewurl = $this->view->baseUrl('season/inputview/id/'.encode($row['id']));
					$html .= '
					<li style="height:55px;" class="display_box" align="left" url="'.$viewurl.'" theid="'.$row['id'].'">
						<img src="'.$media.'" style="width:60px; height:54px; float:left; margin-right:6px;" />
						<span class="name blocked">'.$final_name.'</span>
						'.$farmer_season_details.'
					</li>';
				}
			}
			
			# search season tillage
			$query_activity_tillage = "SELECT s.* FROM seasontillage as s inner join farm as fm on (s.farmid = fm.id) 
				inner join farmer f on (fm.farmerid = f.id)
				WHERE
				(".$farm_filter.") AND  
				(s.activityname like '%".$q."%' or 
				s.ref like '%".$q."%') 
				GROUP BY s.id
				order by s.datecreated desc, s.activityname asc LIMIT 5 ";
			$result_tillage = $conn->fetchAll($query_activity_tillage);
			// debugMessage($query_seasons);
			if(count($result_tillage) > 0){
				$hasdata = true;
				foreach ($result_tillage as $row){
					$hasactivity = true;
					$tillage = new SeasonTillage();
					$tillage->populate($row['id']);
					$name= $tillage->getActivityName()." - ".$tillage->getRef()." [".$tillage->getSeason()->getRef()."]";
					$s_name='<b>'.$q.'</b>';
					$final_name = str_ireplace($q, $s_name, $name);
					$farmer_season_details = '';
					if(!isEmptyString($user->isFarmGroupAdmin())){
						$farmer_season_details = '<span class="blocked">'.$tillage->getFarm()->getFarmer()->getName().'</span><span class="blocked">'.$tillage->getFarm()->getName().'</span>';
					}
					$media = $tillage->getSeason()->getCrop()->getImagePath();
					$viewurl = $this->view->baseUrl('season/tillageview/id/'.encode($row['id']));
					$html .= '
					<li style="height:55px;" class="display_box" align="left" url="'.$viewurl.'" theid="'.$row['id'].'">
						<img src="'.$media.'" style="width:60px; height:54px; float:left; margin-right:6px;" />
						<span class="name blocked">'.$final_name.'</span>
						'.$farmer_season_details.'
					</li>';
				}
			}

			# search season planting
			$query_activity_planting = "SELECT s.* FROM seasonplanting as s inner join farm as fm on (s.farmid = fm.id) 
				inner join farmer f on (fm.farmerid = f.id)
				WHERE
				(".$farm_filter.") AND  
				(s.activityname like '%".$q."%' or 
				s.ref like '%".$q."%') 
				GROUP BY s.id
				order by s.datecreated desc, s.activityname asc LIMIT 5 ";
			$result_planting = $conn->fetchAll($query_activity_planting);
			// debugMessage($query_seasons);
			if(count($result_planting) > 0){
				$hasdata = true;
				foreach ($result_planting as $row){
					$hasactivity = true;
					$planting = new SeasonPlanting();
					$planting->populate($row['id']);
					$name= $planting->getActivityName()." - ".$planting->getRef()." [".$planting->getSeason()->getRef()."]";
					$s_name='<b>'.$q.'</b>';
					$final_name = str_ireplace($q, $s_name, $name);
					$farmer_season_details = '';
					if(!isEmptyString($user->isFarmGroupAdmin())){
						$farmer_season_details = '<span class="blocked">'.$planting->getFarm()->getFarmer()->getName().'</span><span class="blocked">'.$planting->getFarm()->getName().'</span>';
					}
					$media = $planting->getSeason()->getCrop()->getImagePath();
					$viewurl = $this->view->baseUrl('season/plantview/id/'.encode($row['id']));
					$html .= '
					<li style="height:55px;" class="display_box" align="left" url="'.$viewurl.'" theid="'.$row['id'].'">
						<img src="'.$media.'" style="width:60px; height:54px; float:left; margin-right:6px;" />
						<span class="name blocked">'.$final_name.'</span>
						'.$farmer_season_details.'
					</li>';
				}
			}
			
			# search season treatment
			$query_activity_treatment = "SELECT s.* FROM seasontracking as s inner join farm as fm on (s.farmid = fm.id) 
				inner join farmer f on (fm.farmerid = f.id)
				WHERE
				(".$farm_filter.") AND  
				(s.activityname like '%".$q."%' or 
				s.ref like '%".$q."%') 
				GROUP BY s.id
				order by s.datecreated desc, s.activityname asc LIMIT 5 ";
			$result_treatment = $conn->fetchAll($query_activity_treatment);
			// debugMessage($query_seasons);
			if(count($result_treatment) > 0){
				$hasdata = true;
				foreach ($result_treatment as $row){
					$hasactivity = true;
					$treat = new SeasonTracking();
					$treat->populate($row['id']);
					$name= $treat->getActivityName()." - ".$treat->getRef()." [".$treat->getSeason()->getRef()."]";
					$s_name='<b>'.$q.'</b>';
					$final_name = str_ireplace($q, $s_name, $name);
					$farmer_season_details = '';
					if(!isEmptyString($user->isFarmGroupAdmin())){
						$farmer_season_details = '<span class="blocked">'.$treat->getFarm()->getFarmer()->getName().'</span><span class="blocked">'.$treat->getFarm()->getName().'</span>';
					}
					$media = $treat->getSeason()->getCrop()->getImagePath();
					$viewurl = $this->view->baseUrl('season/treatview/id/'.encode($row['id']));
					$html .= '
					<li style="height:55px;" class="display_box" align="left" url="'.$viewurl.'" theid="'.$row['id'].'">
						<img src="'.$media.'" style="width:60px; height:54px; float:left; margin-right:6px;" />
						<span class="name blocked">'.$final_name.'</span>
						'.$farmer_season_details.'
					</li>';
				}
			}
			
			# search season harvest
			$query_activity_harvest = "SELECT s.* FROM seasonharvest as s inner join farm as fm on (s.farmid = fm.id) 
				inner join farmer f on (fm.farmerid = f.id)
				WHERE
				(".$farm_filter.") AND  
				(s.activityname like '%".$q."%' or 
				s.ref like '%".$q."%') 
				GROUP BY s.id
				order by s.datecreated desc, s.activityname asc LIMIT 5 ";
			$result_harvest = $conn->fetchAll($query_activity_harvest);
			// debugMessage($query_seasons);
			if(count($result_harvest) > 0){
				$hasdata = true;
				foreach ($result_harvest as $row){
					$hasactivity = true;
					$harvest = new SeasonHarvest();
					$harvest->populate($row['id']);
					$name= $harvest->getActivityName()." - ".$harvest->getRef()." [".$harvest->getSeason()->getRef()."]";
					$s_name='<b>'.$q.'</b>';
					$final_name = str_ireplace($q, $s_name, $name);
					$farmer_season_details = '';
					if(!isEmptyString($user->isFarmGroupAdmin())){
						$farmer_season_details = '<span class="blocked">'.$harvest->getFarm()->getFarmer()->getName().'</span><span class="blocked">'.$harvest->getFarm()->getName().'</span>';
					}
					$media = $harvest->getSeason()->getCrop()->getImagePath();
					$viewurl = $this->view->baseUrl('season/harvestview/id/'.encode($row['id']));
					$html .= '
					<li style="height:55px;" class="display_box" align="left" url="'.$viewurl.'" theid="'.$row['id'].'">
						<img src="'.$media.'" style="width:60px; height:54px; float:left; margin-right:6px;" />
						<span class="name blocked">'.$final_name.'</span>
						'.$farmer_season_details.'
					</li>';
				}
			}
			
			# search season post harvest
			
			# search season sales
			$query_activity_sale = "SELECT s.* FROM sales as s inner join farm as fm on (s.farmid = fm.id) 
				inner join farmer f on (fm.farmerid = f.id)
				WHERE
				(".$farm_filter.") AND  
				(s.activityname like '%".$q."%' or 
				s.ref like '%".$q."%') 
				GROUP BY s.id
				order by s.datecreated desc, s.activityname asc LIMIT 5 ";
			$result_sale = $conn->fetchAll($query_activity_sale);
			// debugMessage($query_seasons);
			if(count($result_sale) > 0){
				$hasdata = true;
				foreach ($result_sale as $row){
					$hasactivity = true;
					$sale = new Sales();
					$sale->populate($row['id']);
					$name= $sale->getActivityName()." - ".$sale->getRef()." [".$sale->getSeason()->getRef()."]";
					$s_name='<b>'.$q.'</b>';
					$final_name = str_ireplace($q, $s_name, $name);
					$farmer_season_details = '';
					if(!isEmptyString($user->isFarmGroupAdmin())){
						$farmer_season_details = '<span class="blocked">'.$sale->getFarm()->getFarmer()->getName().'</span><span class="blocked">'.$sale->getFarm()->getName().'</span>';
					}
					$media = $sale->getSeason()->getCrop()->getImagePath();
					$viewurl = $this->view->baseUrl('season/saleview/id/'.encode($row['id']));
					$html .= '
					<li style="height:55px;" class="display_box" align="left" url="'.$viewurl.'" theid="'.$row['id'].'">
						<img src="'.$media.'" style="width:60px; height:54px; float:left; margin-right:6px;" />
						<span class="name blocked">'.$final_name.'</span>
						'.$farmer_season_details.'
					</li>';
				}
			}
			
			# check that atleast one activity exists
			if($hasactivity){
				$html = '<li class="separator"><span>Activities</span></li>'.$html;
			}
		}
		
		# search for farmers by admins 
		if($type != 2){
			$query_farmers = "SELECT u.*, u.id as uid, l.name as district, fg.orgname FROM useraccount as u 
				inner join farmer as f on (u.farmerid = f.id)
				".$groupjoin." join farmgroup as fg on (f.farmgroupid = fg.id ".$typequery.")
				left join location as l on (u.locationid = l.id)
				left join userphone as p on (p.userid = u.id)
				WHERE 
				u.firstname like '%".$q."%' or 
				u.lastname like '%".$q."%' or 
				u.othernames like '%".$q."%' or 
				concat(u.firstname,' ',u.lastname) like '%".$q."%' or 
				concat(u.lastname,' ',u.firstname) like '%".$q."%' or 
				concat(u.lastname,' ',u.firstname,' ',u.othernames) like '%".$q."%' or 
				u.username like '%".$q."%' or 
				u.email like '%".$q."%' or
				p.phone like '%".$q."%' 
				GROUP BY u.id
				order by u.firstname asc, u.lastname asc LIMIT 5 ";
				// debugMessage($query);
		
			$result_farmers = $conn->fetchAll($query_farmers);
			// debugMessage($result);
			if(count($result_farmers) > 0){
				$hasdata = true;
				$html .= '<li class="separator"><span>Farmers</span></li>';
				foreach ($result_farmers as $row){
					$user = new UserAccount();
					$user->populate($row['uid']);
					$username=$row['firstname']." ".$row['lastname']." ".$row['othernames'];
					$email=$row['email'];
					$phone= $user->getPhone();
					$media= $user->getThumbnailPicturePath();
					$district=$user->getAddress()->getDistrict()->getName();
					$b_name='<b>'.$q.'</b>';
					$b_email='<b>'.$q.'</b>';
					$b_phone='<b>'.$q.'</b>';
					$final_username = str_ireplace($q, $b_name, $username);
					$final_email = str_ireplace($q, $b_email, $email);
					$final_phone = str_ireplace($q, $b_phone, $phone);
					$contacts = ''; $group = '';
					if(!isEmptyString($phone)){
						$contacts .= 'Phone: '.$final_phone.' ';
					}
					if(!isEmptyString($email)){
						$contacts .= ', Email:'.$final_email.' ';
					}
					if(!isEmptyString($contacts)){
						$contacts = '<span class="blocked">'.$contacts.'</span>';
					}
					if(!isEmptyString($user->getFarmer()->hasFarmGroup())){
						$group = '<span class="blocked">Group: '.$user->getFarmer()->getFarmGroup()->getName().'</span>';
					}
					$viewurl = $this->view->baseUrl('farmer/view/id/'.encode($user->getFarmerID()));
					$html .= '
					<li class="display_box" align="left" url="'.$viewurl.'" theid="'.$user->getID().'">
						<img src="'.$media.'" style="width:60px; height:60px; float:left; margin-right:6px;" />
						<span class="name blocked">'.$final_username.'</span>
						'.$contacts.'
						<span class="blocked">District: '.$district.'</span>
						'.$group.'
					</li>';
				}
			}
		}
		
		# search groups
		if($type == 1){
			$query_groups = "SELECT g.* FROM farmgroup as g 
				WHERE 
				g.orgname like '%".$q."%' 
				GROUP BY g.id
				order by g.orgname asc LIMIT 5 ";
			
			$result_groups = $conn->fetchAll($query_groups);
			// debugMessage($result);
			if(count($result_groups) > 0){
				$hasdata = true;
				$html .= '<li class="separator"><span>Group Profiles</span></li>';
				foreach ($result_groups as $row){
					$group = new FarmGroup();
					$group->populate($row['id']);
					$name = $row['orgname'];
					$email = $row['email'];
					$phone = getShortPhone($row['phone']);
					$media = $this->view->baseUrl('images/farmgroup.png');
					$district = $group->getAddress()->getDistrict()->getName();
					
					$b_name='<b>'.$q.'</b>';
					$b_email='<b>'.$q.'</b>';
					$b_phone='<b>'.$q.'</b>';
					$final_name = str_ireplace($q, $b_name, $name);
					$final_email = str_ireplace($q, $b_email, $email);
					$final_phone = str_ireplace($q, $b_phone, $phone);
					$contacts = '';
					if(!isEmptyString($phone)){
						$contacts .= $final_phone;
					}
					if(!isEmptyString($email)){
						$contacts .= ', '.$final_email;
					}
					if(!isEmptyString($contacts)){
						$contacts = '<span class="blocked">'.$contacts.'</span>';
					}
					if(!isEmptyString($district)){
						$district = '<span class="blocked">'.$district.' District</span>';
					}
					$viewurl = $this->view->baseUrl('farmgroup/view/id/'.encode($row['id']));
					$html .= '
					<li style="height:55px;" class="display_box" align="left" url="'.$viewurl.'" theid="'.$row['id'].'">
						<img src="'.$media.'" style="width:60px; height:54px; float:left; margin-right:6px;" />
						<span class="name blocked">'.$final_name.'</span>
						'.$contacts.'
						'.$district.'
					</li>';
				}
			}
		}
		# crops search. Admin only
		if($type == 1){
			$query_crops = "SELECT c.* FROM commodity as c 
				WHERE 
				c.name like '%".$q."%' AND allowfarmer = 1
				GROUP BY c.id
				order by c.name asc LIMIT 5 ";
			
			$result_crops = $conn->fetchAll($query_crops);
			// debugMessage($result);
			if(count($result_crops) > 0){
				$hasdata = true;
				$html .= '<li class="separator"><span>Crops</span></li>';
				foreach ($result_crops as $row){
					$commodity = new Commodity();
					$commodity->populate($row['id']);
					$name = $row['name'];
					$b_name='<b>'.$q.'</b>';
					$final_name = str_ireplace($q, $b_name, $name);
					$media = $commodity->getImagePath();
					$viewurl = $this->view->baseUrl('commodity/view/id/'.encode($row['id']));
					$html .= '
					<li style="height:55px;" class="display_box" align="left" url="'.$viewurl.'" theid="'.$row['id'].'">
						<img src="'.$media.'" style="width:60px; height:54px; float:left; margin-right:6px;" />
						<span class="name blocked">'.$final_name.'</span>
					</li>';
				}
			}
		}
		# business directory search. All users. If farmer/farmgroup search only public contacts
		if($type == 1 || $type == 2 || $type == 3){	
			$query_contacts = "SELECT c.* FROM contact as c 
				WHERE
				(c.visibility = 3 OR (c.visibility = 1 AND c.createdby = $userid)) AND  
				(c.firstname like '%".$q."%' or 
				c.lastname like '%".$q."%' or 
				c.othernames like '%".$q."%' or 
				concat(c.firstname,' ',c.lastname) like '%".$q."%' or 
				concat(c.lastname,' ',c.firstname) like '%".$q."%' or 
				concat(c.lastname,' ',c.firstname,' ',c.othernames) like '%".$q."%' or
				c.orgname like '%".$q."%') 
				GROUP BY c.id
				order by c.firstname asc, c.lastname asc, c.orgname asc LIMIT 5 ";
			
			$result_contacts = $conn->fetchAll($query_contacts);
			// debugMessage($result);
			if(count($result_contacts) > 0){
				$hasdata = true;
				$html .= '<li class="separator"><span>Business Directory</span></li>';
				foreach ($result_contacts as $row){
					$contact = new Contact();
					$contact->populate($row['id']);
					$name= $contact->isPerson() ? $row['firstname']." ".$row['lastname']." ".$row['othernames'] : $row['orgname'];
					$b_name='<b>'.$q.'</b>';
					$final_name = str_ireplace($q, $b_name, $name);
					$media = $contact->isPerson() ? $this->view->baseUrl('images/contactperson.png') : $this->view->baseUrl('images/contactorg.png');
					$viewurl = $this->view->baseUrl('contact/view/id/'.encode($row['id']));
					$html .= '
					<li style="height:55px;" class="display_box" align="left" url="'.$viewurl.'" theid="'.$row['id'].'">
						<img src="'.$media.'" style="width:60px; height:54px; float:left; margin-right:6px;" />
						<span class="name blocked">'.$final_name.'</span>
					</li>';
				}
			}
		}
		
		# check no data is available for all areas and return no results message
		if(!$hasdata){
			$html .= '
				<li class="display_box" align="center" style="height:30px;">
					<span style="width:100%; display:block; text-align:center;">No results for <b>'.$q.'</b></span>
				</li>';
		}
		echo $html;
    }
}