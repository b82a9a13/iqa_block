<?php
require_once(__DIR__.'/../../../../config.php');
require_login();
use block_iqa\lib;
$lib = new lib();
$returnText = new stdClass();
$p = 'block_iqa';

if(!isset($_SESSION['iqa_coach_content'])){
    $returnText->return = false;
} else if($_SESSION['iqa_coach_content']){
    if(!isset($_POST['c'])){
        $returnText->error = 'No course provided';
    } else if(!isset($_POST['l'])){
        $returnText->error = 'No learner provided';
    } else {
        $courseid = $_POST['c'];
        $learner = $_POST['l'];
        if(!preg_match("/^[0-9]*$/", $courseid) || empty($courseid)){
            $returnText->error = 'Invalid course provided';
        } else if(!preg_match("/^[0-9]*$/", $learner) || empty($learner)){
            $returnText->error = 'Invalid learner provided';
        } else {
            $returnText->return = $lib->get_coach_block_content($learner, $courseid);
        }
    }
}
echo(json_encode($returnText));