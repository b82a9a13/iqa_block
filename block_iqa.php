<?php
use block_iqa\lib;
class block_iqa extends block_base{
    public function init(){
        $this->title = 'IQA';
    }
    public function get_content(){
        $this->content = new stdClass();
        $this->content->text = '';
    }
}