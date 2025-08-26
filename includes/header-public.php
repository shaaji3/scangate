<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
   <!-- Title -->
	<title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' : ''; ?><?php echo APP_NAME; ?></title>

	<!-- Meta -->
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="author" content="<?php echo APP_INFO['company_name']; ?>">
	<meta name="robots" content="index, follow">

	<meta name="keywords" content="<?php echo 'Event, Ticketing, Management';   ?>">
	<meta name="description" content="<?php echo APP_INFO['app_description']; ?>">

	<meta property="og:title" content="<?php echo APP_NAME; ?>">
	<meta property="og:description" content="<?php echo APP_INFO['app_description']; ?>">
	<meta property="og:image" content="<?php echo APP_INFO['app_logo']; ?>">
	<meta name="format-detection" content="telephone=no">

	<!-- MOBILE SPECIFIC -->
	<meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo APP_INFO['app_logo']; ?>">
    <link href="assets/vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <link class="main-css" href="assets/css/style.css" rel="stylesheet">

</head>

<body class="vh-100">
    <div class="authincation h-100">
        <div class="container h-100">
            <div class="row justify-content-center h-100 align-items-center">
                <div class="col-lg-5 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <a href="index.php"><img class="logo-auth" src="assets/images/logo-full.png" alt=""></a>
                            </div>
                            <!-- Content starts here -->




































































































































































_
