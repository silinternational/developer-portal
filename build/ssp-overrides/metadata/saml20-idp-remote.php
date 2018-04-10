<?php

$IDP = getenv('SAML_IDP') ?: 'https://openidp.feide.no';
$SSO_URL = getenv('SAML_SSO_URL') ?: 'https://openidp.feide.no/simplesaml/saml2/idp/SSOService.php';
$SLO_URL = getenv('SAML_SLO_URL') ?: 'https://openidp.feide.no/simplesaml/saml2/idp/SingleLogoutService.php';
$CERT_DATA = getenv('SAML_CERT_DATA') ?: null;
$ORG_NAME = getenv('SAML_ORG_NAME') ?: 'OpenIdP';
$ORG_URL = getenv('SAML_ORG_URL') ?: 'https://openidp.feide.no';

/**
 * SAML 2.0 remote IdP metadata for SimpleSAMLphp.
 *
 * Remember to remove the IdPs you don't use from this file.
 *
 * See: https://simplesamlphp.org/docs/stable/simplesamlphp-reference-idp-remote 
 */
$metadata[$IDP] = array(
    'metadata-set' => 'saml20-idp-remote',
    'entityid' => $IDP,
    'SingleSignOnService' => array(
        0 => array(
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'Location' => $SSO_URL,
        ),
    ),
    'SingleLogoutService' => $SLO_URL,
    'certData' => $CERT_DATA,
    'NameIDFormat' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
    'OrganizationName' => $ORG_NAME,
    'OrganizationDisplayName' => $ORG_NAME,
    'OrganizationURL' => $ORG_URL,
    'authproc' => array(
        50 => array(
            'class' => 'core:AttributeMap',
            'oid2name',
        ),
    ),
    'sign.authnrequest' => true,
);
