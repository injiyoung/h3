<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Regapi extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->model('Regmodel');
	}

	public function total()
	{
		echo $this->Regmodel->totalcount();
	}

	public function post()
	{
		$this->Regmodel->insertreg();
	}
	
	public function get()
	{
		$this->Regmodel->insertreg();
	}	
	
	public function index()
	{
		
	}
}

/* End of file regapi.php */
/* Location: ./application/controllers/regapi.php */