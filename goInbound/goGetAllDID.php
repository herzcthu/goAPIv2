<?php
 /**
 * @file        goGetAllDID.php
 * @brief       API to get all DID Details
 * @copyright 	Copyright (c) 2018 GOautodial Inc.
 * @author		Demian Lizandro A. Biscocho
 * @author     	Alexander Jim H. Abenoja
 * @author      Jeremiah Sebastian V. Samatra
 *
 * @par <b>License</b>:
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

    include_once ("goAPI.php");
    
	$limit 												= (isset($_REQUEST['limit']) ? $astDB->escape($_REQUEST['limit']) : 1000);
    
	// Error Checking
	if (empty($goUser) || is_null($goUser)) {
		$apiresults 									= array(
			"result" 										=> "Error: goAPI User Not Defined."
		);
	} elseif (empty($goPass) || is_null($goPass)) {
		$apiresults 									= array(
			"result" 										=> "Error: goAPI Password Not Defined."
		);
	} elseif (empty($log_user) || is_null($log_user)) {
		$apiresults 									= array(
			"result" 										=> "Error: Session User Not Defined."
		);
	} else {
		// check if goUser and goPass are valid
		$fresults										= $astDB
			->where("user", $goUser)
			->where("pass_hash", $goPass)
			->getOne("vicidial_users", "user,user_level");
		
		$goapiaccess									= $astDB->getRowCount();
		$userlevel										= $fresults["user_level"];
		
		if ($goapiaccess > 0 && $userlevel > 7) {	
			// set tenant value to 1 if tenant - saves on calling the checkIfTenantf function
			// every time we need to filter out requests
			$tenant										= (checkIfTenant($log_group, $goDB)) ? 1 : 0;
			
			if ($tenant) {
				$astDB->where("user_group", $log_group);
				$astDB->orWhere("user_group", "---ALL---");
			} else {
				if (strtoupper($log_group) !== 'ADMIN') {
					//if ($userlevel > 8) {
						$astDB->where("user_group", $log_group);
						$astDB->orWhere("user_group", "---ALL---");
					//}
				}					
			}

			$cols 										= array("did_id", "did_pattern", "did_description", "did_active", "did_route");
			$selectQuery 								= $astDB->get("vicidial_inbound_dids", $limit, $cols);
			
			foreach ($selectQuery as $fresults) {
				$dataDidID[] 							= $fresults['did_id'];
				$dataDidPattern[] 						= $fresults['did_pattern'];
				$dataDidDescription[] 					= $fresults['did_description'];
				$dataActive[] 							= $fresults['did_active'];
				$dataDidRoute[] 						= $fresults['did_route'];
			}
			
			$apiresults 								= array(
				"result" 									=> "success",
				"did_id" 									=> $dataDidID, 
				"did_pattern" 								=> $dataDidPattern, 
				"did_description" 							=> $dataDidDescription, 
				"active" 									=> $dataActive, 
				"did_route" 								=> $dataDidRoute
			);
		} else {
			$err_msg 									= error_handle("10001");
			$apiresults 								= array(
				"code" 										=> "10001", 
				"result" 									=> $err_msg
			);		
		}
	}			
?>
