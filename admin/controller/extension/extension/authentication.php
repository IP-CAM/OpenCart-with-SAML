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

        if ($this->validate()) {
            $this->model_setting_extension->uninstall('authentication', $this->request->get['extension']);

            // Call uninstall method if it exsits
            $this->load->controller('extension/authentication/' . $this->request->get['extension'] . '/uninstall');

            $this->session->data['success'] = $this->language->get('text_success');
        }

        $this->getList();
    }

    protected function getList() {
        $this->load->model('saml/server');

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

                // Compatibility code for old extension folders
                $this->load->language('extension/authentication/' . $extension, 'extension');

                $data['extensions'][] = array(
                    'name'      => $this->language->get('extension')->get('heading_title'),
                    'dlink'  => $this->url->link('extension/authentication/saml/metadata', 'user_token=' . $this->session->data['user_token'], true),
                    'install'   => $this->url->link('extension/extension/authentication/install', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension, true),
                    'uninstall' => $this->url->link('extension/extension/authentication/uninstall', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension, true),
                    'installed' => in_array($extension, $extensions),
                    'dbutton'   => in_array('saml', $extensions) && $this->model_saml_server->getServer() && $this->model_saml_server->isEnabled(),
                    'status'    => $this->model_saml_server->isEnabled(),
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
