<?php
namespace Admin\Model;
use Think\Model;

class BindbankModel extends Model {
			
	//是否默认，1非默认，2默认
	public function bank_flag(){
        return array(
            '1'=>'非默认',
            '2'=>'默认',
        );
    }
  
    
}