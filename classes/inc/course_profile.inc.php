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
    } else if(!isset($_POST['u'])){
        $returnText->error = 'No user provided';
    } else {
        $courseid = $_POST['c'];
        $userid = $_POST['u'];
        if(!preg_match("/^[0-9]*$/", $courseid) || empty($courseid)){
            $returnText->error = 'Invalid course provided';
        } else if(!preg_match("/^[0-9]*$/", $userid) || empty($userid)){
            $returnText->error = 'Invalid user provided';
        } else {
            if(!$lib->check_users_access($userid, $courseid)){
                $returnText->error = "You don't have permission to load the data";
            } else {
                $context = context_course::instance($courseid);
                if(!has_capability('block/iqa:coach', $context)){
                    $returnText->error = "You aren't a coach in the course provided";
                } else {
                    require_capability('block/iqa:coach', $context);
                    $returnText->return = $lib->get_profile_course_content($userid, $courseid);
                }
            }
        }
    }
}
echo(json_encode($returnText));