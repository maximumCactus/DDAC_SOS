<?php
//CleanConnect

require './aws/aws-autoloader.php'; // Include the AWS SDK for PHP autoloader
use Aws\Sns\SnsClient;
use Aws\Exception\AwsException;

session_start();
include('conn.php');

require_once(__DIR__ . '/vendor/autoload.php');

use Pkerrigan\Xray\Trace;
use Pkerrigan\Xray\RemoteSegment;
use Pkerrigan\Xray\Submission\DaemonSegmentSubmitter;


if(isset($_POST['restore']))
{
	$check_exist = mysqli_query($conn, "select email from user_info where email = '".$_POST['email']."' AND member_status = '0'");
	if(mysqli_num_rows($check_exist) == 0){
		echo "<script>alert('User not exist! Please try again.');</script>";
		echo "<script>window.location='forgetpw.php';</script>";
	}
	else{
		$str = substr(md5(time()), 0, 10);
		mysqli_query($conn, "UPDATE user_info SET password = '".MD5($str)."' WHERE email = '".$_POST['email']."'");
		
		$sql = "SELECT password from user_info WHERE email ='".$_POST['email']."'";
        $result1 = mysqli_query($conn, $sql);
		
		$getusername = "SELECT username from user_info WHERE email ='".$_POST['email']."'";
		$result2 = mysqli_query($conn, $getusername);
		$result2_data = mysqli_fetch_assoc($result2);

		
		if (mysqli_num_rows($result1) != 0) {
			while($data = mysqli_fetch_assoc($result1)) {

					$to = $_POST['email'];
					
					// Set your AWS credentials and region
					$region = 'us-east-1'; // e.g., 'us-east-1'

					// Set the ARN of the SNS topic where you want to publish the message
					$topicArn = 'arn:aws:sns:us-east-1:724661425574:forget-password';

					// Set the subject and message body for the email
					$emailSubject = 'Your temporary password - CleanConnect Account';
					$emailMessage = "Username: ".$result2_data['username']." \nTemporary password: ".$str."\n\n Please update your password once you login.";

					$additionalAttributes = [
						'user_email' => [
							'DataType' => 'String',
							'StringValue' => $to,
						],
						// Add more attributes as needed
					];

					try {
						// Start X-Ray Tracing
						Trace::getInstance()
							->setTraceHeader($_SERVER['HTTP_X_AMZN_TRACE_ID'] ?? null)	
							->setParentId($_SESSION['parent_id'])	
							->setTraceId($_SESSION['trace_id'])
							->setIndependent(true)
							->setName('sns:forget-password')
							->setUrl($_SERVER['REQUEST_URI'])
							->setMethod($_SERVER['REQUEST_METHOD'])	
							->begin(100);											
						
						// Instantiate the SNS client
						$snsClient = new Aws\Sns\SnsClient([
							'version' => 'latest',
							'region' => $region,
						]);

						
						// Publish the message to the SNS topic with email attributes
						$result = $snsClient->publish([
							'TopicArn' => $topicArn,
							'Message' => $emailMessage,
							'Subject' => $emailSubject,
							'MessageAttributes' => $additionalAttributes,
						]);
						
						// End X-Ray Tracing before Redirect: Successful Published Message
						Trace::getInstance()
							->end()
							->setResponseCode(http_response_code())
							->submit(new DaemonSegmentSubmitter());

						// Output the MessageId if successful
						// echo "Message published with ID: " . $result['MessageId'] . PHP_EOL;

						$redirect_path = $userrole == "service_provider"? "location:weserve_ServiceProvider/pages/login.php": "location:login.php";
						header($redirect_path);
						
					} catch (Aws\Exception\AwsException $e) {
						// Handle exceptions here
						echo "Error: " . $e->getMessage() . PHP_EOL;
						
						// End X-Ray Tracing: Error to Publish Message
						Trace::getInstance()
							->setError(true)
							->addAnnotation('error', 'Error publishing message: ' . $e->getMessage())
							->end();
					}

			}
		}
		

	}
}
?>