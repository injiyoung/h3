<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * H3 API 모음
 *
 * Created on 2012. 9. 9.
 * @author miyu <hdae124@kthcorp.com>
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
	function regtotal()
	{
		$result=$this->H3apimodel->regtotal();
		$this->global_lib->json_result(array('code'=>'0','code_text'=>'성공','totalcount'=>$result));
	}

	/**
	 * 2012. 9. 11. hdae124@kthcorp.com
	 * 사전등록
	 */
	function regpost()
	{
		if (!$this->input->post('uuid') or !$this->input->post('email') or !$this->input->post('name')) $this->global_lib->error_result(array('code'=>'-3','code_text'=>'파라미터 부족'));
		
		// --------------------------------------------------------------------------
		// 사전등록 기본정보 가져오기
		$regdate=$this->H3apimodel->regDate();
		// --------------------------------------------------------------------------
		// 사전등록 전체 카운트 가져오기
		$regtotal=$this->H3apimodel->regTotal();

		// --------------------------------------------------------------------------
		// 사전등록 제한 카운트 체크
		if ($regtotal >= $regdate['max_count'])
		{
			$this->global_lib->error_result(array('code'=>'-12','code_text'=>'인원마감'));
		}
		// --------------------------------------------------------------------------
		// 시작시간 체크
		if ($regdate['start_date'] >= date( "c", strtotime('now')))
		{
			$this->global_lib->error_result(array('code'=>'-10','code_text'=>'사전등록 시작전'));
		}
		// --------------------------------------------------------------------------
		// 마감시간 체크
		if ($regdate['end_date'] <= date( "c", strtotime('now')))
		{
			$this->global_lib->error_result(array('code'=>'-11','code_text'=>'사전등록 마감'));
		}
		
		// --------------------------------------------------------------------------
		// 회원 체크
		if (!$this->H3apimodel->memberCheck($this->input->post('email')))
		{
			$this->global_lib->error_result(array('code'=>'-4','code_text'=>'회원이 아님'));
		}

		// --------------------------------------------------------------------------
		// 등록된 이메일인지 체크
		if ($this->H3apimodel->regView($this->input->post('email')))
		{
			// --------------------------------------------------------------------------
			// 이미 등록된 이메일
			$this->global_lib->error_result(array('code'=>'-13','code_text'=>'이미 등록된 회원'));
		}
		
		// --------------------------------------------------------------------------
		// 전체 카운트 증가
		$this->H3apimodel->regCountUpdate();
		
		$data['uuid']=$this->input->post('uuid');
		$data['name']=$this->input->post('name');
		$data['email']=$this->input->post('email');
		$data['company']=$this->input->post('company');
		$result=$this->H3apimodel->regPost($data);
		
		$this->global_lib->json_result(array('code'=>'0','code_text'=>'성공'));
	}

	/**
	 * 2012. 9. 11. hdae124@kthcorp.com
	 * 토큰 강제 재발급
	 */
	function retoken()
	{
		$this->global_lib->bass_token(array('reload'=>'Y'));
	}
	
	/**
	 * 2012. 9. 12. hdae124@kthcorp.com
	 * 사전등록 카운트 초기화
	 */
	function resetcount()
	{
		$this->H3apimodel->resetCount();
	}	
	
	/**
	 * 2012. 9. 11. hdae124@kthcorp.com
	 * 패스워드찾기
	 */
	function schpwd()
	{
		if (!$this->input->get('email')) $this->global_lib->error_result(array('code'=>'-3','code_text'=>'파라미터 부족'));
			
		if ($this->H3apimodel->regView($this->input->get('email')))
		{
   			$data['subject']="메롱";
   			$data['body']="바바";
   			$data['email']=$this->input->get('email');
   			$this->global_lib->send_mail($data);
   			
   			$this->global_lib->json_result(array('code'=>'0','code_text'=>'성공'));
		} else {
			$this->global_lib->error_result(array('code'=>'-4','code_text'=>'회원이 아님'));
		}
	}
	
	/**
	 * 2012. 9. 11. hdae124@kthcorp.com
	 * BaaS 환경설정 가져오기
	 */
	function getConfig()
	{
		echo json_encode($this->global_lib->getConfig());
	}	
	
	/**
	 * 2012. 9. 11. hdae124@kthcorp.com
	 * BaaS 환경설정 저장 
	 */
	function setConfig()
	{
		if (!$this->input->get('REG_STARTS_AT') or !$this->input->get('REG_ENDS_AT')) $this->global_lib->error_result(array('code'=>'-3','code_text'=>'파라미터 부족'));
		//$starts_at=date('c',strtotime($this->input->get('starts')));
		//$ends_at=date('c',strtotime($this->input->get('ends')));
		
		$starts_at=$this->input->get('REG_STARTS_AT');
		$ends_at=$this->input->get('REG_ENDS_AT');

		$this->H3apimodel->setConfig(array('starts_at'=>$starts_at,'ends_at'=>$ends_at));
		$this->global_lib->json_result(array('code'=>'0','code_text'=>'성공'));
	}
	
	function index()
	{
		echo "메롱";
	}
}


/* End of file h3api.php */
/* Location:  /2012/application/controllers/h3api.php */