<?php if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class NF_Field_MauticExample
 */
class NF_Mautic_Fields_MauticExample extends NF_Fields_Textbox
{
    protected $_name = 'mautic';

    protected $_section = 'common';

    protected $_type = 'textbox';

    protected $_templates = 'textbox';

    public function __construct()
    {
        parent::__construct();

        $this->_nicename = __( 'Mautic Example Field', 'ninja-forms' );
    }
}