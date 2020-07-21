<?php

namespace WPDev\Facades;

use Cocur\Slugify\Slugify;

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
     * For a more fluid syntax use `OptionsPage::create()`.
     *
     * @param string $page_title The title of the page. By default this will also be used as the menu title and the page slug (a slugified version of course).
     */
    public function __construct(string $page_title)
    {
        $this->pageTitle = $page_title;
        $this->menuTitle = $page_title;
        $this->menuSlug  = (new Slugify())->slugify($page_title);
        $this->callback  = [$this, 'sampleCallback'];
    }

    /**
     * Set the page content callback.
     *
     * @param callable $callback The callback in charge of generating the content for the page.
     */
    public function contentCallback(callable $callback): self
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * The capability required for this menu to be displayed to the user.
     *
     * You still need to check for the correct capability in the content callback.
     */
    public function capability(string $capability): self
    {
        $this->capability = $capability;

        return $this;
    }

    /**
     * Allows for more fluid syntax.
     *
     * @param string $page_title The title of the page. By default this will also be used as the menu title and the page slug (a slugified version of course).
     */
    public static function create(string $page_title): self
    {
        return new static($page_title);
    }

    /**
     * Set the menu icon.
     *
     * @param string $icon name of Dashicon, URL to icon, or base64 encoded svg with fill="black"
     *
     * @link https://developer.wordpress.org/resource/dashicons/
     */
    public function menuIcon(string $icon = ''): self
    {
        $this->menuIcon = $icon;

        return $this;
    }

    /**
     * Set the slug for the page.
     */
    public function menuSlug(string $menu_slug): self
    {
        $this->menuSlug = $menu_slug;

        return $this;
    }

    /**
     * Sets the menu title.
     */
    public function menuTitle(string $menu_title): self
    {
        $this->menuTitle = $menu_title;

        return $this;
    }

    /**
     * Sets the parent slug. Used to make this page a child page.
     */
    public function parentSlug(string $slug = 'options-general.php'): self
    {
        $this->parentSlug = $slug;

        return $this;
    }

    /**
     * Sets the position of the menu item.
     */
    public function position(int $position = 100): self
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Registers the page with WP. Hooks and all.
     */
    public function register(): void
    {
        add_action('admin_menu', function () {
            $this->registerManually();
        });
    }

    /**
     * Registers the page but not within the appropriate hook `admin_menu`.
     */
    public function registerManually(): self
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
            add_menu_page(...$args);
        } else {
            array_unshift($args, $this->parentSlug);
            add_submenu_page(...$args);
        }

        return $this;
    }

    /**
     * A sample page callback. You should be setting your own callback via `contentCallback()`.
     *
     * https://codex.wordpress.org/Creating_Options_Pages#Opening_the_Page
     */
    public function sampleCallback(): void
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
     */
    public function topLevel(bool $bool = true): self
    {
        $this->topLevel = $bool;

        return $this;
    }
}
