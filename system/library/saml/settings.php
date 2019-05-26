<?php

$settings = array(
    // If 'strict' is True, then the PHP Toolkit will reject unsigned
    // or unencrypted messages if it expects them signed or encrypted
    // Also will reject the messages if not strictly follow the SAML
    // standard: Destination, NameId, Conditions ... are validated too.
    'strict' => true,

    // Enable debug mode (to print errors)
    'debug' => false,

    // Set a BaseURL to be used instead of try to guess
    // the BaseURL of the view that process the SAML Message.
    // Ex. http://sp.example.com/
    //     http://example.com/sp/
    'baseurl' => null,

    // Service Provider Data that we are deploying
    'sp' => array(
        // Identifier of the SP entity  (must be a URI)
        'entityId' => 'http://localhost:8888/upload',
        // Specifies info about where and how the <AuthnResponse> message MUST be
        // returned to the requester, in this case our SP.
        'assertionConsumerService' => array(
            // URL Location where the <Response> from the IdP will be returned
            'url' => 'http://localhost:8888/upload/index.php?route=account/saml/saml_login',
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
        'entityId' => 'https://idp.test.local/idp/shibboleth',
        // SSO endpoint info of the IdP. (Authentication Request protocol)
        'singleSignOnService' => array(
            // URL Target of the IdP where the SP will send the Authentication Request Message
            'url' => 'https://idp.test.local/idp/profile/SAML2/Redirect/SSO',
            // SAML protocol binding to be used when returning the <Response>
            // message.  Onelogin Toolkit supports for this endpoint the
            // HTTP-POST binding only
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
        ),
        // Public x509 certificate of the IdP
        'x509cert' => 'MIIDJDCCAgygAwIBAgIVAI19QtUQ3KXn27B5VjiUOLHIzB1uMA0GCSqGSIb3DQEB CwUAMBkxFzAVBgNVBAMMDmlkcC50ZXN0LmxvY2FsMB4XDTE5MDUxNDE5MDI1MVoX DTM5MDUxNDE5MDI1MVowGTEXMBUGA1UEAwwOaWRwLnRlc3QubG9jYWwwggEiMA0G CSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQCZMHUv3ZY5cCc/H7QgPlteh4edMnYA r8lBMx6ICX9LM6sop8P/SbdS3QqBZ/KJkEoSUpJUflO73qv2GsXQNvp9pUu1EEqS YClUWHl5s6rs23/ka/jzB6vonZDJvqOs6qc3gkrqx2Xh6BjYVtVTRfS95S12Timm jkJrbhYN+UIym1IZNgQUYbqdgIiFXPt0eqKdYRAHfAIQKH1IRWdY+Wnqgrj3/qxo zaYWcTrbEFB81fXrMcHGr0rLQ94RbPPmgkSnTL5CZp/bjNpnOy4gUKGgujl+5hoP gPyxF10lChAAeolooL4RwSXl/RVpNcPV2RRP7VMqG/YBXNlTc2b+E3rNAgMBAAGj YzBhMB0GA1UdDgQWBBTGwIiSrKqejek7Eyniiy7ZdGmQzjBABgNVHREEOTA3gg5p ZHAudGVzdC5sb2NhbIYlaHR0cHM6Ly9pZHAudGVzdC5sb2NhbC9pZHAvc2hpYmJv bGV0aDANBgkqhkiG9w0BAQsFAAOCAQEAJ21ujfX6IJzbWijytXq38NvLI2tzP/3x FecX35MnoKax4kGnn8TfAQa9bF7sB3whNIMR4usjTGTI3gvQ45AWPDi/XUI16pIW z+ZeWlvySUdg1Yo2kaP71qAo3U/T8fUku2q/nk6Jc/mPIU2trMF5ySIzJvds2hTY kCsQoqoUtMf5nI2HePmL4s2cfRj8DCxjzO6J6ClRdgtxlawyMnwM99CqaCUMi+kt RXq/8cPoCXJ7o8XS07/uVPgziQLP2RUcZvjpRP2dReik146hoYw/oH/mSlRpArgN GfR4YE+PJ1ba4BUQIBspWzi9j7tm4EX4I/Fv3Ayou+VuFyDsrpSTCQ==',
    ),
);