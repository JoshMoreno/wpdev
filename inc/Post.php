<?php

namespace WPDev;

class Post
{
    protected $content;
    protected $excerpt;
    protected $id = 0;
    protected $title;
    protected $url;
    protected $wpPost;

    /**
     * @param int|WP_Post|null $post Optional. Post ID or post object. Defaults to global $post.
     */
    public function __construct($post = null)
    {
        $this->wpPost = get_post($post);

        if ($this->wpPost instanceof \WP_Post) {
            $this->id = $this->wpPost->ID;
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        if (is_null($this->title)) {
            $this->title = get_the_title($this->id);
        }

        return $this->title;
    }

    public function getUrl()
    {
        if (is_null($this->url)) {
            $this->url = get_permalink($this->id);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Same as what WP does minus the arguments and echo
    |--------------------------------------------------------------------------
    | WP has a get_the_content() that doesn't apply filters or convert
    | shortcodes. So we do the same as the_content() here, minus the arguments
    | and we don't echo.
    */
    public function getContent()
    {
        if (is_null($this->content)) {
            $content       = get_the_content();
            $content       = apply_filters('the_content', $content);
            $this->content = str_replace(']]>', ']]&gt;', $content);
        }

        return $this->content;
    }

    public function getExcerpt()
    {
        if (is_null($this->excerpt)) {
            $this->excerpt = get_the_excerpt($this->id);
        }

        return $this->excerpt;
    }
}