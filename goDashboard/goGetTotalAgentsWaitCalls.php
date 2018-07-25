<?php
 /**
 * @file 		goGetTotalAgentsWaitCalls.php
 * @brief 		API for Dashboard
 * @copyright 	Copyright (C) GOautodial Inc.
 * @author		Jericho James Milo  <james@goautodial.com>
 * @author     	Demian Lizandro A. Biscocho  <demian@goautodial.com>
 * @author     	Chris Lomuntad  <chris@goautodial.com>
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
 
	$log_user 										= $session_user;
	$log_group 										= go_get_groupid($session_user, $astDB); 
	//$log_ip 										= $astDB->escape($_REQUEST["log_ip"]);
	$campaigns 										= allowed_campaigns($log_group, $goDB, $astDB);
	
    // ERROR CHECKING 
	if (!isset($log_user) || is_null($log_user)){
		$apiresults 								= array(
			"result" 									=> "Error: Session User Not Defined."
		);
	} else {		
		$ready										= array(
			"READY", 
			"CLOSER"
		);
		
		$astDB->where("campaign_id", $campaigns, "IN");
		$astDB->where("status", $ready, "IN");
		$data										= $astDB->getValue("vicidial_live_agents", "count(*)");
		
		$apiresults 								= array(
			"result" 									=> "success", 
			"data" 										=> $data
		);
    }
?>
