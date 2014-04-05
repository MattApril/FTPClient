<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class FilePath {

	var $dir = '';
	
    function set( $path )
    {
		$this->dir = $path;
    }
	
	function up()
    {
		$bottomFolderPos = strrpos( $this->dir, "/" );
		$this->dir = substr( $this->dir, 0, $bottomFolderPos );
    }
	
	function append( $file )
    {
		$this->dir .= "/" . $file;
    }
	
	function getAppend( $file )
    {
		return $this->dir . "/" . $file;
    }
	
	// fetch array of sub folder and files on given FTP server, including individual properties (name, size, date, etc)
	function FTP_GetContents( $ftpStream, $folder ) {
	
		$rawList = ftp_rawlist( $ftpStream, $folder );
		
		if( is_array( $children = @ftp_rawlist($ftpStream, $folder) ) ) {
            $items = array();

            foreach ($children as $child) {
                $chunks = preg_split("/\s+/", $child);
                list($item['rights'], $item['number'], $item['user'], $item['group'], $item['size'], $item['month'], $item['day'], $item['year']) = $chunks;
                $item['type'] = $chunks[0]{0} === 'd' ? 'directory' : 'file';
                
				
				// rawlist only returns the year or time, so set year/time properly
				if( strpos($item['year'],':') !== false ) {
					$item['time'] = $item['year'];
					$item['year'] = date("Y");
				} else {
					$item['time'] = false;
				}
				
				//convert month to numeric representation
				$month = $item['month'];
				$item['month'] = date('m', strtotime("$month 1 2011"));
				
				array_splice($chunks, 0, 8);
                $items[implode(" ", $chunks)] = $item;
            }
			
            return $items;
        }
		
		return false;
	}
	
}

/* End of file Someclass.php */