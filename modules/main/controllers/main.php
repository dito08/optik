<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * The Main Controller of No-CMS
 *
 * @author gofrendi
 */
class Main extends CMS_Controller
{
    private function unique_field_name($field_name)
    {
        return 's'.substr(md5($field_name),0,8); //This s is because is better for a string to begin with a letter and not with a number
    }

    private function __random_string($length=10)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

        $size = strlen( $chars );
        $str = '';
        for( $i = 0; $i < $length; $i++ ){
            $str .= $chars[ rand( 0, $size - 1 ) ];
        }
        return $str;
    }

    protected function upload($upload_path, $input_file_name = 'userfile', $submit_name = 'upload')
    {
        $data = array(
            "uploading" => TRUE,
            "success" => FALSE,
            "message" => ""
        );
        if (isset($_POST[$submit_name])) {
            $config['upload_path']   = $upload_path;
            $config['allowed_types'] = 'zip';
            $config['max_size']      = 8 * 1024;
            $config['overwrite']     = TRUE;
            $this->load->library('upload', $config);
            if (!$this->upload->do_upload($input_file_name)) {
                $data['uploading'] = TRUE;
                $data['success']   = FALSE;
                $data['message']   = $this->upload->display_errors();
            } else {
                $this->load->library('unzip');
                $upload_data = $this->upload->data();
                $this->unzip->extract($upload_data['full_path']);
                unlink($upload_data['full_path']);
                $data['uploading'] = TRUE;
                $data['success']   = TRUE;
                $data['message']   = '';
            }
        } else {
            $data['uploading'] = FALSE;
            $data['success']   = FALSE;
            $data['message']   = '';
        }
        return $data;
    }

    protected function recurse_copy($src,$dst) {
        $dir = opendir($src);
        @mkdir($dst);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    $this->recurse_copy($src . '/' . $file,$dst . '/' . $file);
                }
                else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    protected function rrmdir($dir) {
        foreach(glob($dir . '/*') as $file) {
            if(is_dir($file)){
                $this->rrmdir($file);
            } else {
                unlink($file);
            }
        }
        unlink($dir.'/.htaccess');
        rmdir($dir);
    }

    public function module_management()
    {
        $this->cms_guard_page('main_module_management');

        if(isset($_FILES['userfile'])){
            // upload new module
            $directory = basename($_FILES['userfile']['name'],'.zip');

            // subsite_auth
            $subsite_auth_file = FCPATH.'modules/'.$directory.'/subsite_auth.php';
            $backup_subsite_auth_file = FCPATH.'modules/'.$directory.'_subsite_auth.php';
            $subsite_backup = FALSE;
            if(file_exists($subsite_auth_file)){
                copy($subsite_auth_file, $backup_subsite_auth_file);
                $subsite_backup = TRUE;
            }
            // config
            $config_dir = FCPATH.'modules/'.$directory.'/config';
            $backup_config_dir = FCPATH.'modules/'.$directory.'_config';
            $config_backup = FALSE;
            if(file_exists($config_dir) && is_dir($config_dir)){
                $this->recurse_copy($config_dir, $backup_config_dir);
                $config_backup = TRUE;
            }
        }


        $data['upload'] = $this->upload(FCPATH.'modules/', 'userfile', 'upload');
        if($data['upload']['success']){
            if($subsite_backup){
                copy($backup_subsite_auth_file, $subsite_auth_file);
                unlink($backup_subsite_auth_file);
            }
            if($config_backup){
                $this->recurse_copy($backup_config_dir, $config_dir);
                $this->rrmdir($backup_config_dir);
            }
        }

        // show the view
        $modules = $this->cms_get_module_list();
        for($i=0; $i<count($modules); $i++){
            $module = $modules[$i];
            $module_path = $module['module_path'];
        }
        $data['modules'] = $modules;
        $data['upload_new_module_caption'] = $this->cms_lang('Upload New Module');
        $this->view('main/main_module_management', $data, 'main_module_management');
    }

    public function change_theme($theme = NULL)
    {
        $this->cms_guard_page('main_change_theme');
        if(isset($_FILES['userfile'])){
            // upload new module
            $directory = basename($_FILES['userfile']['name'],'.zip');

            // subsite_auth
            $subsite_auth_file = FCPATH.'themes'.$directory.'/subsite_auth.php';
            $backup_subsite_auth_file = FCPATH.'themes/'.$directory.'_subsite_auth.php';
            $subsite_backup = FALSE;
            if(file_exists($subsite_auth_file)){
                copy($subsite_auth_file, $backup_subsite_auth_file);
                $subsite_backup = TRUE;
            }
        }
        // upload new theme
        $data['upload'] = $this->upload('./themes/', 'userfile', 'upload');

        if($data['upload']['success']){
            if($subsite_backup){
                copy($backup_subsite_auth_file, $subsite_auth_file);
                unlink($backup_subsite_auth_file);
            }
        }

        // show the view
        if (isset($theme)) {
            $this->cms_set_config('site_theme', $theme);
            redirect('main/change_theme','refresh');
        } else {
            $data['themes'] = $this->cms_get_theme_list();
            $data['upload_new_theme_caption'] = $this->cms_lang('Upload New Theme');
            $this->view('main/main_change_theme', $data, 'main_change_theme');
        }
    }

    //this is used for the real static page which doesn't has any URL in navigation management
    public function static_page($navigation_name)
    {
        $this->view('CMS_View', NULL, $navigation_name);
    }

    public function login()
    {
        $this->cms_guard_page('main_login');
        // Is registration allowed
        $allow_register = $this->cms_allow_navigate('main_register');
        //retrieve old_url from userdata if exists
        $this->load->library('session');
        $old_url = $this->session->userdata('cms_old_url');

        //get user input
        $identity = $this->input->post('identity');
        $password = $this->input->post('password');

        //set validation rule
        $this->form_validation->set_rules('identity', 'Identity', 'required|xss_clean');
        $this->form_validation->set_rules('password', 'Password', 'required|xss_clean');

        if ($this->form_validation->run()) {
            if ($this->cms_do_login($identity, $password)) {
                //if old_url exist, redirect to old_url, else redirect to main/index
                if (isset($old_url)) {
                    $this->session->set_userdata('cms_old_url', NULL);
                    // seek for the closest url that exist in navigation table to avoid something like manage_x/index/edit/1/error to be appeared
                    $old_url_part = explode('/', $old_url);
                    while(count($old_url_part)>0){
                        $query = $this->db->select('url')
                            ->from(cms_table_name('main_navigation'))
                            ->like('url', implode('/', $old_url_part))
                            ->get();
                        if($query->num_rows()>0){
                            $row = $query->row();
                            $old_url = $row->url;
                            break;
                        }else{
                            $new_old_url_part = array();
                            for($i=0; $i<count($old_url_part)-1; $i++){
                                $new_old_url_part[] = $old_url_part[$i];
                            }
                            $old_url_part = $new_old_url_part;
                        }
                    }
                    redirect($old_url,'refresh');
                } else {
                    redirect('','refresh');
                }
            } else {
                //view login again
                $data = array(
                    "identity" => $identity,
                    "message" => '{{ language:Error }}: {{ language:Login Failed }}',
                    "providers" => $this->cms_third_party_providers(),
                    "login_caption" => $this->cms_lang("Login"),
                    "register_caption" => $this->cms_lang("Register"),
                    "allow_register"=> $allow_register,
                );
                $this->view('main/main_login', $data, 'main_login');
            }
        } else {
            //view login again
            $data = array(
                "identity" => $identity,
                "message" => '',
                "providers" => $this->cms_third_party_providers(),
                "login_caption" => $this->cms_lang("Login"),
                "register_caption" => $this->cms_lang("Register"),
                "allow_register" => $allow_register,
            );
            $this->view('main/main_login', $data, 'main_login');
        }
    }

    public function activate($activation_code)
    {
        $this->cms_activate_account($activation_code);
        redirect('','refresh');
    }

    public function forgot($activation_code = NULL)
    {
        $this->cms_guard_page('main_forgot');
        if (isset($activation_code)) {
            //get user input
            $password = $this->input->post('password');
            //set validation rule
            $this->form_validation->set_rules('password', 'Password', 'required|xss_clean|matches[confirm_password]');
            $this->form_validation->set_rules('confirm_password', 'Password Confirmation', 'required|xss_clean');

            if ($this->form_validation->run()) {
                if ($this->cms_valid_activation_code($activation_code)) {
                    $this->cms_activate_account($activation_code, $password);
                    redirect('','refresh');
                } else {
                    redirect('main/forgot','refresh');
                }
            } else {
                $data = array(
                    "activation_code" => $activation_code,
                    "change_caption" => $this->cms_lang('Change'),
                );
                $this->view('main/main_forgot_change_password', $data, 'main_forgot');
            }
        } else {
            //get user input
            $identity = $this->input->post('identity');

            //set validation rule
            $this->form_validation->set_rules('identity', 'Identity', 'required|xss_clean');

            if ($this->form_validation->run()) {
                if ($this->cms_generate_activation_code($identity, TRUE, 'FORGOT')) {
                    redirect('','refresh');
                } else {
                    $data = array(
                        "identity" => $identity,
                        "send_activation_code_caption"=> $this->cms_lang('Send activation code to my email'),
                    );
                    $this->view('main/main_forgot_fill_identity', $data, 'main_forgot');
                }
            } else {
                $data = array(
                    "identity" => $identity,
                    "send_activation_code_caption"=> $this->cms_lang('Send activation code to my email'),
                );
                $this->view('main/main_forgot_fill_identity', $data, 'main_forgot');
            }
        }
    }

    public function register()
    {
        $this->cms_guard_page('main_register');

        // the honey_pot, every fake input should be empty
        $honey_pot_pass = (strlen($this->input->post('user_name', ''))==0) &&
            (strlen($this->input->post('email', ''))==0) &&
            (strlen($this->input->post('first_name', ''))==0) &&
            (strlen($this->input->post('last_name', ''))==0) &&
            (strlen($this->input->post('password', ''))==0) &&
            (strlen($this->input->post('confirm_password'))==0);
        if(!$honey_pot_pass){
            show_404();
            die();
        }

        $previous_secret_code = $this->session->userdata('__main_registration_secret_code');
        if($previous_secret_code === NULL){
            $previous_secret_code = $this->__random_string();
        }
        //get user input
        //$user_name        = $this->input->post($previous_secret_code.'user_name');
        $email            = $this->input->post($previous_secret_code.'email');
        $first_name       = $this->input->post($previous_secret_code.'first_name');
        $last_name        = $this->input->post($previous_secret_code.'last_name');
        $password         = $this->input->post($previous_secret_code.'password');
        $confirm_password = $this->input->post($previous_secret_code.'confirm_password');

        //set validation rule
        //$this->form_validation->set_rules($previous_secret_code.'user_name', 'User Name', 'required|xss_clean');
        $this->form_validation->set_rules($previous_secret_code.'email', 'E mail', 'required|xss_clean|valid_email');
        $this->form_validation->set_rules($previous_secret_code.'first_name', 'First Name', 'required|xss_clean');
        $this->form_validation->set_rules($previous_secret_code.'last_name', 'Last Name', 'required|xss_clean');
        $this->form_validation->set_rules($previous_secret_code.'password', 'Password', 'required|xss_clean|matches['.$previous_secret_code.'confirm_password]');
        $this->form_validation->set_rules($previous_secret_code.'confirm_password', 'Password Confirmation', 'required|xss_clean');

        // generate new secret code
        $secret_code = $this->__random_string();
        $this->session->set_userdata('__main_registration_secret_code', $secret_code);
        if ($this->form_validation->run() && !$this->cms_is_user_exists($email)) {
            $this->cms_do_register($email, $email, $first_name, $last_name, $password);
            redirect('main/finish_registration','refresh');
        } else {
            $data = array(
                "email" => $email,
                "first_name" => $first_name,
                "last_name" => $last_name,
                "register_caption" => $this->cms_lang('Register'),
                "secret_code" => $secret_code,
            );
            $this->view('main/main_register', $data, 'main_register');
        }
    }

    public function finish_registration(){
        $this->view('main/finish_register');
    }

    public function check_registration()
    {
        if ($this->input->is_ajax_request()) {
            //$user_name = $this->input->post('user_name');
            $email = $this->input->post('email');
            //$user_name_exists    = $this->cms_is_user_exists($user_name);
            $email_exists        = $this->cms_is_user_exists($email);
            $valid_email = preg_match('/@.+\./', $email);
            $message   = "";
            $error = FALSE;
            /*
            if ($user_name == "") {
                $message = $this->cms_lang("Username is empty");
                $error = TRUE;
            } else if ($user_name_exists) {
                $message = $this->cms_lang("Username already exists");
                $error = TRUE;
            } else 
            */
            if (!$valid_email){
                $message = $this->cms_lang("Invalid email address");
                $error = TRUE;
            } else if ($email_exists){
                $message = $this->cms_lang("Email already used");
                $error = TRUE;
            }
            $data = array(
                //"exists" => $user_name_exists || $email_exists,
                "exists" => $email_exists,
                "error" => $error,
                "message" => $message
            );
            $this->cms_show_json($data);
        }
    }

    public function get_layout($theme=''){
        if($this->input->is_ajax_request()){
            if($theme == ''){
                $theme = $this->cms_get_config('site_theme');
            }
            $layout_list = array('');
            $this->load->helper('directory');
            $files = directory_map('themes/'.$theme.'/views/layouts/', 1);
            sort($files);
            foreach($files as $file){
                if(is_dir('themes/'.$theme.'/views/layouts/'.$file)){
                    continue;
                }
                $file = str_ireplace('.php', '', $file);
                $layout_list[] = $file;
            }
            $this->cms_show_json($layout_list);
        }
    }

    public function check_change_profile()
    {
        if ($this->input->is_ajax_request()) {
            //$user_name = $this->input->post('user_name');
            $email = $this->input->post('email');
            //$user_name_exists    = $this->cms_is_user_exists($user_name) && $user_name != $this->cms_user_name();
            $email_exists        = $this->cms_is_user_exists($email) && $email != $this->cms_user_email();
            $valid_email = preg_match('/@.+\./', $email);
            $message   = "";
            $error = FALSE;
            if (!$valid_email){
                $message = $this->cms_lang("Invalid email address");
                $error = TRUE;
            } else if ($email_exists){
                $message = $this->cms_lang("Email already used");
                $error = TRUE;
            }
            $data = array(
                "exists" => $email_exists,
                "error" => $error,
                "message" => $message
            );
            $this->cms_show_json($data);
        }
    }

    public function change_profile()
    {

        $this->cms_guard_page('main_change_profile');

        $SQL   = "SELECT user_name, email, first_name, last_name FROM ".cms_table_name('main_user')." WHERE user_id = " . $this->cms_user_id();
        $query = $this->db->query($SQL);
        $row   = $query->row();

        //get user input
        //$user_name        = $this->input->post('user_name');
        $email            = $this->input->post('email');
        $first_name       = $this->input->post('first_name');
        $last_name        = $this->input->post('last_name');
        $change_password  = $this->input->post('change_password');
        $password         = $this->input->post('password');
        $confirm_password = $this->input->post('confirm_password');

        if (!$change_password) {
            $password = NULL;
        }

        $user_name = $row->user_name;

        if (!$email)
            $email = $row->email;
        if (!$first_name)
            $first_name = $row->first_name;
        if (!$last_name)
            $last_name = $row->last_name;

        //set validation rule
        $this->form_validation->set_rules('email', 'E mail', 'required|xss_clean|valid_email');
        $this->form_validation->set_rules('first_name', 'First Name', 'required|xss_clean');
        $this->form_validation->set_rules('last_name', 'Last Name', 'required|xss_clean');
        $this->form_validation->set_rules('password', 'Password', 'xss_clean|matches[confirm_password]');
        $this->form_validation->set_rules('confirm_password', 'Password Confirmation', 'xss_clean');
        
        if ($this->form_validation->run()) {
            $this->cms_do_change_profile($user_name, $email, $first_name, $last_name, $password);
            redirect('main/change_profile','refresh');
        } else {
            $data = array(
                "current" => 'account_information',
                "email" => $email,
                "first_name" => $first_name,
                "last_name"  => $last_name,
                "change_profile_caption" => $this->cms_lang('Change Profile'),
            );
            $this->view('main/main_change_profile', $data, 'main_change_profile');
        }
    }

    public function account_dashboard()
    {
        $this->cms_guard_page('account_dashboard');

        $SQL   = "
                SELECT A.user_name, U.first_name AS first, U.last_name AS last, U.email, A.*  
                FROM ".cms_table_name('main_user'). " AS U 
                JOIN ".cms_table_name('main_user_address'). " AS A ON U.user_name = A.user_name
                WHERE U.user_id = " . $this->cms_user_id();
        $query = $this->db->query($SQL);
        $row   = $query->row();

        $data = array(
            "current"                   => 'account_dashboard',
            "email"                     => $row->email,
            "first_name"                => $row->first,
            "last_name"                 => $row->last,
            "address_first_name"        => $row->first_name,
            "address_last_name"         => $row->last_name,
            "address_street"            => $row->address,
            "address_city"              => $row->city,
            "address_state"             => $row->state,
            "address_postal"            => $row->postal_code,
            "address_country"           => $row->country,
            "change_profile_caption"    => $this->cms_lang('Change Profile'),
        );
        $this->view('main/main_account_dashboard', $data, 'account_dashboard');
    }

    public function account_address()
    {
        $this->cms_guard_page('account_address');

        $SQL   = "
        SELECT A.*, B.kecamatan, B.kota 
        FROM ".cms_table_name('main_user_address A')." 
        LEFT JOIN ".cms_table_name('shipping_jne B')." ON B.noongkir = A.city
        WHERE A.user_name = '". $this->cms_user_name()."' LIMIT 1";

        $query = $this->db->query($SQL);
        $row   = $query->row();

        //get user input
        //$user_name          = $this->input->post('user_name');
        $first_name         = $this->input->post('first_name');
        $last_name          = $this->input->post('last_name');
        $company            = $this->input->post('company');
        $handphone          = $this->input->post('handphone');
        $telephone          = $this->input->post('telephone');
        $address            = $this->input->post('address');
        $city_code          = $this->input->post('city_code');
        $city               = $this->input->post('city');
        $state              = $this->input->post('state');
        $postal_code        = $this->input->post('postal_code');
        $country            = $this->input->post('country');

        $user_name = $this->cms_user_name();
        if (!$first_name)
            $first_name = $row->first_name;
        if (!$last_name)
            $last_name = $row->last_name;
        if (!$company)
            $company = $row->company;
        if (!$handphone)
            $handphone = $row->handphone;
        if (!$telephone)
            $telephone = $row->telephone;
        if (!$address)
            $address = $row->address;
        if (!$city_code)
            $city_code = $row->city;
        if (!$city)
            $city = $row->kecamatan.', '.$row->kota;
        if (!$state)
            $state = $row->state;
        if (!$postal_code)
            $postal_code = $row->postal_code;
        if (!$country)
            $country = $row->country;

        //set validation rule
        $this->form_validation->set_rules('first_name', 'First Name', 'required|xss_clean');
        $this->form_validation->set_rules('last_name', 'Last Name', 'required|xss_clean');
        $this->form_validation->set_rules('company', 'Company', 'xss_clean');
        $this->form_validation->set_rules('handphone', 'Handphone', 'numeric|xss_clean');
        $this->form_validation->set_rules('telephone', 'Telephone', 'numeric|xss_clean');
        $this->form_validation->set_rules('address', 'Address', 'xss_clean');
        $this->form_validation->set_rules('city_code', 'City', 'xss_clean');
        $this->form_validation->set_rules('state', 'State', 'xss_clean');
        $this->form_validation->set_rules('postal_code', 'Postal Code', 'min_length[5]|max_length[5]|numeric|xss_clean');
        $this->form_validation->set_rules('country', 'Country', 'xss_clean');
        if ($this->form_validation->run()) {
            $this->cms_do_change_address($user_name, $first_name, $last_name, $company, $handphone, $telephone, $address, $city_code, $state, $postal_code, $country);
            redirect('main/account_address','refresh');
        }else {
            $data = array(
                "current"       => 'account_address',
                "first_name"    => $first_name,
                "last_name"     => $last_name,
                "company"       => $company,
                "handphone"     => $handphone,
                "telephone"     => $telephone,
                "address"       => $address,
                "city_code"     => $city_code,
                "city"          => $city,
                "state"         => $state,
                "postal_code"   => $postal_code,
                "country"       => $country,
            );
            $this->view('main/main_account_address', $data, 'account_address');
        }
    }

    public function logout()
    {
        $this->cms_do_logout();
        redirect('','refresh');
    }

    public function index()
    {
        $this->cms_guard_page('main_index');
        $data = array(
            //"submenu_screen" => $this->cms_submenu_screen(NULL)
        );
        $this->view('main/main_index', $data, 'main_index');
    }

    public function management()
    {
        $this->cms_guard_page('main_management');
        $data = array(
            "submenu_screen" => $this->cms_submenu_screen('main_management')
        );
        $this->view('main/main_management', $data, 'main_management');
    }

    public function language($language = NULL)
    {
        $this->cms_guard_page('main_language');
        if (isset($language)) {
            $this->cms_language($language);
            redirect('','refresh');
        } else {
            $data = array(
                "language_list" => $this->cms_language_list()
            );
            $this->view('main/main_language', $data, 'main_language');
        }
    }

    // AUTHORIZATION ===========================================================
    public function authorization()
    {
        $crud = $this->new_crud();
        $crud->unset_jquery();

        $crud->set_table(cms_table_name('main_authorization'));
        $crud->set_subject('Authorization');

        $crud->columns('authorization_id', 'authorization_name', 'description');
        $crud->display_as('authorization_id', 'Code')->display_as('authorization_name', 'Name')->display_as('description', 'Description');

        $crud->unset_texteditor('description');

        $crud->set_subject('Authorization List');

        $crud->unset_add();
        $crud->unset_delete();
        $crud->unset_edit();
        $crud->required_fields('authorization_name');
        $crud->unique_fields('authorization_name');
        $crud->unset_read();

        $crud->set_language($this->cms_language());

        $output = $crud->render();

        $this->view('grocery_CRUD', $output);
    }

    // USER ====================================================================
    public function user()
    {
        $this->cms_guard_page('main_user_management');
        $crud = $this->new_crud();
        $crud->unset_jquery();

        $crud->set_table(cms_table_name('main_user'));
        $crud->set_subject($this->cms_lang('User'));

        $crud->required_fields('user_name','password');
        $crud->unique_fields('user_name');
        $crud->unset_read();

        $crud->columns('email', 'first_name', 'active', 'groups');
        $crud->edit_fields('email', 'first_name', 'active', 'groups');
        $crud->add_fields('email', 'password', 'first_name', 'active', 'groups');
        $crud->field_type('active', 'true_false');

        $crud->display_as('email', $this->cms_lang('Email'))
            ->display_as('first_name', $this->cms_lang('Real Name'))
            ->display_as('active', $this->cms_lang('Active'))
            ->display_as('groups', $this->cms_lang('Groups'));

        $crud->set_relation_n_n('groups', cms_table_name('main_group_user'), cms_table_name('main_group'), 'user_id', 'group_id', 'group_name');
        $crud->callback_before_insert(array(
            $this,
            'before_insert_user'
        ));
        $crud->callback_before_delete(array(
            $this,
            'before_delete_user'
        ));

        if ($crud->getState() == 'edit') {
            $state_info  = $crud->getStateInfo();
            $primary_key = $state_info->primary_key;
            if ($primary_key == $this->cms_user_id() || $primary_key == 1) {
                $crud->callback_edit_field('active', array(
                    $this,
                    'read_only_user_active'
                ));
            }
        }

        $crud->set_lang_string('delete_error_message', 'You cannot delete super admin user or your own account');

        $crud->set_language($this->cms_language());

        $output = $crud->render();

        $this->view('main/main_user', $output, 'main_user_management');
    }

    public function read_only_user_active($value, $row)
    {
        $input   = '<input name="active" value="' . $value . '" type="hidden" />';
        $caption = $value == 0 ? 'Inactive' : 'Active';
        return $input . $caption;
    }

    public function before_insert_user($post_array)
    {
        $post_array['password'] = md5($post_array['password']);
        return $post_array;
    }

    public function before_delete_user($primary_key, $post_array)
    {
        //The super admin user cannot be deleted, a user cannot delete his/her own account
        if (($primary_key == 1) || ($primary_key == $this->cms_user_id())) {
            return false;
        }
        return $post_array;
    }

    // GROUP ===================================================================
    public function group()
    {
        $this->cms_guard_page('main_group_management');
        $crud = $this->new_crud();
        $crud->unset_jquery();

        $crud->set_table(cms_table_name('main_group'));
        $crud->set_subject($this->cms_lang('User Group'));

        $crud->required_fields('group_name');
        $crud->unique_fields('group_name');
        $crud->unset_read();

        $crud->columns('group_name', 'description');
        $crud->edit_fields('group_name', 'description', 'users', 'navigations', 'privileges');
        $crud->add_fields('group_name', 'description', 'users', 'navigations', 'privileges');
        $crud->display_as('group_name', $this->cms_lang('Group'))
            ->display_as('description', $this->cms_lang('Description'))
            ->display_as('users', $this->cms_lang('Users '))
            ->display_as('navigations', $this->cms_lang('Navigations'))
            ->display_as('privileges', $this->cms_lang('Privileges'));


        $crud->set_relation_n_n('users', cms_table_name('main_group_user'), cms_table_name('main_user'), 'group_id', 'user_id', 'user_name');
        $crud->set_relation_n_n('navigations', cms_table_name('main_group_navigation'), cms_table_name('main_navigation'), 'group_id', 'navigation_id', 'navigation_name');
        $crud->set_relation_n_n('privileges', cms_table_name('main_group_privilege'), cms_table_name('main_privilege'), 'group_id', 'privilege_id', 'privilege_name');
        $crud->callback_before_delete(array(
            $this,
            'before_delete_group'
        ));

        $crud->unset_texteditor('description');


        $crud->set_lang_string('delete_error_message', $this->cms_lang('You cannot delete admin group or group which is not empty, please empty the group first'));

        $crud->set_language($this->cms_language());

        $output = $crud->render();

        $this->view('main/main_group', $output, 'main_group_management');
    }

    public function before_delete_group($primary_key)
    {
        $SQL   = "SELECT user_id FROM ".cms_table_name('main_group_user')." WHERE group_id =" . $primary_key . ";";
        $query = $this->db->query($SQL);
        $count = $query->num_rows();

        /* Can only delete group with no user. Admin group cannot be deleted */
        if ($primary_key == 1 || $count > 0) {
            return false;
        }
        return $post_array;
    }

    // NAVIGATION ==============================================================
    public function navigation($parent_id=NULL)
    {
        $this->cms_guard_page('main_navigation_management');
        $crud = $this->new_crud();

        $crud->unset_jquery();

        $crud->set_table(cms_table_name('main_navigation'));
        $crud->set_subject($this->cms_lang('Navigation (Page)'));

        $crud->required_fields('navigation_name', 'title');
        $crud->unique_fields('navigation_name', 'title', 'url');
        $crud->unset_read();

        $crud->columns('navigation_name', 'navigation_child', 'title', 'active');
        $crud->edit_fields('navigation_name', 'parent_id', 'title', 'bootstrap_glyph', 'page_title', 'page_keyword', 'description', 'active', 'only_content', 'is_static', 'static_content', 'url','notif_url', 'default_theme', 'default_layout', 'authorization_id', 'groups', 'index');
        $crud->add_fields('navigation_name', 'parent_id', 'title', 'bootstrap_glyph', 'page_title', 'page_keyword', 'description', 'active', 'only_content', 'is_static', 'static_content', 'url','notif_url', 'default_theme', 'default_layout', 'authorization_id', 'groups', 'index');
        $crud->field_type('active', 'true_false');
        $crud->field_type('is_static', 'true_false');
        // get themes to give options for default_theme field
        $themes     = $this->cms_get_theme_list();
        $theme_path = array();
        foreach ($themes as $theme) {
            $theme_path[] = $theme['path'];
        }
        $crud->field_type('default_theme', 'enum', $theme_path);
        $crud->display_as('navigation_name', $this->cms_lang('Navigation Code'))
            ->display_as('is_root', $this->cms_lang('Is Root'))
            ->display_as('navigation_child', $this->cms_lang('Children'))
            ->display_as('parent_id', $this->cms_lang('Parent'))
            ->display_as('title', $this->cms_lang('Navigation Title (What visitor see)'))
            ->display_as('page_title', $this->cms_lang('Page Title'))
            ->display_as('page_keyword', $this->cms_lang('Page Keyword (Comma Separated)'))
            ->display_as('description', $this->cms_lang('Description'))
            ->display_as('url', $this->cms_lang('URL (Where is it point to)'))
            ->display_as('notif_url', $this->cms_lang('Notification URL'))
            ->display_as('active', $this->cms_lang('Active'))
            ->display_as('is_static', $this->cms_lang('Static'))
            ->display_as('static_content', $this->cms_lang('Static Content'))
            ->display_as('authorization_id', $this->cms_lang('Authorization'))
            ->display_as('groups', $this->cms_lang('Groups'))
            ->display_as('only_content', $this->cms_lang('Only show content'))
            ->display_as('default_theme', $this->cms_lang('Default Theme'))
            ->display_as('default_layout', $this->cms_lang('Default Layout'));

        $crud->order_by('parent_id, index', 'asc');

        $crud->unset_texteditor('description');
        $crud->field_type('only_content', 'true_false');

        $crud->field_type('bootstrap_glyph','enum',array('glyphicon-adjust', 'glyphicon-align-center', 'glyphicon-align-justify', 'glyphicon-align-left', 'glyphicon-align-right', 'glyphicon-arrow-down', 'glyphicon-arrow-left', 'glyphicon-arrow-right', 'glyphicon-arrow-up', 'glyphicon-asterisk', 'glyphicon-backward', 'glyphicon-ban-circle', 'glyphicon-barcode', 'glyphicon-bell', 'glyphicon-bold', 'glyphicon-book', 'glyphicon-bookmark', 'glyphicon-briefcase', 'glyphicon-bullhorn', 'glyphicon-calendar', 'glyphicon-camera', 'glyphicon-certificate', 'glyphicon-check', 'glyphicon-chevron-down', 'glyphicon-chevron-left', 'glyphicon-chevron-right', 'glyphicon-chevron-up', 'glyphicon-circle-arrow-down', 'glyphicon-circle-arrow-left', 'glyphicon-circle-arrow-right', 'glyphicon-circle-arrow-up', 'glyphicon-cloud', 'glyphicon-cloud-download', 'glyphicon-cloud-upload', 'glyphicon-cog', 'glyphicon-collapse-down', 'glyphicon-collapse-up', 'glyphicon-comment', 'glyphicon-compressed', 'glyphicon-copyright-mark', 'glyphicon-credit-card', 'glyphicon-cutlery', 'glyphicon-dashboard', 'glyphicon-download', 'glyphicon-download-alt', 'glyphicon-earphone', 'glyphicon-edit', 'glyphicon-eject', 'glyphicon-envelope', 'glyphicon-euro', 'glyphicon-exclamation-sign', 'glyphicon-expand', 'glyphicon-export', 'glyphicon-eye-close', 'glyphicon-eye-open', 'glyphicon-facetime-video', 'glyphicon-fast-backward', 'glyphicon-fast-forward', 'glyphicon-file', 'glyphicon-film', 'glyphicon-filter', 'glyphicon-fire', 'glyphicon-flag', 'glyphicon-flash', 'glyphicon-floppy-disk', 'glyphicon-floppy-open', 'glyphicon-floppy-remove', 'glyphicon-floppy-save', 'glyphicon-floppy-saved', 'glyphicon-folder-close', 'glyphicon-folder-open', 'glyphicon-font', 'glyphicon-forward', 'glyphicon-fullscreen', 'glyphicon-gbp', 'glyphicon-gift', 'glyphicon-glass', 'glyphicon-globe', 'glyphicon-hand-down', 'glyphicon-hand-left', 'glyphicon-hand-right', 'glyphicon-hand-up', 'glyphicon-hd-video', 'glyphicon-hdd', 'glyphicon-header', 'glyphicon-headphones', 'glyphicon-heart', 'glyphicon-heart-empty', 'glyphicon-home', 'glyphicon-import', 'glyphicon-inbox', 'glyphicon-indent-left', 'glyphicon-indent-right', 'glyphicon-info-sign', 'glyphicon-italic', 'glyphicon-leaf', 'glyphicon-link', 'glyphicon-list', 'glyphicon-list-alt', 'glyphicon-lock', 'glyphicon-log-in', 'glyphicon-log-out', 'glyphicon-magnet', 'glyphicon-map-marker', 'glyphicon-minus', 'glyphicon-minus-sign', 'glyphicon-move', 'glyphicon-music', 'glyphicon-new-window', 'glyphicon-off', 'glyphicon-ok', 'glyphicon-ok-circle', 'glyphicon-ok-sign', 'glyphicon-open', 'glyphicon-paperclip', 'glyphicon-pause', 'glyphicon-pencil', 'glyphicon-phone', 'glyphicon-phone-alt', 'glyphicon-picture', 'glyphicon-plane', 'glyphicon-play', 'glyphicon-play-circle', 'glyphicon-plus', 'glyphicon-plus-sign', 'glyphicon-print', 'glyphicon-pushpin', 'glyphicon-qrcode', 'glyphicon-question-sign', 'glyphicon-random', 'glyphicon-record', 'glyphicon-refresh', 'glyphicon-registration-mark', 'glyphicon-remove', 'glyphicon-remove-circle', 'glyphicon-remove-sign', 'glyphicon-repeat', 'glyphicon-resize-full', 'glyphicon-resize-horizontal', 'glyphicon-resize-small', 'glyphicon-resize-vertical', 'glyphicon-retweet', 'glyphicon-road', 'glyphicon-save', 'glyphicon-saved', 'glyphicon-screenshot', 'glyphicon-sd-video', 'glyphicon-search', 'glyphicon-send', 'glyphicon-share', 'glyphicon-share-alt', 'glyphicon-shopping-cart', 'glyphicon-signal', 'glyphicon-sort', 'glyphicon-sort-by-alphabet', 'glyphicon-sort-by-alphabet-alt', 'glyphicon-sort-by-attributes', 'glyphicon-sort-by-attributes-alt', 'glyphicon-sort-by-order', 'glyphicon-sort-by-order-alt', 'glyphicon-sound-5-1', 'glyphicon-sound-6-1', 'glyphicon-sound-7-1', 'glyphicon-sound-dolby', 'glyphicon-sound-stereo', 'glyphicon-star', 'glyphicon-star-empty', 'glyphicon-stats', 'glyphicon-step-backward', 'glyphicon-step-forward', 'glyphicon-stop', 'glyphicon-subtitles', 'glyphicon-tag', 'glyphicon-tags', 'glyphicon-tasks', 'glyphicon-text-height', 'glyphicon-text-width', 'glyphicon-th', 'glyphicon-th-large', 'glyphicon-th-list', 'glyphicon-thumbs-down', 'glyphicon-thumbs-up', 'glyphicon-time', 'glyphicon-tint', 'glyphicon-tower', 'glyphicon-transfer', 'glyphicon-trash', 'glyphicon-tree-conifer', 'glyphicon-tree-deciduous', 'glyphicon-unchecked', 'glyphicon-upload', 'glyphicon-usd', 'glyphicon-user', 'glyphicon-volume-down', 'glyphicon-volume-off', 'glyphicon-volume-up', 'glyphicon-warning-sign', 'glyphicon-wrench', 'glyphicon-zoom-in', 'glyphicon-zoom-out'));
            $crud->field_type('index','hidden');

        $crud->set_relation('parent_id', cms_table_name('main_navigation'), 'navigation_name');
        $crud->set_relation('authorization_id', cms_table_name('main_authorization'), 'authorization_name');

        $crud->set_relation_n_n('groups', cms_table_name('main_group_navigation'), cms_table_name('main_group'), 'navigation_id', 'group_id', 'group_name');

        if(isset($parent_id) && intval($parent_id)>0){
            $crud->where(cms_table_name('main_navigation').'.parent_id', $parent_id);
            $state = $crud->getState();
            if($state == 'add'){
                $crud->field_type('parent_id', 'hidden', $parent_id);
            }
        }else{
            $crud->where(array(cms_table_name('main_navigation').'.parent_id' => NULL));
        }
        $crud->add_action('Move Up', base_url('modules/'.$this->cms_module_path().'/assets/action_icon/up.png'),
            site_url($this->cms_module_path().'/action_navigation_move_up').'/');
        $crud->add_action('Move Down', base_url('modules/'.$this->cms_module_path().'/assets/action_icon/down.png'),
            site_url($this->cms_module_path().'/action_navigation_move_down').'/');

        $crud->callback_column('active', array(
            $this,
            'column_navigation_active'
        ));

        $crud->callback_column('navigation_child', array(
            $this,
            'column_navigation_child'
        ));

        $crud->callback_before_insert(array(
            $this,
            'before_insert_navigation'
        ));
        $crud->callback_before_delete(array(
            $this,
            'before_delete_navigation'
        ));

        $crud->set_language($this->cms_language());

        $output = $crud->render();

        $navigation_path = array();
        if(isset($parent_id) && intval($parent_id)>0){
            $this->db->select('navigation_name')
                ->from(cms_table_name('main_navigation'))
                ->where('navigation_id', $parent_id);
            $query = $this->db->get();
            if($query->num_rows()>0){
                $row = $query->row();
                $navigation_name = $row->navigation_name;
                $navigation_path = $this->cms_get_navigation_path($navigation_name);
            }
        }
        $output->navigation_path = $navigation_path;

        $this->view('main/main_navigation', $output, 'main_navigation_management');
    }

    public function action_navigation_move_up($primary_key){
        $query = $this->db->select('navigation_name, parent_id')
            ->from(cms_table_name('main_navigation'))
            ->where('navigation_id', $primary_key)
            ->get();
        $row = $query->row();
        $navigation_name = $row->navigation_name;
        $parent_id = $row->parent_id;

        // move up
        $this->cms_do_move_up_navigation($navigation_name);

        // redirect
        if(isset($parent_id)){
            redirect('main/navigation/'.$parent_id.'#record_'.$primary_key,'refresh');
        }else{
            redirect('main/navigation'.'#record_'.$primary_key,'refresh');
        }
    }

    public function action_navigation_move_down($primary_key){
        $query = $this->db->select('navigation_name, parent_id')
            ->from(cms_table_name('main_navigation'))
            ->where('navigation_id', $primary_key)
            ->get();
        $row = $query->row();
        $navigation_name = $row->navigation_name;
        $parent_id = $row->parent_id;

        // move down
        $this->cms_do_move_down_navigation($navigation_name);

        // redirect
        if(isset($parent_id)){
            redirect('main/navigation/'.$parent_id.'#record_'.$primary_key,'refresh');
        }else{
            redirect('main/navigation'.'#record_'.$primary_key,'refresh');
        }
    }

    public function before_insert_navigation($post_array)
    {
        //get parent's navigation_id
        $query = $this->db->select('navigation_id')
            ->from(cms_table_name('main_navigation'))
            ->where('navigation_id', is_int($post_array['parent_id'])? $post_array['parent_id']: NULL)
            ->get();
        $row   = $query->row();

        $parent_id = isset($row->navigation_id) ? $row->navigation_id : NULL;

        //index = max index+1
        $query = $this->db->select_max('index')
            ->from(cms_table_name('main_navigation'))
            ->where('parent_id', $parent_id)
            ->get();
        $row   = $query->row();
        $index = $row->index;
        if (!isset($index)){
            $index = 1;
        }else{
            $index = $index+1;
        }

        $post_array['index'] = $index;

        if (!isset($post_array['authorization_id']) || $post_array['authorization_id'] == '') {
            $post_array['authorization_id'] = 1;
        }

        return $post_array;
    }

    public function before_delete_navigation($primary_key)
    {
        $this->db->delete(cms_table_name('main_quicklink'), array(
            'navigation_id' => $primary_key
        ));
    }

    public function column_navigation_active($value, $row)
    {
        $html = '<a name="record_'.$row->navigation_id.'">&nbsp;</a>';
        $target = site_url($this->cms_module_path() . '/toggle_navigation_active/' . $row->navigation_id);
        if ($value == 0) {
            $html .= '<span target="' . $target . '" class="navigation_active">Inactive</span>';
        } else {
            $html .= '<span target="' . $target . '" class="navigation_active">Active</span>';
        }
        return $html;
    }

    public function column_navigation_child($value, $row)
    {
        $html = '';
        $this->db->select('navigation_id')
            ->from(cms_table_name('main_navigation'))
            ->where('parent_id', $row->navigation_id);
        $query = $this->db->get();
        $child_count = $query->num_rows();
        if($child_count<=0){
            $html .= $this->cms_lang('No Child');
        }else{
            $html .= '<a href="'.site_url($this->cms_module_path().'/navigation/'.$row->navigation_id).'">'.
                $this->cms_lang('Manage Children')
                .'</a>';
        }
        $html .= '&nbsp;|&nbsp;<a href="'.site_url($this->cms_module_path().'/navigation/'.$row->navigation_id).'/add">'.
            $this->cms_lang('Add Child')
            .'</a>';
        return $html;
    }

    public function toggle_navigation_active($navigation_id)
    {
        if ($this->input->is_ajax_request()) {
            $this->db->select('active')->from(cms_table_name('main_navigation'))->where('navigation_id', $navigation_id);
            $query = $this->db->get();
            if ($query->num_rows() > 0) {
                $row       = $query->row();
                $new_value = ($row->active == 0) ? 1 : 0;
                $this->db->update(cms_table_name('main_navigation'), array(
                    'active' => $new_value
                ), array(
                    'navigation_id' => $navigation_id
                ));
                $this->cms_show_json(array(
                    'success' => true
                ));
            } else {
                $this->cms_show_json(array(
                    'success' => false
                ));
            }
        }
    }

    // QUICKLINK ===============================================================
    public function quicklink()
    {
        $this->cms_guard_page('main_quicklink_management');
        $crud = $this->new_crud();
        $crud->unset_jquery();

        $crud->set_table(cms_table_name('main_quicklink'));
        $crud->set_subject($this->cms_lang('Quick Link'));

        $crud->required_fields('navigation_id');
        $crud->unique_fields('navigation_id');
        $crud->unset_read();

        $crud->columns('navigation_id');
        $crud->edit_fields('navigation_id', 'index');
        $crud->add_fields('navigation_id', 'index');

        $crud->display_as('navigation_id', $this->cms_lang('Navigation Code'));

        $crud->order_by('index', 'asc');

        $crud->set_relation('navigation_id', cms_table_name('main_navigation'), 'navigation_name');
        $crud->field_type('index','hidden');

        $crud->callback_before_insert(array(
            $this,
            'before_insert_quicklink'
        ));

        $crud->add_action('Move Up', base_url('modules/'.$this->cms_module_path().'/assets/action_icon/up.png'),
            site_url($this->cms_module_path().'/action_quicklink_move_up').'/');
        $crud->add_action('Move Down', base_url('modules/'.$this->cms_module_path().'/assets/action_icon/down.png'),
            site_url($this->cms_module_path().'/action_quicklink_move_down').'/');

        $crud->callback_column($this->unique_field_name('navigation_id'), array(
            $this,
            'column_quicklink_navigation_id'
        ));

        $crud->set_language($this->cms_language());

        $output = $crud->render();

        $this->view('grocery_CRUD', $output, 'main_quicklink_management');
    }

    public function before_insert_quicklink($post_array)
    {
        $query = $this->db->select_max('index')
            ->from(cms_table_name('main_quicklink'))
            ->get();
        $row   = $query->row();
        $index = $row->index;
        if (!isset($index)){
            $index = 1;
        }else{
            $index = $index+1;
        }

        $post_array['index'] = $index;

        return $post_array;
    }

    public function column_quicklink_navigation_id($value, $row)
    {
        $html = '<a name="record_'.$row->quicklink_id.'">&nbsp;</a>';
        $html .= $value;
        return $html;
    }

    public function action_quicklink_move_up($primary_key){
        $query = $this->db->select('navigation_id')
            ->from(cms_table_name('main_quicklink'))
            ->where('quicklink_id', $primary_key)
            ->get();
        $row = $query->row();
        $navigation_id = $row->navigation_id;

        // move up
        $this->cms_do_move_up_quicklink($navigation_id);

        // redirect
        redirect('main/quicklink'.'#record_'.$primary_key,'refresh');
    }

    public function action_quicklink_move_down($primary_key){
        $query = $this->db->select('navigation_id')
            ->from(cms_table_name('main_quicklink'))
            ->where('quicklink_id', $primary_key)
            ->get();
        $row = $query->row();
        $navigation_id = $row->navigation_id;

        // move up
        $this->cms_do_move_down_quicklink($navigation_id);

        // redirect
        redirect('main/quicklink'.'#record_'.$primary_key,'refresh');
    }

    // PRIVILEGE ===============================================================
    public function privilege()
    {
        $this->cms_guard_page('main_privilege_management');
        $crud = $this->new_crud();
        $crud->unset_jquery();

        $crud->set_table(cms_table_name('main_privilege'));
        $crud->set_subject($this->cms_lang('Privilege'));

        $crud->required_fields('privilege_name');
        $crud->unique_fields('privilege_name');
        $crud->unset_read();

        $crud->columns('privilege_name','title','description');
        $crud->edit_fields('privilege_name','title','description','authorization_id','groups');
        $crud->add_fields('privilege_name','title','description','authorization_id','groups');

        $crud->set_relation('authorization_id', cms_table_name('main_authorization'), 'authorization_name'); //, 'groups');

        $crud->set_relation_n_n('groups', cms_table_name('main_group_privilege'), cms_table_name('main_group'), 'privilege_id', 'group_id', 'group_name');

        $crud->display_as('authorization_id', $this->cms_lang('Authorization'))
            ->display_as('groups', $this->cms_lang('Groups'))
            ->display_as('privilege_name', $this->cms_lang('Privilege Code'))
            ->display_as('title', $this->cms_lang('Title'))
            ->display_as('description', $this->cms_lang('Description'));

        $crud->unset_texteditor('description');

        $crud->set_language($this->cms_language());

        $output = $crud->render();

        $this->view('main/main_privilege', $output, 'main_privilege_management');
    }

    // WIDGET ==================================================================
    public function widget()
    {
        $this->cms_guard_page('main_widget_management');
        $crud = $this->new_crud();
        $crud->unset_jquery();

        $crud->set_table(cms_table_name('main_widget'));
        $crud->set_subject($this->cms_lang('Widget'));

        $crud->required_fields('widget_name');
        $crud->unique_fields('widget_name');
        $crud->unset_read();

        $crud->columns('widget_name', 'title', 'active', 'slug');
        $crud->edit_fields('widget_name', 'title', 'active', 'description', 'is_static', 'static_content', 'url', 'slug', 'authorization_id', 'groups', 'index');
        $crud->add_fields('widget_name', 'title', 'active', 'description', 'is_static', 'static_content', 'url', 'slug', 'authorization_id', 'groups', 'index');
        $crud->field_type('active', 'true_false');
        $crud->field_type('is_static', 'true_false');
        $crud->field_type('index', 'hidden');

        $crud->display_as('widget_name', $this->cms_lang('Widget Code'))
            ->display_as('title', $this->cms_lang('Title (What visitor see)'))
            ->display_as('active', $this->cms_lang('Active'))
            ->display_as('description', $this->cms_lang('Description'))
            ->display_as('url', $this->cms_lang('URL (Where is it point to)'))
            ->display_as('index', $this->cms_lang('Order'))
            ->display_as('is_static', $this->cms_lang('Static'))
            ->display_as('static_content', $this->cms_lang('Static Content'))
            ->display_as('slug', $this->cms_lang('Slug'))
            ->display_as('authorization_id', $this->cms_lang('Authorization'))
            ->display_as('groups', $this->cms_lang('Groups'));

        $crud->order_by('index, slug', 'asc');

        $crud->unset_texteditor('static_content');
        $crud->unset_texteditor('description');

        $crud->set_relation('authorization_id', cms_table_name('main_authorization'), 'authorization_name');

        $crud->set_relation_n_n('groups', cms_table_name('main_group_widget'), cms_table_name('main_group'), 'widget_id', 'group_id', 'group_name');

        $crud->callback_before_insert(array(
            $this,
            'before_insert_widget'
        ));

        $crud->add_action('Move Up', base_url('modules/'.$this->cms_module_path().'/assets/action_icon/up.png'),
            site_url($this->cms_module_path().'/action_widget_move_up').'/');
        $crud->add_action('Move Down', base_url('modules/'.$this->cms_module_path().'/assets/action_icon/down.png'),
            site_url($this->cms_module_path().'/action_widget_move_down').'/');

        $crud->callback_column('active', array(
            $this,
            'column_widget_active'
        ));

        $crud->set_language($this->cms_language());

        $output = $crud->render();

        $this->view('main/main_widget', $output, 'main_widget_management');
    }

    public function before_insert_widget($post_array)
    {
        $query = $this->db->select_max('index')
            ->from(cms_table_name('main_widget'))
            ->get();
        $row   = $query->row();
        $index = $row->index;
        if (!isset($index)){
            $index = 1;
        }else{
            $index = $index+1;
        }

        $post_array['index'] = $index;

        if (!isset($post_array['authorization_id']) || $post_array['authorization_id'] == '') {
            $post_array['authorization_id'] = 1;
        }

        return $post_array;
    }

    public function column_widget_active($value, $row)
    {
        $html = '<a name="record_'.$row->widget_id.'">&nbsp;</a>';
        $target = site_url($this->cms_module_path() . '/toggle_widget_active/' . $row->widget_id);
        if ($value == 0) {
            $html.= '<span target="' . $target . '" class="widget_active">Inactive</span>';
        } else {
            $html.= '<span target="' . $target . '" class="widget_active">Active</span>';
        }
        return $html;
    }

    public function toggle_widget_active($widget_id)
    {
        if ($this->input->is_ajax_request()) {
            $this->db->select('active')->from(cms_table_name('main_widget'))->where('widget_id', $widget_id);
            $query = $this->db->get();
            if ($query->num_rows() > 0) {
                $row       = $query->row();
                $new_value = ($row->active == 0) ? 1 : 0;
                $this->db->update(cms_table_name('main_widget'), array(
                    'active' => $new_value
                ), array(
                    'widget_id' => $widget_id
                ));
                $this->cms_show_json(array(
                    'success' => true
                ));
            } else {
                $this->cms_show_json(array(
                    'success' => false
                ));
            }
        }
    }

    public function action_widget_move_up($primary_key){
        $query = $this->db->select('widget_name')
            ->from(cms_table_name('main_widget'))
            ->where('widget_id', $primary_key)
            ->get();
        $row = $query->row();
        $widget_name = $row->widget_name;

        // move up
        $this->cms_do_move_up_widget($widget_name);

        // redirect
        redirect('main/widget'.'#record_'.$primary_key,'refresh');
    }

    public function action_widget_move_down($primary_key){
        $query = $this->db->select('widget_name')
            ->from(cms_table_name('main_widget'))
            ->where('widget_id', $primary_key)
            ->get();
        $row = $query->row();
        $widget_name = $row->widget_name;

        // move up
        $this->cms_do_move_down_widget($widget_name);

        // redirect
        redirect('main/widget'.'#record_'.$primary_key,'refresh');
    }

    // CONFIG ==================================================================
    public function config()
    {
        $this->cms_guard_page('main_config_management');
        $crud = $this->new_crud();
        $crud->unset_jquery();

        $crud->set_table(cms_table_name('main_config'));
        $crud->set_subject($this->cms_lang('Configuration'));

        $crud->unique_fields('config_name');
        $crud->unset_read();
        $crud->unset_delete();

        $crud->columns('config_name', 'value');
        $crud->edit_fields('config_name', 'value', 'description');
        $crud->add_fields('config_name', 'value', 'description');

        $crud->display_as('config_name', $this->cms_lang('Configuration Key'))
            ->display_as('value', $this->cms_lang('Configuration Value'))
            ->display_as('description', $this->cms_lang('Description'));

        $crud->unset_texteditor('description');
        $crud->unset_texteditor('value');

        $operation = $crud->getState();
        if ( $operation == 'edit' || $operation == 'update' || $operation == 'update_validation') {
            $crud->field_type('config_name', 'readonly');
            $crud->field_type('description', 'readonly');
        }else if( $operation == 'add' || $operation == 'insert' || $operation == 'insert_validation'){
            //$crud->set_rules('config_name', 'Configuration Key', 'required');
            $crud->required_fields('config_name');
        }

        $crud->callback_after_insert(array(
            $this,
            'after_insert_config'
        ));
        $crud->callback_after_update(array(
            $this,
            'after_update_config'
        ));
        $crud->callback_before_delete(array(
            $this,
            'before_delete_config'
        ));

        $crud->set_language($this->cms_language());

        $output = $crud->render();

        $this->view('main/main_config', $output, 'main_config_management');
    }

    public function after_insert_config($post_array, $primary_key){
        // adjust configuration file entry
        cms_config($post_array['config_name'], $post_array['value']);
        return TRUE;
    }

    public function after_update_config($post_array, $primary_key){
        // adjust configuration file entry
        $query = $this->db->select('config_name')->from(cms_table_name('main_config'))->where('config_id', $primary_key)->get();
        if($query->num_rows()>0){
            $row = $query->row();
            $config_name = $row->config_name;
            cms_config($config_name, $post_array['value']);
        }
        return TRUE;
    }

    public function before_delete_config($primary_key){
        $query = $this->db->select('config_name')->from(cms_table_name('main_config'))->where('config_id', $primary_key)->get();
        if($query->num_rows()>0){
            $row = $query->row();
            $config_name = $row->config_name;
            // delete configuration file entry
            cms_config($config_name, '', TRUE);
        }
        return TRUE;
    }

    public function json_is_login(){
        $result = array('is_login'=> $this->cms_user_id()>0);
        $this->cms_show_json($result);
    }

    public function ck_adjust_script(){
        $base_url = base_url();
        $save_base_url = str_replace('/', '\\/', $base_url);
        $ck_editor_adjust_script = '
            $(document).ready(function(){
                if (typeof(CKEDITOR) != "undefined"){
                    function __adjust_ck_editor(){
                        for (instance in CKEDITOR.instances) {
                            // ck_instance
                            ck_instance = CKEDITOR.instances[instance];
                            var name = CKEDITOR.instances[instance].name;
                            var $ck_textarea = $("#cke_"+name+" textarea");
                            var $ck_iframe = $("#cke_"+name+" iframe");
                            var data = ck_instance.getData();
                            if($ck_textarea.length > 0){
                                content = data.replace(
                                    /(src=".*?)('.$save_base_url.')(.*?")/gi,
                                    "$1{{ base_url }}$3"
                                );
                                ck_instance.setData(content);
                            }else if ($ck_iframe.length > 0){
                                content = data.replace(
                                    /(src=".*?)({{ base_url }})(.*?")/gi,
                                    "$1'.$base_url.'$3"
                                );
                                ck_instance.setData(content);
                            }
                            ck_instance.updateElement();
                        }
                    }

                    // when instance ready & form submit, adjust ck editor
                    CKEDITOR.on("instanceReady", function(){
                        __adjust_ck_editor();
                        for (instance in CKEDITOR.instances) {
                            // ck_instance
                            ck_instance = CKEDITOR.instances[instance];
                            ck_instance.on("mode", function(){
                                __adjust_ck_editor();
                            });
                        }
                    });

                    // when form submit, adjust ck editor
                    $("form").submit(function(){
                        for (instance in CKEDITOR.instances) {
                            // ck_instance
                            ck_instance = CKEDITOR.instances[instance];
                            var name = CKEDITOR.instances[instance].name;
                            var $original_textarea = $("textarea#"+name);
                            var data = ck_instance.getData();
                            content = data.replace(
                                /(src=".*?)('.$save_base_url.')(.*?")/gi,
                                "$1{{ base_url }}$3"
                            );
                            ck_instance.setData(content);
                        }
                    });

                    $(document).ajaxComplete(function(event, xhr, settings){
                        if(settings.url == $("#crudForm").attr("action")){
                            __adjust_ck_editor();
                        }
                    });
                }
            });
        ';
        echo $ck_editor_adjust_script;
    }

    public function widget_logout()
    {
        $data = array(
            "user_name" => $this->cms_user_name(),
            "welcome_lang" => $this->cms_lang('Welcome'),
            "logout_lang" => $this->cms_lang('Logout')
        );
        $this->view('main/main_widget_logout', $data);
    }

    public function widget_login()
    {
        $this->login();
    }

    public function widget_register()
    {
        $this->register();
    }

    public function widget_left_nav($first = TRUE, $navigations = NULL){
        if(!isset($navigations)){
            $navigations = $this->cms_navigations();
        }

        if(count($navigations) == 0) return '';
        if($first){
            $result = '<style type="text/css">
                .dropdown-submenu{
                    position:relative;
                }

                .dropdown-submenu > .dropdown-menu
                {
                    top:0;
                    left:100%;
                    margin-top:-6px;
                    margin-left:-1px;
                    -webkit-border-radius:0 6px 6px 6px;
                    -moz-border-radius:0 6px 6px 6px;
                    border-radius:0 6px 6px 6px;
                }

                .dropdown-submenu:hover > .dropdown-menu{
                    display:block;
                }

                .dropdown-submenu > a:after{
                    display:block;
                    content:" ";
                    float:right;
                    width:0;
                    height:0;
                    border-color:transparent;
                    border-style:solid;
                    border-width:5px 0 5px 5px;
                    border-left-color:#cccccc;
                    margin-top:5px;
                    margin-right:-10px;
                }

                .dropdown-submenu:hover > a:after{
                    border-left-color:#ffffff;
                }

                .dropdown-submenu .pull-left{
                    float:none;
                }

                .dropdown-submenu.pull-left > .dropdown-menu{
                    left:-100%;
                    margin-left:10px;
                    -webkit-border-radius:6px 0 6px 6px;
                    -moz-border-radius:6px 0 6px 6px;
                    border-radius:6px 0 6px 6px;
                }
                #_first-left-dropdown{
                    display:block;
                    margin:0px;
                    border:none;
                }
                @media (max-width: 750px){
                    #_first-left-dropdown{
                        position:static;
                    }
                }
            }
            </style>';
        }else{
            $result = '';
        }
        $result .= '<ul  class="dropdown-menu nav nav-pills nav-stacked" '.($first?'id="_first-left-dropdown"':'').'>';
        foreach($navigations as $navigation){
            if(($navigation['allowed'] && $navigation['active']) || $navigation['have_allowed_children']){
                // create badge if needed
                $badge = '';
                if($quicklink['notif_url'] != ''){
                    $badge_id = '__cms_notif_left_navigation_'.$quicklink['navigation_id'];
                    $badge = '&nbsp;<span id="'.$badge_id.'" class="badge"></span>';
                    $badge.= '<script type="text/javascript">
                            $(document).ready(function(){
                                setInterval(function(){
                                    $.ajax({
                                        dataType:"json",
                                        url: "'.addslashes($quicklink['notif_url']).'",
                                        success: function(response){
                                            if(response.success){
                                                $("#'.$badge_id.'").html(response.notif);
                                            }
                                        }
                                    });
                                }, 50000);
                            });
                        </script>
                    ';
                }
                // set active class
                $active = '';
                if($this->cms_ci_session('__cms_navigation_name') == $quicklink['navigation_name']){
                    $active = 'active';
                }
                // make text
                $icon = '<span class="glyphicon '.$navigation['bootstrap_glyph'].'"></span>&nbsp;';
                if($navigation['allowed'] && $navigation['active']){
                    $text = '<a class="dropdown-toggle" href="'.$navigation['url'].'">'.$icon.$navigation['title'].$badge.'</a>';
                }else{
                    $text = $icon.$navigation['title'].$badge;
                }

                if(count($navigation['child'])>0 && $navigation['have_allowed_children']){
                    $result .= '<li class="dropdown-submenu '.$active.'">'.$text.$this->widget_left_nav(FALSE, $navigation['child']).'</li>';
                }else{
                    $result .= '<li class="'.$active.'">'.$text.'</li>';
                }
            }
        }
        $result .= '</ul>';
        // show up
        if($first){
            $this->cms_show_html($result);
        }else{
            return $result;
        }
    }

    public function widget_top_nav($caption = 'Complete Menu', $first = TRUE, $no_complete_menu=FALSE, $no_quicklink=FALSE, $inverse = FALSE, $navigations = NULL){
        $result = '';
        $caption = $this->cms_lang($caption);

        if(!$no_complete_menu){
            if(!isset($navigations)){
                $navigations = $this->cms_navigations();
            }
            if(count($navigations) == 0) return '';


            $result .= '<ul class="dropdown-menu">';
            foreach($navigations as $navigation){
                if(($navigation['allowed'] && $navigation['active']) || $navigation['have_allowed_children']){
                    $navigation['bootstrap_glyph'] = $navigation['bootstrap_glyph'] == ''? 'icon-white': $navigation['bootstrap_glyph'];
                    // make text
                    $icon = '<span class="glyphicon '.$navigation['bootstrap_glyph'].'"></span>&nbsp;';
                    $badge = '';
                    if($navigation['notif_url'] != ''){
                        $badge_id = '__cms_notif_top_nav_'.$navigation['navigation_id'];
                        $badge = '&nbsp;<span id="'.$badge_id.'" class="badge"></span>';
                        $badge.= '<script type="text/javascript">
                                $(document).ready(function(){
                                    function __top_nav_get_badge_'.$badge_id.'(){
                                        $.ajax({
                                            dataType:"json",
                                            url: "'.addslashes($navigation['notif_url']).'",
                                            success: function(response){
                                                if(response.success){
                                                    $("#'.$badge_id.'").html(response.notif);
                                                }
                                            }
                                        });
                                    }
                                    __top_nav_get_badge_'.$badge_id.'();
                                    setInterval(function(){
                                        __top_nav_get_badge_'.$badge_id.'();
                                    }, 50000);
                                });
                            </script>
                        ';
                    }
                    if($navigation['allowed'] && $navigation['active']){
                        $text = '<a href="'.$navigation['url'].'">'.$icon.
                            $navigation['title'].$badge.'</a>';
                    }else{
                        $text = '<a href="#">'.$icon.
                            $navigation['title'].$badge.'</a>';
                    }

                    if(count($navigation['child'])>0 && $navigation['have_allowed_children']){
                        $result .= '<li class="dropdown-submenu">'.
                            $text.$this->widget_top_nav($caption, FALSE, $no_complete_menu, $no_quicklink, $inverse, $navigation['child']).'</li>';
                    }else{
                        $result .= '<li>'.$text.'</li>';
                    }
                }
            }
            $result .= '</ul>';
        }

        // show up
        if($first){
            if(!$no_complete_menu){
                //  hidden-sm hidden-xs
                $result = '<li class="dropdown">'.
                    '<a class="dropdown-toggle" data-toggle="dropdown" href="#">'.$caption.' </a>'.
                    $result.'</li>';
            }
            if(!$no_quicklink){
                $result .= $this->build_quicklink();
            }
            $result =
            '<style type="text/css">
                @media (min-width: 750px){
                    .dropdown-submenu{
                        position:relative;
                    }

                    .dropdown-submenu > .dropdown-menu
                    {
                        top:0;
                        left:100%;
                        margin-top:-6px;
                        margin-left:-1px;
                        -webkit-border-radius:0 6px 6px 6px;
                        -moz-border-radius:0 6px 6px 6px;
                        border-radius:0 6px 6px 6px;
                    }

                    .dropdown-submenu:hover > .dropdown-menu{
                        display:block;
                    }

                    .dropdown-submenu > a:after{
                        display:block;
                        content:" ";
                        float:right;
                        width:0;
                        height:0;
                        border-color:transparent;
                        border-style:solid;
                        border-width:5px 0 5px 5px;
                        border-left-color:#cccccc;
                        margin-top:5px;
                        margin-right:-10px;
                    }

                    .dropdown-submenu:hover > a:after{
                        border-left-color:#ffffff;
                    }

                    .dropdown-submenu .pull-left{
                        float:none;
                    }

                    .dropdown-submenu.pull-left > .dropdown-menu{
                        left:-100%;
                        margin-left:10px;
                        -webkit-border-radius:6px 0 6px 6px;
                        -moz-border-radius:6px 0 6px 6px;
                        border-radius:6px 0 6px 6px;
                    }
                    .dropdown .caret{
                        display:inline-block!important;
                    }
                }
            </style>
            <div class="navbar '.($inverse? 'navbar-inverse' : 'navbar-default').' navbar-fixed-top" role="navigation">
                <div class="container">
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                        <a class="navbar-brand" href="{{ site_url }}"><img src ="{{ site_logo }}" style="max-width:120px;" /></a>
                    </div>
                    <div class="collapse navbar-collapse">
                        <ul class="navbar-nav nav">'.$result.'</ul>
                    </div><!--/.nav-collapse -->
                </div>
            </div>
            <script type="text/javascript">
                // function to adjust navbar size so that it will always fit to the screen

                var _NAVBAR_LI_ORIGINAL_PADDING = $(".navbar-nav > li > a").css("padding-right");
                var _NAVBAR_LI_ORIGINAL_FONTSIZE = $(".navbar-nav > li").css("font-size");
                function __adjust_navbar(){
                    var li_count = $(".navbar-nav > li").length;
                    $(".navbar-nav > li > a").css("padding-left", _NAVBAR_LI_ORIGINAL_PADDING);
                    $(".navbar-nav > li").css("font-size", _NAVBAR_LI_ORIGINAL_FONTSIZE);
                    if($(document).width()>=750){
                        var need_transform = true;
                        while(need_transform){
                            need_transform = false;
                            for(var i=0; i<li_count; i++){
                                var top = $(".navbar-nav > li")[i].offsetTop;
                                if(top>$(".navbar-brand")[0].offsetTop){
                                    need_transform = true;
                                }
                            }
                            if(need_transform){
                                // decrease the padding
                                var currentPadding = $(".navbar-nav > li > a").css("padding-right");
                                var currentPaddingNum = parseFloat(currentPadding, 10);
                                if(currentPaddingNum>10){
                                    newPadding = currentPaddingNum-1;
                                    $(".navbar-nav > li > a").css("padding-right", newPadding);
                                    $(".navbar-nav > li > a").css("padding-left", newPadding);
                                }else{
                                    // decrease the font
                                    var currentFontSize = $(".navbar-nav > li").css("font-size");
                                    var currentFontSizeNum = parseFloat(currentFontSize, 10);
                                    var newFontSize = currentFontSizeNum * 0.8;
                                    $(".navbar-nav > li").css("font-size", newFontSize);
                                }
                            }
                        }
                    }
                }

                // MAIN PROGRAM
                $(document).ready(function(){
                    // override bootstrap default behavior on dropdown click
                    $("a.dropdown-toggle span.anchor-text").on("click touchstart", function(){
                        if(event.stopPropagation){
                            event.stopPropagation();
                        }
                        event.cancelBubble=true;
                        window.location = $(this).parent().attr("href");
                    });
                    // adjust navbar
                    __adjust_navbar();
                    $(window).resize(function() {
                        __adjust_navbar();
                    });
                    $(document).ajaxComplete(function(){
                        __adjust_navbar();
                    });
                });
            </script>';
            $this->cms_show_html($result);
        }else{
            return $result;
        }
    }

    public function widget_top_nav_no_quicklink($caption = 'Complete Menu'){
        $this->widget_top_nav($caption, TRUE, FALSE, TRUE, FALSE, NULL);
    }

    public function widget_quicklink(){
        $this->widget_top_nav('', TRUE, TRUE, FALSE, FALSE, NULL);
    }

    public function widget_top_nav_inverse($caption = 'Complete Menu'){
        $this->widget_top_nav($caption, TRUE, FALSE, TRUE, TRUE, NULL);
    }

    public function widget_top_nav_no_quicklink_inverse($caption = 'Complete Menu'){
        $this->widget_top_nav($caption, TRUE, FALSE, TRUE, TRUE, NULL);
    }

    public function widget_quicklink_inverse(){
        $this->widget_top_nav('', TRUE, TRUE, FALSE, TRUE, NULL);
    }

    public function widget_search($account=FALSE, $cart=FALSE){
        $this->load->library('cart');
        $this->load->helper('url');
        $user_id    = $this->cms_user_id();
        $count_cart = $this->cart->total_items();

        if($user_id == NULL){
            $result = 
            '<div class="navbar navbar-default navbar-fixed-top" style="background-color: #F7F7F7;" role="navigation">
              <div class="container">
                <div class="navbar-header">
                  <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                  </button>
                </div>
                <div class="navbar-collapse collapse">
                  <ul class="nav navbar-nav navbar-right">
                    <li><a href='.base_url().'main/account_dashboard><span class="glyphicon glyphicon-user"></span> My Account</a></li>
                  </ul>
                </div><!--/.nav-collapse -->
              </div>
            </div>';

            $this->cms_show_html($result);
        }
        else{
            $result = 
            '<div class="navbar navbar-default navbar-fixed-top" style="background-color: #F7F7F7;" role="navigation">
              <div class="container">
                <div class="navbar-header">
                  <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                  </button>
                </div>
                <div class="navbar-collapse collapse">
                  <ul class="nav navbar-nav navbar-right">
                    <li class="dropdown">
                      <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-user"></span> My Account <b class="caret"></b></a>
                      <ul class="dropdown-menu">
                        <li><a href='.base_url().'main/account_dashboard><span class="glyphicon glyphicon-user"></span> Account</a></li>
                        <li><a href='.base_url().'main/logout><span class="glyphicon glyphicon-log-out"></span> Log Out</a></li>
                      </ul>
                    </li>
                  </ul>
                </div><!--/.nav-collapse -->
              </div>
            </div>';

            $this->cms_show_html($result);
        } 
    }

    public function widget_search_logo($account=FALSE, $cart=FALSE){
        $this->load->library('cart');
        $this->load->helper('url');
        $user_id    = $this->cms_user_id();
        $count_cart = $this->cart->total_items();

        if($user_id == NULL){
            $result = 
            '<div class="navbar navbar-default navbar-fixed-top" style="background-color: #F7F7F7;" role="navigation">
              <div class="container">
                <div class="navbar-header">
                  <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                  </button>
                </div>
                <div class="navbar-collapse collapse">
                    <a class="navbar-brand" href="{{ site_url }}"><img src ="{{ site_logo }}" style="max-width:220px;" /><span class="label label-info">Beta</span></a>
                    <ul class="nav navbar-nav navbar-right">
                        <li>
                            <form class="navbar-form navbar-left" role="search" action='.base_url().'main/global_search/>
                                <div class="form-group">
                                  <input type="text" id="search-box" name="q" class="form-control" placeholder="" style="width: 200px; value="'.$search_terms.'">
                                </div>
                                <button type="submit" name="search" class="btn btn-warning" style="margin-left: 10px;">Search</button>
                            </form>
                        </li>
                        <li><a href='.base_url().'main/account_dashboard><span class="glyphicon glyphicon-user"></span> My Account</a></li>
                    </ul>
                </div><!--/.nav-collapse -->
              </div>
            </div>';

            $this->cms_show_html($result);
        }
        else{
            $result = 
            '<div class="navbar navbar-default navbar-fixed-top" style="background-color: #F7F7F7;" role="navigation">
              <div class="container">
                <div class="navbar-header">
                  <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                  </button>
                </div>
                <div class="navbar-collapse collapse">
                    <a class="navbar-brand" href="{{ site_url }}"><img src ="{{ site_logo }}" style="max-width:220px;" /><span class="label label-info">Beta</span></a>
                  <ul class="nav navbar-nav navbar-right">
                    <li>
                        <form class="navbar-form navbar-left" role="search" action='.base_url().'main/global_search/>
                            <div class="form-group">
                              <input type="text" id="search-box" name="q" class="form-control" placeholder="" style="width: 200px; value="'.$search_terms.'">
                            </div>
                            <button type="submit" name="search" class="btn btn-warning" style="margin-left: 10px;">Search</button>
                        </form>
                    </li>
                    <li class="dropdown">
                      <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-user"></span> My Account <b class="caret"></b></a>
                      <ul class="dropdown-menu">
                        <li><a href='.base_url().'main/account_dashboard><span class="glyphicon glyphicon-user"></span> Account</a></li>
                        <li><a href='.base_url().'main/logout><span class="glyphicon glyphicon-log-out"></span> Log Out</a></li>
                      </ul>
                    </li>
                    <li><a href='.base_url().'main/cart><span class="glyphicon glyphicon-shopping-cart"></span> My Trolley ('.$count_cart.')</a></li>
                  </ul>
                </div><!--/.nav-collapse -->
              </div>
            </div>';

            $this->cms_show_html($result);
        } 
    }

    public function widget_big_nav($caption = 'Complete Menu', $first = TRUE, $no_complete_menu=TRUE, $no_quicklink=FALSE, $inverse = FALSE, $navigations = NULL){
        $result = '';
        $caption = $this->cms_lang($caption);
        $product_category = $this->get_available_product_category();

        if(!$no_complete_menu){
            if(!isset($navigations)){
                $navigations = $this->cms_navigations();
            }
            if(count($navigations) == 0) return '';


            $result .= '<ul class="dropdown-menu">';
            foreach($navigations as $navigation){
                if(($navigation['allowed'] && $navigation['active']) || $navigation['have_allowed_children']){
                    $navigation['bootstrap_glyph'] = $navigation['bootstrap_glyph'] == ''? 'icon-white': $navigation['bootstrap_glyph'];
                    // make text
                    $icon = '<span class="glyphicon '.$navigation['bootstrap_glyph'].'"></span>&nbsp;';
                    $badge = '';
                    if($navigation['notif_url'] != ''){
                        $badge_id = '__cms_notif_top_nav_'.$navigation['navigation_id'];
                        $badge = '&nbsp;<span id="'.$badge_id.'" class="badge"></span>';
                        $badge.= '<script type="text/javascript">
                                $(document).ready(function(){
                                    function __top_nav_get_badge_'.$badge_id.'(){
                                        $.ajax({
                                            dataType:"json",
                                            url: "'.addslashes($navigation['notif_url']).'",
                                            success: function(response){
                                                if(response.success){
                                                    $("#'.$badge_id.'").html(response.notif);
                                                }
                                            }
                                        });
                                    }
                                    __top_nav_get_badge_'.$badge_id.'();
                                    setInterval(function(){
                                        __top_nav_get_badge_'.$badge_id.'();
                                    }, 50000);
                                });
                            </script>
                        ';
                    }
                    if($navigation['allowed'] && $navigation['active']){
                        $text = '<a href="'.$navigation['url'].'">'.$icon.
                            $navigation['title'].$badge.'</a>';
                    }else{
                        $text = '<a href="#">'.$icon.
                            $navigation['title'].$badge.'</a>';
                    }

                    if(count($navigation['child'])>0 && $navigation['have_allowed_children']){
                        $result .= '<li class="dropdown-submenu">'.
                            $text.$this->widget_top_nav($caption, FALSE, $no_complete_menu, $no_quicklink, $inverse, $navigation['child']).'</li>';
                    }else{
                        $result .= '<li>'.$text.'</li>';
                    }
                }
            }
            $result .= '</ul>';
        }

        $div_item_list = array();
        for($i=0; $i<count($product_category); $i++){
            $item = $product_category[$i];
            $div_item_list[] = 
                '<li>'.
                '<a href="'.base_url().'main/shop_product/'.$item['id'].'" >'.
                '<span><span class="glyphicon icon-white"></span>&nbsp;'.
                $item['name'].'</span></a>'.
                '</li>';
        }
        $pc = '';
        if(count($product_category)>0){
            foreach($div_item_list as $div_item)
            { 
                $pc .= $div_item;
            } 
        }

        // show up
        if($first){
            if(!$no_complete_menu){
                //  hidden-sm hidden-xs
                $result = '<li class="dropdown">'.
                    '<a class="dropdown-toggle" data-toggle="dropdown" href="#">'.$caption.'</a>'.
                    $result.'</li>';
            }
            if(!$no_quicklink){
                $result .= $this->build_quicklink();
            }

            $result =
            '<div class="container">
                <div class="row">
                    <div class="col-sm-2">
                        <a class="navbar-brand" href="{{ site_url }}"><img src ="{{ site_logo }}" style="max-width:120px;" /></a>
                    </div>
                    <div class="col-sm-10">
                        <div class="top-slogan text-right">{{ site_slogan }}</div>
                        <ul class="nav nav-pills nav-custom" style="padding-top: 12px;">'.
                        $result.
                        '</ul>
                    </div>
                </div>
                <hr/>
            </div>';
            $this->cms_show_html($result);
        }else{
            return $result;
        }
    }

    public function widget_big_nav_style2($caption = 'Complete Menu', $first = TRUE, $no_complete_menu=TRUE, $no_quicklink=FALSE, $inverse = FALSE, $navigations = NULL){
        $result = '';
        $caption = $this->cms_lang($caption);

        if(!$no_complete_menu){
            if(!isset($navigations)){
                $navigations = $this->cms_navigations();
            }
            if(count($navigations) == 0) return '';


            $result .= '<ul class="dropdown-menu">';
            foreach($navigations as $navigation){
                if(($navigation['allowed'] && $navigation['active']) || $navigation['have_allowed_children']){
                    $navigation['bootstrap_glyph'] = $navigation['bootstrap_glyph'] == ''? 'icon-white': $navigation['bootstrap_glyph'];
                    // make text
                    $icon = '<span class="glyphicon '.$navigation['bootstrap_glyph'].'"></span>&nbsp;';
                    $badge = '';
                    if($navigation['notif_url'] != ''){
                        $badge_id = '__cms_notif_top_nav_'.$navigation['navigation_id'];
                        $badge = '&nbsp;<span id="'.$badge_id.'" class="badge"></span>';
                        $badge.= '<script type="text/javascript">
                                $(document).ready(function(){
                                    function __top_nav_get_badge_'.$badge_id.'(){
                                        $.ajax({
                                            dataType:"json",
                                            url: "'.addslashes($navigation['notif_url']).'",
                                            success: function(response){
                                                if(response.success){
                                                    $("#'.$badge_id.'").html(response.notif);
                                                }
                                            }
                                        });
                                    }
                                    __top_nav_get_badge_'.$badge_id.'();
                                    setInterval(function(){
                                        __top_nav_get_badge_'.$badge_id.'();
                                    }, 50000);
                                });
                            </script>
                        ';
                    }
                    if($navigation['allowed'] && $navigation['active']){
                        $text = '<a href="'.$navigation['url'].'">'.$icon.
                            $navigation['title'].$badge.'</a>';
                    }else{
                        $text = '<a href="#">'.$icon.
                            $navigation['title'].$badge.'</a>';
                    }

                    if(count($navigation['child'])>0 && $navigation['have_allowed_children']){
                        $result .= '<li class="dropdown-submenu">'.
                            $text.$this->widget_top_nav($caption, FALSE, $no_complete_menu, $no_quicklink, $inverse, $navigation['child']).'</li>';
                    }else{
                        $result .= '<li>'.$text.'</li>';
                    }
                }
            }
            $result .= '</ul>';
        }

        // show up
        if($first){
            if(!$no_complete_menu){
                //  hidden-sm hidden-xs
                $result = '<li class="dropdown">'.
                    '<a class="dropdown-toggle" data-toggle="dropdown" href="#">'.$caption.'</a>'.
                    $result.'</li>';
            }
            if(!$no_quicklink){
                $result .= $this->build_quicklink();
            }
            $result =
            '<div class="container">
                <div class="row">
                    <div class="col-sm-12">
                        <ul class="nav nav-pills nav-justified top-menu">'.$result.'</ul>
                    </div>
                </div>
            </div>';
            $this->cms_show_html($result);
        }else{
            return $result;
        }
    }

    private function build_quicklink($quicklinks = NULL,$first = TRUE){
        if(!isset($quicklinks)){
            $quicklinks = $this->cms_quicklinks();
        }
        if(count($quicklinks) == 0) return '';

        $current_navigation_name = $this->cms_ci_session('__cms_navigation_name');
        $current_navigation_path = $this->cms_get_navigation_path($current_navigation_name);
        $html = '';

        foreach($quicklinks as $quicklink){
            // if navigation is not active then skip it
            if(!$quicklink['active']){
                continue;
            }
            // create icon if needed
            $icon = '';
            if($first){
                $icon_class = $quicklink['bootstrap_glyph'].' icon-white';
            }else{
                $icon_class = $quicklink['bootstrap_glyph'];
            }
            if($quicklink['bootstrap_glyph'] != '' || !$first){
                $icon_class = $icon_class==''? 'icon-white': $icon_class;
                $icon = '<span class="glyphicon '.$icon_class.'"></span>&nbsp;';
            }
            // create badge if needed
            $badge = '';
            if($quicklink['notif_url'] != ''){
                $badge_id = '__cms_notif_quicklink_'.$quicklink['navigation_id'];
                $badge = '&nbsp;<span id="'.$badge_id.'" class="badge"></span>';
                $badge.= '<script type="text/javascript">
                        $(document).ready(function(){
                            function __quicklink_get_badge_'.$badge_id.'(){
                                $.ajax({
                                    dataType:"json",
                                    url: "'.addslashes($quicklink['notif_url']).'",
                                    success: function(response){
                                        if(response.success){
                                            $("#'.$badge_id.'").html(response.notif);
                                        }
                                    }
                                });
                            }
                            __quicklink_get_badge_'.$badge_id.'();
                            setInterval(function(){
                                __quicklink_get_badge_'.$badge_id.'();
                            }, 50000);
                        });
                    </script>
                ';
            }
            // set active class
            $active = '';
            if($current_navigation_name == $quicklink['navigation_name']){
                $active = 'active';
            }else{
                foreach($current_navigation_path as $navigation_parent){
                    if($quicklink['navigation_name'] == $navigation_parent['navigation_name']){
                        $active = 'active';
                        break;
                    }
                }
            }
            // create li based on child availability
            if(count($quicklink['child'])==0){
                $html.= '<li class="'.$active.'">';
                $html.= anchor($quicklink['url'], '<span>'.$icon.$quicklink['title'].$badge.'</span>');
                $html.= '</li>';
            }else{
                if($first){
                    $html.= '<li class="dropdown '.$active.'">';
                    $html.= '<a class="dropdown-toggle" data-toggle="dropdown" href="'.$quicklink['url'].'">'.
                        '<span class="anchor-text">'.$icon.$quicklink['title'].$badge.'</span>'.
                        '&nbsp;</a>'; // hidden-sm hidden-xs
                    $html.= $this->build_quicklink($quicklink['child'],FALSE);
                    $html.= '</li>';
                }else{
                    $html.= '<li class="dropdown-submenu '.$active.'">';
                    $html.= '<a href="'.$quicklink['url'].'">'.
                        '<span>'.$icon.$quicklink['title'].$badge.'</span></a>';
                    $html.= $this->build_quicklink($quicklink['child'],FALSE);
                    $html.= '</li>';
                }
            }
        }

        if(!$first){
            $html = '<ul class="dropdown-menu">'.$html.'</ul>';
        }
        return $html;
    }

    public function widget_all_product($category = 'all'){
        $this->shop_product('all');
    }

    public function widget_new_product(){
        $this->new_product(1,3);
    }

    public function widget_testimonial(){
        $this->testimonial_list(4);
    }

    function global_search($search_terms = '')
    {
        // If the form has been submitted, rewrite the URL so that the search
        // terms can be passed as a parameter to the action. Note that there
        // are some issues with certain characters here.
        $this->load->helper('url');
        $search_terms = $this->input->get('q');
        if ($this->input->post('q'))
        {
            redirect('/main/global_search/'.$this->input->get('q'));
        }
 
        if ($search_terms)
        {
            // Load the model and perform the search
            //$this->model('page_model');
            $results = $this->No_CMS_Model->search($search_terms);
        }
 
        // Render the view, passing it the necessary data
        $this->view('search_result', array(
            'search_terms' => $search_terms,
            'results' => @$results
        ));
    }

    public function testimonial($limit=NULL)
    {
        // the honey_pot, every fake input should be empty
        $honey_pot_pass = (strlen($this->input->post('rating', ''))==0) &&
            (strlen($this->input->post('content', ''))==0);
        if(!$honey_pot_pass){
            show_404();
            die();
        }

        // get previously generated secret code
        $previous_secret_code = $this->session->userdata('testimoni_secret_code');

        if($previous_secret_code === NULL){
            $previous_secret_code = $this->__random_string();
        }

        $rating = $this->input->post($previous_secret_code.'rating');
        $content = $this->input->post($previous_secret_code.'content');

        $success = TRUE;
        $error_message = '';
        $show_success_message = FALSE;
        if($this->input->post('send')){

            if($honey_pot_pass){
                if($content == NULL || $content == ''){
                    $success = FALSE;
                    $error_message = 'Message is empty';
                }

                if($success){
                    $data['rating'] = $rating;
                    $data['message'] = $content;
                    $data['date'] = date('Y-m-d');
                    $data['status'] = '0';
                    $data['user_id'] = $this->cms_user_id();
                    
                    $this->db->insert($this->cms_complete_table_name('commerce_testimonial'), $data);
                    $rating = '';
                    $content = '';
                    $show_success_message = TRUE;
                }
            }else{
                show_404();
                die();
            }
        }

        // generate new secret code
        $secret_code = $this->__random_string();
        $this->session->set_userdata('testimoni_secret_code', $secret_code);

        $data['secret_code'] = $secret_code;
        $data['success'] = $success;
        $data['show_success_message'] = $show_success_message;
        $data['error_message'] = $error_message;
        $data['rating'] = $rating;
        $data['content'] = $content;

        $data['title'] = 'Customer Testimonials';
        $data['testimonial_list'] = $this->cms_get_testimonial($limit);  

        $this->view('main/main_testimonial', $data,
        $this->cms_complete_navigation_name('testimonial'));   
    }

    public function testimonial_list($limit=NULL)
    {
        $data['title'] = 'Customer Testimonials';
        $data['testimonial_list'] = $this->cms_get_testimonial($limit); 
        $this->view('main/widget_testimonial', $data);   
    }

    public function newsletter()
    {
        $email = $this->input->post('email');

        $SQL   = "SELECT email FROM ".cms_table_name('commerce_newsletter')." WHERE email = "."'$email'";
        $query = $this->db->query($SQL);
        $row   = $query->num_rows();

        if($row>0)
        {
            echo 'fail';
        }
        else
        {
            $data['email'] = $email;
            $this->db->insert($this->cms_complete_table_name('commerce_newsletter'), $data);
            echo 'ok';
        }
    }

    // ORDER ===================================================================================
    public function account_order()
    {
        $this->cms_guard_page('account_order');

        $user_id    = $this->cms_user_id();
        $order      = $this->cms_ecommerce_order($user_id);

        $data['order_list'] = $order;
        $data['current'] = 'account_orders';
        
        $this->view('main/main_ecommerce_order', $data, 'account_order');
    }

    public function get_detail_order()
    {
        $order_id   = $this->input->post('id');
        $detail_order     = $this->cms_ecommerce_order_detail($order_id);

        $data['order_detail'] = $detail_order;

        $this->view('main/table_order', $data); 
    }

    public function get_info_order()
    {
        $order_id   = $this->input->post('id');
        $detail     = $this->cms_ecommerce_order_info($order_id);
        
        $status_text = "";
        if($detail->status == 0){
            $status_text = "Pending";
        }else if($detail->status == 1){
            $status_text = "Processing";
        }else{
            $status_text = "Process/Paid";
        }

        $data['order_no'] = $detail->order_no;
        $data['date'] = date("d-m-Y", strtotime($detail->date));
        $data['time'] = $detail->time;
        $data['status'] = $status_text;
        $total_order = $detail->total-$detail->shipping;
        $data['total'] = number_format($total_order,0,",",".");
        $data['shipping'] = number_format($detail->shipping,0,",",".");
        $data['total_order'] = number_format($detail->total,0,",",".");
        echo json_encode($data);
    }

    public function account_wishlist()
    {
        $this->cms_guard_page('account_wishlist');

        $user_id          = $this->cms_user_id();
        $order            = $this->cms_ecommerce_wishlist($user_id);
        $data['wishlist'] = $order;
        $data['current']  = 'account_wishlist';
        
        $this->view('main/main_ecommerce_wishlist', $data, 'account_wishlist');
    }

    public function remove_wishlist()
    {
        $wishlist_id  = $this->input->post('wishlist_id');

        $result     = $this->cms_ecommerce_remove_wishlist($wishlist_id);

        echo $result;
    }

    public function manage_order(){
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // initialize groceryCRUD
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $crud = $this->new_crud();
        $crud->unset_jquery();

        // check state
        $state = $crud->getState();
        $state_info = $crud->getStateInfo();
        $primary_key = isset($state_info->primary_key)? $state_info->primary_key : NULL;

        switch($state){
            case 'unknown': break;
            case 'list' : break;
            case 'add' :  break;
            case 'edit' : break;
            case 'delete' : break;
            case 'insert' : break;
            case 'update' : break;
            case 'ajax_list' : break;
            case 'ajax_list_info': break;
            case 'insert_validation': break;
            case 'update_validation': break;
            case 'upload_file': break;
            case 'delete_file': break;
            case 'ajax_relation': break;
            case 'ajax_relation_n_n': break;
            case 'success': break;
            case 'export': break;
            case 'print': break;
        }

        // set model
        //$crud->set_model($this->cms_module_path().'/grocerycrud_article_model');

        // adjust groceryCRUD's language to No-CMS's language
        $crud->set_language($this->cms_language());

        // table name
        $crud->set_table($this->cms_complete_table_name('commerce_order'));

        // set subject
        $crud->set_subject('Order');

        // displayed columns on list
        $crud->columns('order_no','date','time','status','total','shipping','shipping_address','user_id','transfer_date','items');
        // displayed columns on edit operation
        $crud->edit_fields('status','shipping_address','items');
        // displayed columns on add operation
        //$crud->add_fields('product_id','product_name','brand','type','price','availibility','info','size','category','photos');
        $crud->field_type('status','dropdown',
            array('1' => 'Processing','2' => 'Processed/Paid'));

        $crud->required_fields('status');
        $crud->unique_fields('order_no');
        //$crud->set_rules('availibility', 'Available', 'integer');
        $crud->unset_add();

        // caption of each columns
        $crud->display_as('order_no','No Order');
        $crud->display_as('total_qty','Qty');
        $crud->display_as('user_id','First Name');
        $crud->display_as('first_name','First Name');
        $crud->display_as('transfer_date','Transferred Date');
        $crud->display_as('items','Items');

        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // HINT: Put set relation (lookup) codes here
        // (documentation: http://www.grocerycrud.com/documentation/options_functions/set_relation)
        // eg:
        //      $crud->set_relation( $field_name , $related_table, $related_title_field , $where , $order_by );
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $crud->set_relation('user_id', cms_table_name('main_user'), 'first_name');

        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // HINT: Put set relation_n_n (detail many to many) codes here
        // (documentation: http://www.grocerycrud.com/documentation/options_functions/set_relation_n_n)
        // eg:
        //      $crud->set_relation_n_n( $field_name, $relation_table, $selection_table, $primary_key_alias_to_this_table,
        //          $primary_key_alias_to_selection_table , $title_field_selection_table, $priority_field_relation );
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // HINT: Put custom field type here
        // (documentation: http://www.grocerycrud.com/documentation/options_functions/field_type)
        // eg:
        //      $crud->field_type( $field_name , $field_type, $value  );
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //$crud->field_type('author_user_id', 'hidden');
        //$crud->field_type('date', 'hidden');
        //$crud->field_type('allow_comment', 'true_false');
        //$crud->unset_texteditor('info');

        $crud->unset_read();
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // HINT: Put callback here
        // (documentation: httm://www.grocerycrud.com/documentation/options_functions)
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $crud->callback_before_insert(array($this,'order_before_insert'));
        $crud->callback_before_update(array($this,'order_before_update'));
        $crud->callback_before_delete(array($this,'order_before_delete'));
        $crud->callback_after_insert(array($this,'order_after_insert'));
        $crud->callback_after_update(array($this,'order_after_update'));
        $crud->callback_after_delete(array($this,'order_after_delete'));

        $crud->callback_column('items',array($this, 'callback_column_items'));
        $crud->callback_field('items',array($this, 'callback_field_items'));

        //$crud->callback_column('photos',array($this, 'callback_column_photos'));
        //$crud->callback_field('photos',array($this, 'callback_field_photos'));
        //$crud->callback_column('comments',array($this, 'callback_column_comments'));
        //$crud->callback_field('comments',array($this, 'callback_field_comments'));

        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // render
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $output = $crud->render();
        $this->view('main/main_ecommerce_manage', $output,
            $this->cms_complete_navigation_name('manage_order'));
    }

    public function order_before_insert($post_array){
        return TRUE;
    }

    public function order_after_insert($post_array, $primary_key){
        $success = $this->order_after_insert_or_update($post_array, $primary_key);
        return $success;
    }

    public function order_before_update($post_array, $primary_key){
        return TRUE;
    }

    public function order_after_update($post_array, $primary_key){
        $success = $this->order_after_insert_or_update($post_array, $primary_key);
        return $success;
    }

    public function order_before_delete($primary_key){
        $this->db->delete($this->cms_complete_table_name('commerce_order_detail'),
              array('order_no'=>$primary_key));
        return TRUE;
    }

    public function order_after_delete($primary_key){
        return TRUE;
    }

    public function order_after_insert_or_update($post_array, $primary_key){
        return TRUE;
    }

    // returned on insert and edit
    public function callback_field_items($value, $primary_key){
        $module_path = $this->cms_module_path();
        $query = $this->db->select('B.*, C.product_name, C.product_id AS product_no, C.brand, C.type, D.size')
            ->from($this->cms_complete_table_name('commerce_order A'))
            ->join($this->cms_complete_table_name('commerce_order_detail B'), 'B.order_no = A.order_no', 'RIGHT')
            ->join($this->cms_complete_table_name('commerce_product C'), 'C.id = B.product_id')
            ->join($this->cms_complete_table_name('commerce_size D'), 'D.id = B.size')
            ->where('A.id', $primary_key)
            ->get();
        $result = $query->result();
        $data['result'] = $result;
        
        return $this->load->view($this->cms_module_path().'/field_product_order',$data, TRUE);
    }

    // returned on view
    public function callback_column_items($value, $row){
        $module_path = $this->cms_module_path();

        $query = $this->db->select('product_id, size, qty, subtotal')
            ->from($this->cms_complete_table_name('commerce_order_detail'))
            ->where('order_no', $row->order_no)
            ->get();
        $num_row = $query->num_rows();
        // show how many records
        if($num_row>1){
            return $num_row .' Items';
        }else if($num_row>0){
            return $num_row .' Item';
        }else{
            return 'No_Photo';
        }
    }

    // PRODUCT ================================================================================
    private function randomize_string($value){
        $time = date('Y:m:d H:i:s');
        return substr(md5($value.$time),0,6);
    }

    public function product_category_list()
    {
        $data = array();
        $data['categories'] = $this->get_available_product_category();
        $data['module_path'] = $this->cms_module_path();
        $this->view($this->cms_module_path().'/widget_product_category', $data);
    }

    public function shop_product($id=NULL)
    {
        $category = $id;
        
        $data['current'] = $id;

        $SQL   = "SELECT category_name FROM ".cms_table_name('commerce_category')." WHERE id = "."'$id'";
        $query = $this->db->query($SQL);
        $row   = $query->row();

        if($row != NULL){
            $data['category_name'] = $row->category_name;
        }else{
            $data['category_name'] = '';
        }
        $data['product_list'] = $this->cms_ecommerce_product($category,NULL);    

        $this->view('main/main_ecommerce_shop', $data);
    }

    public function manage_size(){
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // initialize groceryCRUD
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $crud = $this->new_crud();
        $crud->unset_jquery();

        // set model
        //$crud->set_model($this->cms_module_path().'/grocerycrud_slide_model');

        // adjust groceryCRUD's language to No-CMS's language
        $crud->set_language($this->cms_language());

        // table name
        $crud->set_table($this->cms_complete_table_name('commerce_size'));

        // set subject
        $crud->set_subject('Size');

        // displayed columns on list
        $crud->columns('id','size');
        // displayed columns on edit operation
        $crud->edit_fields('size');
        // displayed columns on add operation
        $crud->add_fields('size');

        // caption of each columns
        $crud->display_as('size','Size');

        $crud->required_fields('size');


        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // HINT: Put callback here
        // (documentation: httm://www.grocerycrud.com/documentation/options_functions)
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $crud->callback_before_insert(array($this,'gallery_before_insert'));
        $crud->callback_before_update(array($this,'gallery_before_update'));
        $crud->callback_before_delete(array($this,'gallery_before_delete'));
        $crud->callback_after_insert(array($this,'gallery_after_insert'));
        $crud->callback_after_update(array($this,'gallery_after_update'));
        $crud->callback_after_delete(array($this,'gallery_after_delete'));



        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // render
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $output = $crud->render();
        $this->view('main/main_ecommerce_manage', $output,
            $this->cms_complete_navigation_name('manage_size'));
    }

    public function manage_product_category(){
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // initialize groceryCRUD
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $crud = $this->new_crud();
        $crud->unset_jquery();

        // set model
        //$crud->set_model($this->cms_module_path().'/grocerycrud_slide_model');

        // adjust groceryCRUD's language to No-CMS's language
        $crud->set_language($this->cms_language());

        // table name
        $crud->set_table($this->cms_complete_table_name('commerce_category'));

        // set subject
        $crud->set_subject('Product Category');

        // displayed columns on list
        $crud->columns('id','category_name');
        // displayed columns on edit operation
        $crud->edit_fields('category_name');
        // displayed columns on add operation
        $crud->add_fields('category_name');

        // caption of each columns
        $crud->display_as('category_name','Category Name');

        $crud->required_fields('category_name');


        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // HINT: Put callback here
        // (documentation: httm://www.grocerycrud.com/documentation/options_functions)
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $crud->callback_before_insert(array($this,'gallery_before_insert'));
        $crud->callback_before_update(array($this,'gallery_before_update'));
        $crud->callback_before_delete(array($this,'gallery_before_delete'));
        $crud->callback_after_insert(array($this,'gallery_after_insert'));
        $crud->callback_after_update(array($this,'gallery_after_update'));
        $crud->callback_after_delete(array($this,'gallery_after_delete'));



        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // render
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $output = $crud->render();
        $this->view('main/main_ecommerce_manage', $output,
            $this->cms_complete_navigation_name('manage_product_category'));
    }

    public function manage_product_frame(){
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // initialize groceryCRUD
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $crud = $this->new_crud();
        $crud->unset_jquery();

        // set model
        //$crud->set_model($this->cms_module_path().'/grocerycrud_slide_model');

        // adjust groceryCRUD's language to No-CMS's language
        $crud->set_language($this->cms_language());

        // table name
        $crud->set_table($this->cms_complete_table_name('product_frame'));

        // set subject
        $crud->set_subject('Product Frame');

        // displayed columns on list
        $crud->columns('id','nama','harga','stok');
        // displayed columns on edit operation
        $crud->edit_fields('id','nama','harga','stok');
        // displayed columns on add operation
        $crud->add_fields('id','nama','harga','stok');

        // caption of each columns
        $crud->display_as('nama','Nama Frame');
        $crud->display_as('id','ID Product');

        $crud->required_fields('id','nama','harga','stok');
        $crud->set_rules('stok', 'Stok', 'integer');


        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // HINT: Put callback here
        // (documentation: httm://www.grocerycrud.com/documentation/options_functions)
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $crud->callback_before_insert(array($this,'gallery_before_insert'));
        $crud->callback_before_update(array($this,'gallery_before_update'));
        $crud->callback_before_delete(array($this,'gallery_before_delete'));
        $crud->callback_after_insert(array($this,'gallery_after_insert'));
        $crud->callback_after_update(array($this,'gallery_after_update'));
        $crud->callback_after_delete(array($this,'gallery_after_delete'));



        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // render
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $output = $crud->render();
        $this->view('main/main_ecommerce_manage', $output,
            $this->cms_complete_navigation_name('manage_product_frame'));
    }

    public function manage_product(){
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // initialize groceryCRUD
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $crud = $this->new_crud();
        $crud->unset_jquery();

        // check state
        $state = $crud->getState();
        $state_info = $crud->getStateInfo();
        $primary_key = isset($state_info->primary_key)? $state_info->primary_key : NULL;

        switch($state){
            case 'unknown': break;
            case 'list' : break;
            case 'add' :  break;
            case 'edit' : break;
            case 'delete' : break;
            case 'insert' : break;
            case 'update' : break;
            case 'ajax_list' : break;
            case 'ajax_list_info': break;
            case 'insert_validation': break;
            case 'update_validation': break;
            case 'upload_file': break;
            case 'delete_file': break;
            case 'ajax_relation': break;
            case 'ajax_relation_n_n': break;
            case 'success': break;
            case 'export': break;
            case 'print': break;
        }

        // set model
        //$crud->set_model($this->cms_module_path().'/grocerycrud_article_model');

        // adjust groceryCRUD's language to No-CMS's language
        $crud->set_language($this->cms_language());

        // table name
        $crud->set_table($this->cms_complete_table_name('commerce_product'));

        // set subject
        $crud->set_subject('Product');

        // displayed columns on list
        $crud->columns('product_name','brand','type','old_price','price','availibility','size','category');
        // displayed columns on edit operation
        $crud->edit_fields('brand','product_name','type','weight','show_m2','old_price','price','tax','availibility','info','size','category','photos');
        // displayed columns on add operation
        $crud->add_fields('product_id','product_name','brand','type','weight','show_m2','old_price','price','tax','availibility','info','size','category','photos');

        // $crud->field_type('category','dropdown',
        //     array('1' => 'Doormats', '2' => 'Rugs','3' => 'Special'));
        $crud->field_type('availibility','true_false');
        $crud->field_type('show_m2','true_false');
        $crud->field_type('info','text');
        $crud->required_fields('product_id','product_name','brand','type','price','availibility');
        $crud->unique_fields('product_id');
        $crud->set_rules('availibility', 'Available', 'integer');
        $crud->set_rules('show_m2', 'Show', 'integer');
        $crud->unset_read();

        // caption of each columns
        $crud->display_as('product_id','Product ID');
        $crud->display_as('product_name','Product Name');
        $crud->display_as('brand','Brand');
        $crud->display_as('type','Type');
        $crud->display_as('weight','Weight/Kg');
        $crud->display_as('show_m2','Show "per m2"');
        $crud->display_as('price','Price');
        $crud->display_as('old_price','Old Price');
        $crud->display_as('tax','Iclude Tax (%)');
        $crud->display_as('availibility','Available');
        $crud->display_as('info','Description');
        $crud->display_as('size','Product Size');
        $crud->display_as('category','Category');
        $crud->display_as('photos','Photos');

        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // HINT: Put set relation (lookup) codes here
        // (documentation: http://www.grocerycrud.com/documentation/options_functions/set_relation)
        // eg:
        //      $crud->set_relation( $field_name , $related_table, $related_title_field , $where , $order_by );
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //$crud->set_relation('author_user_id', cms_table_name('main_user'), 'user_name');

        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // HINT: Put set relation_n_n (detail many to many) codes here
        // (documentation: http://www.grocerycrud.com/documentation/options_functions/set_relation_n_n)
        // eg:
        //      $crud->set_relation_n_n( $field_name, $relation_table, $selection_table, $primary_key_alias_to_this_table,
        //          $primary_key_alias_to_selection_table , $title_field_selection_table, $priority_field_relation );
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $crud->set_relation_n_n('size',
            $this->cms_complete_table_name('commerce_product_size'),
            $this->cms_complete_table_name('commerce_size'),
            'product_id', 'size_id',
            'size', NULL);

        $crud->set_relation_n_n('category',
            $this->cms_complete_table_name('commerce_product_category'),
            $this->cms_complete_table_name('commerce_category'),
            'product_id', 'category_id',
            'category_name', NULL);

        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // HINT: Put custom field type here
        // (documentation: http://www.grocerycrud.com/documentation/options_functions/field_type)
        // eg:
        //      $crud->field_type( $field_name , $field_type, $value  );
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //$crud->field_type('author_user_id', 'hidden');
        //$crud->field_type('date', 'hidden');
        //$crud->field_type('allow_comment', 'true_false');
        $crud->unset_texteditor('info');


        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // HINT: Put callback here
        // (documentation: httm://www.grocerycrud.com/documentation/options_functions)
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $crud->callback_before_insert(array($this,'product_before_insert'));
        $crud->callback_before_update(array($this,'product_before_update'));
        $crud->callback_before_delete(array($this,'product_before_delete'));
        $crud->callback_after_insert(array($this,'product_after_insert'));
        $crud->callback_after_update(array($this,'product_after_update'));
        $crud->callback_after_delete(array($this,'product_after_delete'));

        $crud->callback_column('photos',array($this, 'product_callback_column_photos'));
        $crud->callback_field('photos',array($this, 'product_callback_field_photos'));

        //$crud->callback_column('photos',array($this, 'callback_column_photos'));
        //$crud->callback_field('photos',array($this, 'callback_field_photos'));
        //$crud->callback_column('comments',array($this, 'callback_column_comments'));
        //$crud->callback_field('comments',array($this, 'callback_field_comments'));

        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // render
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $output = $crud->render();
        $this->view('main/main_ecommerce_manage', $output,
            $this->cms_complete_navigation_name('manage_product'));
    }

    public function manage_userrr(){
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // initialize groceryCRUD
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $crud = $this->new_crud();
        $crud->unset_jquery();

        // check state
        $state = $crud->getState();
        $state_info = $crud->getStateInfo();
        $primary_key = isset($state_info->primary_key)? $state_info->primary_key : NULL;

        switch($state){
            case 'unknown': break;
            case 'list' : break;
            case 'add' :  break;
            case 'edit' : break;
            case 'delete' : break;
            case 'insert' : break;
            case 'update' : break;
            case 'ajax_list' : break;
            case 'ajax_list_info': break;
            case 'insert_validation': break;
            case 'update_validation': break;
            case 'upload_file': break;
            case 'delete_file': break;
            case 'ajax_relation': break;
            case 'ajax_relation_n_n': break;
            case 'success': break;
            case 'export': break;
            case 'print': break;
        }

        // set model
        //$crud->set_model($this->cms_module_path().'/grocerycrud_article_model');

        // adjust groceryCRUD's language to No-CMS's language
        $crud->set_language($this->cms_language());

        // table name
        $crud->set_table($this->cms_complete_table_name('commerce_product'));

        // set subject
        $crud->set_subject('Product');

        // displayed columns on list
        $crud->columns('product_name','brand','type','old_price','price','availibility','size','category');
        // displayed columns on edit operation
        $crud->edit_fields('brand','product_name','type','weight','show_m2','old_price','price','tax','availibility','info','size','category','photos');
        // displayed columns on add operation
        $crud->add_fields('product_id','product_name','brand','type','weight','show_m2','old_price','price','tax','availibility','info','size','category','photos');

        // $crud->field_type('category','dropdown',
        //     array('1' => 'Doormats', '2' => 'Rugs','3' => 'Special'));
        $crud->field_type('availibility','true_false');
        $crud->field_type('show_m2','true_false');
        $crud->field_type('info','text');
        $crud->required_fields('product_id','product_name','brand','type','price','availibility');
        $crud->unique_fields('product_id');
        $crud->set_rules('availibility', 'Available', 'integer');
        $crud->set_rules('show_m2', 'Show', 'integer');
        $crud->unset_read();

        // caption of each columns
        $crud->display_as('product_id','Product ID');
        $crud->display_as('product_name','Product Name');
        $crud->display_as('brand','Brand');
        $crud->display_as('type','Type');
        $crud->display_as('weight','Weight/Kg');
        $crud->display_as('show_m2','Show "per m2"');
        $crud->display_as('price','Price');
        $crud->display_as('old_price','Old Price');
        $crud->display_as('tax','Iclude Tax (%)');
        $crud->display_as('availibility','Available');
        $crud->display_as('info','Description');
        $crud->display_as('size','Product Size');
        $crud->display_as('category','Category');
        $crud->display_as('photos','Photos');

        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // HINT: Put set relation (lookup) codes here
        // (documentation: http://www.grocerycrud.com/documentation/options_functions/set_relation)
        // eg:
        //      $crud->set_relation( $field_name , $related_table, $related_title_field , $where , $order_by );
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //$crud->set_relation('author_user_id', cms_table_name('main_user'), 'user_name');

        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // HINT: Put set relation_n_n (detail many to many) codes here
        // (documentation: http://www.grocerycrud.com/documentation/options_functions/set_relation_n_n)
        // eg:
        //      $crud->set_relation_n_n( $field_name, $relation_table, $selection_table, $primary_key_alias_to_this_table,
        //          $primary_key_alias_to_selection_table , $title_field_selection_table, $priority_field_relation );
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $crud->set_relation_n_n('size',
            $this->cms_complete_table_name('commerce_product_size'),
            $this->cms_complete_table_name('commerce_size'),
            'product_id', 'size_id',
            'size', NULL);

        $crud->set_relation_n_n('category',
            $this->cms_complete_table_name('commerce_product_category'),
            $this->cms_complete_table_name('commerce_category'),
            'product_id', 'category_id',
            'category_name', NULL);

        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // HINT: Put custom field type here
        // (documentation: http://www.grocerycrud.com/documentation/options_functions/field_type)
        // eg:
        //      $crud->field_type( $field_name , $field_type, $value  );
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //$crud->field_type('author_user_id', 'hidden');
        //$crud->field_type('date', 'hidden');
        //$crud->field_type('allow_comment', 'true_false');
        $crud->unset_texteditor('info');


        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // HINT: Put callback here
        // (documentation: httm://www.grocerycrud.com/documentation/options_functions)
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $crud->callback_before_insert(array($this,'product_before_insert'));
        $crud->callback_before_update(array($this,'product_before_update'));
        $crud->callback_before_delete(array($this,'product_before_delete'));
        $crud->callback_after_insert(array($this,'product_after_insert'));
        $crud->callback_after_update(array($this,'product_after_update'));
        $crud->callback_after_delete(array($this,'product_after_delete'));

        $crud->callback_column('photos',array($this, 'product_callback_column_photos'));
        $crud->callback_field('photos',array($this, 'product_callback_field_photos'));

        //$crud->callback_column('photos',array($this, 'callback_column_photos'));
        //$crud->callback_field('photos',array($this, 'callback_field_photos'));
        //$crud->callback_column('comments',array($this, 'callback_column_comments'));
        //$crud->callback_field('comments',array($this, 'callback_field_comments'));

        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // render
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $output = $crud->render();
        $this->view('main/main_ecommerce_manage', $output,
            $this->cms_complete_navigation_name('manage_product'));
    }

    public function product_before_insert($post_array){
        return $post_array;
    }

    public function product_after_insert($post_array, $primary_key){
        $success = $this->product_after_insert_or_update($post_array, $primary_key);
        return $success;
    }

    public function product_before_update($post_array, $primary_key){
        return TRUE;
    }

    public function product_after_update($post_array, $primary_key){
        $success = $this->product_after_insert_or_update($post_array, $primary_key);
        return $success;
    }

    public function product_before_delete($primary_key){
        //delete corresponding photo
        $this->db->delete($this->cms_complete_table_name('commerce_photo'),
            array('photo_id'=>$primary_key));
        return TRUE;
    }

    public function product_after_delete($primary_key){
        return TRUE;
    }

    public function product_after_insert_or_update($post_array, $primary_key){
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //
        // SAVE CHANGES OF photo
        //  * The photo data in in json format.
        //  * It can be accessed via $_POST['md_real_field_photos_col']
        //
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $data = json_decode($this->input->post('md_real_field_photos_col'), TRUE);
        $insert_records = $data['insert'];
        $update_records = $data['update'];
        $delete_records = $data['delete'];
        $real_column_names = array('photo_id', 'url');
        $set_column_names = array();
        $many_to_many_column_names = array();
        $many_to_many_relation_tables = array();
        $many_to_many_relation_table_columns = array();
        $many_to_many_relation_selection_columns = array();
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //  DELETED DATA
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        foreach($delete_records as $delete_record){
            $detail_primary_key = $delete_record['primary_key'];
            $this->db->delete($this->cms_complete_table_name('commerce_photo'),
                 array('photo_id'=>$detail_primary_key));
        }
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //  INSERTED DATA
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        foreach($insert_records as $insert_record){
            $this->load->library('image_moo');
            $upload_path = FCPATH.'modules/main/assets/uploads/';

            $record_index = $insert_record['record_index'];
            $tmp_name = $_FILES['md_field_photos_col_url_'.$record_index]['tmp_name'];
            $file_name = $_FILES['md_field_photos_col_url_'.$record_index]['name'];
            $file_name = $this->randomize_string($file_name).$file_name;
            move_uploaded_file($tmp_name, $upload_path.$file_name);
            $data = array(
                'url' => $file_name,
            );
            $data['product_id'] = $primary_key;
            $this->db->insert($this->cms_complete_table_name('commerce_photo'), $data);

            $thumbnail_name = 'thumb_'.$file_name;
            $this->image_moo->load($upload_path.$file_name)->resize(800,370)->save($upload_path.$thumbnail_name,true);
        }
        return TRUE;
    }

    // returned on insert and edit
    public function product_callback_field_photos($value, $primary_key){
        $module_path = $this->cms_module_path();
        $this->config->load('grocery_crud');
        $date_format = $this->config->item('grocery_crud_date_format');

        if(!isset($primary_key)) $primary_key = -1;
        $query = $this->db->select('photo_id, url')
            ->from($this->cms_complete_table_name('commerce_photo'))
            ->where('product_id', $primary_key)
            ->get();
        $result = $query->result_array();

        // get options
        $options = array();
        $data = array(
            'result' => $result,
            'options' => $options,
            'date_format' => $date_format,
            'module_path' => $this->cms_module_path(),
        );
        return $this->load->view($this->cms_module_path().'/field_product_photos',$data, TRUE);
    }

    // returned on view
    public function product_callback_column_photos($value, $row){
        $module_path = $this->cms_module_path();
        $query = $this->db->select('photo_id, url')
            ->from($this->cms_complete_table_name('commerce_photo'))
            ->where('product_id', $row->product_id)
            ->get();
        $num_row = $query->num_rows();
        // show how many records
        if($num_row>1){
            return $num_row .' Photos';
        }else if($num_row>0){
            return $num_row .' Photo';
        }else{
            return 'No_Photo';
        }
    }

    public function main_product($id)
    {
        $this->cms_guard_page('main_product');
        $row = $this->cms_ecommerce_product_detail($id);  

        $data['product'] = $row;

        $this->load->helper('cookie');
        $cookie = array(
            'name'   => 'recent_view',
            'value'  => $id,
            'expire' => '86500'
        );

        $this->input->set_cookie($cookie);
        $this->view('main/main_ecommerce_product', $data, 'main_product');
    }

    public function new_product($id=NULL,$limit=NULL)
    {
        $category = $id;

        $data['product_list'] = $this->cms_ecommerce_product($category,$limit);        
        $this->view('main/widget_ecommerce_shop', $data);
    }

    public function product_have_buy($id=NULL)
    {
        $user_id    = $this->cms_user_id();
        $product_id=$this->input->cookie('recent_view');
        $data['product_list'] = $this->cms_ecommerce_have_buy($product_id,NULL);        
    
        $this->view('main/main_ecommerce_have_buy', $data);
    }

    public function product_recent_view()
    {
        $this->load->helper('cookie');
        $product_id=$this->input->cookie('recent_view');
        $data['product_list'] = $this->cms_ecommerce_have_buy(NULL,$product_id);        
    
        $this->view('main/main_ecommerce_recent', $data);
    }

    // CART =======================================================================================================

    public function cart()
    {
        $this->cms_guard_page('cart');

        $this->load->library('cart');

        $data['product'] = '';
        $data['shipping_fee'] = $this->get_shipping_fee();
        $this->view('main/main_ecommerce_cart', $data, 'cart');
    }

    public function product_add_wishlist()
    {
        $product_id  = $this->input->post('product_id');
        $qty  = $this->input->post('qty');
        $user_id    = $this->cms_user_id();

        if($user_id == NULL)
        {
            $status_text = "You're not logged in, Please login first";
        }
        else
        {
            $product     = $this->cms_ecommerce_add_wishlist($product_id,$user_id,$qty);
        
            $status_text = "";
            if($product == TRUE){
                $status_text = "Success added to Wishlist";
            }else{
                $status_text = "Product already on your wishlist";
            }
        }

        $data['message'] = $status_text;
        echo json_encode($data);
    }

    public function product_add_cart()
    {
        $product_id     = $this->input->post('product_id');
        $qty            = $this->input->post('qty');
        $size            = $this->input->post('size');
        $user_id        = $this->cms_user_id();

        $row = $this->cms_ecommerce_product_cart($product_id);  
        //$data['product'] = $row;

        $this->load->library('cart');
        $data = array(
           'id'        => $row['id'],
           'qty'       => $qty,
           'price'     => $row['price'],
           'weight'    => $row['weight'],
           'name'      => $row['product_name'],
           'brand'     => $row['brand'],
           'type'      => $row['type'],
           'image_url' => $row['photos'],
           'size'      => $size,
           'size_list' => $row['size']
        );

        $this->cart->insert($data);

        echo "ok";
    }

    public function product_remove_cart($id)
    {
        $this->cms_guard_page('cart');

        $this->load->library('cart');

        $data = array(
           'rowid' => $id,
           'qty'   => 0
        );
        $this->cart->update($data);

        $data2['shipping_fee'] = $this->get_shipping_fee();

        //var_dump($data);die();
        $this->view('main/main_ecommerce_cart',$data2,'cart');
    }

    public function checkout()
    {
        $this->cms_guard_page('checkout');
        $check_status = $this->check_order_exist($this->cms_user_id());

        if($check_status == FALSE)
        {
            redirect('main/account_order','refresh');
        }

        $this->load->library('cart');
        $data = '';

        if($_POST){
            $data = $_POST;
        }
        
        $this->cart->update($data);
        $data2['shipping_fee'] = $this->get_shipping_fee();
        
        $this->view('main/main_ecommerce_checkout',$data2,'checkout');
    }

    public function shipping_info()
    {
        $this->cms_guard_page('shipping_info');
        $SQL           = "
        SELECT A.*, B.kecamatan, B.kota 
        FROM ".cms_table_name('main_user_address A')." 
        LEFT JOIN ".cms_table_name('shipping_jne B')." ON B.noongkir = A.city
        WHERE A.user_name = '". $this->cms_user_name()."' LIMIT 1";
        $query         = $this->db->query($SQL);
        $row           = $query->row();
        
        $user_name     = $row->user_name;
        $exist_address = $row->first_name.' '.$row->last_name.', '.$row->address.' '.$row->state.', '.$row->kecamatan.', '.$row->kota.', '.$row->postal_code;

        $data['exist_address'] = $exist_address;
        $data['city_code'] = $row->city;
        $this->view('main/main_ecommerce_shipping', $data, 'shipping_info');
    }

    private function randomize_order_id()
    {
        $time = date('mdHi');
        $user_id = $this->cms_user_id();
        return $time.$user_id;
    }

    public function mail_template()
    {
        $data['site_url'] = $this->cms_get_config('base_url');
        $data['bank'] = 'bca';
        $this->load->library('cart');
        $this->view('main/order_mail_template', $data);
    }

    public function payment_terms()
    {
        $this->cms_guard_page('payment_terms');

        $this->load->library('cart');

        //get user input
        //$user_name          = $this->input->post('user_name');
        $account_number     = $this->input->post('account_number');
        $bank               = $this->input->post('bank');
        $account_name       = $this->input->post('account_name');
        $address            = $this->input->post('address');
        $city_code          = $this->input->post('city_code');
        $email              = $this->cms_user_email();
        $user_name          = $this->cms_user_name();

        $id_order           = $this->randomize_order_id();
        $shipping_fee       = $this->get_shipping_fee_other($city_code);

        $grand_total        = intval($this->cart->total()) + intval($shipping_fee);
        $total_fee          = $grand_total;
        $total_qty          = $this->cart->total_items();

        if(!$address)
            redirect('main/payment_terms','refresh');
        if (!$account_number)
            $account_number = '';
        if (!$bank)
            $bank = '';
        if (!$account_name)
            $account_name = '';


        //set validation rule
        $this->form_validation->set_rules('bank', 'Bank', 'required|xss_clean');
        $this->form_validation->set_rules('account_name', 'Account Name', 'required|xss_clean');
        $this->form_validation->set_rules('account_number', 'Account Number', 'required|numeric|xss_clean');

        if ($this->form_validation->run()) {

            $data = array(
                "order_no"          => $id_order,
                "date"              => date('Y-m-d'),
                "time"              => date('H:i:s'),
                "status"            => '0',
                "shipping"          => $shipping_fee,
                "total"             => $total_fee,
                "total_qty"         => $total_qty,
                "user_id"           => $this->cms_user_id(),
                "shipping_address"  => $address,
                "bank"              => $bank,
                "bank_account_no"   => $account_number,
                "bank_account_name" => $account_name
            );


            $detail = array();

            $i = 1;
            foreach ($this->cart->contents() as $items) {
                $detail[] = array(
                    "order_no"   =>$id_order,
                    "product_id" =>$items['id'],
                    "qty"        =>$items['qty'],
                    "subtotal"   =>$items['subtotal'],
                    "size"       =>$items['size']
                );
            }
            $execute = $this->cms_do_save_order($id_order,$data,$detail);
            
            if($execute == TRUE){
                if($this->cms_get_config('send_mail') == 'true')
                {
                    //Send Mail to User
                    $data['user'] = $user_name;
                    $data['grand_total'] = $grand_total;
                	$data['site_url'] = $this->cms_get_config('base_url');
                    //Sending Mail Order
                    $email_from_address = $this->cms_get_config('cms_email_reply_address');
                    $email_from_name    = $this->cms_get_config('cms_email_reply_name');
                    $email_to_address   = $this->cms_user_email();

                    $email_subject = 'Order Detail '+$id_order;
                    $email_message = $this->view('main/order_mail_template', $data, false);

                    $send = $this->cms_send_email($email_from_address, $email_from_name, $email_to_address, $email_subject, $email_message);

                    //send Mail to Admin
                    $email_to_address   = 'goldendragon@flamboo.com';

                    $email_subject = 'Order Baru Golden Dragon '+$id_order;
                    $email_message = 'Anda mendapat pesanan baru dari pesanan online. Pembayaran di transfer melalui bank '.$bank.' senilai '.$grand_total.'. Silahkan akses website admin untuk melihat detail order.';

                    $send = $this->cms_send_email($email_from_address, $email_from_name, $email_to_address, $email_subject, $email_message);
                }
                $this->cart->destroy();
                redirect('main/finish_order/'.$id_order);
            }
        }
        else 
        {
            $data = array(
                "current"       => 'account_address',
                "address"       => $address,
                "email"         => $email,
                "city_code"     => $city_code
            );
            $this->view('main/main_ecommerce_payment_terms', $data, 'payment_terms');
        }       
    }

    public function finish_order($id_order)
    {
        if(!$id_order){
            redirect('main/account_order','refresh');
        }

        $this->cms_guard_page('finish_order');

        $this->load->helper('share');

        $user_id    = $this->cms_user_id();
        $user_email = $this->cms_user_email();
        $user_name  = $this->cms_user_name();

        $SQL   = "
            SELECT A.*, B.first_name, B.last_name, C.product_id, D.product_name, D.brand, E.url, D.id 
            FROM ".cms_table_name('commerce_order A')."
            JOIN ".cms_table_name('main_user B')." ON B.user_id = A.user_id
            JOIN ".cms_table_name('commerce_order_detail C')." ON C.order_no = A.order_no
            LEFT JOIN ".cms_table_name('commerce_product D')." ON D.product_id = C.product_id
            LEFT JOIN ".cms_table_name('commerce_photo E')." ON E.product_id = C.product_id
            WHERE A.user_id = '". $user_id."' AND A.order_no = '". $id_order."' LIMIT 1";

        $query = $this->db->query($SQL);
        $row   = $query->row();

        if($query->num_rows() != 0){
            $data = array(
                "user_email"   => $user_email,
                "full_name"    => $row->first_name.' '.$row->last_name,
                "order_number" => $id_order,
                "qty_order"    => $row->total_qty,
                "bank"         => $row->bank,
                "product_name" => $row->product_name,
                "brand"        => $row->brand,
                "url"          => $row->url,
                "product_id"   => $row->id,
                "grand_total"  => $row->total
            );
            $this->view('main/main_ecommerce_finish_order', $data, 'finish_order');
        }
        else{
            redirect('main/account_order','refresh');
        }
    }

    public function get_unpayment_order()
    {
        return $this->No_CMS_Model->get_unpayment_order($this->cms_user_id());
    }

    public function payment_confirmation()
    {
        $order_no = $this->get_unpayment_order();
        $this->cms_guard_page('services');
        $data['order_number'] = $order_no;

        $order_no       = $this->input->post('order_no');
        $payment_method = $this->input->post('payment_method');
        $transfer_date  = $this->input->post('transfer_date');
        $user_id        = $this->cms_user_id();

        //set validation rule
        $this->form_validation->set_rules('order_no', 'Order No', 'required|numeric|xss_clean');
        $this->form_validation->set_rules('payment_method', 'Payment Methos', 'required|xss_clean');
        $this->form_validation->set_rules('transfer_date', 'Transfer Date', 'required');

        
        if ($this->form_validation->run()) {
            $dmy = date("Y-m-d", strtotime(str_replace('/','-', $transfer_date)));
            $data = array(
                "transfer_date"     => $dmy,
                "payment_by"        => $payment_method,
                "status"            => '1',
            );
            $execute = $this->cms_do_confirm_payment($order_no,$user_id,$data);
            if($execute == TRUE){
                redirect('main/account_order');
            }
        }else{
            $data['current'] ='payment_confirmation';
            $this->view('main/main_ecommerce_payment_confirm', $data, 'services');
        }
    }

    public function shipping_payment_terms()
    {
        $this->cms_guard_page('shipping_payment_terms');
        $data = array(
            "submenu_screen" => $this->cms_submenu_screen(NULL),
            "current"   => 'spt'
        );
        $this->view('main/main_shipping_payment_terms', $data, 'shipping_payment_terms');
    }

    public function get_shipping_fee(){
        $SQL   = "SELECT city FROM ".cms_table_name('main_user_address')." WHERE user_name = "."'".$this->cms_user_email()."'";
        $query = $this->db->query($SQL);

        if($query->num_rows() > 0){
            $row   = $query->row();
            $user_city = $row->city;
            $shipping_fee = $this->cms_ecommerce_shipping($user_city);
            //var_dump($shipping_fee);die();
            if($shipping_fee != NULL){
                $this->load->library('cart');
                $weight = 0;
                foreach ($this->cart->contents() as $items) {
                    $items_weight = floatval($items['weight'])*intval($items['qty']);
                    $weight = floatval($items_weight)+floatval($weight);
                }
                $result=intval($shipping_fee->harga)*ceil($weight);
                return $result;
            }else{
                return 'No City Match';
            }
        }else
        {
            return 'No City Detected';
        }
    }

    public function lookup()
    {
        $keyword=$this->input->post('term');
        $data['response']='false';
        $query=$this->cms_find_city($keyword);

        if(! empty($query))
        {
            $data['response']='true';
            $data['message']= array();
            foreach($query as $row)
            {
                $data['message'][]= array(
                    'id'=>$row['noongkir'],
                    'value'=>$row['kecamatan'].', '.$row['kota'],
                    'harga'=>$row['harga']
                );
            }
        }
        echo json_encode($data);
    }

    public function get_shipping_fee_other($city){
        $user_city = $city;
        $shipping_fee = $this->cms_ecommerce_shipping($user_city);
        //var_dump($shipping_fee);die();
        if($shipping_fee != NULL){
            $this->load->library('cart');
            $weight = 0;
            foreach ($this->cart->contents() as $items) {
                $items_weight = floatval($items['weight'])*intval($items['qty']);
                $weight = floatval($items_weight)+floatval($weight);
            }
            $result=intval($shipping_fee->harga)*ceil($weight);
            return $result;
        }else{
            return 'No City Match';
        }
    }

    // GALLERY ==========================================================================================
    public function gallery_product($id=null)
    {
        $category = $id;
        $title = $id;

        if($id == 'residence'){
            $category = 1;
            $title = "Residence";
        }else if($id == 'function'){
            $category = 2;
            $title = "Function Room";
        }else if($id == 'staircase'){
            $category = 3;
            $title = "Staircase";
        }else if($id == 'office'){
            $category = 4;
            $title = "Office";
        }else if($id == 'hotel'){
            $category = 5;
            $title = "Hotel & Apartment";
        }else if($id == 'restaurant'){
            $category = 6;
            $title = "Restaurant";
        }else{
            $title = "All";
        }

        $data['current'] = $id;
        $data['title'] = $title;

        $data['gallery_list'] = $this->cms_ecommerce_gallery($category);        
        $this->view('main/main_ecommerce_gallery', $data);
    }

    public function manage_gallery(){
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // initialize groceryCRUD
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $crud = $this->new_crud();
        $crud->unset_jquery();

        // set model
        //$crud->set_model($this->cms_module_path().'/grocerycrud_slide_model');

        // adjust groceryCRUD's language to No-CMS's language
        $crud->set_language($this->cms_language());

        // table name
        $crud->set_table($this->cms_complete_table_name('gallery_product'));

        // set subject
        $crud->set_subject('Gallery');

        // displayed columns on list
        $crud->columns('product_id','place','category','image_url');
        // displayed columns on edit operation
        $crud->edit_fields('product_id','place','category','image_url');
        // displayed columns on add operation
        $crud->add_fields('product_id','place','category','image_url');

        // caption of each columns
        $crud->display_as('product_id','Product ID / Name');
        $crud->display_as('place','Place / Location');
        $crud->display_as('image_url','Image (243*162px)');

        $crud->required_fields('image_url','product_id','place','category');

        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // HINT: Put set relation (lookup) codes here
        // (documentation: http://www.grocerycrud.com/documentation/options_functions/set_relation)
        // eg:
        //      $crud->set_relation( $field_name , $related_table, $related_title_field , $where , $order_by );
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $crud->set_relation('product_id', cms_table_name('commerce_product'), '{product_id} - {product_name}');

        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // HINT: Put set relation_n_n (detail many to many) codes here
        // (documentation: http://www.grocerycrud.com/documentation/options_functions/set_relation_n_n)
        // eg:
        //      $crud->set_relation_n_n( $field_name, $relation_table, $selection_table, $primary_key_alias_to_this_table,
        //          $primary_key_alias_to_selection_table , $title_field_selection_table, $priority_field_relation );
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////


        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // HINT: Put custom field type here
        // (documentation: http://www.grocerycrud.com/documentation/options_functions/field_type)
        // eg:
        //      $crud->field_type( $field_name , $field_type, $value  );
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $crud->set_field_upload('image_url','modules/'.$this->cms_module_path().'/assets/uploads/gallery');
        $crud->field_type('category','dropdown',
            array('1' => 'Residence', '2' => 'Function Room','3' => 'Stair Case','4' => 'Office','5' => 'Hotel & Apartment','6' => 'Restaurant'));


        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // HINT: Put callback here
        // (documentation: httm://www.grocerycrud.com/documentation/options_functions)
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $crud->callback_before_insert(array($this,'gallery_before_insert'));
        $crud->callback_before_update(array($this,'gallery_before_update'));
        $crud->callback_before_delete(array($this,'gallery_before_delete'));
        $crud->callback_after_insert(array($this,'gallery_after_insert'));
        $crud->callback_after_update(array($this,'gallery_after_update'));
        $crud->callback_after_delete(array($this,'gallery_after_delete'));



        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // render
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $output = $crud->render();
        $this->view('main/main_ecommerce_manage', $output,
            $this->cms_complete_navigation_name('manage_gallery'));
    }

    public function gallery_before_insert($post_array){
        return TRUE;
    }

    public function gallery_after_insert($post_array, $primary_key){
        $success = $this->gallery_after_insert_or_update($post_array, $primary_key);
        return $success;
    }

    public function gallery_before_update($post_array, $primary_key){
        return TRUE;
    }

    public function gallery_after_update($post_array, $primary_key){
        $success = $this->gallery_after_insert_or_update($post_array, $primary_key);
        return $success;
    }

    public function gallery_before_delete($primary_key){
        return TRUE;
    }

    public function gallery_after_delete($primary_key){
        return TRUE;
    }

    public function gallery_after_insert_or_update($post_array, $primary_key){
        return TRUE;
    }

    public function get_info_gallery(){
        $gallery_id   = $this->input->post('id');
        $detail     = $this->cms_ecommerce_gallery_info($gallery_id);
        
        $data['name'] = $detail->product_name;
        $data['id'] = $detail->product_id;
        $data['place'] = $detail->place;
        $data['brand'] = $detail->brand;
        $data['image_url'] = $detail->image_url;

        echo json_encode($data);
    }

    // ADMIN PANEL ===============================================================
    public function admin()
    {
        $data['total_customer'] = $this->No_CMS_Model->get_total_customer();
        $data['total_order'] = $this->No_CMS_Model->get_total_order();
        $data['total_order_today'] = $this->No_CMS_Model->get_total_order_today();

        $data['order'] = $this->No_CMS_Model->get_list_order();

        //$data['paid_order'] = $this->No_CMS_Model->get_list_order(1);
        //$data['confirm_order'] = $this->No_CMS_Model->get_list_order(2);

        $this->cms_guard_page('admin');
        $this->view('main/admin_panel', $data);   
    }
   public function maintenance(){
        $this->view('main/maintenance'); 
    }

    public function test_mail(){
    	//$data['site_url'] = $this->cms_get_config('base_url');
    	//$data['full_name'] = "Eddy Christiandy";
    	//var_dump($data);die();
    	$data = array(
            "order_no"          => '001',
            "date"              => date('Y-m-d'),
            "time"              => date('H:i:s'),
            "status"            => '0',
            "shipping"          => '10000',
            "total"             => '20000',
            "total_qty"         => '1',
            "user_id"           => '1',
            "shipping_address"  => 'just Test',
            "bank"              => 'BCA',
            "bank_account_no"   => '4290750235',
            "bank_account_name" => 'Eddy'
        );
        $data['user'] = 'Eddy Christiandy';
        $data['grand_total'] = '30000';

    	$email_from_address = $this->cms_get_config('cms_email_reply_address');
        $email_from_name    = $this->cms_get_config('cms_email_reply_name');
        $email_to_address   = 'echristiandy@gmail.com';

        $email_subject = 'Order No '.$data['order_no'];
        $email_message = $this->view('main/order_mail_template',$data, true);
        //$email_message = 'JUST TESTING';
        //$this->view('main/order_mail_template', $data, false);
       	$send = $this->cms_send_email($email_from_address, $email_from_name, $email_to_address, $email_subject, $email_message);

        $email_to_address   = 'eddy.christiandy@outlook.com';
        $bank = 'bca';
        $grand_total = '30000';

        $email_subject = 'Order Baru Golden Dragon '+date('Y-m-d');
        $email_message = 'Anda mendapat pesanan baru dari pesanan online. Pembayaran di transfer melalui bank '.$bank.' senilai '.$grand_total.'. Silahkan akses website admin untuk melihat detail order.';

        $send = $this->cms_send_email($email_from_address, $email_from_name, $email_to_address, $email_subject, $email_message);
    }
}