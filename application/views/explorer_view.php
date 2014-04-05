<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Welcome to ftpClient</title>
	<script src="<?php echo base_url() . "assets/js/jquery-1.11.0.js" ?>"></script>
	<script src="<?php echo base_url() . "assets/js/jqcontextmenu.js" ?>"></script>
	<script src="<?php echo base_url() . "assets/js/explorer.js" ?>"></script>
	
	<script type="text/javascript">
		base_url = '<?=base_url() . "index.php"?>';
	</script>
	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>assets/css/explorer.css">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>assets/css/jqcontextmenu.css" />
</head>

<body>

	<div id="infoPanel">

		<span id="connection"> <?php echo $this->session->userdata('username') . "@" . $this->session->userdata('hostname'); ?> </span>
		
		<input type="text" name="currentDirectory" id="currentDirectory" value="<?php echo $this->filepath->dir; ?>" placeholder="/">
		<button type="button" id="changeDir">go</button>
		
		<?php echo form_open( 'login/newSession' ); ?>
			<input type='submit' value='Logout' id="logout">
		</form>
		
	</div>
	
	<div id="nav">
		
		<?php 
		$attributes = array('autocomplete' => 'off'); //autocomplete=off required to prevent firefox from using cached values on refresh
		echo form_open( 'manager/upload', $attributes ); ?>
			<input type='submit' value='upload' id="uploadBtn">
			<?php $this->filepath->dir; echo form_input( array('name' => 'currentDirectory', 'type'=>'hidden', 'id' => 'uploadDirectory', 'value' => $this->filepath->dir ) ); ?>
		</form>
		
		<button type="button" id="dirUp">^</button>
		<button type="button" id="newFolder">New folder</button>
	
	</div>
	
	<!-- <div id="explorerWrapper"> -->
	
	<table id="explorerTable">
		<thead id="explorerHead">
			<tr>
				<th class="fileName">Name</th>
				<th class="fileDate">Date modified</th>
				<th class="fileSize">Size</th>
			</tr>
		</thead>
		
		<tbody id="explorer">
		<?php
		foreach( $files as $file => $property ) {
			
			// directory
			if( $property['type'] == 'directory' ) {
				
				echo "<tr class='directory' id='$file' draggable='true' onDragStart='dragStart(event)' onDragOver='allowDrop(event)' onDrop='drop(event)' >";
				echo "<td class='fileName'> <a href='#'>$file</a> </td>";
				echo "<td class='fileDate'>" . $property['month'] . "/" . $property['day'] . "/" . $property['year'] . " " . $property['time'] . "</td>";
				echo "<td class='fileSize'></td>";
				
			} else { // file
				
				echo "<tr class='file' id='$file' draggable='true' onDragStart='dragStart(event)' >";
				echo "<td class='fileName'> <a href='#'>$file</a> </td>";
				echo "<td class='fileDate'>" . $property['month'] . "/" . $property['day'] . "/" . $property['year'] . " " . $property['time'] . "</td>";
				echo "<td class='fileSize'>" . $property['size'] . "</td>";
			}
		
			echo "</tr>";
		}
		?>
		</tbody>
	
	</table>
	<!--</div>-->
	
	<div id="popups">

		<!-- TEST Popup-->
		<div id="popup" class="pop" style="display:none;">
			
			<p>This is a popup window</p>
		
		</div>
		
		<!-- New Folder Popup-->
		<div id="folderName_popup" class="pop" style="display:none;">
			
			<input type="text" id="folderName">
			<button type="button" id="createDir">create</button>
		
		</div>
		
		<!-- Delete Confirmation Popup -->
		<div id="delete" class="pop" style="display:none;">
			Are you sure you want to delete <span class="content"></span> item(s) permanently?
			<button type="button" id="deleteConfirm">Yes</button> <button type="button" class="closePopup">No</button>
		</div>
		
		<!-- Upload Form Popup -->
		<div id="upload" class="pop" style="display:none;">
			<?php echo form_open_multipart( 'manager/doUpload' ); ?>

				<?php echo form_upload('userfile'); ?>
				<br/>
				<?php echo form_submit('submit', 'upload'); ?>
				
				<?php echo form_hidden( 'currentDirectory', $this->filepath->dir ); ?>

			</form>
		</div>
		
	</div>
	
	<!-- Context Menu -->
	<ul id="contextmenu_main" class="jqcontextmenu">
		<li><a href="#">delete</a></li>
		<li><a href="#">Item 2a</a></li>
		<li><a href="#">Item 1a</a></li>
		<li><a href="#">Item 2a</a></li>
	</ul>
	
</body>
</html>