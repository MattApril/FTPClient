<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Welcome to ftpClient</title>
	
</head>
<body>

<?php echo form_open_multipart( 'manager/doUpload' ); ?>

	<?php echo form_upload('userfile'); ?>
	<br/>
	<?php echo form_submit('submit', 'upload'); ?>
	
	<?php echo form_hidden( 'currentDirectory', $this->filepath->dir ); ?>

</form>

</body>
</html>