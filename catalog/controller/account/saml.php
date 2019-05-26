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
                    // Public x509 certificate of the IdP
                    'x509cert' => $serverDetails['idp_cert'],
                ),
            );
            try {
                $samlSettings = new OneLogin_Saml2_Settings($settings);
                $authRequest = new OneLogin_Saml2_AuthnRequest($samlSettings);
                $samlRequest = $authRequest->getRequest();
                $parameters = array('SAMLRequest' => $samlRequest);
                $parameters['RelayState'] = OneLogin_Saml2_Utils::getSelfURLNoQuery();
                $idpData = $samlSettings->getIdPData();
                $ssoUrl = $idpData['singleSignOnService']['url'];
                $url = OneLogin_Saml2_Utils::redirect($ssoUrl, $parameters, true);
                header("Location: $url");
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
                    // Public x509 certificate of the IdP
                    'x509cert' => $serverDetails['idp_cert'],
                ),
            );

            $this->load->model('account/customer');
            $this->load->language('account/login');

            try {
                if (isset($this->request->post['SAMLResponse'])) {
                    $samlSettings = new OneLogin_Saml2_Settings($settings);
                    $samlResponse = new OneLogin_Saml2_Response($samlSettings, $this->request->post['SAMLResponse']);
                    if ($samlResponse->isValid()) {
                        if ($samlResponse->getAttributesWithFriendlyName()) {
                            $userAttrs = $samlResponse->getAttributesWithFriendlyName();
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
                                    $this->session->data['error'] = $this->language->get('unregistered_saml_user');
                                    $this->response->redirect($this->url->link('account/login'));
                                }
                            } else {
                                $this->session->data['error'] = $this->language->get('no_mail_attr');
                                $this->response->redirect($this->url->link('account/login'));
                            }
                        }
                    } else {
                        $this->session->data['error'] = $samlResponse->getError();
                        $this->response->redirect($this->url->link('account/login'));
                    }
                } else {
                    $this->session->data['error'] = $this->language->get('no_saml_response');
                    $this->response->redirect($this->url->link('account/login'));
                }
            } catch (Exception $e) {
                $this->session->data['error'] = $this->language->get('invalid_saml_response') . ': ' . $e->getMessage();
                $this->response->redirect($this->url->link('account/login'));
            }
        }
    }
}
