<?php
class ControllerAccountSaml extends Controller {
    private $error = array();

    public function start_saml() {
        $this->load->model('saml/server');

        if ($this->model_saml_server->getServer() && $this->model_saml_server->isEnabled()) {
            $servers = $this->model_saml_server->getServer();
            $serverDetails = $servers[0];

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
                        'url' => HTTPS_SERVER . 'index.php?route=account/saml/saml_login',
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
                    'singleLogoutService' => array (
                        // URL Location of the IdP where the SP will send the SLO Request
                        'url' => $serverDetails['slo_url'],
                        // SAML protocol binding to be used when returning the <Response>
                        // message.  Onelogin Toolkit supports for this endpoint the
                        // HTTP-Redirect binding only
                        'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                    ),
                    // Public x509 certificate of the IdP
                    'x509cert' => $serverDetails['idp_cert'],
                ),
            );
            try {
                $auth = new OneLogin_Saml2_Auth($settings);
                $auth->login();
            } catch (Exception $ex) {
                $this->session->data['error'] = $ex->getMessage();
                $this->response->redirect($this->url->link('account/login'));
            }
        }
    }

    public function saml_login() {
        $this->load->model('saml/server');

        if ($this->model_saml_server->getServer() && $this->model_saml_server->isEnabled()) {
            $servers = $this->model_saml_server->getServer();
            $serverDetails = $servers[0];

            $this->load->model('account/customer');
            $this->load->language('account/login');

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
                        'url' => HTTPS_SERVER . 'index.php?route=account/saml/saml_login',
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
                    'singleLogoutService' => array (
                        // URL Location of the IdP where the SP will send the SLO Request
                        'url' => $serverDetails['slo_url'],
                        // SAML protocol binding to be used when returning the <Response>
                        // message.  Onelogin Toolkit supports for this endpoint the
                        // HTTP-Redirect binding only
                        'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                    ),
                    // Public x509 certificate of the IdP
                    'x509cert' => $serverDetails['idp_cert'],
                ),
            );

            $auth = new OneLogin_Saml2_Auth($settings);

            try {
                $auth->processResponse();
                $errors = $auth->getErrors();

                if (!empty($errors)) {
                    throw new Exception(implode(', ', $errors));
                }
                if (!$auth->isAuthenticated()) {
                    throw new Exception();
                }

                $this->session->data['samlNameId'] = $auth->getNameId();
                $this->session->data['samlNameIdFormat'] = $auth->getNameIdFormat();
                $this->session->data['samlSessionIndex'] = $auth->getSessionIndex();
                $this->session->data['nameidNameQualifier'] = $auth->getNameIdNameQualifier();
                if ($auth->getAttributesWithFriendlyName()) {
                    $userAttrs = $auth->getAttributesWithFriendlyName();
                    if (isset($userAttrs['mail'])) {
                        if ($this->model_account_customer->getCustomerByEmail($userAttrs['mail'][0])) {
                            if (!$this->customer->login($userAttrs['mail'][0], '', true)) {
                                $this->error['warning'] = $this->language->get('error_login');

                                $this->model_account_customer->addLoginAttempt($userAttrs['mail'][0]);
                            } else {
                                $this->model_account_customer->deleteLoginAttempts($userAttrs['mail'][0]);
                            }

                            unset($this->session->data['guest']);

                            $this->response->redirect($this->url->link('account/account'));
                        } else {
                            throw new Exception($this->language->get('unregistered_saml_user'));
                        }
                    } else {
                        throw new Exception($this->language->get('no_mail_attr'));
                    }
                } else {
                    throw new Exception('No user attributes in the response');
                }
            } catch (Exception $e) {
                $this->session->data['error'] = $e->getMessage();
                $this->response->redirect($this->url->link('account/login'));
            }
        }
    }
}
