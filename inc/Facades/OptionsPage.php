<?php

namespace WPDev\Facades;

use Cocur\Slugify\Slugify;
use Webmozart\Assert\Assert;

class OptionsPage
{
    private $pageTitle;
    private $menuTitle;
    private $capability = 'manage_options';
    private $menuSlug;
    private $menuIcon;
    private $position;
    private $callback;
    private $topLevel = false;
    private $parentSlug = 'options-general.php';

    /**
     * Constructor. For a more fluid syntax use `OptionsPage::create()`.
     *
     * @param string $page_title The title of the page. By default this will also be used as the menu title and the page slug (a slugified version of course).
	 * @throws \InvalidArgumentException
     */
    public function __construct($page_title)
    {
    	Assert::string($page_title);
        $this->pageTitle = $page_title;
        $this->menuTitle = $page_title;
        $this->menuSlug  = (new Slugify())->slugify($page_title);
        $this->callback  = [$this, 'sampleCallback'];
    }

    /**
     * Set the page content callback.
     *
     * @param callable $callback The callback in charge of generating the content for the page.
     *
     * @return $this
     */
    public function contentCallback(callable $callback)
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * The capability required for this menu to be displayed to the user.
     *
     * You still need to check for the correct capability in the content callback.
     *
     * @param string $capability
     *
     * @return $this
	 * @throw \InvalidArgumentException
     */
    public function capability($capability)
    {
    	Assert::string($capability);
        $this->capability = $capability;

        return $this;
    }

    /**
     * Allows for more fluid syntax.
     *
     * @param string $page_title The title of the page. By default this will also be used as the menu title and the page slug (a slugified version of course).
     *
     * @return $this
	 * @throw \InvalidArgumentException
     */
    public static function create($page_title)
    {
    	Assert::string($page_title);
        return new static($page_title);
    }

    /**
     * Set the menu icon.
     *
     * @param string $icon name of Dashicon, URL to icon, or base64 encoded svg with fill="black"
     *
     * @link https://developer.wordpress.org/resource/dashicons/
     *
     * @return $this
	 * @throw \InvalidArgumentException
     */
    public function menuIcon($icon = '')
    {
    	Assert::string($icon);
        $this->menuIcon = $icon;

        return $this;
    }

    /**
     * Set the slug for the page.
     *
     * @param string $menu_slug
     *
     * @return $this
	 * @throw \InvalidArgumentException
     */
    public function menuSlug($menu_slug)
    {
    	Assert::string($menu_slug);
        $this->menuSlug = $menu_slug;

        return $this;
    }

    /**
     * Sets the menu title.
     *
     * @param string $menu_title
     *
     * @return $this
	 * @throw \InvalidArgumentException
     */
    public function menuTitle($menu_title)
    {
    	Assert::string($menu_title);
        $this->menuTitle = $menu_title;

        return $this;
    }

    /**
     * Sets the parent slug. Used to make this page a child page.
     *
     * @param string $slug The slug of the parent page.
     *
     * @return $this
	 * @throw \InvalidArgumentException
     */
    public function parentSlug($slug = 'options-general.php')
    {
    	Assert::string($slug);
        $this->parentSlug = $slug;

        return $this;
    }

    /**
     * Sets the position of the menu item.
     *
     * @param int $position
     *
     * @return $this
	 * @throws \InvalidArgumentException
     */
    public function position($position = 100)
    {
    	Assert::integer($position);
        $this->position = $position;

        return $this;
    }

    /**
     * Registers the page with WP. Hooks and all.
     */
    public function register()
    {
        add_action('admin_menu', function () {
            $this->registerManually();
        });
    }

    /**
     * Registers the page but not within the appropriate hook `admin_menu`.
     */
    public function registerManually()
    {
        $args = [
            $this->pageTitle,
            $this->menuTitle,
            $this->capability,
            $this->menuSlug,
            $this->callback,
        ];

        if ($this->topLevel) {
            $args[] = $this->menuIcon;
            call_user_func_array('add_menu_page', $args);
        } else {
            array_unshift($args, $this->parentSlug);
            call_user_func_array('add_submenu_page', $args);
        }

        return $this;
    }

    /**
     * A sample page callback. You should be setting your own callback via `contentCallback()`.
     *
     * https://codex.wordpress.org/Creating_Options_Pages#Opening_the_Page
     */
    public function sampleCallback()
    {
        if ( ! current_user_can($this->capability)) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        ob_start(); ?>

        <div class="wrap">
            <h1><?=$this->pageTitle?></h1>
            <p>Success! But you have not specified a callback to populate this page with content.</p>
        </div>

        <?php echo ob_get_clean();
    }

    /**
     * Make the page top level.
     *
     * @param bool $bool
     *
     * @return $this
	 * @throws \InvalidArgumentException
     */
    public function topLevel($bool = true)
    {
    	Assert::boolean($bool);
        $this->topLevel = $bool;

        return $this;
    }
}
