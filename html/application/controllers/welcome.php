<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends CI_Controller {

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
		$this->load->library('pagination');
		$this->load->library('parser');
		$this->load->model('Welcomemodel','',true);

					
		$this->Welcomemodel->insert_e();
		
		$config['base_url'] = 'http://example.com/index.php/test/page/';
		$config['total_rows'] = 200;
		$config['per_page'] = 20;
		
				
		$this->pagination->initialize($config);
		
		//echo $this->pagination->create_links();
		
		echo "a";
		
		$this->load->library('test');	

		//$this->test->test2();
		
		$data= array(
				'title'=>'메롱',
				'ddd'=>array(
						array('title2' => 'Title 1', 'body' => 'Body 1'),
						array('title2' => 'Title 2', 'body' => 'Body 2'),
						array('title2' => 'Title 3', 'body' => 'Body 3'),
						array('title2' => 'Title 4', 'body' => 'Body 4'),
						array('title2' => 'Title 5', 'body' => 'Body 5'))
		);
				
		$this->parser->parse('welcome_message',$data);
		$this->output->enable_profiler(TRUE);
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */