<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class WatchRecord extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('watchrecord_model');
        $this->load->helper('url');
    }

    public function view() {
        $data['watchrecords'] = $this->watchrecord_model->get_watchrecords();
        $data['wr_begin'] = $this->watchrecord_model->get_wr_begin();
        $data['title'] = "收视记录";
        $this->load->view('templates/header',$data);
        $this->load->view('watchrecord/test');
        $this->load->view('templates/footer');
    }

    public function strtotime() {
        $this->watchrecord_model->strtotime();
        // $data['watchrecords'] = $this->watchrecord_model->get_watchrecords();
        $this->load->view('watchrecord/converttest');
    }
   

    /////////产生日期//////////////////////////////////////////////////
    public function generateday($year, $month) {
        $array = array();
        for ($i = 1; $i < 30; $i++) {
            $array[] = $year . '-' . $month . '-' . ($i + 1);
        }

        return $array;
    }

    /////////////每日开机总人数////////////////////////////////////////////
    public function totalpeoplebyday() {
        $days = $this->generateday(2011, 3);
        $result = array();
        foreach ($days as $day) {
            $daytime = strtotime($day);
            $result[$day] = $this->watchrecord_model->get_amountByday($daytime);
        }
        return $result;
    }

    ////////////////////缁熻姣忔椂寮€鏈虹殑浜烘暟/////////////////////////////////////
    // public function totalpeoplebytime() {
    //     $days   = $this->generateday(2011, 3);
    //     $temp   = array();
    //     $result = array(24);
    // 
    //     foreach ($days as $day) {
    //         $daytime = strtotime($day);
    //         $temp[]  = $this->watchrecord_model->get_amountBytime($daytime);
    // 
    //     }
    // 
    //     for ($i = 0; $i < 24; $i++) {
    //         foreach ($temp as $key => $tp) {
    //             $result[$i] = isset($result[$i]) ? $result[$i] + $tp[$i] : $tp[$i];
    // 
    //         }
    //     }
    // 
    //     return $result;
    // 
    // }
    /////////////按照性别查询每日开机人数////////////////////////////////////////////
    public function totalpeoplebydayandsex() {
        $days = $this->generateday(2011, 3);
        $result = array();
        $genderArray = array('男', '女');
        foreach ($genderArray as $gender) {
            foreach ($days as $day) {
                $daytime = strtotime($day);
                $result[$gender][$day] = $this->watchrecord_model->get_amountBydayandsex($daytime, $gender);
            }
        }
        return $result;
    }

    public function totalpeoplebydayandoptions($options) {
        $days = $this->generateday(2011, 3);
        $result = array();
        foreach ($days as $day) {
            $daytime = strtotime($day);
            $result[$day] = $this->watchrecord_model->get_amountBydayandoptions($daytime, $options);
        }
        return $result;
    }

    public function byday() {
        $data['days'] = $this->totalpeoplebyday();
        $data['title'] = '每日开机人数';
        $this->load->view('templates/header', $data);
        $this->load->view('watchrecord/amountbyday');
        $this->load->view('templates/footer');
    }

    public function bytime() {
        $data['time'] = $this->totalpeoplebytime();
        $data['title'] = '每时开机人数';
        $this->load->view('templates/header', $data);
        $this->load->view('watchrecord/amountbytime');
        $this->load->view('templates/footer');
    }

    public function bygender() {
        $data['days'] = $this->totalpeoplebydayandsex();
        $data['title'] = '按性别每日开机人数';
        $this->load->view('templates/header', $data);
        $this->load->view('watchrecord/amountbyday');
        $this->load->view('templates/footer');
    }

    public function byoptions() {;
//        $incomegroup=$_GET['incomegroup'];
//        $age_low =$_GET['age-low'];
//        $age_high=$_GET['age-high'];
        $options=$_GET;
       
        $datas=$this->totalpeoplebydayandoptions($options);
        $result=array();
        foreach ($datas as $data){
            $result[]=$data;
        }
        echo  json_encode($result);
       
//        $data['title'] = '每日开机人数';
//        $this->load->view('templates/header', $data);
//        $this->load->view('watchrecord/amountbyday');
//        $this->load->view('templates/footer');
    }
    public function bytimeandoptions(){
           $options=$_GET;
           echo json_encode($this->totalpeoplebytime($options));
    }

    ////////////////////每时开机人数///////////////////////////////////////////////////////////////
    public function totalpeoplebytime($options=array()) { 
     
        $peopleIdArray = array();
        $this->load->driver('cache');
        $is_cache_people_hour_per_day_json = $this->cache->file->get('people_hour_per_day');
        if (0) {
            $people_hour_per_day_json = $is_cache_people_hour_per_day_json;
        } else {
            $is_cache_people_begin_time_by_day = $this->cache->file->get('people_begin_time_by_day');
            if (0) {
                $people_begin_time_by_day = $is_cache_people_begin_time_by_day;
            } else {
                for ($i = 1; $i < 30; $i++) {
                    $people_ids = $this->watchrecord_model->get_amountBytime_2($i,$options);//得到一天之中开机的人的id
                    foreach ($people_ids as $k => $people_id) {
                        $peopleIdArray[$i][$people_id['WR_PplID']] = $this->watchrecord_model->get_open_time_by_day_and_people($i, $people_id['WR_PplID']);
                    }
                }
                $people_begin_time_by_day = json_encode($peopleIdArray);
                $this->cache->file->save('people_begin_time_by_day', $people_begin_time_by_day, 99999999);
            }
            $people_begin_time_by_day_array = json_decode($people_begin_time_by_day, true);
            $people_hour_per_day = array();
            foreach ($people_begin_time_by_day_array as $day => $people_ids) {
                $baseDayTime = strtotime('2011-03-02 00:00:00');
                $dayTime = $baseDayTime + $day * 60 * 60 * 24;
                $people_hour_per_day[] = $this->getPeopleOpenTimeByDay($dayTime, $people_ids);
            }
            $people_hour_per_day_json = json_encode($people_hour_per_day);
            $this->cache->file->save('people_hour_per_day', $people_hour_per_day_json, 99999999);
        }
        $people_hour_per_day_array = json_decode($people_hour_per_day_json, true);
        $people_hour_array = array();
        for ($i = 0; $i < 30; $i++) {
            for ($j = 0; $j < 24; $j++) {
                $people_hour_per_day_array[$i][$j] = isset($people_hour_per_day_array[$i][$j]) ? $people_hour_per_day_array[$i][$j] : 0;
                $people_hour_array[$j] = isset($people_hour_array[$j]) ? $people_hour_array[$j] + $people_hour_per_day_array[$i][$j] : $people_hour_per_day_array[$i][$j];
            }
        }
      
        
        return $people_hour_array;
    }

    public function getPeopleOpenTimeByDay($dayTime, $docs) {
        $result = array();
        foreach ($docs as $k => $doc) {
            $hour = floor(($doc[0]['WR_BeginTime'] - $dayTime) / (60 * 60 * 1.0));
            $result[$hour] = isset($result[$hour]) ? $result[$hour] + 1 : 1;
            ksort($result);
        }
        return $result;
    }
    
 
    public function comparebyday(){
        $data['title']="每日开机对比";
        $this->load->view('templates/header',$data);
        $this->load->view('watchrecord/comparebyday');
        $this->load->view('templates/footer');
    }
    public function comparebytime(){
        $data['title']="每时开机对比";
        $this->load->view('templates/header',$data);
        $this->load->view('watchrecord/comparebytime');
        $this->load->view('templates/footer');
    }
    public function comparebydayandoptions(){
         $options=$_GET;
         $job=array();
         $Ppl_Sex=array();
         $results=array();
         $finalresults=array();
         foreach ($options as $key=>$option){
         
                    $finaloption=array();
                    foreach ($option as $opt){
                        if(!strrpos($opt['name'], '[]')){
                            $finaloption[$opt['name']]=$opt['value']; 
                        }
                        if (strrpos($opt['name'], 'Ppl_Sex')===0) {
                            
                           $Ppl_Sex[]=$opt['value'];
                           $finaloption['Ppl_Sex']=$Ppl_Sex;
                        }               
                        if(strrpos($opt['name'],'job')===0){
                            $job[]=$opt['value'];
                            $finaloption['job']=$job;
                        }
                    }
                  
                $datas=$this->totalpeoplebydayandoptions($finaloption);
                $result=array();
            
                foreach ($datas as $data){
                 $result[]=$data;
                }   
               $results["data"]=$result;
               $results["name"]="曲线".($key+1);
               $finalresults[]=$results;
               
            
         } 



         echo json_encode($finalresults);
        
         
        
        
    }
     public function comparebytimeandoptions(){
             $options=$_GET;
         $job=array();
         $Ppl_Sex=array();
         $results=array();
         $finalresults=array();
         foreach ($options as $key=>$option){
         
                    $finaloption=array();
                    foreach ($option as $opt){
                        if(!strrpos($opt['name'], '[]')){
                            $finaloption[$opt['name']]=$opt['value']; 
                        }
                        if (strrpos($opt['name'], 'Ppl_Sex')===0) {
                            
                           $Ppl_Sex[]=$opt['value'];
                           $finaloption['Ppl_Sex']=$Ppl_Sex;
                        }               
                        if(strrpos($opt['name'],'job')===0){
                            $job[]=$opt['value'];
                            $finaloption['job']=$job;
                        }
                    }
                
                $datas=$this->totalpeoplebytime($finaloption);
                $result=array();
            
                foreach ($datas as $data){
                 $result[]=$data;
                }   
               $results["data"]=$result;
               $results["name"]="曲线".($key+1);
               $finalresults[]=$results;            
         } 
         echo json_encode($finalresults);     
         }

    public function birthdaytoage(){
        $this->watchrecord_model->birthdaytoage();
    }
    public function  callingtonum(){
        $this->watchrecord_model->callingtonumber();
    }
     public function  incometonum(){
        $this->watchrecord_model->incometonumber();
    }
    
}


