<?php
    include_once("../goFunctions.php");
    
	$campaign_id = mysqli_real_escape_string($link, $_REQUEST['campaign_id']);
	$phone_numbers = str_replace(" ", "\n", rawurldecode(mysqli_real_escape_string($link, $_REQUEST['phone_numbers'])));
	$stage = mysqli_real_escape_string($link, $_REQUEST['stage']);
	$user_id = mysqli_real_escape_string($link, $_REQUEST['user_id']);
	$ip_address = mysqli_real_escape_string($link, $_REQUEST['hostname']);
		
	$log_user = mysqli_real_escape_string($link, $_REQUEST['log_user']);
	$log_group = mysqli_real_escape_string($link, $_REQUEST['log_group']);
	
	$usergroup = get_usergroup($user_id, $link);
	$allowed_campaigns = get_allowed_campaigns($usergroup, $link);
	$cnt = 0;
	
	if ($campaign_id == "INTERNAL"){
		$dnc_numbers = explode("\r\n",$phone_numbers);
		
		foreach ($dnc_numbers as $dnc){
			$query_dnc = mysqli_query($link, "SELECT phone_number AS cnt FROM vicidial_dnc WHERE phone_number='$dnc'");
			$idnc_exist = mysqli_num_rows($query_dnc);
			$query_campaign_dnc = mysqli_query($link, "SELECT phone_number AS cnt FROM vicidial_campaign_dnc WHERE phone_number='$dnc'");
			$cdnc_exist = mysqli_num_rows($query_campaign_dnc);
			
			if ($idnc_exist < 1 && $cdnc_exist < 1){
				if ($stage == "ADD" && $dnc != ''){
					if (count($allowed_campaigns) > 1 && !in_array($allowed_campaigns,"---ALL---")) {
						foreach ($allowed_campaigns as $camp) {
							$query = mysqli_query($link, "INSERT INTO vicidial_campaign_dnc VALUES('$dnc','$camp');");
						}
					} else {
						$query = mysqli_query($link, "INSERT INTO vicidial_dnc VALUES('$dnc');");
					}
					$cnt++;
				}
			} else {
				if ($stage == "DELETE" && $dnc != ''){
					if (count($allowed_campaigns) > 1 && !in_array($allowed_campaigns,"---ALL---")) {
						foreach ($allowed_campaigns as $camp) {
							$query = mysqli_query($link, "DELETE FROM vicidial_campaign_dnc WHERE phone_number='$dnc';");
						}
					} else {
						$query = mysqli_query($link, "DELETE FROM vicidial_dnc WHERE phone_number='$dnc';");
					}
					
					if ($query) {
						$cnt++;
					}
				}
			}
		}

		if ($cnt){
			if ($stage == "DELETE")
				$msg = "deleted";
			else
				$msg = "added";
			
			$details = ucfirst($msg) . " {$cnt} numbers " . ($msg == 'added' ? 'to' : 'from') . " Internal DNC list";
			$log_id = log_action($linkgo, $stage, $log_user, $ip_address, $details, $log_group);
		} else {
			if ($stage == "ADD")
				$msg = "already exist";
			else
				$msg = "does not exist";
		}
    } else {
		$dnc_numbers = explode("\r\n",$phone_numbers);

		foreach ($dnc_numbers as $dnc){
			$query = mysqli_query($link, "SELECT phone_number AS cnt FROM vicidial_campaign_dnc WHERE phone_number='$dnc' AND campaign_id='$campaign_id';");
			$cdnc_exist = mysqli_num_rows($query);
			$query2 = mysqli_query($link, "SELECT phone_number AS cnt FROM vicidial_dnc WHERE phone_number='$dnc';");
			$idnc_exist = mysqli_num_rows($query2);
		
			if ($idnc_exist < 1 && $cdnc_exist < 1){
				if ($stage == "ADD"){
					$query = mysqli_query($link, "INSERT INTO vicidial_campaign_dnc VALUES('$dnc','$campaign_id');");
					$cnt++;
				}
			} else {
				if ($stage == "DELETE"){
					if ($cdnc_exist > 0) {
						$query = mysqli_query($link, "DELETE FROM vicidial_campaign_dnc WHERE phone_number='$dnc' AND campaign_id='$campaign_id';");
						$cnt++;
					}
					
					if ($campaign_id === '' && $idnc_exist > 0) {
						$query = mysqli_query($link, "DELETE FROM vicidial_dnc WHERE phone_number='$dnc';");
						$cnt++;
					}
				}
			}
		}
				
		if ($cnt){
			if ($stage == "ADD")
				$msg = "added";
			else
				$msg = "deleted";
			
			$details = ucfirst($msg) . " {$cnt} numbers " . ($msg == 'added' ? 'to' : 'from') . " Campaign DNC list";
			$log_id = log_action($linkgo, $stage, $log_user, $ip_address, $details, $log_group);
		} else {
			if ($stage == "ADD")
				$msg = "already exist";
			else
				$msg = "does not exist";
		}
	}
    
	$apiresults = array(
		"result"   => "success",
		"msg"      => $msg
	);
	
	function get_usergroup($user_id, $link){
		$query = mysqli_query($link, "select user_group from vicidial_users where user_id='$user_id';");
		$resultsu = mysqli_fetch_array($query);
		$groupid = $resultsu['user_group'];
		return $groupid;
	}
	function get_allowed_campaigns($groupid, $link){
		$query2 = mysql_query($link, "select campaign_id from vicidial_campaigns where user_group = '$groupid'");
		$check = mysqli_num_rows($query2);
		if($check > 0){
			while($resultc = mysqli_fetch_array($query2)){
				$allowed_campaigns = $resultc['campaign_id'];
			}
		}else{
			$allowed_campaigns = array('---ALL---');
		}
		return $allowed_campaigns;
	}
?>