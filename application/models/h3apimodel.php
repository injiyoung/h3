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
		
		$max_count=$data['MAX_COUNT'];
		
		unset($data['MAX_COUNT']);
	
    	$cdata['url']="h3info/".$result_json['uuid'];
    	$cdata['post']="false";
    	$cdata['customerquest']="PUT";
    	$cdata['postfields']=json_encode($data);
    	$result=$this->global_lib->baas_curl($cdata);
    	
    	$stmt = $this->db->prepare("update reg_date set start_date='".$data['REG_STARTS_AT']."',end_date='".$data['REG_ENDS_AT']."',max_count='".$max_count."' ");
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
		if (!$result) {
			$errorinfo=$this->db->errorInfo();
			log_message('Error', '[resetCount] update 실패 : '.$errorinfo[2]);
			$this->global_lib->error_result(array('code'=>'-1','code_text'=>'DB오류'));
		}		
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
     	return $row[0];
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
    
    	$json_result=json_decode(@$result['result_data'],true);
    	
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
   
   /**
    * 2012. 9. 17. hdae124@kthcorp.com
    * 비밀번호 찾기 정보 저장 
    */
   function schpwdPost($data)
   {
   		$pwdkey=hash("sha256", $data['email'].strtotime("now"));
	   	$stmt = $this->db->prepare("insert into pwd_email(email,pwdkey,create_date) values (:email,:pwdkey,:create_date) ");
	   	$result=$stmt->execute(array(':email'=>$data['email'],'pwdkey'=>$pwdkey,'create_date'=>date('Y-m-d H:i:s')));

	   	if (!$result) {
	   		$errorinfo=$this->db->errorInfo();
	   		log_message('Error', '[regCountUpdate] insert 실패 : '.$errorinfo[2]);
	   		$this->global_lib->error_result(array('code'=>'-1','code_text'=>'DB오류'));
	   	}
	   	
	   	return $pwdkey;
   }
	   	
	/**
	 * 2012. 9. 17. hdae124@kthcorp.com
	 * 비밀번호 찾기 정고 가져오기
	 */
	function schpwdGet($data)
	{
	   	$stmt = $this->db->prepare('SELECT * FROM pwd_email where pwdkey=:pwdkey and create_date>:todaydate order by create_date desc limit 1');
	   	$result=$stmt->execute(array(':pwdkey'=>$data['pwdkey'],':todaydate'=>date('Y-m-d H:i:s',strtotime('-6 hours'))));

	   	if (!$result) {
	   		$errorinfo=$this->db->errorInfo();
	   		log_message('Error', '[regTotal] select 실패 : '.$errorinfo[2]);
	   		$this->global_lib->error_result(array('code'=>'-1','code_text'=>'DB오류'));
	   	}
	   	
		$row = $stmt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_LAST);
     	return $row;
   }
   
   /**
    * 2012. 9. 17. hdae124@kthcorp.com
    * 비밀번호 변경API
    */
   function changePasswd($data) 
   {
   	// 여기에 BaaS변경 모듈
/* 	   	$cdata['url']="user/".$result_json['uuid'];
	   	$cdata['post']="false";
	   	$cdata['customerquest']="PUT";
	   	$cdata['postfields']=json_encode(array("REG_STARTS_AT"=>$data['starts_at'],"REG_ENDS_AT"=>$data['ends_at'])); */
   }
   
} 

/* End of file h3apimodel.php */
/* Location:  /2012/application/models/h3apimodel.php */