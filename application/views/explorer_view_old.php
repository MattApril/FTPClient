<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Welcome to ftpClient</title>
	
</head>
<body>

<ul>
	<?php echo form_open('manager/dirUp'); ?>
	<input type='submit' value='up'>
	<?php 
	$CI =& get_instance();
	echo form_hidden( 'cd', $this->currentDirectory ); ?>
	</form>
	
	<?php echo form_open('manager/upload'); ?>
	<input type='submit' value='upload'>
	<?php echo form_hidden( 'cd', $this->currentDirectory ); ?>
	</form>
	
	<!-- FOLDERS -->
	 
	<?php
	echo form_open('manager/dirAppend');

	$dirList = $this->ftp->list_files();
	
	foreach( $dirList as $file ) {
		if( ftp_size($this->ftp->conn_id, $file) == -1 ) {
			echo "<br/><input type='submit' name='dir' value='$file'>";
		}
	}
	?>
	
	<?php echo form_hidden( 'cd', $this->currentDirectory ); ?>
	
	</form>
	
	
	<!-- FILES -->
	<?php
	$attr = array( "target" => "_blank" );
	echo form_open('manager/download', $attr);
	
	foreach( $dirList as $file ) {
		if( ftp_size($this->ftp->conn_id, $file) != -1 ) {
			echo "<br/>" . $file;
			echo "<input type='submit' name='download' value='$file'>";
		}
	}
	?>
	
	<?php echo form_hidden( 'cd', $this->currentDirectory ); ?>
	
	</form>
	
	
	
</ul>

</body>
</html>