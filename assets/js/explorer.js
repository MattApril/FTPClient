var lastRightClicked;
var currentDirectory;
var sortKey = "fileName";

jQuery(document).ready(function($) {
	
	currentDirectory = $( "#currentDirectory" ).val();
	sortBy( sortKey );
	
	// handle file click
	$( "#explorer" ).on( 'click', '.file a', function( e ) {
		e.preventDefault();
		
		var dir = { target: e.target.parentNode.parentNode.id, current: currentDirectory };
		var gRequest = base_url + "/manager/download?target=" + dir.target + "&current=" + dir.current;
		var win = window.open(gRequest);
		
	});
	
	//handle directory click
	$( "#explorer" ).on( 'click', '.directory a', function( e ) {
		e.preventDefault();
		
		var dir = { target: e.target.parentNode.parentNode.id, current: currentDirectory };
		
		$.post( base_url + "/ajax/cd", dir, function( data ) {
			if( data ) {
				//alert(data);
				var dataObj = JSON.parse( data );
				var html = HTMLbuildTableRows( dataObj.directoryData );
				
				$( ".directory" ).remove(); // clear current directory
				$( ".file" ).remove(); // clear current directory
				
				$( "#explorer" ).append( html );
				sortBy( sortKey );
				
				setCurrentDirectory( dataObj.directoryPath );
			}
		});
	});
	
	// jump to directory
	$( "#changeDir" ).click( function(e) {
		var dir = { target: $( "#currentDirectory" ).val() };
		
		$.post( base_url + "/ajax/setDirectory", dir, function( data ) {
			
			if( data ) {
				//alert(data);
				
				var dataObj = JSON.parse( data );
				var html = HTMLbuildTableRows( dataObj.directoryData );
				
				$( ".directory" ).remove(); // clear current directory
				$( ".file" ).remove(); // clear current directory
				
				$( "#explorer" ).append( html );
				sortBy( sortKey );
				
				setCurrentDirectory( dataObj.directoryPath );
			} else {
				alert("directory not found");
			}
		});
	});
	
	// move up one directory
	$( "#dirUp" ).click( function() {
	
		var dir = { current: currentDirectory };
		
		$.post( base_url + "/ajax/dirUp", dir, function( data ) {
			if( data ) {
				//alert(data);
				var dataObj = JSON.parse( data );
				
				var html = HTMLbuildTableRows( dataObj.directoryData );
				
				$( ".directory" ).remove(); // clear current directory
				$( ".file" ).remove(); // clear current directory
				
				$( "#explorer" ).append( html );
				sortBy( sortKey );
				
				setCurrentDirectory( dataObj.directoryPath );
			}
		});
	});
	
	// create folder
	$( "#createDir" ).click( function() {
	
		var name = $( "#folderName" ).val();
		var dir = { current: currentDirectory, target: name };
		
		$.post( base_url + "/ajax/createDir", dir, function( data ) {
			if( data ) {
				if( data == "true" ) {
					//create folder on client
					
					var folderData = new Object();
					var directoryData = new Object();
					var d = new Date();
					
					//set folder properties
					folderData.day = addZero( d.getDay() );
					folderData.month = addZero( d.getMonth() + 1 );
					folderData.year = d.getFullYear();
					folderData.time = addZero( d.getHours() ) + ":" + addZero( d.getMinutes() );
					folderData.type = "directory";
					
					// set directory structure with folder name
					directoryData[name] = folderData;
					
					// generate and add folder element
					var html = HTMLbuildTableRows( directoryData );
					$( "#explorer" ).append( html );
					
					sortBy( sortKey );
					
				} else {
					
				}
			}
			
		});
		$(this).closest(".pop").hide();
		$("#overlay").remove();
	});
	
	//file sorting
	$( "#explorerHead th" ).click( function(e){
		sortKey = $(this).attr('class');
		sortBy( sortKey );
	});
	
	$( "#newFolder" ).click( function() {
	
		// folder name popup
		buildPopup( "folderName_popup", false );
		
	});
	
	// set directory value before upload submit
	$( "#uploadBtn" ).click( function() {
		$( "#uploadDirectory" ).val( currentDirectory );
	});
	
	// file delete confirmation and execution
	$( "#deleteConfirm" ).click( function() {
		ftpDelete();
		$(this).closest(".pop").hide();
		$("#overlay").remove();
	});
	
	// generic popup close
	$( ".closePopup" ).click( function( e ) {
		$(this).closest(".pop").hide();
		$("#overlay").remove();
	});
	
	//file selection handlers
	$( "#explorer" ).on( 'click', 'tr', function( e ) {
		var $row = $(e.target);
		
		// exclude table heading
		if( $row[0].tagName !== "TH" ) {
			if($row[0].tagName !== "TR") $row = $row.closest("tr"); // if td was selected
			$row.toggleClass("selected");
			
			if(e.ctrlKey === false) {
				$row.siblings().removeClass("selected");
			}
		}
	});
	
	// right click selection
	$('#explorer').on('contextmenu', 'tr', function( e ){
		var $row = $(e.target);
		
		// exclude table heading
		if( $row[0].tagName !== "TH" ) {
			if($row[0].tagName !== "TR") $row = $row.closest("tr") // get parent row
			lastRightClicked = $row.attr("id");
			
			// select this item, and deselect others
			if( ! $row.hasClass('selected') ) {
				$row.addClass("selected");
				$row.siblings().removeClass("selected");
			}
		}
		
	});
	
	//context menu
	$('#explorer').addcontextmenu('contextmenu_main');
	
	//handlers
	$( ".jqcontextmenu a" ).click( function() {
		switch( this.innerHTML ) {
			
			case "delete":
				var items = getSelectedItems().length;
				buildPopup( "delete", items );
			break;
			
			default:
			break;
			
		}
	});
	
});

function HTMLbuildTableRows( directoryData ) {
	var i;
	var output = "";
	
	jQuery.each( directoryData, function( name, property ) {
	
		if( property.type == 'directory' ) {
			output += "<tr class='directory' id='" + name + "' draggable='true' onDragStart='dragStart(event)' onDragOver='allowDrop(event)' onDrop='drop(event)' >";
			output += "<td class='fileName'><a href='#'>" + name + "</a></td>";
			output += "<td class='fileDate'>" + property.month + "/" + property.day + "/" + property.year + " " + property.time + "</td>";
			output += "<td class='fileSize'></td>";
		} else {
			output += "<tr class='file' id='" + name + "' draggable='true' onDragStart='dragStart(event)' >";
			output += "<td class='fileName'><a href='#'>" + name + "</a></td>";
			output += "<td class='fileDate'>" + property.month + "/" + property.day + "/" + property.year + " " + ( property.time == false ? "" : property.time ) + "</td>";
			output += "<td class='fileSize'>" + property.size + "</td>";
		}
	});
	
	return output;
}

function ftpDelete() {
	var selection = getSelectedItems();
	
	// make sure selection is not empty (clicked on TH, etc)
	var dir = { current: currentDirectory, target: selection };
		
		jQuery.post( base_url + "/ajax/delete", dir, function( data ) {
			if( data ) {
				// delete files client side
				if( data == "true" ) {
					jQuery(".selected").remove(); // might be problematic, try to use 'selection' instead
				} else {
					// .remove() everything that was not returned
				}
			} else {
				alert("An error occured");
				// error
			}
		});
	//ajax request, pass array of files to delete
}

// return ID array of selected files and folders
function getSelectedItems() {
	var items = [];
	
	jQuery(".selected").each( function() {
		items.push( jQuery(this).attr('id') );
	});
	
	return items;
}

function buildPopup( id, content ) {
	//create overlay div
	
	var over = document.createElement('div');
	over.setAttribute('id', 'overlay');
	document.body.appendChild(over);
	
	//display popup div
	if(content !== false) {
		jQuery(".content").html( content );
	}
	jQuery("#" + id).show();
	
	jQuery("#overlay").click( function(e) {
		if (e.target === this){
			this.remove();
			jQuery("#" + id).hide();
		}
	});
}

function setCurrentDirectory( dir ) {
	currentDirectory = dir;
	jQuery( "#currentDirectory" ).val( dir );
}

/* date formatting helper */
function addZero(i) {
	if (i<10) {
		i="0" + i;
	}
	return i;
}

function dragStart(e) {
	//console.log( "drag start: " + jQuery(e.target).closest("tr").attr("id") );
	var file = jQuery(e.target).closest("tr").attr("id");
	e.dataTransfer.setData("Text", file);
}

function allowDrop(e) {
	e.preventDefault();
}

function drop(e) {
	e.preventDefault();
	//console.log( jQuery(e.target).closest("tr").attr("id") );
	//console.log( e.dataTransfer.getData("Text") );
	var sourceFile = e.dataTransfer.getData("Text");
	var sourcePath = currentDirectory + "/" + sourceFile;
	var targetPath = currentDirectory + "/" + jQuery(e.target).closest("tr").attr("id");
	
	if(sourcePath !== targetPath) {
	
		var dir = { source: sourcePath,
					target: targetPath,
					current: currentDirectory
					};
			
		jQuery.post( base_url + "/ajax/move", dir, function( data ) {
			if( data == "true" ) {
				//delete source file
				alert("delete" + sourceFile);
				jQuery("#" + periodEscape(sourceFile) ).remove();
			}
		});
	}
}

function sortBy( sortKey ) {
	if( sortKey == "fileName" ) {
		jQuery("#explorer .directory").sort(sortName).appendTo('#explorer');
		jQuery("#explorer .file").sort(sortName).appendTo('#explorer');
	} else if( sortKey == "fileDate" ) {
		jQuery("#explorer .directory").sort(sortDate).appendTo('#explorer');
		jQuery("#explorer .file").sort(sortDate).appendTo('#explorer');
	} else if( sortKey == "fileSize" ) {
		jQuery("#explorer .directory").sort(sortSize).appendTo('#explorer');
		jQuery("#explorer .file").sort(sortSize).appendTo('#explorer');
	}
}

function sortName(a,b) {
	return a.childNodes[0].innerHTML.toLowerCase() > b.childNodes[0].innerHTML.toLowerCase() ? 1 : -1;
};

function sortDate(a,b) {
	aDate = new Date( a.childNodes[1].innerHTML );
	bDate = new Date( b.childNodes[1].innerHTML );
	return aDate > bDate ? 1 : -1;
};

function sortSize(a,b) {
	return parseInt( a.childNodes[2].innerHTML ) - parseInt( b.childNodes[2].innerHTML );
};

function periodEscape(str) {
	return str.replace(".", "\\.");
}