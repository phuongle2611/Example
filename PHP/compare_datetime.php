<?php

class Compare_DateTime_Example {
/**
	 * return single location data per system
	 * @param  [type] $system [description]
	 * @return [type]         [description]
	 */
	private function get_single_location($system){
		$location = new stdClass();
		if ($system){
			$Fingerprint = $system->Fingerprint;
			$Fingerprint_Object = DataObject::get_one('EXAMPLE_CLASS',"TEST = '$Fingerprint'");
			$location->lat = (float)$Fingerprint_Object->Lat;
			$location->lng = (float)$Fingerprint_Object->Lng;
			$location->title = $system->Title;
			$location->draggable = false;
			$title = $system->Title;
			$location->label = $title[0];
			$location->address = $Fingerprint_Object->Address;
			if ($system->Expiry){
				$location->expiry = (string)$system->Expiry;
				$expiry_datetime = new DateTime($location->expiry);
				$current_datetime = new DateTime();
				$interval = $expiry_datetime->diff($current_datetime);
				if ($interval->days >= 60){
					$location->color = 'green';
				}else if($interval->days < 60 && $interval->days > 1){
					$location->color = 'blue';
				}else if ($interval->days < 1){
					$location->color = 'red';
				}
			}else{
				$location->expiry = '';
				$location->color = 'red';
			}
			if ($system->URI){
				$location->www = $system->URI;
			}else{
				$location->www = '';
			}
		}
		return $location;
	}

	/**
	 * return array of all locations belong to
	 * @param  [type] $ABCTEST [description]
	 * @return [type]           [description]
	 */
	private function get_multi_locations_per_ABCTEST($ABCTEST){
		$Systems = DataObject::get('EXAMPLE_CLASS',"TESTID = '$ABCTEST'");
		$locations = array();
		foreach ($Systems as $key => $system) {
			array_push($locations, $this->get_single_location($system));
		}
		return $locations;
	}

	/**
	 * this function is to return location data of all the system in csv string for google API use
	 * @param  [type] $http [description]
	 * @return [type]       [description]
	 */
	public function getmapdata($http){
		$rsitucneictneionoicn = (string) Controller::curr()->getRequest()->getVar('ervstf');
		$return_obj = new stdClass();
		$return_obj->center = new stdClass();
		$return_obj->locations = array();
		if ($rsitucneictneionoicn==''){
			$return_obj->defaultZoom = 5;
			#get overview of all systems
			$ABCTEST = (string) Controller::curr()->getRequest()->getVar('errw44tv57by46bw464w67');
			$return_obj->locations = $this->get_multi_locations_per_ABCTEST($ABCTEST);
			$return_obj->center->lat = (float)$return_obj->locations[0]->lat;
			$return_obj->center->lng = (float)$return_obj->locations[0]->lng;
		}else{
			$return_obj->defaultZoom = 11;
			#get overview of series of system in comma
			$search_comma = strpos($rsitucneictneionoicn, ',');
			if ($search_comma==false){
				#single system
				$system=DataObject::get_by_id('EXAMPLE_CLASS',$rsitucneictneionoicn);
				$location = $this->get_single_location($system);
				$return_obj->locations = array($location);
				$return_obj->center->lat = (float)$location->lat;
				$return_obj->center->lng = (float)$location->lng;
			}else{
				#multi system
				$myArray = explode(',',$rsitucneictneionoicn);
				foreach ($myArray as $key => $value) {
					$system=DataObject::get_by_id('EXAMPLE_CLASS',$value);
					$location = $this->get_single_location($system);
					array_push($return_obj->locations, $location);
					if ($key==0){
						$return_obj->center->lat = (float)$location->lat;
						$return_obj->center->lng = (float)$location->lng;
					}
				}
			}
		}
		return json_encode($return_obj);
	}