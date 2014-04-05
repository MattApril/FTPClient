<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Welcome to ftpClient</title>
	
</head>
<body>

<?php echo form_open('login/process'); ?>
    <ul>  
        <li><label for="ftphost">Host</label>  
        <input type="text" name="ftphost" placeholder="" required></li>
		
		<li><label for="username">Username</label>  
        <input type="text" name="username" placeholder="password"></li>
		
        <li><label for="password">Password</label>  
        <input type="password" name="password" placeholder="password"></li>
		
        <li>
        <input type="submit" value="Login"></li>
    </ul>
	
</form>

</body>
</html>