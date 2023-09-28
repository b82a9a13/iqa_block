<?php
require_once(__DIR__.'/../../../../config.php');
require_login();
use block_iqa\lib;
$lib = new lib();
$returnText = new stdClass();
$p = 'block_iqa';

if(!isset($_SESSION['iqa_profile_content'])){
    $returnText->return = false;
} else if($_SESSION['iqa_profile_content']){
    if(!isset($_POST['c'])){
        $returnText->error = 'No course provided';
    } else {
        $courseid = $_POST['c'];
        if(!preg_match("/^[0-9]*$/", $courseid) || empty($courseid)){
            $returnText->error = 'Invalid course provided';
        } else {
            $context = context_course::instance($courseid);
            if(!has_capability('block/iqa:learner', $context)){
                $returnText->error = 'You are not a learner in the course provided';
            } else {
                require_capability('block/iqa:learner', $context);
                $returnText->return = $lib->get_profile_content_course_user($courseid);
            }
        }
    }
}
echo(json_encode($returnText));