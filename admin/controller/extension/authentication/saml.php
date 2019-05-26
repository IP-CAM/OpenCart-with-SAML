<?php
class ControllerExtensionAuthenticationSaml extends Controller {
    private $error = array();

    public function index() {
        $this->load->language('extension/authentication/saml');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('saml/server');

        $serverDetails = $this->model_saml_server->getServer();

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            if (!$serverDetails){
                $this->model_saml_server->setServer($this->request->post);
            } else {
                $serverId = $serverDetails[0]['id'];
                $this->model_saml_server->updateServer($serverId, $this->request->post);
            }


            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('extension/authentication/saml', 'user_token=' . $this->session->data['user_token'] . '&type=report', true));
        }

        $data['success'] = '';
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['idp_cert'])) {
            $data['error_idp_cert'] = $this->error['idp_cert'];
        } else {
            $data['error_idp_cert'] = '';
        }

        if (isset($this->error['sp_entity_id'])) {
            $data['error_sp_entity_id'] = $this->error['sp_entity_id'];
        } else {
            $data['error_sp_entity_id'] = '';
        }

        if (isset($this->error['idp_entity_id'])) {
            $data['error_idp_entity_id'] = $this->error['idp_entity_id'];
        } else {
            $data['error_idp_entity_id'] = '';
        }

        if (isset($this->error['sso_url'])) {
            $data['error_sso_url'] = $this->error['sso_url'];
        } else {
            $data['error_sso_url'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=report', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/authentication/saml', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/authentication/saml', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=report', true);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $httpReferrer = parse_url($this->request->server['HTTP_REFERER']);
        if (!in_array(!$httpReferrer['port'], array(80, 443))) {
            $data['acs_url'] = $httpReferrer['scheme'] . '://' . $httpReferrer['host'] . ':' . $httpReferrer['port'] . '/index.php?route=account/saml/saml_login';
        } else {
            $data['acs_url'] = $httpReferrer['scheme'] . '://' . $httpReferrer['host'] . '/index.php?route=account/saml/saml_login';
        }
        $editData = $serverDetails ? $serverDetails[0] : array();
        $data = array_merge($data, $editData);

        $this->response->setOutput($this->load->view('extension/authentication/saml_form', $data));
    }

    public function metadata() {
        $this->load->model('saml/server');

        if ($this->model_saml_server->getServer() && $this->model_saml_server->isEnabled()) {
            $servers = $this->model_saml_server->getServer();
            $serverDetails = $servers[0];


            header('Content-Type: text/xml');
            header('Content-Disposition: attachment; filename="sp-metadata.xml"');

            $host = explode('admin/index.php', $this->request->server['HTTP_REFERER'], 2);

            $settings = array(
                'strict' => true,
                'debug' => false,
                'baseurl' => null,

                'sp' => array(
                    // Identifier of the SP entity  (must be a URI)
                    'entityId' => $serverDetails['sp_entity_id'],
                    // Specifies info about where and how the <AuthnResponse> message MUST be
                    // returned to the requester, in this case our SP.
                    'assertionConsumerService' => array(
                        // URL Location where the <Response> from the IdP will be returned
                        'url' => $host[0] . 'index.php?route=account/saml/saml_login',
                        // SAML protocol binding to be used when returning the <Response>
                        // message.  Onelogin Toolkit supports for this endpoint the
                        // HTTP-Redirect binding only
                        'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                    ),
                    // Specifies constraints on the name identifier to be used to
                    // represent the requested subject.
                    // Take a look on lib/Saml2/Constants.php to see the NameIdFormat supported
                    'NameIDFormat' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',

                    // Usually x509cert and privateKey of the SP are provided by files placed at
                    // the certs folder. But we can also provide them with the following parameters
                    'x509cert' => '',
                    'privateKey' => '',
                ),

                // Identity Provider Data that we want connect with our SP
                'idp' => array(
                    // Identifier of the IdP entity  (must be a URI)
                    'entityId' => $serverDetails['idp_entity_id'],
                    // SSO endpoint info of the IdP. (Authentication Request protocol)
                    'singleSignOnService' => array(
                        // URL Target of the IdP where the SP will send the Authentication Request Message
                        'url' => $serverDetails['sso_url'],
                        // SAML protocol binding to be used when returning the <Response>
                        // message.  Onelogin Toolkit supports for this endpoint the
                        // HTTP-POST binding only
                        'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                    ),
                    // Public x509 certificate of the IdP
                    'x509cert' => $serverDetails['idp_cert'],
                ),
            );

            $samlSettings = new OneLogin_Saml2_Settings($settings);

            $sp = $samlSettings->getSPData();
            $samlMetadata = OneLogin_Saml2_Metadata::builder($sp);
            echo $samlMetadata;
        }

    }

    private function validate() {
        if (!$this->user->hasPermission('modify', 'extension/authentication/saml')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if ((utf8_strlen(trim($this->request->post['sp_entity_id'])) < 2) || (utf8_strlen(trim($this->request->post['sp_entity_id'])) > 100)) {
            $this->error['sp_entity_id'] = $this->language->get('error_sp_entity_id');
        }

        if ((utf8_strlen(trim($this->request->post['idp_entity_id'])) < 2) || (utf8_strlen(trim($this->request->post['idp_entity_id'])) > 100)) {
            $this->error['idp_entity_id'] = $this->language->get('error_idp_entity_id');
        }

        if ((utf8_strlen(trim($this->request->post['idp_cert'])) < 2) || (utf8_strlen(trim($this->request->post['idp_cert'])) > 3000)) {
            $this->error['idp_cert'] = $this->language->get('error_idp_cert');
        }

        if ((utf8_strlen($this->request->post['sso_url']) > 96) || !filter_var($this->request->post['sso_url'], FILTER_VALIDATE_URL)) {
            $this->error['sso_url'] = $this->language->get('error_sso_url');
        }

        return !$this->error;
    }
}