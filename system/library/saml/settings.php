<?php

    $spBaseUrl = 'http://localhost:8888/upload'; //or http://<your_domain>

    $settingsInfo = array (
        'sp' => array (
            'entityId' => $spBaseUrl,
            'assertionConsumerService' => array (
                'url' => $spBaseUrl.'/index.php?route=account/saml/saml_login',
            ),
            'singleLogoutService' => array (
                'url' => $spBaseUrl.'/index.php?route=account/saml/saml_logout',
            ),
            'NameIDFormat' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
        ),
        'idp' => array (
            'entityId' => 'https://idp.test.local/idp/shibboleth',
//            'entityId' => 'https://app.onelogin.com/saml/metadata/b8a55882-dbe0-4e85-90fc-9620a13f5468',
            'singleSignOnService' => array (
                'url' => 'https://idp.test.local/idp/profile/SAML2/Redirect/SSO',
//                'url' => 'https://logpoint-dev.onelogin.com/trust/saml2/http-post/sso/936893',
            ),
            'singleLogoutService' => array (
                'url' => 'https://logpoint-dev.onelogin.com/trust/saml2/http-redirect/slo/936893',
            ),
            'x509cert' => 'wIBAgIVAI19QtUQ3KXn27B5VjiUOLHIzB1uMA0GCSqGSIb3DQEB CwUAMBkxFzAVBgNVBAMMDmlkcC50ZXN0LmxvY2FsMB4XDTE5MDUxNDE5MDI1MVoX DTM5MDUxNDE5MDI1MVowGTEXMBUGA1UEAwwOaWRwLnRlc3QubG9jYWwwggEiMA0G CSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQCZMHUv3ZY5cCc/H7QgPlteh4edMnYA r8lBMx6ICX9LM6sop8P/SbdS3QqBZ/KJkEoSUpJUflO73qv2GsXQNvp9pUu1EEqS YClUWHl5s6rs23/ka/jzB6vonZDJvqOs6qc3gkrqx2Xh6BjYVtVTRfS95S12Timm jkJrbhYN+UIym1IZNgQUYbqdgIiFXPt0eqKdYRAHfAIQKH1IRWdY+Wnqgrj3/qxo zaYWcTrbEFB81fXrMcHGr0rLQ94RbPPmgkSnTL5CZp/bjNpnOy4gUKGgujl+5hoP gPyxF10lChAAeolooL4RwSXl/RVpNcPV2RRP7VMqG/YBXNlTc2b+E3rNAgMBAAGj YzBhMB0GA1UdDgQWBBTGwIiSrKqejek7Eyniiy7ZdGmQzjBABgNVHREEOTA3gg5p ZHAudGVzdC5sb2NhbIYlaHR0cHM6Ly9pZHAudGVzdC5sb2NhbC9pZHAvc2hpYmJv bGV0aDANBgkqhkiG9w0BAQsFAAOCAQEAJ21ujfX6IJzbWijytXq38NvLI2tzP/3x FecX35MnoKax4kGnn8TfAQa9bF7sB3whNIMR4usjTGTI3gvQ45AWPDi/XUI16pIW z+ZeWlvySUdg1Yo2kaP71qAo3U/T8fUku2q/nk6Jc/mPIU2trMF5ySIzJvds2hTY kCsQoqoUtMf5nI2HePmL4s2cfRj8DCxjzO6J6ClRdgtxlawyMnwM99CqaCUMi+kt RXq/8cPoCXJ7o8XS07/uVPgziQLP2RUcZvjpRP2dReik146hoYw/oH/mSlRpArgN GfR4YE+PJ1ba4BUQIBspWzi9j7tm4EX4I/Fv3Ayou+VuFyDsrpSTCQ==',
        ),
    );
