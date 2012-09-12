<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @author : hdae124@kthcorp.com
 * @date : 2012. 9. 10.
 */

class H3apimodel extends CI_Model {
	var $db ="";
	
    function __construct()
    {
    	parent::__construct();
    	$this->global_lib->bass_token();
    	
        try{
        	$this->db = new PDO ("sqlite:h3.sqlite");
        } catch(PDOException $e){
        	$errorinfo=$this->db->errorInfo();
        	log_message('Error', '[sqlite] DB연동 실패'.$e);
        	$this->global_lib->json_result(array('code'=>'-1'));
        }
    }
    
    function setConfig($data)
    {
    	$cdata['url']="h3info/fabbe2d1-f33c-11e1-a329-020053a90013/";
    	$cdata['post']="false";
    	$cdata['customerquest']="PUT";
    	$cdata['postfields']=json_encode(array("REG_STARTS_AT"=>$data['starts_at'],"REG_ENDS_AT"=>$data['ends_at']));
    	$cdata['httpheader']=array("Authorization: Bearer ".$this->global_lib->apptoken);
    	$result=$this->global_lib->baas_curl($cdata);

    	if ($result['http_code']!=200) {
    		$this->output->set_status_header('500');
    		$result = $this->db->exec("update reg_data set totalcount=totalcount-1");
    		log_message('Error', '[regPost] BaaS 등록 실패 : '.$result['http_code'].' - '.$result['error_text']);
    		$this->global_lib->json_result(array('code'=>'-2'));
    	}
    	
    	return array('code'=>'0');
    }
        
    
	function resetCount() 
	{
		$stmt = $this->db->prepare('update reg_data set totalcount=0;');
		$result=$stmt->execute();
	}    
    
   /**
    * 2012. 9. 11. hdae124@kthcorp.com
    * 전체 합계
    */
   function regTotal() 
   {
    	$stmt = $this->db->prepare('SELECT * FROM reg_data');
    	if (!$stmt) {
    		$errorinfo=$this->db->errorInfo();
    		log_message('Error', '[regTotal] table 실패 : '.$errorinfo[2]);
    		$this->global_lib->json_result(array('code'=>'-1'));    		
    	}
    	$result=$stmt->execute();
    	if (!$result) {
    		$errorinfo=$this->db->errorInfo();
    		log_message('Error', '[regTotal] select 실패 : '.$errorinfo[2]);
    		$this->global_lib->json_result(array('code'=>'-1'));
    	}
    	$row = $stmt->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_LAST);
     	return array('code'=>'0','totalcount'=>$row[0]);
    }
    

    /**
     * 2012. 9. 11. hdae124@kthcorp.com
     * 시작/마감 시간, 사전등록제한카운트 가져오기
     */
    function regDate() 
    {
    	$stmt = $this->db->prepare('SELECT * FROM reg_date');
    	if (!$stmt) {
    		$errorinfo=$this->db->errorInfo();
    		log_message('Error', '[regDate] table 실패 : '.$errorinfo[2]);
    		$this->global_lib->json_result(array('code'=>'-1'));
    	}
    	$result=$stmt->execute();
    	if (!$result) {
    		$errorinfo=$this->db->errorInfo();
    		log_message('Error', '[regDate] select 실패 : '.$errorinfo[2]);
    		$this->global_lib->json_result(array('code'=>'-1'));
    	}
    	$row = $stmt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_LAST);
    	return $row;
    }
    

    /**
     * 2012. 9. 11. hdae124@kthcorp.com
     * BaaS 사전등록 조회 
     */
    function regView($email)
    {
    	$cdata['url']="registration?filter=EMAIL='".$email."'";
    	$cdata['post']="false";
    	$cdata['httpheader']=array("Authorization: Bearer ".$this->global_lib->apptoken);
    	 
    	$result=$this->global_lib->baas_curl($cdata);
    
    	if (@$result['http_code']!=200) {
    		$this->output->set_status_header('500');
    		log_message('Error', '[regView] BaaS 조회 실패 : '.@$result['http_code'].' - '.$result['error_text']);
    		$this->global_lib->json_result(array('code'=>'-2'));
    	} else {
    		$json_result=json_decode($result['result_data'],true);
    		if ($json_result['entities']) {
    			return $json_result['entities'];
    		} else {
    			return "";;
    		}
    	}
    }
        
    /**
     * 2012. 9. 12. hdae124@kthcorp.com
     * 회원 체크 
     */
    function memberCheck($email)
    {
    	$cdata['url']="user?filter=email='".$email."'";
    	$cdata['post']="false";
    	$cdata['httpheader']=array("Authorization: Bearer ".$this->global_lib->apptoken);
    	
    	$result=$this->global_lib->baas_curl($cdata);
    	 
    	if (@$result['http_code']!=200) {
    		$this->output->set_status_header('500');
    		log_message('Error', '[memberCheck] BaaS 조회 실패 : '.@$result['http_code'].' - '.$result['error_text']);
    		$this->global_lib->json_result(array('code'=>'-2'));
    	} else {
    		$json_result=json_decode($result['result_data'],true);
    		if ($json_result['entities']) {
    			return $json_result['entities'];
    		} else {
    			return "";; 
    		}
    	}    	
    }
    
    /**
     * 2012. 9. 11. hdae124@kthcorp.com
     * 전체 카운트 증가 
     */
    function regCountUpdate()
    {
    	$result = $this->db->exec("update reg_data set totalcount=totalcount+1");
    	if (!$result) {
    		$errorinfo=$this->db->errorInfo();
    		log_message('Error', '[regCountUpdate] insert 실패 : '.$errorinfo[2]);
    		$this->global_lib->json_result(array('code'=>'-1'));
    	}    	
    }
    
    /**
     * 2012. 9. 11. hdae124@kthcorp.com
     * 사전등록 
     */
    function regPost($data)
    {
    	$cdata['url']="registration";
    	$cdata['post']="true";
    	$cdata['httpheader']=array("Authorization: Bearer ".$this->global_lib->apptoken);
    	$cdata['postfields']=json_encode(array("MEMBER_UUID"=>$data['uuid'],"NAME"=>$data['name'],"EMAIL"=>$data['email'],"COMPANY"=>$data['company'] ));
    	
    	$result=$this->global_lib->baas_curl($cdata);
    	
    	if ($result['http_code']!=200) {
    		$this->output->set_status_header('500');
    		$result = $this->db->exec("update reg_data set totalcount=totalcount-1");
    		log_message('Error', '[regPost] BaaS 등록 실패 : '.$result['http_code'].' - '.$result['error_text']);    		
    		$this->global_lib->json_result(array('code'=>'-2'));
    	} 
   	
    	return array('code'=>'0');
   }
   
   
} 

/* End of file h3apimodel.php */
/* Location:  /application/models/h3apimodel.php */