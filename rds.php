<?php
//CleanConnect
session_start();
include('conn.php');

require_once(__DIR__ . '/vendor/autoload.php');

use Pkerrigan\Xray\Trace;
use Pkerrigan\Xray\SqlSegment;
use Pkerrigan\Xray\Submission\DaemonSegmentSubmitter;

Trace::getInstance()
    ->setTraceHeader($_SERVER['HTTP_X_AMZN_TRACE_ID'] ?? null)	
	->setParentId($_SESSION['parent_id'])	
	->setTraceId($_SESSION['trace_id'])
	->setIndependent(true)	
    ->setName('cleanconnect1.chvg7z4daj7l.us-east-1.rds.amazonaws.com')
    ->setUrl($_SERVER['REQUEST_URI'])
    ->setMethod($_SERVER['REQUEST_METHOD'])	
    ->begin(100);

Trace::getInstance()
    ->getCurrentSegment()
    ->addSubsegment(
        (new SqlSegment())
            ->setName('cleanconnect1')
			->setUrl('cleanconnect1.chvg7z4daj7l.us-east-1.rds.amazonaws.com')
            ->setDatabaseType('MySQL Community')            
            ->begin(100)    
    );
	
if(isset($_POST['login']))
{
	$carry_forward_query = "";
	
	$check_exist = mysqli_query($conn, "select * from ".$_POST['user_role']."_info"." where username_e = '".MD5($_POST['username'])."' ");
	$carry_forward_query .= "SELECT * FROM " .$_POST['user_role']. "_info WHERE username_e =?; ";
	
	if(mysqli_num_rows($check_exist) == 0){
		collect_db_xray_traces($carry_forward_query);		
		echo "<script> alert('User not exist!');</script>";
		echo "<script>window.location='login.php';</script>";
	}
	
	$check_exist1 = mysqli_query($conn, "select * from ".$_POST['user_role']."_info"." where username_e = '".MD5($_POST['username'])."' AND member_status = '-1'");
	
	$carry_forward_query .= "SELECT * FROM " .$_POST['user_role']. "_info WHERE username_e =? AND member_status = '-1'; ";
	
	if(mysqli_num_rows($check_exist1) == 1){		
		collect_db_xray_traces($carry_forward_query);	
		echo "<script> alert('Account banned!');</script>";
		echo "<script>window.location='login.php';</script>";
	}
	else{
		$query = mysqli_query($conn, "select * from ".$_POST['user_role']."_info"." where username_e = '".MD5($_POST['username'])."' AND password = '".MD5($_POST['password'])."' AND member_status = '0'");
		
		$carry_forward_query .= "SELECT * FROM " .$_POST['user_role']. "_info WHERE username_e =? AND password =? AND member_status = '0'; ";
		collect_db_xray_traces($carry_forward_query);
		
		if(mysqli_num_rows($query) != 0){
			while($data = mysqli_fetch_assoc($query)) {
				$cookie = $_POST['user_role'] == "service_provider"? "service_provider_name": "username";
				setcookie($cookie, MD5($data[$cookie]), time() + (86400 * 30), "/");
			}
			   
			$redirect_path = $_POST['user_role'] == "service_provider"? "location:weserve_ServiceProvider/pages/home.php": "location:home.php";
			header($redirect_path);
		}else{
			echo "<script> alert('Password Incorrect. Please try again.');</script>";			
			echo "<script>window.location='login.php';</script>";
		}
	}
	
	Trace::getInstance()
		->getCurrentSegment()
		->end();

	Trace::getInstance()
		->end()
		->setResponseCode(http_response_code())
		->submit(new DaemonSegmentSubmitter());	
}

function collect_db_xray_traces($query) {
	Trace::getInstance()
		->getCurrentSegment()
		->setQuery($query)
		->end();

	Trace::getInstance()
		->end()
		->setResponseCode(http_response_code())
		->submit(new DaemonSegmentSubmitter());
}
?>