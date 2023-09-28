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

    //Get the users full name for a specific user id
    private function get_user_fullname($id): string{
        global $DB;
        $record = $DB->get_record_sql('SELECT firstname, lastname FROM {user} WHERE id = ?',[$id]);
        return $record->firstname.' '.$record->lastname;
    }

    //Genereate content for the current user and their highest level role
    public function get_user_content(): string{
        global $DB;
        $userid = $this->get_userid();
        if(!$DB->record_exists('iqa_assignment', [$DB->sql_compare_text('iqaid') => $userid])){
            return '';
        } else {
            return 'You are IQA assigned';
        }
        return '';
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
        $content = '<div class="text-center">';
        if(empty($courses)){
            $records = $DB->get_records_sql('SELECT DISTINCT {role_assignments}.id as id, {enrol}.courseid as courseid, {role_assignments}.roleid as roleid, {user_enrolments}.userid as userid FROM {enrol}
                INNER JOIN {user_enrolments} ON {user_enrolments}.enrolid = {enrol}.id
                INNER JOIN {context} ON {context}.instanceid = {enrol}.courseid
                INNER JOIN {role_assignments} ON {role_assignments}.contextid = {context}.id
                INNER JOIN {course} ON {course}.id = {enrol}.courseid
                WHERE {user_enrolments}.userid = {role_assignments}.userid AND {role_assignments}.roleid = 5 AND {user_enrolments}.status = 0 AND {role_assignments}.userid = ?',
            [$userid]);
            if(empty($records)){
                return '';
            } else {
                foreach($records as $record){
                    $content .= "<button class='btn btn-primary ml-1' onclick='profile_course(".$record->courseid.", null)'>".$this->get_coursename($record->courseid)."</button>";
                }
            }
        } else {
            foreach($courses as $course){
                $content .= "<button class='btn btn-primary ml-1' onclick='profile_course($course, $id)'>".$this->get_coursename($course)."</button>";
            }
        }
        $content .= '<h2 id="iqa_block_error" class="text-danger" style="display:none;"></h2></div>';
        return $content;
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
            return $this->get_profile_course_content($id, $courseid);
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
    public function get_profile_course_content($userid, $courseid): string{
        global $DB;
        //Get course modules data
        $records = $DB->get_records_sql('SELECT cm.id as id, m.name as name, cm.module as module FROM {course_modules} cm
            LEFT JOIN {modules} m ON m.id = cm.module
            WHERE cm.course = ? AND cm.completion != 0',
        [$courseid]);
        $tmp = [];
        $info = get_fast_modinfo($courseid);
        foreach($info->cms as $inf){
            foreach($records as $record){
                if($record->id == $inf->id){
                    array_push($tmp, [$record->id, $inf->name, $record->name]);
                }
            }
        }
        //Get completion state for the modules
        $comps = $DB->get_records_sql('SELECT coursemoduleid FROM {course_modules_completion} WHERE userid = ? AND completionstate = 1',[$userid]);
        $array = [];
        foreach($tmp as $tm){
            $complete = false;
            foreach($comps as $comp){
                if($comp->coursemoduleid == $tm[0]){
                    $complete = true;
                }
            }
            if($complete){
                array_push($array, [$tm[0], $tm[1], $tm[2], 'Complete']);
            } else if(!$complete){
                array_push($array, [$tm[0], $tm[1], $tm[2], 'Incomplete']);
            }
        }
        //Generate the html for the content
        $return = '';
        if($array != []){
            $return = '
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Module Name</th>
                            <th>Module Type</th>
                            <th>Completion State</th>
                        </tr>
                    </thead>
                    <tbody>
            ';
            foreach($array as $arr){
                $return .= "
                        <tr>
                            <td><a href='./../mod/$arr[2]/view.php?id=$arr[0]' target='_blank'>$arr[1]</a></td>
                            <td>$arr[2]</td>
                ";
                $return .= ($arr[3] == 'Complete') ? "<td style='background-color:green;'></td>" : "<td style='background-color:red;'></td>";
                $return .= "
                        </tr>
                ";
            }
            $return .= '
                    </tbody>
                </table>
            ';
            $return = str_replace("  ","",$return);
        } else {
            $return = 'No data available';
        }
        return $return;
    }

    //Get profile content for a the current user and the course id provided
    public function get_profile_content_course_user($courseid): string{
        global $DB;
        $userid = $this->get_userid();
        $records = $DB->get_records_sql('SELECT DISTINCT {role_assignments}.id as id, {enrol}.courseid as courseid, {role_assignments}.roleid as roleid, {user_enrolments}.userid as userid FROM {enrol}
            INNER JOIN {user_enrolments} ON {user_enrolments}.enrolid = {enrol}.id
            INNER JOIN {context} ON {context}.instanceid = {enrol}.courseid
            INNER JOIN {role_assignments} ON {role_assignments}.contextid = {context}.id
            INNER JOIN {course} ON {course}.id = {enrol}.courseid
            WHERE {user_enrolments}.userid = {role_assignments}.userid AND {role_assignments}.roleid = 5 AND {user_enrolments}.status = 0 AND {role_assignments}.userid = ? AND {enrol}.courseid = ?',
        [$userid, $courseid]);
        if(count($records) > 0){
            return $this->get_profile_course_content($userid, $courseid);
        }
        return '';
    }

    //Get all the learners for the current course and create the html
    public function get_course_content_coach($id): string{
        global $DB;
        $userid = $this->get_userid();
        $records = $DB->get_records_sql('SELECT DISTINCT {role_assignments}.id as id, {enrol}.courseid as courseid, {role_assignments}.roleid as roleid, {user_enrolments}.userid as userid FROM {enrol}
            INNER JOIN {user_enrolments} ON {user_enrolments}.enrolid = {enrol}.id
            INNER JOIN {context} ON {context}.instanceid = {enrol}.courseid
            INNER JOIN {role_assignments} ON {role_assignments}.contextid = {context}.id
            INNER JOIN {course} ON {course}.id = {enrol}.courseid
            WHERE {user_enrolments}.userid = {role_assignments}.userid AND {role_assignments}.roleid = 5 AND {user_enrolments}.status = 0 AND {enrol}.courseid = ?',
        [$id]);
        $return = '';
        if(count($records) > 0){
            $return = '<div class="text-center">';
            foreach($records as $record){
                $return .= "<button class='btn btn-primary ml-1' onclick='iqa_course_content($id, $record->userid)'>".$this->get_user_fullname($record->userid)."</button>";
            }
            $return .= '<h2 id="iqa_block_error" class="text-danger" style="display:none;"></h2></div>';
            $return = $return;
        }
        return $return;
    }

    //Get content for a specific course id and user id for the course page
    public function get_course_content_learner($courseid, $userid): string{
        return $this->get_profile_course_content($userid, $courseid);
    }

    public function get_course_content_learner_user($id): string{
        return $this->get_profile_content_course_user($id);
    }
}