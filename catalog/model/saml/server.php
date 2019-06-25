<?php
class ModelSamlServer extends Model {
    public function getServer() {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "saml_server LIMIT 1");

        return $query->rows;
    }

    public function setServer($data) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "saml_server SET sso_url = '" . $this->db->escape($data['sso_url']) . "', slo_url = '" . $this->db->escape($data['slo_url']) . "', sp_entity_id = '" . $this->db->escape($data['sp_entity_id']) . "', enabled = '" . $this->db->escape($data['enabled']) . "', idp_cert = '" . $this->db->escape($data['idp_cert']) . "', idp_entity_id = '" . $this->db->escape($data['idp_entity_id']) . "', date_added = NOW()");
    }

    public function updateServer($serverId, $data) {
        $this->db->query("UPDATE " . DB_PREFIX . "saml_server SET sso_url = '" . $this->db->escape($data['sso_url']) . "', slo_url = '" . $this->db->escape($data['slo_url']) . "', sp_entity_id = '" . $this->db->escape($data['sp_entity_id']) . "', enabled = '" . $this->db->escape($data['enabled']) . "', idp_cert = '" . $this->db->escape($data['idp_cert']) . "', idp_entity_id = '" . $this->db->escape($data['idp_entity_id']) . "', date_added = NOW() WHERE id = '" . $serverId . "'");
    }

    public function isEnabled() {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "saml_server LIMIT 1");

        if ($query->rows) {
            return (int)$query->rows[0]['enabled'];
        } else {
            return 0;
        }
    }
}