<?php
/**
 * Plugin name: Custom Plugin
 * Description: Description goes here.
 * Version: 1.0.0
 * Author: Josh Moreno
 * Author URI: http://JoshMoreno.com
 */

namespace WPDev\CustomPostType;

use Whoops\Example\Exception;

class CustomPostType
{
    // todo method for capability_type
    // todo method for capabilities
    // todo method for map_meta_cap
    // todo method for capability_type
    // todo method for capabilities

    private $name;
    public $singularName = '';
    public $pluralName = '';
    public $overrideArgs = [];

    public function __construct(string $name = '')
    {
        if ( ! $name) {
            throw new \Exception('Did not receive a name for the custom post type');
        }

        $this->name = $name;

        $this->validateName();
    }

    /**
     * Adds to the 'supports' array
     *
     * @param string $arg
     *
     * @return $this
     */
    public function addSupportArg($arg = '')
    {
        if ( ! $arg) {
            return $this;
        }

        if (empty($this->overrideArgs['supports'])) {
            $this->overrideArgs['supports'] = [];
        }

        $this->overrideArgs['supports'][] = $arg;

        return $this;
    }

    private function buildArgs()
    {
        return array_merge($this->buildDefaultArgs(), $this->overrideArgs);
    }

    private function buildDefaultArgs()
    {
        $defaultArgs           = [];
        $defaultArgs['public'] = true;
        $defaultArgs['labels'] = [
            'name'                  => $this->getPluralName(),
            'singular_name'         => $this->getSingularName(),
            //'add_new'               => "", // defaults to 'Add New'
            'add_new_item'          => "Add New {$this->getSingularName()}",
            'edit_item'             => "Edit {$this->getSingularName()}",
            'new_item'              => "New {$this->getSingularName()}",
            'view_item'             => "View {$this->getSingularName()}",
            'view_items'            => "View {$this->getPluralName()}",
            'search_items'          => "Search {$this->getPluralName()}",
            'not_found'             => "No {$this->getPluralName()} found",
            'not_found_in_trash'    => "No {$this->getPluralName()} found in Trash",
            'parent_item_colon'     => "Parent {$this->getSingularName()}:",
            'all_items'             => "All {$this->getPluralName()}",
            'archives'              => "{$this->getSingularName()} Archives",
            'attributes'            => "{$this->getSingularName()} Attributes",
            'insert_into_item'      => "Insert into {$this->getSingularName()}",
            'uploaded_to_this_item' => "Uploaded to this {$this->getSingularName()}",
            //'featured_image'        => '', // defaults to 'Featured Image'
            //'set_featured_image'    => '', // defaults to 'Set featured image'
            //'remove_featured_image' => '', // defaults to 'Remove featured image'
            //'use_featured_image'    => '', // defaults to 'Use as featured image'
            //'menu_name'             => '', // defaults to name from above
            //'filter_items_list'     => "Filter {$this->getPluralName()}",
            //'items_list_navigation' => '',
            //'items_list'            => '',
            //'name_admin_bar'        => '', // defaults to singular_name from above
        ];

        return $defaultArgs;
    }

    /**
     * Whether or not the post_type can be exported
     * Default: true
     *
     * @param bool $bool
     *
     * @return \WPDev\CustomPostType\CustomPostType
     */
    public function canExport(bool $bool = true)
    {
        return $this->setArg('can_export', $bool);
    }

    /**
     * Whether to delete posts of this type when deleting a user. If true, posts of this type
     * belonging to the user will be moved to trash when then user is deleted. If false, posts
     * of this type belonging to the user will not be trashed or deleted. If not set (the default),
     * posts are trashed if post_type_supports('author'). Otherwise posts are not trashed or deleted.
     *
     * Default: null
     *
     * @param bool $bool
     *
     * @return \WPDev\CustomPostType\CustomPostType
     */
    public function deleteWithUser(bool $bool = true)
    {
        return $this->setArg('delete_with_user', $bool);
    }

    public function deregister(string $name = '')
    {
        if ( ! $name) {
            throw new \Exception('Need to pass in the name of the post type to deregister');
        }

        unregister_post_type($name);
    }

    private function formatName(bool $plural = false)
    {
        $name = str_replace('_', ' ', $this->name);

        // capitalize hyphenated words
        if (strpos($name, '-')) {
            $name = implode('-', array_map('ucfirst', explode('-', $name)));
        }

        if ($plural) {
            $name .= 's';
        }

        return ucwords($name);
    }

    private function getSingularName()
    {
        if ( ! $this->singularName) {
            $this->singularName = $this->formatName();
        }

        return $this->singularName;
    }

    private function getPluralName()
    {
        if ( ! $this->pluralName && ! $this->singularName) {
            $this->pluralName = $this->formatName(true);
        } elseif ( ! $this->pluralName) {
            $this->pluralName = $this->singularName.'s';
        }

        return $this->pluralName;
    }

    /**
     * Enables post type archives. Will use $post_type as archive slug by default.
     * Note: Will generate the proper rewrite rules if rewrite is enabled.
     * Also use rewrite to change the slug used. If string, it should be translatable.
     *
     * @param bool|string $val
     *
     * @return \WPDev\CustomPostType\CustomPostType
     */
    public function hasArchive($val = true)
    {
        return $this->setArg('has_archive', $val);
    }

    /**
     * This sets the endpoint mask. However rewrite['ep_mask'] takes precedence if it's set there too.
     *
     * @param int|const $endpoint Constant preferred to avoid future failure (core updates)
     *
     * @return \WPDev\CustomPostType\CustomPostType
     */
    public function permalinkEPMask($endpoint = EP_PERMALINK)
    {
        return $this->setArg('permalink_epmask', $endpoint);
    }

    /**
     * True (default) will use the post type slug
     * False disables query_var key use. A post type cannot be loaded at /?{query_var}={single_post_slug}
     * A string essentially overrides the post type slug /?{query_var_string}={single_post_slug}
     *
     * Remember this is for query_vars not for permalink slug.
     *
     * @param bool|string $query_var
     *
     * @return \WPDev\CustomPostType\CustomPostType
     */
    public function queryVar($query_var = true)
    {
        return $this->setArg('query_var', $query_var);
    }

    /**
     * Use this method if you want need to use a named function
     * to register your post type.
     *
     * @return \WP_Error|\WP_Post_Type
     */
    public function registerManually()
    {
        return register_post_type($this->name, $this->buildArgs());
    }

    /**
     * This is the easiest way to register your post type.
     * However it uses an anonymous function so if you need to allow other plugins
     * to be able to use @see remove_action() then you should use @see registerManually()
     *
     * @param callable|null $callback
     */
    public function register(callable $callback = null)
    {
        add_action('init', function () use ($callback) {
            if ($callback) {
                $response = register_post_type($this->name, $this->buildArgs());
                $callback($response);
            } else {
                register_post_type($this->name, $this->buildArgs());
            }
        });
    }

    /**
     * Provide a callback function that will be called when setting up the meta boxes for the edit form.
     * The callback function takes one argument $post, which contains the WP_Post object for the currently edited post.
     * Do remove_meta_box() and add_meta_box() calls in the callback.
     *
     * @param array|string $callback
     *
     * @return \WPDev\CustomPostType\CustomPostType
     */
    public function registerMetaBoxCB($callback = '')
    {
        return $this->setArg('register_meta_box_cb', $callback);
    }

    /**
     * The base slug that this post type will use when accessed using the REST API.
     * Default: $post_type
     *
     * @param string $rest_base
     *
     * @return \WPDev\CustomPostType\CustomPostType
     */
    public function restBase(string $rest_base)
    {
        if ( ! $rest_base) {
            $rest_base = $this->name;
        }

        return $this->setArg('rest_base', $rest_base);
    }

    /**
     * An optional custom controller to use instead of WP_REST_Posts_Controller. Must be a subclass of WP_REST_Controller.
     * Default: WP_REST_Posts_Controller
     *
     * @param string $controller
     *
     * @return \WPDev\CustomPostType\CustomPostType
     */
    public function restControllerClass(string $controller = 'WP_REST_Posts_Controller')
    {
        return $this->setArg('rest_controller_class', $controller);
    }

    /**
     * ['slug']         string Customize the permalink structure slug. Defaults to the $post_type value. Should be translatable.
     * ['with_front']   bool Should the permalink structure be prepended with the front base.
     *                  (example: if your permalink structure is /blog/,
     *                  then your links will be: false->/news/, true->/blog/news/). Defaults to true
     * ['feeds']        bool Should a feed permalink structure be built for this post type. Defaults to has_archive value.
     * ['pages']        bool Should the permalink structure provide for pagination. Defaults to true
     * ['ep_mask']      const If not specified, then it inherits from permalink_epmask(if permalink_epmask is set),
     *                  otherwise defaults to EP_PERMALINK
     *                  see @link https://make.wordpress.org/plugins/2012/06/07/rewrite-endpoints-api/
     *                  and also @link https://code.tutsplus.com/articles/the-rewrite-api-post-types-taxonomies--wp-25488
     *
     * @param array|bool $val (see above)
     *
     * @return \WPDev\CustomPostType\CustomPostType
     */
    public function rewrite($val = true)
    {
        return $this->setArg('rewrite', $val);
    }

    /**
     * Set an arg. Can be used to override the defaults.
     * This is just a catch-all. In case there isn't a more semantic
     * method or if that's just your preference.
     *
     * @param string $key
     * @param mixed $val
     *
     * @return $this
     */
    public function setArg(string $key = '', $val = '')
    {
        $this->overrideArgs[$key] = $val;

        return $this;
    }

    /**
     * Whether to expose this post type in the REST API.
     *
     * @param bool $bool
     *
     * @return \WPDev\CustomPostType\CustomPostType
     */
    public function showInRest(bool $bool = true)
    {
        return $this->setArg('show_in_rest', $bool);
    }

    /**
     * Self explanatory, but a word from the docs...
     * If you set to true, on the taxonomy page (ex: taxonomy.php)
     * WordPress will not find your posts and/or pagination will make 404 error...
     *
     * @param bool $bool
     *
     * @return \WPDev\CustomPostType\CustomPostType
     */
    public function excludeFromSearch(bool $bool = false)
    {
        return $this->setArg('exclude_from_search', $bool);
    }

    /**
     * Whether the post type is hierarchical (e.g. page). Allows Parent to be specified.
     * The 'supports' parameter should contain 'page-attributes' to show the
     * parent select box on the editor page.
     *
     * Note: this parameter was intended for Pages. Be careful when choosing it
     * for your custom post type - if you are planning to have very many entries
     * (say - over 2-3 thousand), you will run into load time issues. With this
     * parameter set to true WordPress will fetch all IDs of that particular post
     * type on each administration page load for your post type. Servers with
     * limited memory resources may also be challenged by this parameter being set to true.
     *
     * @param bool $bool
     *
     * @return \WPDev\CustomPostType\CustomPostType
     */
    public function hierarchical(bool $bool = false)
    {
        return $this->setArg('hierarchical', $bool);
    }

    /**
     * Whether to use the internal default meta capability handling.
     * Note: If set it to false then standard admin role can't edit the posts types.
     * Then the edit_post capability must be added to all roles to add or edit the posts types.
     *
     * @param bool $bool
     *
     * @return \WPDev\CustomPostType\CustomPostType
     */
    public function mapMetaCap(bool $bool = true)
    {
        return $this->setArg('map_meta_cap', $bool);
    }

    /**
     * @param string $icon name of Dashicon, URL to icon, or base64 encoded svg with fill="black"
     *
     * @link https://developer.wordpress.org/resource/dashicons/
     *
     * @return \WPDev\CustomPostType\CustomPostType
     */
    public function menuIcon(string $icon = '')
    {
        return $this->setArg('menu_icon', $icon);
    }

    /**
     * The position in the menu order the post type should appear. show_in_menu must be true.
     * Default: defaults to below Comments
     * 5 - below Posts
     * 10 - below Media
     * 15 - below Links
     * 20 - below Pages
     * 25 - below comments
     * 60 - below first separator
     * 65 - below Plugins
     * 70 - below Users
     * 75 - below Tools
     * 80 - below Settings
     * 100 - below second separator
     *
     * @param int $position
     *
     * @return \WPDev\CustomPostType\CustomPostType
     */
    public function menuPosition(int $position = 25)
    {
        return $this->setArg('menu_position', $position);
    }

    public function setPluralName(string $plural_name = '')
    {
        $this->pluralName = $plural_name;

        return $this;
    }

    /**
     * Implies:
     * exclude_from_search = false
     * publicly_queryable = true
     * show_in_nav_menus = true
     * show_ui = true
     *
     * @param bool $bool
     *
     * @return \WPDev\CustomPostType\CustomPostType
     */
    public function public(bool $bool = true)
    {
        return $this->setArg('public', $bool);
    }

    /**
     * Whether queries can be performed on the front end as part of parse_request().
     *
     * @param bool $bool
     *
     * @return \WPDev\CustomPostType\CustomPostType
     */
    public function publiclyQueryable(bool $bool = true)
    {
        return $this->setArg('publicly_queryable', $bool);
    }

    /**
     * Whether to make this post type available in the WordPress admin bar.
     * Default: value of the show_in_menu argument
     *
     * @param bool $bool
     *
     * @return \WPDev\CustomPostType\CustomPostType
     */
    public function showInAdminBar(bool $bool = true)
    {
        return $this->setArg('show_in_admin_bar', $bool);
    }

    /**
     * @param bool|string $val - If string is given it will be a submenu
     * if that url exists. Examples: 'tools.php' or 'edit.php?post_type=page';
     *
     * @return \WPDev\CustomPostType\CustomPostType
     */
    public function showInMenu($val = true)
    {
        return $this->setArg('show_in_menu', $val);
    }

    /**
     * Whether post_type is available for selection in navigation menus.
     * Default: value of public argument
     *
     * @param bool $bool
     *
     * @return \WPDev\CustomPostType\CustomPostType
     */
    public function showInNavMenus(bool $bool = true)
    {
        return $this->setArg('show_in_nav_menus', $bool);
    }

    /**
     * Whether to generate a default UI for managing this post type in the admin.
     * Default: value of public argument
     *
     * @param bool $bool
     *
     * @return \WPDev\CustomPostType\CustomPostType
     */
    public function showUI(bool $bool = true)
    {
        return $this->setArg('show_ui', $bool);
    }

    public function setSingularName(string $singular_name = '')
    {
        $this->singularName = $singular_name;

        return $this;
    }

    /**
     * @return \WPDev\CustomPostType\CustomPostType
     */
    public function supportsAuthor()
    {
        return $this->addSupportArg('author');
    }

    /**
     * @return \WPDev\CustomPostType\CustomPostType
     */
    public function supportsComments()
    {
        return $this->addSupportArg('comments');
    }

    /**
     * @return \WPDev\CustomPostType\CustomPostType
     */
    public function supportsCustomFields()
    {
        return $this->addSupportArg('custom-fields');
    }

    /**
     * @return \WPDev\CustomPostType\CustomPostType
     */
    public function supportsEditor()
    {
        return $this->addSupportArg('editor');
    }

    /**
     * @return \WPDev\CustomPostType\CustomPostType
     */
    public function supportsExcerpt()
    {
        return $this->addSupportArg('excerpt');
    }

    /**
     * Alias for @see \WPDev\CustomPostType\CustomPostType::supportsThumbnail()
     *
     * @return \WPDev\CustomPostType\CustomPostType
     */
    public function supportsFeaturedImage()
    {
        return $this->supportsThumbnail();
    }

    /**
     * @return \WPDev\CustomPostType\CustomPostType
     */
    public function supportsPageAttributes()
    {
        return $this->addSupportArg('page-attributes');
    }

    public function supportsPostFormats()
    {
        return $this->addSupportArg('post-formats');
    }

    /**
     * @return \WPDev\CustomPostType\CustomPostType
     */
    public function supportsRevisions()
    {
        return $this->addSupportArg('revisions');
    }

    /**
     * @return \WPDev\CustomPostType\CustomPostType
     */
    public function supportsThumbnail()
    {
        return $this->addSupportArg('thumbnail');
    }

    /**
     * @return \WPDev\CustomPostType\CustomPostType
     */
    public function supportsTitle()
    {
        return $this->addSupportArg('title');
    }

    /**
     * @return \WPDev\CustomPostType\CustomPostType
     */
    public function supportsTrackbacks()
    {
        return $this->addSupportArg('trackbacks');
    }

    /**
     * False can be passed as value instead of an array to
     * prevent default (title and editor) behavior
     *
     * @param array|false $features
     *
     * @return \WPDev\CustomPostType\CustomPostType
     */
    public function supports($features = ['editor', 'title'])
    {
        return $this->setArg('supports', $features);
    }

    /**
     * An array of registered taxonomies like category or post_tag that will be used with this post type.
     * This can be used in lieu of calling register_taxonomy_for_object_type() directly.
     * Custom taxonomies still need to be registered with register_taxonomy().
     *
     * @param array $taxonomies
     *
     * @return \WPDev\CustomPostType\CustomPostType
     */
    public function taxonomies(array $taxonomies = [])
    {
        return $this->setArg('taxonomies', $taxonomies);
    }

    private function validateName()
    {
        $reserved_names = [
            'post',
            'page',
            'attachment',
            'revision',
            'nav_menu_item',
            'custom_css',
            'customize_changeset',
            'action',
            'author',
            'order',
            'theme',
        ];

        if (in_array($this->name, $reserved_names)) {
            throw new \Exception("'{$this->name}' is a WordPress reserved name");
        }

        if (strpos($this->name, ' ') !== false) {
            throw new \Exception('Post type machine name cannot contain spaces.');
        }

        if (strtolower($this->name) !== $this->name) {
            throw new \Exception('Post type machine name cannot contain capital letters.');
        }

        if (strlen($this->name) > 20) {
            throw new \Exception('Post type machine name cannot exceed 20 characters. Current name is '.strlen($this->name).' characters long.');
        }
    }
}

$projects = new CustomPostType('package');
$projects->register();

