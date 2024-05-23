<?php

namespace Municipio\PostTypeDesign;

use Municipio\PostTypeDesign\ConfigFromPageId;
use Municipio\PostTypeDesign\ConfigSanitizer;
use Kirki\Compatibility\Kirki;

class SaveDesigns {
    public function __construct(private string $optionName) 
    {
        add_action('wp', array($this, 'storeDesign'));
    }

    public function storeDesign($customizerManager = null) 
    {
        $postTypes = get_post_types(['public' => true], 'names');
        if (empty($postTypes)) {
            return;
        }

        $fields = Kirki::$all_fields;

        // echo '<pre>' . print_r( $fields, true ) . '</pre>';
        if (!empty($fields)) {

            foreach ($fields as $key => $field) {
                echo '<pre>' . print_r( $field, true ) . '</pre>';
            }
            // die;
            // $fields = array_map(function($value, $key) {
            //     if (!preg_match('/\[.*?\]/', $key)) {
            //         return $value;
            //     }
            // }, $fields, array_keys($fields));
        }

        // echo '<pre>' . print_r( $fields, true ) . '</pre>';
        // die;

        
        $designOption   = get_option('post_type_design');
        foreach ($postTypes as $postType) {
            $design = get_theme_mod($postType . '_load_design');

            if (empty($design) || isset($designOption[$postType])) {
                continue;
            }

            $designConfig = (new ConfigFromPageId($design))->get();

            $configTransformerInstance = new ConfigSanitizer($designConfig);
            $configTransformerInstance->setKeys(MultiColorKeys::get());
            $configTransformerInstance->setKeys(ColorKeys::get());
            $designConfig = $configTransformerInstance->transform();

            if (!empty($designConfig)) {
                $designOption[$postType] = $designConfig;
                update_option('post_type_design', $designOption);
            }
        }
    }
}