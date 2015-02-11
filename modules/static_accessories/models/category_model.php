<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Category_Model extends CMS_Model{
    public function get(){
        $query = $this->db->select('image_url, title, url_page, content')
            ->from($this->cms_complete_table_name('category'))
            ->get();
        return $query->result_array();
    }
}
