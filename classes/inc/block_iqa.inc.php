<?php
require_once(__DIR__.'/../../../../config.php');
require_login();
use block_iqa\lib;
$lib = new lib();
$returnText = new stdClass();
$p = 'block_iqa';

if(!isset($_SESSION['iqa_user_content'])){
    $returnText->return = false;
} else if($_SESSION['iqa_user_content']){
    if(!isset($_POST['c'])){
        $returnText->error = 'No course provided';
    } elseif(!isset($_POST['l'])){
        $returnText->error = 'No learner provided';
    } else {
        $course = $_POST['c'];
        $learner = $_POST['l'];
        if(!preg_match("/^[0-9]*$/", $course) || empty($course)){
            $returnText->error = 'Invalid course provided';
        } elseif(!preg_match("/^[0-9]*$/", $learner) || empty($learner)){
            $returnText->error = 'Invalid learner provided';
        } else {
            $content = $lib->get_learner_content($learner, $course);
            if($content != ''){
                $returnText->return = $content;
            } else {
                $returnText->return = "You aren't assigned as IQA for the learner and course provided";
            }
        }
    }
}

echo(json_encode($returnText));