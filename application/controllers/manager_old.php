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
		
		// re-connect to FTP
		$config['hostname'] = $this->session->userdata('hostname');
		$config['username'] = $this->session->userdata('username');
		$config['password'] = $this->session->userdata('password');
		
		$this->ftp->connect( $config );
		if( $this->input->post("cd") != false ) {
			$this->currentDirectory = $this->input->post("cd");
			ftp_chdir( $this->ftp->conn_id , $this->currentDirectory );
		}
		//echo "<br/>construct changed dir..." . ftp_pwd( $this->ftp->conn_id );
	}
	
	public function index()
	{		
		$this->load->view('explorer_view');
	}
	
	public function dirAppend() {
		
		$append = $this->input->post('dir');
		//$newDirectory = $this->currentDirectory . "/" . $append;
		
		if( ftp_chdir($this->ftp->conn_id, $append) ) {
			//echo "<br/>Changed dir to - " . $this->currentDirectory;
			$this->currentDirectory .= "/" . $append;
		} else {
			echo "<br/>Failed to changed dir to - " . $append;
		}
		
		$this->load->view('explorer_view');
	}
	
	public function dirUp() {
		
		// up directory, and modify currentDir string on success
		if( ftp_cdup( $this->ftp->conn_id ) ) {
			$bottomFolderPos = strrpos( $this->currentDirectory, "/" );
			$this->currentDirectory = substr( $this->currentDirectory, 0, $bottomFolderPos );
		}
		
		$this->load->view('explorer_view');
	}
	
	public function upload() {
		$this->load->view('upload_view');
	}
	
	public function doUpload() {
		
		$config['upload_path'] = './uploads/';
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
			
			$remote_file = $this->currentDirectory . "/" . $data['upload_data']['file_name'];
			
			if( ftp_put( $this->ftp->conn_id, $remote_file, $data['upload_data']['full_path'], FTP_ASCII ) ) {
				//echo "good";
			} else {
				//echo "failed";
			}
			
			$this->load->view('explorer_view', $data);
		}
		
		
	}
	
	public function download() {
		$name = $this->input->post("download");;
		$remote_file = $this->currentDirectory . "/" . $name;
		
		// use output buffering to catch file contents
		ob_start();
		
		if( ftp_get( $this->ftp->conn_id, "php://output", $remote_file, FTP_BINARY) ) {
			$data['content'] = ob_get_contents();
			$data['filename'] = $name;
		} else {
			//echo "download failed";
		}
		
		ob_end_clean();
		
		$this->load->view('download_view', $data );
	}
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */