<?php
class ControllerExtensionExtensionAuthentication extends Controller {
    private $error = array();

    public function index() {
        $this->load->language('extension/extension/authentication');

        $this->load->model('setting/extension');

        $this->getList();
    }

    public function install() {
        $this->load->language('extension/extension/authentication');

        $this->load->model('setting/extension');

        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "saml_server`");

        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "saml_server` (
                             `id` INT(11) NOT NULL AUTO_INCREMENT,
                             `sp_entity_id` VARCHAR(300) NOT NULL,
                             `idp_entity_id` VARCHAR(300) NOT NULL,
                             `sso_url` VARCHAR(300) NOT NULL,
                             `idp_cert` VARCHAR(3000) NOT NULL,
                             `enabled` INT(10) NOT NULL DEFAULT '0',
                             `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                             PRIMARY KEY (`id`)
                            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");

        if ($this->validate()) {
            $this->model_setting_extension->install('authentication', $this->request->get['extension']);

            $this->load->model('user/user_group');

            $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/authentication/' . $this->request->get['extension']);
            $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/authentication/' . $this->request->get['extension']);

            // Compatibility
            $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'authentication/' . $this->request->get['extension']);
            $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'authentication/' . $this->request->get['extension']);

            // Call install method if it exsits
            $this->load->controller('extension/authentication/' . $this->request->get['extension'] . '/install');

            $this->session->data['success'] = $this->language->get('text_success');
        }

        $this->getList();
    }

    public function uninstall() {
        $this->load->language('extension/extension/authentication');

        $this->load->model('setting/extension');

        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "saml_server`");

        if ($this->validate()) {
            $this->model_setting_extension->uninstall('authentication', $this->request->get['extension']);

            // Call uninstall method if it exsits
            $this->load->controller('extension/authentication/' . $this->request->get['extension'] . '/uninstall');

            $this->session->data['success'] = $this->language->get('text_success');
        }

        $this->getList();
    }

    protected function getList() {

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        $extensions = $this->model_setting_extension->getInstalled('authentication');

        foreach ($extensions as $key => $value) {
            if (!is_file(DIR_APPLICATION . 'controller/extension/authentication/' . $value . '.php') && !is_file(DIR_APPLICATION . 'controller/authentication/' . $value . '.php')) {
                $this->model_setting_extension->uninstall('authentication', $value);

                unset($extensions[$key]);
            }
        }

        $data['extensions'] = array();

        // Compatibility code for old extension folders
        $files = glob(DIR_APPLICATION . 'controller/extension/authentication/*.php');

        if ($files) {
            foreach ($files as $file) {
                $extension = basename($file, '.php');

                $dbutton = false;
                $status = false;

                $res = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "saml_server'");
                if ((boolean) $res->num_rows) {
                    $this->load->model('saml/server');

                    $dbutton = in_array($extension, $extensions) && $this->model_saml_server->getServer() && $this->model_saml_server->isEnabled();
                    $status = $this->model_saml_server->isEnabled();
                }

                // Compatibility code for old extension folders
                $this->load->language('extension/authentication/' . $extension, 'extension');

                $data['extensions'][] = array(
                    'name'      => $this->language->get('extension')->get('heading_title'),
                    'dlink'  => $this->url->link('extension/authentication/saml/metadata', 'user_token=' . $this->session->data['user_token'], true),
                    'install'   => $this->url->link('extension/extension/authentication/install', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension, true),
                    'uninstall' => $this->url->link('extension/extension/authentication/uninstall', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension, true),
                    'installed' => in_array($extension, $extensions),
                    'dbutton'   => $dbutton,
                    'status'    => $status,
                    'edit'       => $this->url->link('extension/authentication/' . $extension, 'user_token=' . $this->session->data['user_token'], true)
                );
            }
        }

        $data['promotion'] = $this->load->controller('extension/extension/promotion');

        $this->response->setOutput($this->load->view('extension/extension/authentication', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/extension/authentication')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }
}
