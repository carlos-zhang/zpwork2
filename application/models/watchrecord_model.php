<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class WatchRecord_model extends CI_Model {
    public function __construct() {
        $this->load->database();
    }

    public function get_watchrecords($limt, $offset) {
        $this->db->order_by('WR_BeginTime', 'desc');

        $this->db->select('WR_ID,WR_Begin,WR_BeginTime,WR_End,WR_EndTime');
        $query = $this->db->get('watchrecordpeoplesample', $limt, $offset);

        return $query->result_array();
    }

    public function get_wr_begin() {
        $query = $this->db->query('SELECT WR_Begin FROM WATCHRECORDPEOPLESAMPLE');

        return $query->result_array();
    }


    public function get_amountByday($dayTime) {

		$this->db->distinct();
		$this->db->select('WR_PplID');
		$this->db->from('watchrecordpeoplesample');
		$this->db->where('WR_BeginTime >=', $dayTime);
		$this->db->where('WR_BeginTime <=', $dayTime + 24 * 60 * 60);
		return $this->db->get()->num_rows();

    }

    public function get_amountBytime($dayTime) {
        $result = array();
        for ($i = 0; $i < 24; $i++) {
            $sql        = "SELECT distinct WR_PplID FROM watchrecordpeoplesample WHERE WR_BeginTime>=? AND WR_BeginTime<=?";
            $query      = $this->db->query($sql, array($dayTime + $i * 60 * 60, $dayTime + ($i + 1) * 60 * 60));
            $result[$i] = $query->num_rows();
        }

        return $result;
    }
	
    
////////////得到一天之中开机的人的id//////////////////////	
    public function get_amountBytime_2($i,$options) {
		$dayTime = strtotime('2011-03-02 00:00:00');
		$this->db->distinct();
		$this->db->select('WR_PplID');
		$this->db->from('watchrecordpeoplesample');
                $this->db->join('peoplesample', 'watchrecordpeoplesample.WR_PplID = peoplesample.Ppl_ID');

		$this->db->where('watchrecordpeoplesample.WR_BeginTime >=', $dayTime + $i * 24 * 60 * 60);
		$this->db->where('watchrecordpeoplesample.WR_BeginTime < ', $dayTime + ($i + 1) * 24 * 60 * 60);
                
                 if(count($options)>0){
                 
                    foreach ($options as $key=>$name){
                       
                        if($name!=''){
                            if($key=='Ppl_Incomenum'&&$name==7)
                                                                continue;
                            if($key=='age-low'){
                                
                            $this->db->where('peoplesample.Ppl_age >=',$name);
                                                        continue;
                        }
                        if($key=='age-high'){
                            $this->db->where('peoplesample.Ppl_age <',$name);
                            continue;
                        }
                         if($key=='job'){
                            
                                $this->db->where_in('peoplesample.Ppl_Callingnum',$name); 
                            
                           
                            continue;
                        }
                        if($key=='Ppl_Sex'){
                          
                               $this->db->where_in('peoplesample.Ppl_Sex',$name);                         
                            
                            continue;
                        }
                        $this->db->where('peoplesample.'.$key.' =', $name);
                        }
                      
                    }
                }
		$peopleIdArray = $this->db->get()->result_array();
		return $peopleIdArray;
    }
	//////////////////一天之中开机人的最早开机时间/////////////////
	public function get_open_time_by_day_and_people($i, $people_id) {
		$dayTime = strtotime('2011-03-02 00:00:00');
		$this->db->select('WR_BeginTime');
		$this->db->from('watchrecordpeoplesample');
		$this->db->where('WR_PplID =', $people_id);
		$this->db->where('WR_BeginTime >=', $dayTime + $i * 24 * 60 * 60);
		$this->db->where('WR_BeginTime < ', $dayTime + ($i + 1) * 24 * 60 * 60);
		$this->db->order_by('WR_BeginTime', 'asc')->limit(1);
		return $this->db->get()->result_array();
		
	}

    public function get_amountBydayandsex($dayTime, $gender) {		
		$this->db->distinct();
		$this->db->select('WR_PplID');

        $this->db->from('watchrecordpeoplesample');
		$this->db->join('peoplesample', 'watchrecordpeoplesample.WR_PplID = peoplesample.Ppl_ID');
		$this->db->where('watchrecordpeoplesample.WR_BeginTime >=', $dayTime);
		$this->db->where('watchrecordpeoplesample.WR_BeginTime <', $dayTime + 24 * 60 * 60);
		$this->db->where('peoplesample.Ppl_Sex =', $gender);
		return $this->db->get()->num_rows();
    }
    
     public function get_amountBydayandoptions($dayTime, $options) {		
		$this->db->distinct();
		$this->db->select('WR_PplID');

                $this->db->from('watchrecordpeoplesample');
		$this->db->join('peoplesample', 'watchrecordpeoplesample.WR_PplID = peoplesample.Ppl_ID');
		$this->db->where('watchrecordpeoplesample.WR_BeginTime >=', $dayTime);
		$this->db->where('watchrecordpeoplesample.WR_BeginTime <', $dayTime + 24 * 60 * 60);
                if(count($options)>0){
                    
                    foreach ($options as $key=>$name){
                       
                        if($name!=''){
                            if($key=='Ppl_Incomenum'&&$name==7)
                                                                continue;
                            if($key=='age-low'){
                            $this->db->where('peoplesample.Ppl_age >=',$name);
                                                        continue;
                        }
                        if($key=='age-high'){
                            $this->db->where('peoplesample.Ppl_age <',$name);
                            continue;
                        }
                        if($key=='job'){
                            
                                $this->db->where_in('peoplesample.Ppl_Callingnum',$name); 
                            
                           
                            continue;
                        }
                        if($key=='Ppl_Sex'){
                          
                               $this->db->where_in('peoplesample.Ppl_Sex',$name);                         
                            
                            continue;
                        }
                        $this->db->where('peoplesample.'.$key.' =', $name);
                        }
                      
                    }
                }
                
		return $this->db->get()->num_rows();
    }
    
    public function birthdaytoage(){
        $this->db->select('Ppl_Birthday');
        $this->db->select('Ppl_Id');
        $this->db->from('peoplesample');
        $birthdays=$this->db->get()->result_array();
        $now=  strtotime('2013-4-9');
        foreach ($birthdays as $birthday){
        $age=2013-substr($birthday['Ppl_Birthday'], 0,4) ;
        $data = array('Ppl_age' => $age);

        $this->db->where('Ppl_id', $birthday['Ppl_Id']);
        $this->db->update('peoplesample', $data); 
        }
            
    }
    public function callingtonumber(){
        $this->db->select('Ppl_Calling');
        $this->db->select('Ppl_Id');
        $this->db->from('peoplesample');
        $callings= $this->db->get()->result_array();
        $callingsobj=array("媒体/广告/咨询"=>"1"
            ,"交通/运输"=>"2",
            "农业/水产"=>"3",
            "政府机关"=>"4",
            "教育/培训"=>"5",
            "医疗/保健/制药"=>"6",
            "服务业"=>"7",
            "酒店/旅游/餐饮"=>"8",
            "金融（银行/证券/保险）"=>"9",
            "工业/地质"=>"10",
            "房地产/建筑"=>"11",
            "贸易/进出口"=>"12",
            "计算机（IT/互联网）"=>"13",
            "交通运输/邮电通信"=>"14",
            "广播电视/文化艺术"=>"15",
            "其他"=>"16");
        foreach ($callings as $calling){
            if($calling['Ppl_Calling']=="")
                $data=array('Ppl_Callingnum'=>'16');
            else {
                $data=array('Ppl_Callingnum'=>$callingsobj[$calling['Ppl_Calling']]);
            }
            
            $this->db->where('Ppl_id', $calling['Ppl_Id']);
            $this->db->update('peoplesample', $data);
        }
    }
    
        public function incometonumber(){
        $this->db->select('Ppl_IncomeGroup');
        $this->db->select('Ppl_Id');
        $this->db->from('peoplesample');
        $incomes= $this->db->get()->result_array();
        $incomesobj=array("1000元及以下"=>"1"
            ,"1001-2000元"=>"2",
            "2001-3000元"=>"3",
            "3001-5000元"=>"4",
            "5001-8000元"=>"5",
            "8000元以上"=>"6",           
            "其它"=>"-1",
            );
        foreach ($incomes as $income){
            if($income['Ppl_IncomeGroup']==""||$income['Ppl_IncomeGroup']=="无收入")
                $data=array('Ppl_Incomenum'=>'-1');
            else {
                $data=array('Ppl_Incomenum'=>$incomesobj[$income['Ppl_IncomeGroup']]);
            }
            
            $this->db->where('Ppl_id', $income['Ppl_Id']);
            $this->db->update('peoplesample', $data);
        }
    }

}
?>
