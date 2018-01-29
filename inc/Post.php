<?php

namespace WPDev;

class Post
{
    protected $content;
    protected $createdDate;
    protected $excerpt;
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
            $this->id = $this->wpPost->ID;
        }
    }

    public function createdDate($date_format = '')
    {
        if (is_null($this->createdDate)) {
            $this->createdDate = get_the_date($date_format, $this->postElseId());
        }

        return $this->createdDate;
    }


    public function id()
    {
        return $this->id;
    }

    public function title()
    {
        if (is_null($this->title)) {
            $this->title = get_the_title($this->postElseId());
        }

        return $this->title;
    }

    public function url()
    {
        if (is_null($this->url)) {
            $this->url = get_permalink($this->postElseId());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Same as what WP does minus the arguments and echo
    |--------------------------------------------------------------------------
    | WP has a get_the_content() that doesn't apply filters or convert
    | shortcodes. So we do the same as the_content() here, minus the arguments
    | and we don't echo.
    | * Note that this can only be called reliably within The Loop. WP needs
    | $GLOBALS['post'] to be set. Lame.
    */
    public function content()
    {
        if (is_null($this->content)) {
            $content       = get_the_content();
            $content       = apply_filters('the_content', $content);
            $this->content = str_replace(']]>', ']]&gt;', $content);
        }

        return $this->content;
    }

    public function excerpt()
    {
        if (is_null($this->excerpt)) {
            $this->excerpt = get_the_excerpt($this->postElseId());
        }

        return $this->excerpt;
    }

    public function modifiedDate($date_format = '')
    {
        if (is_null($this->modifiedDate)) {
            $this->modifiedDate = get_the_modified_date($date_format, $this->postElseId());
        }

        return $this->modifiedDate;
    }

    /**
     * @return false|\WP_Post
     */
    public function parent()
    {
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
    public function postType()
    {
        if ($this->hasWpPost()) {
            return $this->wpPost->post_type;
        }

        return '';
    }

    public function status()
    {
        if (is_null($this->status)) {
            $this->status = get_post_status($this->postElseId());
        }

        return $this->status;
    }

    protected function hasWpPost()
    {
        return ($this->wpPost instanceof \WP_Post);
    }

    protected function postElseId()
    {
        if ($this->hasWpPost()) {
            return $this->wpPost;
        }

        return $this->id;
    }
}