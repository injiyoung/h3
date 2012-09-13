<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 관리자용
 *
 * Created on 2012. 9. 13.
 * @author miyu <hdae124@kthcorp.com>
 * @version 1.0
 */

class manage extends CI_Controller {
	function __construct()
	{
		parent::__construct();
		$this->load->library('global_lib');
		//$this->load->model('H3apimodel');
	}
	
	function panel() {
		$data['h3info']=$this->global_lib->getConfig();
		$this->load->view('manage/manage.html',$data);
	}
}