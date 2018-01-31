<?php

namespace WPDev;

class Post
{
    protected $ancestors;
    protected $content;
    protected $createdDate;
    protected $excerpt;
    protected $globalPostStash;
    protected $modifiedDate;
    protected $id = 0;
    protected $parent;
    protected $parentId;
    protected $status;
    protected $title;
    protected $url;
    protected $wpPost;

    /**
     * @param int|WP_Post|null $post Optional. Post ID or post object. Defaults to global $post.
     */
    public function __construct($post = null)
    {
        $this->wpPost = get_post($post);

        if ($this->hasWpPost()) {
            $this->id = (int)$this->wpPost->ID;
        }
    }

    /**
     * @return \WPDev\Post[] Array of \WPDev\Post objects
     */
    public function ancestors()
    {
        if (is_null($this->ancestors)) {
            $ancestors       = get_post_ancestors($this->postElseId());
            $this->ancestors = array_map(function ($ancestor_id) {
                return new Post($ancestor_id);
            }, $ancestors);
        }

        return $this->ancestors;
    }

    /**
     * @param string $date_format
     *
     * @return false|string
     */
    public function createdDate($date_format = '')
    {
        if (is_null($this->createdDate)) {
            $this->createdDate = get_the_date($date_format, $this->postElseId());
        }

        return $this->createdDate;
    }

    /**
     * @return int
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function title()
    {
        if (is_null($this->title)) {
            $this->title = get_the_title($this->postElseId());
        }

        return $this->title;
    }

    /**
     * @return false|string
     */
    public function url()
    {
        if (is_null($this->url)) {
            $this->url = get_permalink($this->postElseId());
        }

        return $this->url;
    }

    /*
    |--------------------------------------------------------------------------
    | Same as the_content() except we don't echo.
    |--------------------------------------------------------------------------
    | WP has a get_the_content() that doesn't apply filters or convert
    | shortcodes. Inconsistent. Doesn't accept an id or WP_Post object either. Lame.
    | Rather than duplicating core code here we just capture the output.
    */
    /**
     * @param null $more_link_text
     * @param bool $strip_teaser
     *
     * @return string
     */
    public function content($more_link_text = null, $strip_teaser = false)
    {
        if (is_null($this->content)) {
            $this->setupWpGlobals();

            // capture the output of the_content()
            ob_start();
            the_content($more_link_text, $strip_teaser);
            $this->content = ob_get_clean();

            $this->restoreWpGlobals();
        }

        return $this->content;
    }

    /**
     * @return string
     */
    public function excerpt()
    {
        if (is_null($this->excerpt)) {
            $this->excerpt = get_the_excerpt($this->postElseId());
        }

        return $this->excerpt;
    }

    /**
     * @param string $date_format
     *
     * @return false|string
     */
    public function modifiedDate($date_format = '')
    {
        if (is_null($this->modifiedDate)) {
            $this->modifiedDate = get_the_modified_date($date_format, $this->postElseId());
        }

        return $this->modifiedDate;
    }

    /**
     * @return bool|\WPDev\Post
     */
    public function parent()
    {
        if (!$this->parentId()) {
            $this->parent = false;
            return $this->parent;
        }

        if (is_null($this->parent)) {
            $parent = get_post($this->parentId());

            if ($parent instanceof \WP_Post) {
                $this->parent = new Post($parent);
            } else {
                // set to false so this is_null() check won't rerun next time
                // since WP will return null on failure
                $this->parent = false;
            }
        }

        return $this->parent;
    }

    /**
     * @return int
     */
    public function parentId()
    {
        if (is_null($this->parentId)) {
            $this->parentId = ($this->hasWpPost()) ? $this->wpPost->post_parent : 0;
        }

        return (int)$this->parentId;
    }

    /**
     * @return string
     */
    public function postType()
    {
        if ($this->hasWpPost()) {
            return $this->wpPost->post_type;
        }

        return '';
    }

    /**
     * @return false|string
     */
    public function status()
    {
        if (is_null($this->status)) {
            $this->status = get_post_status($this->postElseId());
        }

        return $this->status;
    }

    /**
     * @return bool
     */
    protected function hasWpPost()
    {
        return ($this->wpPost instanceof \WP_Post);
    }

    /**
     * @return int|\WP_Post
     */
    protected function postElseId()
    {
        if ($this->hasWpPost()) {
            return $this->wpPost;
        }

        return $this->id;
    }

    /*
    |--------------------------------------------------------------------------
    | Restore globals
    |--------------------------------------------------------------------------
    | This is the cleanup of our setupWpGlobals() method.
    | An attempt to leave no trace 🤫
    */
    protected function restoreWpGlobals()
    {
        if (isset($this->globalPostStash)) {
            $GLOBALS['post'] = $this->globalPostStash;
            setup_postdata($this->globalPostStash);
            unset($this->globalPostStash);
        } else {
            unset($GLOBALS['post']);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Setup messy globals 😞
    |--------------------------------------------------------------------------
    | This is a bit of a hack so we can reliably call functions
    | like get_the_content() that depend on being called inside
    | The Loop (aka having post as a global along with other related globals).
    */
    protected function setupWpGlobals()
    {
        // stash the current global so we can set it back up after we're done
        if (isset($GLOBALS['post'])) {
            $this->globalPostStash = $GLOBALS['post'];
        }

        $GLOBALS['post'] = $this->postElseId();
        setup_postdata($this->postElseId());
    }
}