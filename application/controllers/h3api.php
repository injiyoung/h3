<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class h3api extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->model('H3apimodel');
	}

	public function regtotal()
	{
		echo $this->H3apimodel->regtotal();
	}

	public function regpost()
	{
		$this->H3apimodel->regpost();
	}

	// 토큰 강제 재발급
	public function retoken()
	{
		$this->H3apimodel->retoken();
	}
	
	public function schpwd()
	{
		$this->H3apimodel->insertreg();
	}
	
	// 환경설정 가져오기
	public function getconfig()
	{
		echo json_encode($this->H3apimodel->getconfig());
	}	
	
	public function index()
	{
		
	}
}

/* End of file regapi.php */
/* Location: ./application/controllers/regapi.php */