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
    	
        try{
        	$this->db = new PDO ("sqlite:h3.sqlite");
        } catch(PDOException $e){
        	$errorinfo=$this->db->errorInfo();
        	log_message('Error', '[sqlite] DB연동 실패'.$e);
        	$this->global_lib->error_result(array('code'=>'-1','code_text'=>'DB오류'));
        }
    }
    
    function setConfig($data)
    {
    	// --------------------------------------------------------------------------
    	// 가장 최신에 수정된 h3info를 가지고 온다.
		$result_json=$this->global_lib->getConfig();
		
    	$cdata['url']="h3info/".$result_json['uuid'];
    	$cdata['post']="false";
    	$cdata['customerquest']="PUT";
    	$cdata['postfields']=json_encode(array("REG_STARTS_AT"=>$data['starts_at'],"REG_ENDS_AT"=>$data['ends_at']));
    	$result=$this->global_lib->baas_curl($cdata);
    	
    	$stmt = $this->db->prepare("update reg_date set start_date='".$data['starts_at']."',end_date='".$data['ends_at']."' ");
    	if (!$stmt) {
    		$errorinfo=$this->db->errorInfo();
    		log_message('Error', '[setConfig] table 실패 : '.$errorinfo[2]);
    		$this->global_lib->error_result(array('code'=>'-1','code_text'=>'DB오류'));
    	} else {
    		$result=$stmt->execute();    	
    	}
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
    		$this->global_lib->error_result(array('code'=>'-1','code_text'=>'DB오류'));    		
    	}
    	$result=$stmt->execute();
    	if (!$result) {
    		$errorinfo=$this->db->errorInfo();
    		log_message('Error', '[regTotal] select 실패 : '.$errorinfo[2]);
    		$this->global_lib->error_result(array('code'=>'-1','code_text'=>'DB오류'));
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
    		$this->global_lib->error_result(array('code'=>'-1','code_text'=>'DB오류'));
    	}
    	$result=$stmt->execute();
    	if (!$result) {
    		$errorinfo=$this->db->errorInfo();
    		log_message('Error', '[regDate] select 실패 : '.$errorinfo[2]);
    		$this->global_lib->error_result(array('code'=>'-1','code_text'=>'DB오류'));
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
    	$result=$this->global_lib->baas_curl($cdata);
    
    	$json_result=json_decode($result['result_data'],true);
    	
    	if ($json_result['entities']) {
    		return $json_result['entities'];
    	} else {
    		return "";;
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
    	$result=$this->global_lib->baas_curl($cdata);
    	 
   		$json_result=json_decode($result['result_data'],true);
   		
   		if ($json_result['entities']) {
   			return $json_result['entities'];
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
    		$this->global_lib->error_result(array('code'=>'-1','code_text'=>'DB오류'));
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
    	$cdata['postfields']=json_encode(array("MEMBER_UUID"=>$data['uuid'],"NAME"=>$data['name'],"EMAIL"=>$data['email'],"COMPANY"=>$data['company'] ));
    	$result=$this->global_lib->baas_curl($cdata);
   }
   
   
} 

/* End of file h3apimodel.php */
/* Location:  /application/models/h3apimodel.php */