<?php
use Mautic\Auth\ApiAuth;
use Mautic\MauticApi;

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'NF_Abstracts_Action' )) exit;

/**
 * Class NF_Action_MauticExample
 */
final class NF_Mautic_Actions_SendToMautic extends NF_Abstracts_Action
{
    /**
     * @var string
     */
    protected $_name  = 'mautic';

    /**
     * @var array
     */
    protected $_tags = array();

    /**
     * @var string
     */
    protected $_timing = 'normal';

    /**
     * @var int
     */
    protected $_priority = '10';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->_nicename = __( 'Send to Mautic', 'ninja-forms' );

        $this->_settings['field_map'] = array(
            'name' => 'field_map',
            'type' => 'option-repeater',
            'label' => __( 'Field Map' ) . ' <a href="#" class="nf-add-new">' . __( 'Add New' ) . '</a>',
            'width' => 'full',
            'group' => 'primary',
            'columns'           => array(
                'label'          => array(
                    'header' => 'Ninja Forms Field Key',
                    'column1'    => __( 'Ninja Forms Field Key' ),
                    'default'   => '',
                ),
                'value'          => array(
                    'header' => 'Mautic Field Key',
                    'column2'    => __( 'Mautic Field Key' ),
                    'default'   => '',
                ),
            ),
        );
    }

    public function process( $action_settings, $form_id, $data )
    {
        $contactId = $_COOKIE['mtc_id'];

        $settings = array(
            'baseUrl'          => $baseUrl = Ninja_Forms()->get_setting('mautic_api_base_url'),

            'version'          => Ninja_Forms()->get_setting('mautic_api_version'),
            'clientKey'        => Ninja_Forms()->get_setting('mautic_api_client_key'),
            'clientSecret'     => Ninja_Forms()->get_setting('mautic_api_client_secret'),
            'callback'         => Ninja_Forms()->get_setting('mautic_api_callback'),
        );

        $settings['accessToken']        = Ninja_Forms()->get_setting('mautic_api_access_token');
        $settings['accessTokenSecret']  = Ninja_Forms()->get_setting('mautic_api_access_token_secret');
        $settings['refreshToken']  = Ninja_Forms()->get_setting('mautic_api_access_refresh_token');
        $settings['accessTokenExpires']  = Ninja_Forms()->get_setting('mautic_api_access_token_expires');

        $initAuth = new ApiAuth();

        /** @var Mautic\Auth\OAuth $auth */
        $auth = $initAuth->newAuth($settings);

        try {
            if ($auth->validateAccessToken()) {

                if ($auth->accessTokenUpdated()) {
                    $accessTokenData = $auth->getAccessTokenData();

                    if ( Ninja_Forms()->get_setting('mautic_api_version') == 'OAuth1a') {
                        Ninja_Forms()->update_setting('mautic_api_access_token', $accessTokenData['access_token']);
                        Ninja_Forms()->update_setting('mautic_api_access_token_secret', $accessTokenData['access_token_secret']);
                        Ninja_Forms()->update_setting('mautic_api_access_token_expires', $accessTokenData['expires']);
                    }

                    elseif ( Ninja_Forms()->get_setting('mautic_api_version') == 'OAuth2') {
                        Ninja_Forms()->update_setting('mautic_api_access_token', $accessTokenData['access_token']);
                        Ninja_Forms()->update_setting('mautic_api_access_token_expires', $accessTokenData['expires']);
                        Ninja_Forms()->update_setting('mautic_api_access_token_type', $accessTokenData['token_type']);
                        Ninja_Forms()->update_setting('mautic_api_access_refresh_token', $accessTokenData['refresh_token']);
                    }
                }
            }
        } catch (\Exception $e) {
            error_log("Unable to connect to Mautic API");
        }

        $api = new MauticApi();
        $contactApi = $api->newApi('contacts', $auth, $baseUrl);
        $updatedData = [];

        foreach ($data['fields'] as $field) {
            if (isset($field['manual_key']) && $field['manual_key'] && isset($field['key'])) {
                $ninjaFormKey = $field['key'];

                $matches = array_filter($action_settings['field_map'], function($fieldMapField) use($ninjaFormKey) {
                    return $fieldMapField['label'] == $ninjaFormKey;
                });
                if (count($matches) !== 1) {
                    continue;
                }
                $match = array_pop($matches);

                $mauticKey = $match['value'];

                $updatedData[$mauticKey] = $field['value'];
            }
        }

        $contactApi->edit($contactId, $updatedData);

        return $data;
    }
}
