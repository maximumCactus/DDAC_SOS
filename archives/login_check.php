<?php
//CleanConnect
session_start();
include('conn.php');

require_once './aws/aws-autoloader.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Aws\XRay\XRayClient;
use Aws\Sdk;
use Aws\Credentials\Credentials;

//print_r($_SERVER);

$credentials = new Credentials(
    'ASIA2ROJP2WTEHIFQWM4',      // Access key ID
    '4upuk/GAwIli6Tvr3WXGzrGOdqSRKvrS7wf1ddLC',  // Secret access key
    'FwoGZXIvYXdzEH8aDMmMaqiQVuCb4HZvpSK+AZe5fed+WGRc6a9ijUjc1Rh9tlICqzRG8RyM4CDlZVHSNVcZBsl7u62zpV6l9sElrpBVFoJVhvlSKstChEwm4PeGkStCfE0A++svQBM6TbW/52YUjIVLrrTO/Vj3seqmM1y2zJpJ2NjHYY3yUawZ9SdAvyY1ANoYm3snbmfbQi22h/mGoubYjOffMu279n44QdMx9hXJgHx+N7Zcgex/3k/cSLj26HV91XPXiCmDGljynOzR9lYezfs6vVlfS+wojd2upgYyLVaiDl5PuxE6naGb4uGNnemg/Qasg4rzLGqTPXTp4DYgY05N5gLBXLRosKSuNg==', // Session token (if using temporary credentials)
);

$xrayClient = new XRayClient([
    'version' => 'latest',
    'region' => 'us-east-1', // Replace with your desired AWS region
	'credentials' => $credentials,
]);

// Generate a 64-bit unique identifier in hexadecimal format
$id = bin2hex(random_bytes(8));

// Generate the timestamp part of the trace_id (8 hexadecimal digits)
$timestampHex = dechex(time());

// Generate a 96-bit identifier for the trace (24 hexadecimal digits)
$traceIdIdentifier = bin2hex(random_bytes(12));

// Combine the version, timestamp, and identifier to form the trace_id
$traceId = '1-' . $timestampHex . '-' . $traceIdIdentifier;
$startTime = time();

$segment = [
    'name' => 'cleanconnect1.chvg7z4daj7l.us-east-1.rds.amazonaws.com',
	'namespace' => 'remote',
    'id'   => $id,
	'type' => 'subsegment',
    'trace_id' => $_SESSION['trace_id'],
	'parent_id' => $_SESSION['parent_id'],
    'start_time' => $startTime, // Replace with the actual start time of the segment in Unix timestamp format
    'in_progress' => true
];

// Encode the segment as a JSON string
$segmentJson = json_encode($segment);

// Create an array of trace segments (you can add multiple segments if needed)
$traceSegments = [$segmentJson];

// Send the trace segments to X-Ray using the PutTraceSegments method
$result = $xrayClient->putTraceSegments(['TraceSegmentDocuments' => $traceSegments]);


if(isset($_POST['login']))
{
	$check_exist = mysqli_query($conn, "select * from ".$_POST['user_role']."_info"." where username_e = '".MD5($_POST['username'])."' ");
	if(mysqli_num_rows($check_exist) == 0){
		echo "<script> alert('User not exist!');</script>";
		echo "<script>window.location='login.php';</script>";
	}
	
	$check_exist1 = mysqli_query($conn, "select * from ".$_POST['user_role']."_info"." where username_e = '".MD5($_POST['username'])."' AND member_status = '-1'");
	if(mysqli_num_rows($check_exist1) == 1){
		echo "<script> alert('Account banned!');</script>";
		echo "<script>window.location='login.php';</script>";
	}
	else{
		$query = mysqli_query($conn, "select * from ".$_POST['user_role']."_info"." where username_e = '".MD5($_POST['username'])."' AND password = '".MD5($_POST['password'])."' AND member_status = '0'");
		if(mysqli_num_rows($query) != 0){
		while($data = mysqli_fetch_assoc($query)) {
				$cookie = $_POST['user_role'] == "service_provider"? "service_provider_name": "username";
               setcookie($cookie, MD5($data[$cookie]), time() + (86400 * 30), "/");
	   }
	   	   
		// Handle XRay before redirect
		$segment_completed = [
			'name' => 'cleanconnect1.chvg7z4daj7l.us-east-1.rds.amazonaws.com',
			'namespace' => 'remote',
			'id'   => $id,
			'trace_id' => $_SESSION['trace_id'],
			'parent_id' => $_SESSION['parent_id'],
			'start_time' => $startTime, // Replace with the actual start time of the segment in Unix timestamp format
			'end_time' => time(), // Replace with the actual start time of the segment in Unix timestamp format
			'in_progress' => false
		];

		// Encode the segment as a JSON string
		$segmentJson_completed = json_encode($segment_completed);

		// Create an array of trace segments (you can add multiple segments if needed)
		$traceSegments_completed = [$segmentJson_completed];

		//print_r($_SERVER);

		// Send the trace segments to X-Ray using the PutTraceSegments method
		$result = $xrayClient->putTraceSegments(['TraceSegmentDocuments' => $traceSegments_completed]);		
		   
		$redirect_path = $_POST['user_role'] == "service_provider"? "location:weserve_ServiceProvider/pages/home.php": "location:home.php";
		header($redirect_path);
		}else{
			echo "<script> alert('Password Incorrect. Please try again.');</script>";			
			echo "<script>window.location='login.php';</script>";
		}
	}
}
?>