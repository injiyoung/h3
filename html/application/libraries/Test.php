<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Test {

     public function Test()
     {
     	
     	$CI =& get_instance();
  	
     	echo $CI->config->item('uri_protocol');
     	echo "test";
     }
 }
