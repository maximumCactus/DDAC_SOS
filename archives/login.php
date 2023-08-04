<?php

session_start();

//connect to conn.php to link to mysql database
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
    'name' => 'LoginSegment',
    'id'   => $id,
    'trace_id' => $traceId,
    'start_time' => $startTime, // Replace with the actual start time of the segment in Unix timestamp format
    'in_progress' => true
];

// Encode the segment as a JSON string
$segmentJson = json_encode($segment);

// Create an array of trace segments (you can add multiple segments if needed)
$traceSegments = [$segmentJson];

// Send the trace segments to X-Ray using the PutTraceSegments method
$result = $xrayClient->putTraceSegments(['TraceSegmentDocuments' => $traceSegments]);

?>

<!doctype html>
<html class="no-js" lang="zxx">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
         <title>CleanConnect - Login</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="manifest" href="site.webmanifest">

		<!-- CSS here -->
            <link rel="stylesheet" href="assets/css/bootstrap.min.css">
            <link rel="stylesheet" href="assets/css/owl.carousel.min.css">
            <link rel="stylesheet" href="assets/css/flaticon.css">
            <link rel="stylesheet" href="assets/css/price_rangs.css">
            <link rel="stylesheet" href="assets/css/slicknav.css">
            <link rel="stylesheet" href="assets/css/animate.min.css">
            <link rel="stylesheet" href="assets/css/magnific-popup.css">
            <link rel="stylesheet" href="assets/css/fontawesome-all.min.css">
            <link rel="stylesheet" href="assets/css/themify-icons.css">
            <link rel="stylesheet" href="assets/css/slick.css">
            <link rel="stylesheet" href="assets/css/nice-select.css">
            <link rel="stylesheet" href="assets/css/style.css">
   </head>

   <body>
    <!-- Preloader Start -->
    <div id="preloader-active">
        <div class="preloader d-flex align-items-center justify-content-center">
            <div class="preloader-inner position-relative">
                <div class="preloader-circle"></div>
                <div class="preloader-img pere-text">
                    <img src="assets/img/logo/logo.png" alt="">
                </div>
            </div>
        </div>
    </div>
    <!-- Preloader Start -->
    <header>
        <!-- Header Start -->
       <div class="header-area header-transparrent">
           <div class="headder-top header-sticky">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-3 col-md-2">
                            <!-- Logo -->
                            <div class="logo">
                                <a href="home.php"><img src="assets/img/logo/logo.png" alt=""></a>
                            </div>  
                        </div>
                        <div class="col-lg-9 col-md-9">
                            <div class="menu-wrapper">
                                <!-- Main-menu -->
                                <div class="main-menu">
                                    <nav class="d-none d-lg-block">
                                        <ul id="navigation">
											<li style="font-size:55px;">Log In</li>
                                        </ul>
                                    </nav>
                                </div>          
                                
                            </div>
                        </div>
                        <!-- Mobile Menu -->
                        <div class="col-12">
                            <div class="mobile_menu d-block d-lg-none"></div>
                        </div>
                    </div>
                </div>
           </div>
       </div>
        <!-- Header End -->
    </header>
    <main>

        <!-- slider Area Start-->
        <div class="slider-area ">
            <!-- Mobile Menu -->
            <div class="slider-active">
                <div class="single-slider slider-height d-flex align-items-center" data-background="assets/img/hero/h2_hero.jpg">
                    <div class="container">
                        <div class="content">
							<div class="container">
								<div class="row">
									<div class="col-md-5">
									</div>
								
								<div class="col-md-6 contents">
									<div class="row justify-content-center">
										<div class="col-md-8">
											<div class="mb-4">
											<h1>Log In</h1>
											</div>
											
											<form class="form" method = "post" action = "login_check.php">
											<div class="form-group first">
											<label for="name">Log In As</label>
											<select name="user_role" class="form-select form-select-sm mb-3" aria-label=".form-select-sm example">
												<option selected value="user">User</option>
												<option value="service_provider">Service Provider</option>											  
											</select>
											</div>	
											<div class="form-group first">
											<label for="username">Username</label>
											<input type="text" class="form-control" id="username" name="username" placeholder="Please enter your username" required/>
											</div>
											
											<div class="form-group last mb-4">
											<label for="password">Password</label>
											<input type="password" class="form-control" id="password" name="password" placeholder="Please enter your password" required/>
											</div>
											
											<input type="submit" value="Log In" name="login" class="btn btn-block btn-primary">
											
											<br><br>
											<div>
											<ul>
											<li><a href="forgetpw.php" style="float: right; color: black;">Forgot Password?</a></li>
											<br>
											<li><a href="register.php" style="float: right; color: black;">New to CleanConnect? Sign Up</a></li>
											<br>
											<li><a href="weserve_Admin/pages/sign-in.php" style="float: right; color: black;">Admin Login</a></li>
											</ul>
											</div>
											</form>
										</div>
									</div>
								</div>
								</div>
							</div>
						</div>

  
    <script src="js/jquery-3.3.1.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/main.js"></script>
                    </div>
                </div>
            </div>
        </div>
        <!-- slider Area End-->
        

    </main>
    <footer>
        <!-- Footer Start-->
        <div class="footer-area footer-bg">
            <div class="container">
                
               <!--  -->
               <div class="row footer-wejed justify-content-between">
                    
                    <div class="col-xl-3 col-lg-3 col-md-4 col-sm-5">
                    <div class="footer-tittle-bottom">
                        <span>Expertise</span>
                        <p>service</p>
                    </div>
                    </div>
                    <div class="col-xl-3 col-lg-3 col-md-4 col-sm-5">
                        <div class="footer-tittle-bottom">
                            <span>Various</span>
                            <p>categories</p>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-3 col-md-4 col-sm-5">
                        <!-- Footer Bottom Tittle -->
                        <div class="footer-tittle-bottom">
                            <span>Competitive</span>
                            <p>price</p>
                        </div>
                    </div>
               </div>
            </div>
        </div>
        <!-- footer-bottom area -->
        <div class="footer-bottom-area footer-bg">
            <div class="container">
                <div class="footer-border">
                     <div class="row d-flex justify-content-between align-items-center">
                         <div class="col-xl-10 col-lg-10 ">
                             <div class="footer-copy-right">
                                 <p><!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
  Copyright &copy;<script>document.write(new Date().getFullYear());</script> All rights reserved | CleanConnect
  <!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. --></p>
                             </div>
                         </div>
                     </div>
                </div>
            </div>
        </div>
        <!-- Footer End-->
    </footer>

  <!-- JS here -->
	
		<!-- All JS Custom Plugins Link Here here -->
        <script src="./assets/js/vendor/modernizr-3.5.0.min.js"></script>
		<!-- Jquery, Popper, Bootstrap -->
		<script src="./assets/js/vendor/jquery-1.12.4.min.js"></script>
        <script src="./assets/js/popper.min.js"></script>
        <script src="./assets/js/bootstrap.min.js"></script>
	    <!-- Jquery Mobile Menu -->
        <script src="./assets/js/jquery.slicknav.min.js"></script>

		<!-- Jquery Slick , Owl-Carousel Plugins -->
        <script src="./assets/js/owl.carousel.min.js"></script>
        <script src="./assets/js/slick.min.js"></script>
        <script src="./assets/js/price_rangs.js"></script>
        
		<!-- One Page, Animated-HeadLin -->
        <script src="./assets/js/wow.min.js"></script>
		<script src="./assets/js/animated.headline.js"></script>
        <script src="./assets/js/jquery.magnific-popup.js"></script>

		<!-- Scrollup, nice-select, sticky -->
        <script src="./assets/js/jquery.scrollUp.min.js"></script>
        <script src="./assets/js/jquery.nice-select.min.js"></script>
		<script src="./assets/js/jquery.sticky.js"></script>
        
        <!-- contact js -->
        <script src="./assets/js/contact.js"></script>
        <script src="./assets/js/jquery.form.js"></script>
        <script src="./assets/js/jquery.validate.min.js"></script>
        <script src="./assets/js/mail-script.js"></script>
        <script src="./assets/js/jquery.ajaxchimp.min.js"></script>
        
		<!-- Jquery Plugins, main Jquery -->	
        <script src="./assets/js/plugins.js"></script>
        <script src="./assets/js/main.js"></script>
        
    </body>
</html>
<?php
$segment = [
    'name' => 'LoginSegment',
    'id'   => $id,
    'trace_id' => $traceId,
	'start_time' => $startTime,
    'end_time' => time(),
	'origin' => 'AWS::ElasticBeanstalk::Environment',
    'in_progress' => false
];

// Encode the segment as a JSON string
$segmentJson = json_encode($segment);

// Create an array of trace segments (you can add multiple segments if needed)
$traceSegments = [$segmentJson];

//print_r($_SERVER);

// Send the trace segments to X-Ray using the PutTraceSegments method
$result = $xrayClient->putTraceSegments(['TraceSegmentDocuments' => $traceSegments]);

// Put trace id and parent id into sessions, to be used in subsegments
$_SESSION['trace_id'] = $traceId;
$_SESSION['parent_id'] = $id;
?>