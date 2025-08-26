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
	<meta name="author" content="DexignZone">
	<meta name="robots" content="index, follow">

	<meta name="keywords" content="	admin dashboard, admin template, administration, analytics, bootstrap, bootstrap admin, coupon, deal, modern, responsive admin dashboard, ticket, ticket dashboard, ticket system, admin panel,	Ticketing admin, Dashboard template, Bootstrap HTML, Ticket management, Event ticketing, Responsive design, User-friendly interface, Efficiency, Streamlining operations, Event management, Ticket sales, Customizable template, Stylish design, Modern dashboard">
	<meta name="description" content="Discover Tixia, the ultimate solution for ticketing administration. Our Bootstrap HTML Template empowers you to streamline ticketing tasks, enhancing operational efficiency with style and ease. Simplify your processes and elevate your ticketing management experience today.">

	<meta property="og:title" content="Tixia - Ticketing Admin Dashboard Bootstrap HTML Template | DexignZone">
	<meta property="og:description" content="Discover Tixia, the ultimate solution for ticketing administration. Our Bootstrap HTML Template empowers you to streamline ticketing tasks, enhancing operational efficiency with style and ease. Simplify your processes and elevate your ticketing management experience today.">
	<meta property="og:image" content="assets/images/social-image.png">
	<meta name="format-detection" content="telephone=no">

	<meta name="twitter:title" content="Tixia - Ticketing Admin Dashboard Bootstrap HTML Template | DexignZone">
	<meta name="twitter:description" content="Discover Tixia, the ultimate solution for ticketing administration. Our Bootstrap HTML Template empowers you to streamline ticketing tasks, enhancing operational efficiency with style and ease. Simplify your processes and elevate your ticketing management experience today.">
	<meta name="twitter:image" content="assets/images/social-image.png">
	<meta name="twitter:card" content="summary_large_image">

	<!-- MOBILE SPECIFIC -->
	<meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon.png">
    <link href="assets/vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <link class="main-css" href="assets/css/style.css" rel="stylesheet">

</head>

<body class="vh-100">
    <div class="authincation h-100">
        <div class="container h-100">
            <div class="row justify-content-center h-100 align-items-center">
                <div class="col-md-6">
                    <div class="authincation-content">
                        <div class="row no-gutters">
                            <div class="col-xl-12">
                                <div class="auth-form">
                                    <div class="text-center mb-3">
                                        <a href="index.php"><img src="assets/images/logo-full.png" alt=""></a>
                                    </div>
                                    <!-- Content starts here -->
