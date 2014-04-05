<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ajax extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -  
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	 
	public $currentDirectory;
	 
	public function __construct()
	{
		parent::__construct();
		
		$this->load->library('FilePath');
		
		// re-connect to FTP
		$config['hostname'] = $this->session->userdata('hostname');
		$config['username'] = $this->session->userdata('username');
		$config['password'] = $this->session->userdata('password');
		
		if( $this->ftp->connect( $config ) ) {
			
			if( $this->input->post("current") != false ) {
				$this->filepath->set( $this->input->post("current") );
				ftp_chdir( $this->ftp->conn_id , $this->filepath->dir );
			}
		
		} else {
			echo "ftp connection failed";
		}
		//echo "<br/>construct changed dir..." . ftp_pwd( $this->ftp->conn_id );
	}
	
	public function index()
	{
		//$data['files'] = $this->filepath->FTP_GetContents( $this->ftp->conn_id, $this->filepath->dir );
		//$this->load->view( 'explorer_view', $data );
	}
	
	public function doUpload() {
	
		if( $_FILES['userfile']['size'] != 0 ) {
			
			// destination
			$remote_file = $this->filepath->getAppend( $_FILES['userfile']['name'] );
			
			if( ftp_put( $this->ftp->conn_id, $remote_file, $_FILES['userfile']['tmp_name'], FTP_BINARY ) ) {
				//echo "good";
			} else {
				//echo "failed";
			}
			
			$this->load->view('explorer_view');
		} else {
			$this->load->view('upload_view');
		}
		
	}
	
	/************************
	**						*
	**						*
	**						*
	* START AJAX FUNCTIONS 	*
	**						*
	**						*
	**						*
	************************/
	
	// append directory
	public function cd() {
		$append = $this->input->post('target');
		
		if( ftp_chdir($this->ftp->conn_id, $append) ) {
			$this->filepath->append( $append );
			// return json obj
			echo json_encode(  array( "directoryPath" => $this->filepath->dir, "directoryData"=>$this->filepath->FTP_GetContents( $this->ftp->conn_id, $this->filepath->dir ) )  );
		} else {
			// should go to root?
			echo ""; //return empty
		}
	}
	
	public function setDirectory() {
		$target = $this->input->post('target');
		
		/*sanitization steps*/
		$target = trim( $target );
		
		// if target is a file, change to parent directory instead
		if( ftp_size( $this->ftp->conn_id, $target ) != -1 ) {
			$this->filepath->set( $target );
			$this->filepath->up();
			$target = $this->filepath->dir;
		}
		// start with "/" prefix always
		if( substr($target, 0, 1) != "/" ) {
			$target = "/" . $target;
		}
		
		// target ready, test directory and send data on success
		if( @ftp_chdir( $this->ftp->conn_id , $target ) ) {
			$this->filepath->set( $target );
			echo json_encode(  array( "directoryPath" => $this->filepath->dir, "directoryData"=>$this->filepath->FTP_GetContents( $this->ftp->conn_id, $this->filepath->dir ) )  );
		}
	}
	
	public function dirUp() {
		
		// up directory, and modify currentDir string on success
		if( ftp_cdup( $this->ftp->conn_id ) && $this->filepath->dir != "" ) {
			$this->filepath->up();
			
			// return json obj
			echo json_encode(  array( "directoryPath" => $this->filepath->dir, "directoryData"=>$this->filepath->FTP_GetContents( $this->ftp->conn_id, $this->filepath->dir ) )  );
			
		} else {
			// default: go to root
			$this->filepath->set("");
			
			// return json obj
			echo json_encode(  array( "directoryPath" => $this->filepath->dir, "directoryData"=>$this->filepath->FTP_GetContents( $this->ftp->conn_id, $this->filepath->dir ) )  );
		}
		
	}
	
	public function createDir() {
		$newDir = $this->input->post('target');
		if( ftp_mkdir( $this->ftp->conn_id, $newDir ) == true ) {
			echo "true";
		} else {
			echo "An error occurred";
		}
	}
	
	public function download() {
		$name = $this->input->post("target");
		$remote_file = $this->filepath->getAppend( $name );
		
		// use output buffering to catch file contents
		ob_start();
		
		if( ftp_get( $this->ftp->conn_id, "php://output", $remote_file, FTP_BINARY) ) {
			$data['content'] = ob_get_contents();
			$data['filename'] = $name;
		} else {
			//echo "download failed";
		}
		
		ob_end_clean();
		
		echo( $data );
	}
	
	public function delete() {
		$files = $this->input->post("target");
		$failures = array();
		
		foreach( $files as $file ) {
		
			if( ftp_size( $this->ftp->conn_id, $file ) != -1 ) { //file
				$success = $this->ftp->delete_file( $file );
			 } else { // folder
				$success = $this->ftp->delete_dir( $file );
			 }
			 
			 // file failed to delete
			 if( !$success ) {
				$failures.push($file);
			 }
		}
		
		if( empty($failures) ) {
			echo "true";
		} else {
			echo "some files failed to delete";
		}
	}
	
	public function move() {
		$source = $this->input->post("source");
		$target = $this->input->post("target");
		$sourceFile = substr( $source, strrpos($source, "/") );
		$new = $target . $sourceFile;
		
		if( $this->ftp->rename( $source, $new ) ) {
			echo "true";
		} else {
			echo "false";
		}
		
	}
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */