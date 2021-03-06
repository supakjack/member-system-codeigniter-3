<?php
/*
Register
Controller of Register Member
@author Supak Pukdam
@Create Date 2563-04-20
*/
require_once(APPPATH . 'controllers/Mem_controller.php');
class Register extends Mem_controller
{
    /*Inherited from Mem_controller
	* @name __construct
	* @input   -
	* @output  -
	* @author Supak Pukdam
	* @Create Date 2563-04-20
	*/
    public function __construct()
    {
        parent::__construct();
    }

    /*show Register page
	* @name show
	* @input   -
	* @output  'v_register' register page OR 'v_authen' Authen page
	* @author Supak Pukdam
	* @Create Date 2563-04-20
	*/
    public function show()
    {
        if (get_cookie('authen') === null) {
            redirect(base_url(), 'refresh');
        } else {
            $this->load->model('M_pf_name', 'mpn');

            $rs_pf_name = $this->mpn->select();
            $data = [
                'rs_pf_name' => $rs_pf_name,
                'ex_mem_picture' => $this->ex_mem_picture
            ];
            $this->output('v_register', $data);
        }
    }

    /*edit Register
	* @name edit
	* @input    @post[reg_id] id form management page ,
	* @output  'v_management' management page OR 'v_authen' Authen page
	* @author Supak Pukdam
	* @Create Date 2563-04-20
	*/
    public function edit()
    {
        if (get_cookie('authen') === null) {
            redirect(base_url(), 'refresh');
        } else {
            $data = $this->input->post();

            $this->db->trans_begin();

            $this->load->model('M_register', 'mrg');
            $this->mrg->reg_id = $data['reg_id'];
            $this->mrg->reg_username = $data['username'];
            $this->mrg->reg_password = $data['password'];
            $this->mrg->reg_timestamp = $data['reg_timestamp'];
            $this->mrg->reg_use = 'y';
            $this->mrg->reg_admin = 'n';
            $this->mrg->update();

            $this->load->model('M_member', 'mmb');
            $this->mmb->mem_id = $data['mem_id'];
            $this->mmb->mem_fname = $data['firstname'];
            $this->mmb->mem_lname = $data['lastname'];


            $this->load->library('upload', $this->upload_config);
            if ($this->upload->do_upload('upfile')) {
                $img = $this->upload->data();
                $path_to_del_file = 'images/' . $data['mem_picture'];
                if (unlink($path_to_del_file)) {
                    $this->mmb->mem_picture = $img['file_name'];
                }
            } else {
                $this->mmb->mem_picture = $data['mem_picture'];
            }

            $this->mmb->mem_addr = $data['address'];
            $this->mmb->mem_tel = $data['tel'];
            $this->mmb->mem_email = $data['email'];
            $this->mmb->mem_fb = $data['facebook'];
            $this->mmb->mem_ig = $data['instagram'];
            $this->mmb->mem_reg_id = $data['reg_id'];
            $this->mmb->mem_pfn_id = $data['prefixname'];
            $this->mmb->update();

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
            } else {
                $this->db->trans_commit();
                redirect(base_url('Management/show'), 'refresh');
            }
        }
    }

    /*cancel Register by reg_id
	* @name cancel
	* @input    $id number form management page ,
	* @output  'v_management' management page OR 'v_authen' Authen page
	* @author Supak Pukdam
	* @Create Date 2563-04-20
	*/
    public function cancel($id)
    {
        if (get_cookie('authen') === null) {
            redirect(base_url(), 'refresh');
        } else {
            $this->load->model('M_register', 'mrg');
            $this->mrg->reg_id = $id;
            $this->mrg->reg_use = 'n';
            $this->mrg->update_reg_use();
            redirect(base_url('Management/show'), 'refresh');
        }
    }

    /*enroll Register
	* @name enroll
	* @input    $data @post value array from 'v_register'
	* @output  'v_management' management page OR 'v_authen' Authen page
	* @author Supak Pukdam
	* @Create Date 2563-04-20
	*/
    public function enroll()
    {
        if (get_cookie('authen') === null) {
            redirect(base_url(), 'refresh');
        } else {
            $data = $this->input->post();

            $this->load->library('upload', $this->upload_config);
            if (!$this->upload->do_upload('upfile')) {
                $error = ['error' => $this->upload->display_errors()];
                var_dump($error);
            } else {
                $img = $this->upload->data();

                $this->db->trans_begin();

                $this->load->model('M_register', 'mrg');
                $this->mrg->reg_username = $data['username'];
                $this->mrg->reg_password = $data['password'];
                $this->mrg->reg_use = 'y';
                $this->mrg->reg_admin = 'n';
                $this->mrg->insert();

                $reg_id =  $this->db->insert_id();

                $this->load->model('M_member', 'mmb');
                $this->mmb->mem_fname = $data['firstname'];
                $this->mmb->mem_lname = $data['lastname'];
                $this->mmb->mem_picture = $img['file_name'];
                $this->mmb->mem_addr = $data['address'];
                $this->mmb->mem_tel = $data['tel'];
                $this->mmb->mem_email = $data['email'];
                $this->mmb->mem_fb = $data['facebook'];
                $this->mmb->mem_ig = $data['instagram'];
                $this->mmb->mem_reg_id = $reg_id;
                $this->mmb->mem_pfn_id = $data['prefixname'];
                $this->mmb->insert();


                if ($this->db->trans_status() === FALSE) {
                    $this->db->trans_rollback();
                } else {
                    $this->db->trans_commit();
                    redirect(base_url('Management/show'), 'refresh');
                }
            }
        }
    }
}

/* End of file Register.php */
