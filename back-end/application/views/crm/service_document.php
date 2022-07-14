<!DOCTYPE html>
<html>
<head>
	 <title>Service Document</title>
</head>
<body>
<?php if($crmPDetails)  { ?>

<header>
  <center><strong><b><u>Service Document</u></b></strong></center>
</header>
<div class="container" id="main-content">

	<h2>Dear <?php echo $crmPDetails['firstname'].' '.$crmPDetails['middlename'].' '.$crmPDetails['lastname']; ?></h2>
	<p>Your NDIS Number : <?php echo $crmPDetails['ndis_num']; ?></p>

	<p>
		Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
	</p>
	<p>
		Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
	</p>
  <footer>
    <br/>    <br/>    <br/>    <br/>    <br/>
    <div style="text-align:right">Sign here</div>
  </footer>
</div>

<?php }  ?>
</body>
</html>
