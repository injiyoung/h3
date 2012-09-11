<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 @author : hdae124@kthcorp.com
 @date : 2012. 9. 11.
*/

class h3api extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('global_lib');
		$this->load->model('H3apimodel');
	}

	// 전체사전등록수
	public function regtotal()
	{
		$result=$this->H3apimodel->regtotal();
		$this->global_lib->json_result($result);
	}

	// 사전등록
	public function regpost()
	{
		// 사전등록 기본정보 가져오기
		$regdate=$this->H3apimodel->regDate();
		// 사전등록 전체 카운트 가져오기
		$regtotal=$this->H3apimodel->regTotal();

		// 사전등록 제한 카운트 체크
		if ($regtotal['totalcount'] >= $regdate['max_count'])
		{
			$this->global_lib->json_result(array('code'=>'-12'));
		}
		// 시작시간 체크
		if ($regdate['start_date'] >= date( "Y-m-d H:i:s", strtotime('now')))
		{
			$this->global_lib->json_result(array('code'=>'-10'));
		}
		// 마감시간 체크
		if ($regdate['end_date'] <= date( "Y-m-d H:i:s", strtotime('now')))
		{
			$this->global_lib->json_result(array('code'=>'-11'));
		}
		
		// 등록된 이메일인지 체크
		$this->H3apimodel->regView($this->input->post('email'));
		
		// 전체 카운트 증가
		$this->H3apimodel->regCountUpdate();
		
		$data['uuid']=$this->input->post('uuid');
		$data['name']=$this->input->post('name');
		$data['email']=$this->input->post('email');
		$data['company']=$this->input->post('company');
		$result=$this->H3apimodel->regPost($data);
		$this->global_lib->json_result($result);
	}

	// 토큰 강제 재발급
	public function retoken()
	{
		$this->H3apimodel->retoken();
	}
	
	// 패스워드찾기
	public function schpwd()
	{
		$this->H3apimodel->schpwd();
	}
	
	// 환경설정 가져오기
	public function getconfig()
	{
		echo json_encode($this->H3apimodel->getconfig());
	}	
	
	public function index()
	{
		echo "메롱";
	}
}

/* End of file regapi.php */
/* Location: ./application/controllers/regapi.php */