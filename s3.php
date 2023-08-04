 <?php
require '../../aws/aws-autoloader.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

session_start();
include('../../conn.php');

require_once('../../vendor/autoload.php');

use Pkerrigan\Xray\Trace;
use Pkerrigan\Xray\RemoteSegment;
use Pkerrigan\Xray\Submission\DaemonSegmentSubmitter;

if(isset($_POST['submit']))
{
	$bucketName = 'cleanconnect-image';
	$region = 'us-east-1'; // Replace with your desired AWS region
	try {
		// Start X-Ray Tracing for S3
		Trace::getInstance()
			->setTraceHeader($_SERVER['HTTP_X_AMZN_TRACE_ID'] ?? null)	
			->setParentId($_SESSION['parent_id'])	
			->setTraceId($_SESSION['trace_id'])
			->setIndependent(true)	
			->setName('cleanconnect-image')
			->setUrl($_SERVER['REQUEST_URI'])
			->setMethod($_SERVER['REQUEST_METHOD'])	
			->begin(100);
			
		Trace::getInstance()
			->getCurrentSegment()
			->addSubsegment(
				(new RemoteSegment())
					->setName('s3://cleanconnect-image/images/')          
					->begin(100)    
			);
		
		$s3 = new Aws\S3\S3Client([
			'version' => 'latest',
			'region' => $region
		]);	
	} catch (Aws\Exception\AwsException $e) {
		echo 'Error creating S3 client: ' . $e->getMessage();
		
		// End X-Ray Tracing: Error to Upload Image
		Trace::getInstance()
			->getCurrentSegment()
			->setError(true)
			->addAnnotation('error', 'Error creating S3 client: ' . $e->getMessage())
			->end();
			
		Trace::getInstance()
			->end()
			->setResponseCode(http_response_code())
			->submit(new DaemonSegmentSubmitter());
	}
	

	
	$category = $_POST['category'];
	// $image = $_POST['image'];
	$servicedescription = $_POST['servicedescription'];
	$servicename = $_POST['servicename'];

    $selectedState = $_POST['states'];
	$selectedStateString = "";
	$counter = 0;

    foreach($selectedState as $state){
		
		if ($counter == 0){
			$selectedStateString.=$state;
			$counter++;
		}else{
			$selectedStateString = $selectedStateString.", ".$state;
		}

    }

	$img_path = "http://placehold.it/500";
	
	if(isset($_FILES["image"])) {
		$file = $_FILES["image"];
		$fileName = $file["name"];
		$fileTmp = $file["tmp_name"];
		$fileError = $file["error"];

		if ($fileError === 0){
			try {
			$result = $s3->putObject([
				'Bucket' => $bucketName,
				'Key' => 'images/' . $fileName,
				'SourceFile' => $fileTmp,
				//'ACL' => 'public-read' // Optional: Set the object's ACL permissions
			]);

			// The image has been uploaded successfully
			echo 'Image uploaded successfully. Public URL: ' . $result['ObjectURL'];
			$img_path = 'https://cleanconnect-image.s3.amazonaws.com/images/'.$fileName;
			
			// End X-Ray Tracing: Successfully Uploaded Image
			Trace::getInstance()
				->getCurrentSegment()
				->end();
				
			Trace::getInstance()
				->end()
				->setResponseCode(http_response_code())
				->submit(new DaemonSegmentSubmitter());

				
			} catch (Aws\Exception\AwsException $e) {
				
				// An error occurred during the upload
				echo 'Error uploading image: ' . $e->getMessage();
				
				// End X-Ray Tracing: Error to Upload Image
				Trace::getInstance()
					->getCurrentSegment()
					->setError(true)
					->addAnnotation('error', 'Error uploading image: ' . $e->getMessage())
					->end();
					
				Trace::getInstance()
					->end()
					->setResponseCode(http_response_code())
					->submit(new DaemonSegmentSubmitter());
			}
		} else {			
			echo 'Error uploading image. Error code: ' . $fileError;
			
			// End X-Ray Tracing: Error to Upload Image
				Trace::getInstance()
					->getCurrentSegment()
					->setError(true)
					->addAnnotation('error', 'Error uploading image. Error code: ' . $fileError)
					->end();
					
				Trace::getInstance()
					->end()
					->setResponseCode(http_response_code())
					->submit(new DaemonSegmentSubmitter());
		}
	}




	$price = $_POST['price'];
	$servicesstatus = $_POST['servicesstatus'];


	$service_provider_name = $_COOKIE['service_provider_name'];
	$service_provider_name1 = mysqli_query($conn, "select service_provider_name from service_provider_info where MD5(CONCAT(service_provider_name)) ='".$_COOKIE['service_provider_name']."'");
	// echo "select service_provider_name from service_provider_info where MD5(CONCAT(service_provider_name)) ='".$_COOKIE['service_provider_name']."'";
	$row_service_provider_name1 = mysqli_fetch_assoc($service_provider_name1);
	$display = $row_service_provider_name1['service_provider_name'];

	//Booking Status : 0- pending, 1-complete, -1-cancel
	//check if the user exist in the database


	$queryTemplate = "INSERT INTO `service`(`category`, `service_name`, `service_desc`, `state`, `service_status`, `service_provider_name`, `price`, `image`) VALUES ('%s','%s','%s','%s','%s','%s','%s','%s')";

	$queryString = sprintf($queryTemplate,$category,$servicename,$servicedescription,$selectedStateString,$servicesstatus,$display,$price,$img_path);
	// echo $queryString;
	$query = mysqli_query($conn, $queryString);
	
	header('location:home.php');
}
?>

