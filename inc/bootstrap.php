<?php

namespace WPDev;

use Brain\Hierarchy\Hierarchy;
use Symfony\Component\VarDumper\VarDumper;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;
use WPDev\Controller\ControllerLoader;
use WPDev\Debug\Dumper;
use WPDev\Factories\PostFactory;
use WPDev\Models\Post;
use WP_Query;

/*
|--------------------------------------------------------------------------
| Setup Dumper
|--------------------------------------------------------------------------
| Better dumper for debugging
*/
VarDumper::setHandler([Dumper::class, 'dump']);

/*
|--------------------------------------------------------------------------
| Initialize Whoops
|--------------------------------------------------------------------------
| Better and prettier error handling
*/
if (defined( 'WP_DEBUG') && WP_DEBUG && (!defined( 'WP_CLI' ) || !WP_CLI)) {
	$whoops = new Run;
	$whoops->pushHandler(new PrettyPageHandler)->register();
}

/*
|--------------------------------------------------------------------------
| Setup Controllers
|--------------------------------------------------------------------------
*/
function data($template)
{
    // If we don't have a template...do what WP would do
    if ( ! $template && current_user_can('switch_themes')) {
        $theme = wp_get_theme();
        if ($theme->errors()) {
            wp_die($theme->errors());
        }
    }

    $default_data = [
        'Post'  => PostFactory::make(get_post()),
        'Posts' => [],
    ];

    if ( ! empty($GLOBALS['wp_query'])) {
        $default_data['Posts'] = PostFactory::makeFromQuery($GLOBALS['wp_query']);
    }

    // Load Controllers then include the template
    $controller = ControllerLoader::create(new Hierarchy)->getController();

    /** @var \WPDev\Controller\ControllerInterface $controller */
    if ($controller) {
        // set the default data so controller can access it in the build method
        $controller->defaultData = $default_data;
        $data                    = array_merge($default_data, $controller->build());
    } else {
        $data = $default_data;
    }

    extract($data);

    include $template;
}
add_filter('template_include', __NAMESPACE__.'\\data', 1000);

/*
|--------------------------------------------------------------------------
| Load this plugin first
|--------------------------------------------------------------------------
| WordPress sorts and loads plugins alphabetically.
| Here we find the wpdev plugin and move it to the front.
| And we have to do it every time a plugin is activated since
| WP always calls sort()
*/
function on_plugin_activation()
{
    // should end up evaluating to 'wpdev/wpdev.php'
    $path    = basename(dirname(__DIR__)).'/wpdev.php';
    $plugins = get_option('active_plugins', []);
    $key     = array_search($path, $plugins);

    if ($key !== false) {
        array_splice($plugins, $key, 1);
        array_unshift($plugins, $path);
        update_option('active_plugins', $plugins);
    }
}
add_action('activated_plugin', __NAMESPACE__.'\\on_plugin_activation');

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