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
                $array = $lib->get_profile_course_content($userid, $courseid);
                if($array != []){
                    $returnText->return = '
                        <table>
                            <thead>
                                <tr>
                                    <th>Module Name</th>
                                    <th>Module Type</th>
                                </tr>
                            </thead>
                            <tbody>
                    ';
                    foreach($array as $arr){
                        $returnText->return .= "
                                <tr>
                                    <td>$arr[0]</td>
                                    <td>$arr[1]</td>
                                    <td>$arr[2]</td>
                                </tr>
                        ";
                    }
                    $returnText->return .= '
                            </tbody>
                        </table>
                    ';
                    $returnText->return = str_replace("  ","",$returnText->return);
                }
            }
        }
    }
}
echo(json_encode($returnText));