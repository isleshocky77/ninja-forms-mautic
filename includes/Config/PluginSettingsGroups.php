<?php if ( ! defined( 'ABSPATH' ) ) exit;

return apply_filters( 'nf_mautic_plugin_settings_groups', array(

    'mautic' => array(
        'id' => 'mautic',
		'label' => __( 'Mautic Settings', 'ninja-forms-mautic' ),
    ),

));
