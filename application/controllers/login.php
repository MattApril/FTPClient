<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login extends CI_Controller {

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
	public function index()
	{
		$this->load->view('login_view');
	}
	
	public function process() {
	
		$config['hostname'] = $this->input->post('ftphost');
		$config['username'] = $this->input->post('username');
		$config['password'] = $this->input->post('password');
		$config['debug'] = TRUE;
		
		if ( $this->ftp->connect($config) ) {
			// set directory model
			// redirect to ftp controller (which will set view)
			$this->session->set_userdata( $config );
			redirect("manager");
		} else {
			$this->load->view('login_view');
		}
		
	}
	
	public function newSession() {
		$this->session->sess_destroy();
		$this->load->view('login_view');
	}
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */