
<?php if ( ! defined( 'ABSPATH' ) ) exit;


$settings['mautic_api_base_url'] = array(
    'id'    => 'mautic_api_base_url',
    'type'  => 'textbox',
    'label' => __( 'API Base URL', 'ninja-forms-mautic' ),
    'desc'  => __( 'Enter your Mautic Base Url' ),
);

$settings['mautic_api_version'] = array(
    'id'    => 'mautic_api_version',
    'type'  => 'select',
    'options' => array(
        array(
            'label' => __( 'Select One', 'ninja-forms' ),
            'value' => '',
        ),
        array(
            'label' => __( 'OAuth1a', 'ninja-forms' ),
            'value' => 'OAuth1a',
        ),
        array(
            'label' => __( 'OAuth2', 'ninja-forms' ),
            'value' => 'OAuth2',
        ),
    ),
    'label' => __( 'API OAuth Version', 'ninja-forms-mautic' ),
    'desc'  => __( 'Enter your Mautic Authentication Version (e.g. OAuth1a or OAuth2' ),
);

$settings['mautic_api_client_key'] = array(
    'id'    => 'mautic_api_client_key',
    'type'  => 'textbox',
    'label' => __( 'API Client Key', 'ninja-forms-mautic' ),
    'desc'  => __( 'Enter your API Client Key' ),
);

$settings['mautic_api_client_secret'] = array(
    'id'    => 'mautic_api_client_secret',
    'type'  => 'textbox',
    'label' => __( 'API Client Secret', 'ninja-forms-mautic' ),
    'desc'  => __( 'Enter your Mautic Client Secret' ),
);

$settings['mautic_api_callback'] = array(
    'id'    => 'mautic_api_callback',
    'type'  => 'textbox',
    'label' => __( 'API Callback', 'ninja-forms-mautic' ),
    'desc'  => __( 'OPTIONAL: Enter your Mautic Callback. Will use this page if not entered.' ),
);

$settings['mautic_authorize'] = array(
    'id'    => 'mautic_authorize',
    'type'  => 'html',
    'label' => __( 'Click button to authorize Mautic', 'ninja-forms-mautic' ),
    'html' => '<button type="submit" id="ninja_forms[mautic_authorize]" name="ninja_forms[mautic_authorize]" class="button-primary" value="1">'
        . __(
            (Ninja_Forms()->get_setting('mautic_api_access_token') && Ninja_Forms()->get_setting('mautic_api_access_token_secret') ? 'Re-' : '')
        . 'Authorize', 'ninja-forms-mautic' )
        . '</button>'
);

if (!strlen(Ninja_Forms()->get_setting('mautic_api_access_token')) && !isset($_GET['oauth_verifier']) && !isset($_GET['state'])) {

    $settings['mautic_api_access_token'] = array(
        'id'    => 'mautic_api_access_token',
        'type'  => 'textbox',
        'label' => __( 'API Access Token', 'ninja-forms-mautic' ),
        'desc'  => __( 'The access token when fetched or manually entered' ),
    );

    $settings['mautic_api_access_token_secret'] = array(
        'id'    => 'mautic_api_access_token_secret',
        'type'  => 'textbox',
        'label' => __( 'API Access Token Secret', 'ninja-forms-mautic' ),
        'desc'  => __( 'The access token secret when fetched or manually entered' ),
    );
} else {
    $settings['mautic_deauthorize'] = array(
        'id'    => 'mautic_deauthorize',
        'type'  => 'html',
        'label' => __( 'Click button to revoke Mautic', 'ninja-forms-mautic' ),
        'html' => '<button type="submit" id="ninja_forms[mautic_deauthorize]" name="ninja_forms[mautic_deauthorize]" class="button-primary" value="1">'
            . __( 'De-Authorize', 'ninja-forms-mautic' ). '</button>'
    );

}

$settings['mautic_api_last_status'] = array(
    'id'    => 'mautic_api_last_status',
    'type'  => 'html',
    'label' => __( 'API - Last Status', 'ninja-forms-mautic' ),
    'html' => 'Status: ' . Ninja_Forms()->get_setting('mautic_api_last_status'),
);

return apply_filters( 'ninja_forms_mautic_plugin_settings', $settings );
