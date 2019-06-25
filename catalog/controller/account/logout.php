<?php
class ControllerAccountLogout extends Controller {
	public function index() {
		if ($this->customer->isLogged()) {
			$this->customer->logout();

			unset($this->session->data['shipping_address']);
			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_address']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['comment']);
			unset($this->session->data['order_id']);
			unset($this->session->data['coupon']);
			unset($this->session->data['reward']);
			unset($this->session->data['voucher']);
			unset($this->session->data['vouchers']);

			$logoutUrl = $this->logoutSaml();
            $this->session->data['logoutUrl'] = $logoutUrl;

			$this->response->redirect($this->url->link('account/logout', '', true));
		}

		$this->load->language('account/logout');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['logoutUrl'] = $this->session->data['logoutUrl'];

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_logout'),
			'href' => $this->url->link('account/logout', '', true)
		);

		$data['continue'] = $this->url->link('common/home');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('common/logout_success', $data));
	}

    public function logoutSaml() {
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
                        'url' => 'https://159.203.4.189/idp/profile/SAML2/Redirect/SLO',
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

            $returnTo = null;
            $parameters = array();
            $nameId = null;
            $sessionIndex = null;
            $nameIdFormat = null;
            $nameIdNameQualifier = null;
            if (isset($this->session->data['samlNameId'])) {
                $nameId = $this->session->data['samlNameId'];
            }
            if (isset($this->session->data['samlSessionIndex'])) {
                $sessionIndex = $this->session->data['samlSessionIndex'];
            }
            if (isset($this->session->data['samlNameIdFormat'])) {
                $nameIdFormat = $this->session->data['samlNameIdFormat'];
            }
            if (isset($this->session->data['nameidNameQualifier'])) {
                $nameIdNameQualifier = $this->session->data['nameidNameQualifier'];
            }
            $logoutUrl = $auth->logout($returnTo, $parameters, $nameId, $sessionIndex, true, $nameIdFormat, $nameIdNameQualifier);
            return $logoutUrl;
        }
    }
}
