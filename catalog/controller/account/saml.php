<?php
class ControllerAccountSaml extends Controller {
    private $error = array();

    public function start_saml() {
        require_once DIR_SYSTEM . 'library/saml/settings.php';
        try {
            $auth = new OneLogin_Saml2_Auth($settingsInfo);
            $auth->login();
        }
        catch (Exception $ex) {
            $this->session->data['error'] = $ex->getMessage();
            $this->response->redirect($this->url->link('account/login'));
        }

    }

    public function saml_login() {

        require_once DIR_SYSTEM . 'library/saml/settings.php';

        if (isset($_SESSION) && isset($_SESSION['AuthNRequestID'])) {
            $requestID = $_SESSION['AuthNRequestID'];
        } else {
            $requestID = null;
        }


        $auth = new OneLogin_Saml2_Auth($settingsInfo);

        $auth->processResponse($requestID);

        if ($auth->getLastErrorReason()) {
            $this->session->data['error'] = $auth->getLastErrorReason();
            $this->response->redirect($this->url->link('account/login'));
        }

        $userEmails = $auth->getAttributeWithFriendlyName('mail');
        if ($userEmails) {
            if ($this->model_account_customer->getCustomerByEmail($userEmails[0])) {
                $this->customer->login($email=$auth->getAttributeWithFriendlyName('mail'), $override=true);

                unset($this->session->data['guest']);

                $this->response->redirect($this->url->link('account/success'));
            }
        }

    }

//    private function validate($data) {
//
//        if ((utf8_strlen($data['email']) > 96) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
//            $this->error['email'] = $this->language->get('error_email');
//        }
//
//        return !$this->error;
//    }

//    private function setLogin($email) {
//        $customer_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "saml_customer WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($email)) . "' AND status = '1'");
//
//        if ($customer_query->num_rows) {
//            $this->session->data['customer_id'] = $customer_query->row['customer_id'];
//            $this->session->data['is_saml_user'] = true;
//
//            $this->customer_id = $customer_query->row['customer_id'];
//            $this->firstname = $customer_query->row['firstname'];
//            $this->lastname = $customer_query->row['lastname'];
//            $this->customer_group_id = $customer_query->row['customer_group_id'];
//            $this->email = $customer_query->row['email'];
////            $this->telephone = $customer_query->row['telephone'];
//            $this->newsletter = $customer_query->row['newsletter'];
////            $this->address_id = $customer_query->row['address_id'];
//
//            $this->db->query("UPDATE " . DB_PREFIX . "customer SET language_id = '" . (int)$this->config->get('config_language_id') . "', ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "' WHERE customer_id = '" . (int)$this->customer_id . "'");
//
//            return true;
//        } else {
//            return false;
//        }
//    }
}
