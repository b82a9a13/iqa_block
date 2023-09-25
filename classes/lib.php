<?php 
/**
 * @package   block_iqa
 * @author    Robert Tyrone Cullen
 * @var stdClass $plugin
 */
namespace block_iqa;

class lib{

    //Get the current user id
    private function get_userid(): int{
        global $USER;
        return $USER->id;
    }

    //Get the course full name for a specific course id
    private function get_coursename($id): string{
        global $DB;
        return $DB->get_record_sql('SELECT fullname FROM {course} WHERE id = ?',[$id])->fullname;
    }

    //Genereate content for the current user and their highest level role
    public function get_user_content(): int{
        global $DB;
        $userid = $this->get_userid();
        //add code which gets the relevant content
        return 0;
    }

    //get content specific for the id provided as long as the current user has the correct permissions to do so.
    public function get_profile_content($id): string{
        global $DB;
        //Check if the current user has permission to access content related to the user provided. Add course to $course array for all courses they have permission for
        $userid = $this->get_userid();
        $records = $DB->get_records_sql('SELECT DISTINCT {role_assignments}.id as id, {enrol}.courseid as courseid, {role_assignments}.roleid as roleid, {user_enrolments}.userid as userid FROM {enrol}
            INNER JOIN {user_enrolments} ON {user_enrolments}.enrolid = {enrol}.id
            INNER JOIN {context} ON {context}.instanceid = {enrol}.courseid
            INNER JOIN {role_assignments} ON {role_assignments}.contextid = {context}.id
            INNER JOIN {course} ON {course}.id = {enrol}.courseid
            WHERE ({user_enrolments}.userid = {role_assignments}.userid AND {role_assignments}.roleid IN (3,4) AND {user_enrolments}.status = 0 AND {role_assignments}.userid = ?) OR
            ({user_enrolments}.userid = {role_assignments}.userid AND {role_assignments}.roleid = 5 AND {user_enrolments}.status = 0 AND {role_assignments}.userid = ?)',
        [$userid, $id]);
        $array = [];
        $courses = [];
        foreach($records as $record){
            if(($record->userid == $userid && in_array($record->roleid, [3, 4])) || ($record->userid == $id && $record->roleid == 5)){
                if(!isset($array[$record->courseid])){
                    $array[$record->courseid] = [];
                }
                if(!in_array($record->userid, $array[$record->courseid])){
                    array_push($array[$record->courseid], $record->userid);
                }
                if(count($array[$record->courseid]) == 2){
                    array_push($courses, $record->courseid);
                }
            }
        }
        //Output content if $courses is not empty
        if(empty($courses)){
            return '';
        } else {
            $content = '<div class="text-center">';
            foreach($courses as $course){
                $content .= "<button class='btn btn-primary' onclick='profile_course($course, $id)'>".$this->get_coursename($course)."</button>";
            }
            $content .= '<h2 id="iqa_block_error" class="text-danger" style="display:none;"></h2></div>';
            return $content;
        }
    }

    //get content specific for the user id and course id provided as long as the current user has permissions to do so.
    public function get_profile_content_course($id, $courseid): string{
        global $DB;
        $userid = $this->get_userid();
        $records = $DB->get_records_sql('SELECT DISTINCT {role_assignments}.id as id, {enrol}.courseid as courseid, {role_assignments}.roleid as roleid, {user_enrolments}.userid as userid FROM {enrol}
            INNER JOIN {user_enrolments} ON {user_enrolments}.enrolid = {enrol}.id
            INNER JOIN {context} ON {context}.instanceid = {enrol}.courseid
            INNER JOIN {role_assignments} ON {role_assignments}.contextid = {context}.id
            INNER JOIN {course} ON {course}.id = {enrol}.courseid
            WHERE ({user_enrolments}.userid = {role_assignments}.userid AND {role_assignments}.roleid IN (3,4) AND {user_enrolments}.status = 0 AND {role_assignments}.userid = ?) OR
            ({user_enrolments}.userid = {role_assignments}.userid AND {role_assignments}.roleid = 5 AND {user_enrolments}.status = 0 AND {role_assignments}.userid = ?) AND {enrol}.courseid = ?',
        [$userid, $id, $courseid]);
        $array = [];
        $course = false;
        foreach($records as $record){
            if(($record->userid == $userid && in_array($record->roleid, [3, 4])) || ($record->userid == $id && $record->roleid == 5)){
                if(!isset($array[$record->courseid])){
                    $array[$record->courseid] = [];
                }
                if(!in_array($record->userid, $array[$record->courseid])){
                    array_push($array[$record->courseid], $record->userid);
                }
                if(count($array[$record->courseid]) == 2){
                    $course = true;
                }
            }
        }
        if(!$course){
            return '';
        } else if($course){
            return 'Has permission';
        }
    }

    //Validate wether the current user has permissions for the data for a specific user id and course id
    public function check_users_access($id, $courseid): bool{
        global $DB;
        $userid = $this->get_userid();
        $records = $DB->get_records_sql('SELECT DISTINCT {role_assignments}.id as id, {enrol}.courseid as courseid, {role_assignments}.roleid as roleid, {user_enrolments}.userid as userid FROM {enrol}
            INNER JOIN {user_enrolments} ON {user_enrolments}.enrolid = {enrol}.id
            INNER JOIN {context} ON {context}.instanceid = {enrol}.courseid
            INNER JOIN {role_assignments} ON {role_assignments}.contextid = {context}.id
            INNER JOIN {course} ON {course}.id = {enrol}.courseid
            WHERE ({user_enrolments}.userid = {role_assignments}.userid AND {role_assignments}.roleid IN (3,4) AND {user_enrolments}.status = 0 AND {role_assignments}.userid = ?) OR
            ({user_enrolments}.userid = {role_assignments}.userid AND {role_assignments}.roleid = 5 AND {user_enrolments}.status = 0 AND {role_assignments}.userid = ?) AND {enrol}.courseid = ?',
        [$userid, $id, $courseid]);
        $array = [];
        $course = false;
        foreach($records as $record){
            if(($record->userid == $userid && in_array($record->roleid, [3, 4])) || ($record->userid == $id && $record->roleid == 5)){
                if(!isset($array[$record->courseid])){
                    $array[$record->courseid] = [];
                }
                if(!in_array($record->userid, $array[$record->courseid])){
                    array_push($array[$record->courseid], $record->userid);
                }
                if(count($array[$record->courseid]) == 2){
                    $course = true;
                }
            }
        }
        return $course;
    }

    //This is used to load content for a specifc user id and course id on the user profile page
    public function get_profile_course_content($userid, $courseid): array{
        global $DB;
        $records = $DB->get_records_sql('SELECT cm.id as id, m.name as name, cm.module as module FROM {course_modules} cm
            LEFT JOIN {modules} m ON m.id = cm.module
            WHERE cm.course = ? AND cm.completion != 0',
        [$courseid]);
        $comps = $DB->get_records_sql('SELECT coursemoduleid FROM {course_modules_completion} WHERE userid = ? AND completionstate = 1',[$userid]);
        $array = [];
        foreach($records as $record){
            $complete = false;
            foreach($comps as $comp){
                if($comp->coursemoduleid == $record->id){
                    $complete = true;
                }
            }
            if($complete){
                array_push($array, [$record->name, $record->id, 'Complete']);
            } else if(!$complete){
                array_push($array, [$record->name, $record->id, 'Incomplete']);
            }
        }
        return $array;
    }
}