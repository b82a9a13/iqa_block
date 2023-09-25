<?php
use block_iqa\lib;
class block_iqa extends block_base{
    
    public function init(){
        $this->title = 'IQA';
    }
    
    public function get_content(){
        $this->content = new stdClass();
        $this->content->text = '';
        $lib = new lib();
        if(isset($_GET['id'])){
            $id = $_GET['id'];
            if(!preg_match("/^[0-9]*$/", $id) || empty($id)){
                return;
            } else {
                if(!isset($_GET['course'])){
                    $content = $lib->get_profile_content($id);
                    if($content != ''){
                        $this->content->text .= $content.'<div id="iqa_course_content"></div><script src="./../blocks/iqa/amd/src/block_iqa_profile.js"></script>';
                        $_SESSION['iqa_profile_content'] = true;
                    }
                } else {
                    //Need to add to the view.php page for this to work
                    $courseid = $_GET['course'];
                    if(!preg_match("/^[0-9]*$/", $courseid) || empty($courseid)){
                        return;
                    } else {
                        $this->content->text .= $lib->get_profile_content_course($id, $courseid);
                    }
                }
            }
        } else {
            $this->content->text .= $lib->get_user_content();
        }
    }
}