<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * user관련 처리
 *
 * Created on 2012. 9. 17.
 * @author miyu <hdae124@kthcorp.com>
 */
class user extends CI_Controller {
	function __construct()
	{
		parent::__construct();
		$this->load->library('global_lib');
		$this->load->model('H3apimodel');
	}

	function resetPasswd() {
		$data['email']=$this->input->get('email');
		$data['pwdkey']=$this->input->get('pwdkey');
		
		$data['base_url']=$this->config->item('base_url');		
		$this->load->view('user/resetPasswd.html',$data);
	}
}


/* End of file manage.php */
/* Location: /2012/application/controllers/manage.php */