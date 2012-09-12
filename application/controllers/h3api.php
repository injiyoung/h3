<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @author : hdae124@kthcorp.com
 * @date : 2012. 9. 10.
 */

class h3api extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('global_lib');
		$this->load->model('H3apimodel');
	}

	/**
	 * 2012. 9. 11. hdae124@kthcorp.com
	 * 전체사전등록수
	 */
	public function regtotal()
	{
		$result=$this->H3apimodel->regtotal();
		$this->global_lib->json_result($result);
	}

	/**
	 * 2012. 9. 11. hdae124@kthcorp.com
	 * 사전등록
	 */
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
		if ($this->H3apimodel->regView($this->input->post('email')))
		{
			// 이미 등록된 이메일
			$this->global_lib->json_result(array('code'=>'-13'));
		}
		
		// 전체 카운트 증가
		$this->H3apimodel->regCountUpdate();
		
		$data['uuid']=$this->input->post('uuid');
		$data['name']=$this->input->post('name');
		$data['email']=$this->input->post('email');
		$data['company']=$this->input->post('company');
		$result=$this->H3apimodel->regPost($data);
		
		$this->global_lib->json_result($result);
	}

	/**
	 * 2012. 9. 11. hdae124@kthcorp.com
	 * 토큰 강제 재발급
	 */
	public function retoken()
	{
		$this->global_lib->bass_token(array('reload'=>'Y'));
	}
	
	/**
	 * 2012. 9. 11. hdae124@kthcorp.com
	 * 패스워드찾기
	 */
	
	public function member(){
		print_r($this->H3apimodel->regView($this->input->get('email')));
	}
	public function schpwd()
	{
		if (!$this->input->get('email')) $this->global_lib->json_result(array('code'=>'-3'));
			
		if ($this->H3apimodel->regView($this->input->get('email')))
		{
   			$data['subject']="메롱";
   			$data['body']="바바";
   			$data['email']=$this->input->get('email');
   			$this->global_lib->send_mail($data);
   			
   			$this->global_lib->json_result(array('code'=>'0'));
		} else {
			$this->global_lib->json_result(array('code'=>'-11'));
		}
	}
	
	/**
	 * 2012. 9. 11. hdae124@kthcorp.com
	 * BaaS 환경설정 가져오기
	 */
	public function getConfig()
	{
		echo json_encode($this->global_lib->getConfig());
	}	
	
	/**
	 * 2012. 9. 11. hdae124@kthcorp.com
	 * BaaS 환경설정 저장 
	 */
	public function setConfig()
	{
		if (!$this->input->get('starts') or !$this->input->get('ends')) $this->global_lib->json_result(array('code'=>'-3'));
		$starts_at=date('c',strtotime($this->input->get('starts')));
		$ends_at=date('c',strtotime($this->input->get('ends')));
		echo json_encode($this->H3apimodel->setConfig(array('starts_at'=>$starts_at,'ends_at'=>$ends_at)));
	}
	
	public function index()
	{
		echo "메롱";
	}
}

/* End of file h3api.php */
/* Location: ./application/controllers/h3api.php */