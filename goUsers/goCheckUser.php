<?php
   /****************************************************
   #### Name: goCheckUser.php	                      ####
   #### Description: API to check for existing data ####
   #### Version: 4.0                                ####
   #### Copyright: GOAutoDial Ltd. (c) 2011-2016    ####
   #### Written by: Alexander Jim H. Abenoja        ####
   #### License: AGPLv2                             ####
   ****************************************************/
    
    include_once ("goAPI.php");
 
    // POST or GET Variables
      $user = explode(",",$_REQUEST['user']);
      $phone_login = $_REQUEST['phone_login'];

      // Phone Login Check optional when not null
      if($phone_login != NULL){
        $astDB->where("extension", $phone_login);
        $queryPhoneCheck = $astDB->getOne("phones", "extension");
        //  $queryPhoneCheck = "SELECT extension FROM phones WHERE extension = '$phone_login';";
        $countCheckResult2 = $astDB->count;      
          if($countCheckResult2 > 0) {
            $apiresults = array("result" => "success");
          }else{
            $apiresults = array("result" => "fail", "phone_login" => "There is no phone that matches your input.");
          }
      }
        
      // User Duplicate Check
      if($user != NULL){
        $astDB->where("user", $user);
        $queryUserCheck = $astDB->getOne("vicidial_users", "user");
        //"SELECT user FROM vicidial_users WHERE user = '$user';";
        $countCheckResult1 = $astDB->count;  
          if($countCheckResult1 > 0) {
            $validate1 = $validate1 + 1;
            $apiresults = array("result" => "user", "user" => "There are 1 or more users with that User ID.");
          }else{
            $apiresults = array("result" => "success");
          }
      }
        
      
?>
