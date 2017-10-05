<?php use Mautic\Auth\ApiAuth;

if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Class NF_Example_Admin_Settings
 *
 * This is an example implementation of a Settings Class for a Ninja Forms Add-on.
 * The Ninja Forms Settings Submenu page handles registering, rendering, and saving settings.
 * Settings handled by Ninja Forms can be access using the Ninja Forms API.
 * Multiple WordPress Hooks are available for interacting with settings processing.
 */
final class NF_Mautic_Admin_Settings
{
    /**
     * NF_Example_Admin_Settings constructor.
     *
     * The following WordPress hooks are listed in processing order.
     */
    public function __construct()
    {
        if(session_id() == '')
            session_start();

        /*
         * On Settings Page Load
         */
        add_filter( 'ninja_forms_plugin_settings',                array( $this, 'plugin_settings'        ), 10, 1 );
        add_filter( 'ninja_forms_plugin_settings_groups',         array( $this, 'plugin_settings_groups' ), 10, 1 );
        /*
         * On Settings Page Save (Submit)
         */
        add_filter( 'ninja_forms_update_setting_mautic_api_client_key', array( $this, 'update_setting' ), 10, 1 );
        add_action( 'ninja_forms_save_setting_mautic_authorize',     array( $this, 'authorize_setting'   ), 10, 1 );
        add_action( 'ninja_forms_save_setting_mautic_deauthorize',     array( $this, 'deauthorize_setting'   ), 10, 1 );
    }

    /**
     * Add Plugin Settings
     *
     * Add a new setting within a defined setting group.
     * The setting's configuration is similar to Action Settings and Fields Settings.
     *
     * @param array $settings
     * @return array $settings
     */
    public function plugin_settings( $settings )
    {
        $settings[ 'mautic' ] = NF_Mautic()->config( 'PluginSettings' );

        // Authorize if needed
        if (isset($_GET['oauth_verifier']) || isset($_GET['state'])) {
            $this->authorize_setting(1);
        }

        return $settings;
    }

    /**
     * Add Plugin Settings Groups
     *
     * Add a new Settings Groups for this plugin's settings.
     * The grouped settings will be rendered as a metabox in the Ninja Forms Settings Submenu page.
     *
     * @param array $groups
     * @return array $groups
     */
    public function plugin_settings_groups( $groups )
    {
        $groups = array_merge( $groups, NF_Mautic()->config( 'PluginSettingsGroups' ) );
        return $groups;
    }

    /**
     * Sanitize the auth code
     *
     * @param $value
     * @return mixed
     */
    public function update_setting( $value )
    {
        return sanitize_text_field( trim( $value ) );
    }

    /**
     * De-Authorize
     *
     * If the "remove button" is clicked then delete the auth codes
     *
     * @param $value
     * @return void
     */
    public function deauthorize_setting( $value ) {
        if ( 1 !== absint( $value ) ) {
            return;
        }
        Ninja_Forms()->update_setting('mautic_api_access_token', false);
        Ninja_Forms()->update_setting('mautic_api_access_token_secret', false);
        Ninja_Forms()->update_setting('mautic_api_access_token_expires', false);
        Ninja_Forms()->update_setting('mautic_api_access_token_type', false);
        Ninja_Forms()->update_setting('mautic_api_access_refresh_token', false);


        wp_redirect(admin_url() . 'admin.php?page=nf-settings#'.NF_Mautic::BOOKMARK);
        wp_redirect( Ninja_Forms()->get_setting('mautic_api_callback', NF_Mautic()->getDefaultCallback()) );
    }

    /**
     * Authorize
     *
     * If the "remove button" is clicked then delete the auth codes
     *
     * @param $value
     * @return void
     */
    public function authorize_setting( $value )
    {
        if ( 1 !== absint( $value ) ) {
            return;
        }

        $settings = array(
            'baseUrl'          => $baseUrl = Ninja_Forms()->get_setting('mautic_api_base_url'),

            'version'          => Ninja_Forms()->get_setting('mautic_api_version'),
            'clientKey'        => Ninja_Forms()->get_setting('mautic_api_client_key'),
            'clientSecret'     => Ninja_Forms()->get_setting('mautic_api_client_secret'),
            'callback'         => Ninja_Forms()->get_setting('mautic_api_callback')
                ?: NF_Mautic()->getDefaultCallback(),
        );

        $settings['accessToken']        = Ninja_Forms()->get_setting('mautic_api_access_token');
        $settings['accessTokenSecret']  = Ninja_Forms()->get_setting('mautic_api_access_token_secret');
        $settings['refreshToken']  = Ninja_Forms()->get_setting('mautic_api_access_refresh_token');
        $settings['accessTokenExpires']  = Ninja_Forms()->get_setting('mautic_api_access_token_expires');

        $initAuth = new ApiAuth();
        $auth = $initAuth->newAuth($settings);

        try {
            if ($auth->validateAccessToken()) {

                if ($auth->accessTokenUpdated()) {
                    Ninja_Forms()->update_setting('mautic_api_last_status', 'Access Token Updated');

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

                    else {
                        Ninja_Forms()->update_setting('mautic_api_last_status', 'Invalid OAuth Version');
                        throw new \Exception("Invalid OAuth version");
                    }
                } else {
                    Ninja_Forms()->update_setting('mautic_api_last_status', 'Access Token already Valid');
                }
            } else {
                Ninja_Forms()->update_setting('mautic_api_last_status', 'Invalid access token');
            }
        } catch (Exception $e) {
            Ninja_Forms()->update_setting('mautic_api_last_status', 'ERROR: ' . $e->getMessage());
            // Do Error handling
        }

        wp_redirect(admin_url() . 'admin.php?page=nf-settings#' . NF_Mautic::BOOKMARK);
    }

} // End Class NF_Example_Admin_Settings
