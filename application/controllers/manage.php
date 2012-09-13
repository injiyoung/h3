<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 관리자용
 *
 * Created on 2012. 9. 13.
 * @author miyu <hdae124@kthcorp.com>
 */

class manage extends CI_Controller {
	function __construct()
	{
		parent::__construct();
		$this->load->library('global_lib');
	}
	
	function panel() {
		$data['today']=date('c');
		$data['h3info']=$this->global_lib->getConfig();
		$data['base_url']=$this->config->item('base_url');
		$this->load->view('manage/manage.html',$data);
	}
}


/* End of file manage.php */
/* Location: /2012/application/controllers/manage.php */