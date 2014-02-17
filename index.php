<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>WP Project Builder</title>
	<link rel="stylesheet" href="assets/builder.css" />
	<link rel="shortcut icon" type="image/x-icon" href="./favicon.ico">
	<script type='text/javascript' src='//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js'></script>
	<script type='text/javascript' src='assets/validationEngine.js'></script>
	<script type='text/javascript' src='assets/builder.js'></script>
</head>
<body>
	<div class="main">
		<?php include 'builder.php'; ?>
	</div>
</body>
</html>