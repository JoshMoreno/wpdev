<?php

namespace WPDev;

use Brain\Hierarchy\Hierarchy;
use Symfony\Component\VarDumper\VarDumper;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;
use WPDev\Controller\ControllerLoader;
use WPDev\Debug\Dumper;
use WPDev\Factories\PostFactory;

/*
|--------------------------------------------------------------------------
| Setup Dumper
|--------------------------------------------------------------------------
| Better dumper for debugging
*/
VarDumper::setHandler([Dumper::class, 'dump']);

/*
|--------------------------------------------------------------------------
| Setup Controllers
|--------------------------------------------------------------------------
*/
function default_data() {
    $data = [
        'Posts' => [],
    ];

    if (is_home()) {
        $data['Post'] = PostFactory::make(get_option('page_for_posts', true));
    } else {
        $data['Post'] = PostFactory::make(get_post());
    }

    if ( ! empty($GLOBALS['wp_query'])) {
        $data['Posts'] = PostFactory::makeFromQuery($GLOBALS['wp_query']);
    }

    // Load Controllers then include the template
    $controller = ControllerLoader::create(new Hierarchy)->getController();

    /** @var \WPDev\Controller\ControllerInterface $controller */
    if ($controller) {
        // set the default data so controller can access it in the build method
        $controller->defaultData = $data;
        $data = array_merge($data, $controller->build());
    }

    return apply_filters('wpdev.templateData', $data);
}

function render_template($template)
{
    // If we don't have a template...do what WP would do
    if ( ! $template && current_user_can('switch_themes')) {
        $theme = wp_get_theme();
        if ($theme->errors()) {
            wp_die($theme->errors());
        }
    }

    extract(default_data(), EXTR_OVERWRITE);

    include $template;
}

add_filter('after_setup_theme', function() {
    if (class_exists('Roots\\Sage\\Container')) {
        // sage loops over these classes and allows us to filter
        // the template data. We want to do it on all so let's
        // add a class to all pages that we can hook into.
        add_filter('body_class', function($classes, $class) {
            $classes[] = 'all';
            return $classes;
        }, 10, 2);

        // sage theme wants complete control of template_include so
        // we'll instead hook into their filter
        add_filter("sage/template/all/data", function($data, $template) {
            return array_merge($data, default_data());
        }, 10, 2);
        return;
    }

    add_filter('template_include', __NAMESPACE__.'\\render_template', PHP_INT_MAX);
});

/*
|--------------------------------------------------------------------------
| Load this plugin first
|--------------------------------------------------------------------------
| WordPress sorts and loads plugins alphabetically.
| Here we find the wpdev plugin and move it to the front.
| And we have to do it every time a plugin is activated since
| WP always calls sort()
*/
function on_pre_update_option_active_plugins($value, $old_value, $option)
{
	// should end up evaluating to 'wpdev/wpdev.php'
	$path = basename(dirname(__DIR__)).'/wpdev.php';
	$key = array_search($path, $value);

	if ($key !== false) {
		array_splice($value, $key, 1);
		array_unshift($value, $path);
	}

	return $value;
}

add_filter('pre_update_option_active_plugins', __NAMESPACE__.'\\on_pre_update_option_active_plugins', 10, 3);

/*
|--------------------------------------------------------------------------
| Flush rewrite rules on every plugin activation
|--------------------------------------------------------------------------
| This is intended to be used in conjunction with the included Facades. The
| facades will take care of calling the appropriate functions in the
| activation and deactivation hooks. This will handle flushing the rewrite
| rules.
|
| This is supposedly an "expensive operation". In reality the cost is
| pretty negligible. Especially when considering the cost of handling the
| activation hooks in each and every one of your plugins for simple things
| like registering a post type.
*/
function flush_rewrite_rules_on_activation()
{
    flush_rewrite_rules();
}
add_action('activated_plugin', __NAMESPACE__.'\\flush_rewrite_rules_on_activation');