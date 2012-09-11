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

	function __construct()
	{
		parent::__construct();
		exit;
	}	
	public function search()
	{
		print_r($this->uri->uri_to_assoc(2));
		echo $this->input->get('aa');
	}	
	
	
	public function index()
	{
		try{
			$db = new PDO ("sqlite:h3.sqlite");
		} catch(PDOException $e){
			$errorinfo=$db->errorInfo();
			log_message('Error', 'DB연동 실패'.$e);
			exit;
		}
		
		$stmt = $db->prepare('SELECT * FROM reg_data');
		if (!$stmt) {
			$errorinfo=$db->errorInfo();
			log_message('Error', 'table 실패 : '.$errorinfo[2]);
			exit;
		}		
		
		$result=$stmt->execute();
		if ($result) {
			$row = $stmt->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_LAST);
			
			$result = $db->exec("update reg_data set totalcount=totalcount+1");
			if (!$result) {
				$errorinfo=$db->errorInfo();
				log_message('Error', 'insert 실패 : '.$errorinfo[2]);
				exit;
			} else {
				echo $row[0];
			}			
		} else {
			$errorinfo=$db->errorInfo();
			log_message('Error', 'select 실패 : '.$errorinfo[2]);
			exit;		
		}
		

			exit;
				
/*		
		
$this->load->driver('cache');

$foo=1;

if ($this->cache->apc->get('foo')) {
	$foo=$this->cache->apc->get('foo')+1;
}

$this->cache->apc->save('foo', $foo, 100000000);
		

echo $this->cache->apc->get('foo');
*/
		$this->load->library('pagination');
		$this->load->library('parser');
		$this->load->model('Welcomemodel','',true);
/*		
		$this->load->database();
		
		$query = $this->db->query('SELECT * FROM open_chat LIMIT 1');
		
foreach ($query->result_array() as $row)
 {
print_r($row);
 }
*/		

	
		$this->Welcomemodel->insert_e();
		
		
		
		
		$config['base_url'] = 'http://example.com/index.php/test/page/';
		$config['total_rows'] = 200;
		$config['per_page'] = 20;
		
				
		$this->pagination->initialize($config);
		
		//echo $this->pagination->create_links();
		
		$this->load->helper('url');

	
		//$this->test->test();
		
		//$this->output->cache(1);
		$this->load->helper('directory');
		
		//print_r(directory_map('./', 2));
	
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
		
		$this->output->set_output("Asdasd");
		
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */