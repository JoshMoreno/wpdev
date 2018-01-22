<?php

namespace WPDev;

use Brain\Hierarchy\Hierarchy;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

/*
|--------------------------------------------------------------------------
| Initialize Whoops
|--------------------------------------------------------------------------
| Better and prettier error handling
|
*/
$whoops = new Run;
$whoops->pushHandler(new PrettyPageHandler)->register();

/*
|--------------------------------------------------------------------------
| Setup Controllers
|--------------------------------------------------------------------------
|
*/
add_action('template_redirect', function () {


    //foreach( $templates as $template ) {
    //    $path = get_template_directory() . "/templates/{$template}.phtml";
    //    if ( file_exists( $path ) ) {
    //        require $path;
    //        exit();
    //    }
    //}

});

class Dog {
    public static function getName()
    {
        return 'Mocha';
    }
}

function data($template) {
    // If we don't have a template...do what WP would do
    if (!$template && current_user_can( 'switch_themes' )) {
        $theme = wp_get_theme();
        if ( $theme->errors() ) {
            wp_die( $theme->errors() );
        }
    }

    // Load Controllers then include the template
    $templates = (new Hierarchy())->getTemplates();
    new ControllerLoader(new Hierarchy);
    dump($templates);

    include $template;
}
add_filter('template_include', __NAMESPACE__ . '\\data', 1000);
