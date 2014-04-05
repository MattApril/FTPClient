<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Manager extends CI_Controller {

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
		
		/* catch missing sessions */
		if( ! $this->session->userdata('hostname') ) {
			redirect( "login" );
		}
		
		$this->load->library('FilePath');
		
		// re-connect to FTP
		$config['hostname'] = $this->session->userdata('hostname');
		$config['username'] = $this->session->userdata('username');
		$config['password'] = $this->session->userdata('password');
		
		if( $this->ftp->connect( $config ) ) {
			
			if( $this->input->post("currentDirectory") != false || $this->input->get("cd") != false ) {
				
				$filepath = ( $this->input->get("cd") == false ) ? $this->input->post("currentDirectory") : $this->input->get("cd");
				
				$this->filepath->set( $filepath );
				ftp_chdir( $this->ftp->conn_id , $this->filepath->dir );
			}
		
		} else {
			echo "ftp connection failed";
		}
		//echo "<br/>construct changed dir..." . ftp_pwd( $this->ftp->conn_id );
	}
	
	public function index()
	{
		$data['files'] = $this->filepath->FTP_GetContents( $this->ftp->conn_id, $this->filepath->dir );
		$this->load->view( 'explorer_view', $data );
	}
	
	public function dirAppend() {
		
		$append = $this->input->post('dir');
		
		if( ftp_chdir($this->ftp->conn_id, $append) ) {
			$this->filepath->append( $append );
		} else {
			echo "<br/>Failed to changed dir to - " . $append;
		}
		
		$this->load->view('explorer_view');
	}
	
	public function dirUp() {
		
		// up directory, and modify currentDir string on success
		if( ftp_cdup( $this->ftp->conn_id ) ) {
			$this->filepath->up();
		}
		
		$this->load->view('explorer_view');
	}
	
	public function upload() {
		$this->load->view('upload_view');
	}
	
/* 	public function doUpload_old() {
		
		$config['upload_path'] = 'php://temp';
		$config['allowed_types'] = '*';
		$this->load->library('upload', $config);

		if ( ! $this->upload->do_upload() )
		{
			echo $this->upload->display_errors();
			
			$error = array('error' => $this->upload->display_errors());
			$this->load->view('upload_view', $error);
		}
		else
		{
			$data = array('upload_data' => $this->upload->data());
			
			$remote_file = $this->filepath->getAppend( $data['upload_data']['file_name'] );
			
			if( ftp_put( $this->ftp->conn_id, $remote_file, $data['upload_data']['full_path'], FTP_ASCII ) ) {
				//echo "good";
			} else {
				//echo "failed";
			}
			
			$this->load->view('explorer_view', $data);
		}
		
		
	} */
	
	public function doUpload() {
	
		if( $_FILES['userfile']['size'] != 0 ) {
			
			// destination
			$remote_file = $this->filepath->getAppend( $_FILES['userfile']['name'] );
			
			if( ftp_put( $this->ftp->conn_id, $remote_file, $_FILES['userfile']['tmp_name'], FTP_BINARY ) ) {
				//echo "good";
				// redirect and cd to current directory
				redirect( base_url() . "index.php/manager?cd=" . $this->filepath->dir );
			} else {
				//echo "failed";
				$this->load->view('upload_view');
			}
			
			//$data['files'] = $this->filepath->FTP_GetContents( $this->ftp->conn_id, $this->filepath->dir );
			
			//$this->load->view('explorer_view', $data);
		} else {
			$this->load->view('upload_view');
		}
		
	}
	
	public function download() {
		$name = $this->input->get("target");
		$currentDir = $this->input->get("current");
		$this->filepath->set( $currentDir );
		$remote_file = $this->filepath->getAppend( $name );
		
		// use output buffering to catch file contents
		ob_start();
		
		if( ftp_get( $this->ftp->conn_id, "php://output", $remote_file, FTP_BINARY) ) {
			$data['content'] = ob_get_contents();
			$data['filename'] = $name;
		} else {
			echo "download failed";
			//echo "download failed";
			// need to do something here to prevent user from downloading error text
		}
		
		ob_end_clean();
		
		$this->load->view('download_view', $data );
	}
	
	public function delete() {
		
	}
	
	public function test() {
		print_r( $this->filepath->FTP_GetContents( $this->ftp->conn_id, "" ) );
	}
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */