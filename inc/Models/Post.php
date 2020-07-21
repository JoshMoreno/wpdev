<?php

namespace WPDev\Models;

use WP_Error;

class Post
{
    protected $ancestors;
    protected $content;
    protected $createdDate;
    protected $excerpt;
    protected $featuredImageId;
    protected $globalPostStash;
    protected $modifiedDate;
    protected $id = 0;
    protected $parent;
    protected $parentId;
    protected $status;
    protected $taxonomies;
    protected $terms = [];
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
	 * Delegates to the respective public method.
	 *
	 * @return mixed|null
	 */
	public function __get(string $key)
	{
		try {
			$method = new \ReflectionMethod($this, $key);
			if ($method->isPublic()) {
				return $this->$key();
			}
		} catch (\ReflectionException $e) {
			return null;
		}
    }

    public static function create($post = null): self
    {
        return new static($post);
    }

    /**
     * Uses ACF's `get_field()` to fetch a field value.
     *
     * @param string $fieldName The field key
     * @param bool $format Should ACF format the value for you
     *
     * @return mixed|null
     */
    public function acfField(string $fieldName, bool $format = true)
    {
        if (!$this->isAcfActive()) {
            return $this->field($fieldName, $format);
        }

        return get_field($fieldName, $this->id, $format);
    }

    public function acfFields(bool $format = true): array
    {
        if ($this->isAcfActive()) {
            return get_fields($this->id, $format);
        }

        return [];
    }

    /**
     * Retrieves the parent posts in direct parent to highest level ancestor order. Each post will be an instance of `\WPDev\Models\Post`.
     *
     * The direct parent is returned as the first value in the array.
     * The highest level ancestor is returned as the last value in the array.
     *
     * @return \WPDev\Models\Post[]
     */
    public function ancestors(): array
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
     * The date the post was created.
     *
     * @param string $date_format A date format string. Defaults to `get_option('date_format')` date format set in the WP backend.
     *
     * @return false|string The formatted date. False on failure.
     */
    public function createdDate(string $date_format = '')
    {
        if (is_null($this->createdDate)) {
            $this->createdDate = get_the_date($date_format, $this->postElseId());
        }

        return $this->createdDate;
    }

    public function featuredImage(string $size = 'full'): Image
    {
        return Image::create($this->featuredImageId(), $size);
    }

    public function featuredImageId(): int
    {
        if (is_null($this->featuredImageId)) {
            $this->featuredImageId = (int) $this->field('_thumbnail_id');
        }

        return $this->featuredImageId;
    }

    /**
     * Gets a field value using `get_post_meta()`.
     *
     * @return mixed The field value if it exists. Else an empty string if $single_value = true or an empty array if $single_value = false.
     */
    public function field(string $fieldName, bool $single_value = true)
    {
        return get_post_meta($this->id, $fieldName, $single_value);
    }

    public function hasFeaturedImage(): bool
    {
        return (bool) $this->featuredImageId();
    }

	/**
	 * Checks whether the post has the term. If no arg passed checks if it has any term.
	 *
	 * @param string|int|array $term The term name/term_id/slug or array of them to check for.
	 * @param string $taxonomy_name Taxonomy name
	 * @return bool True if the current post has any of the given tags (or any tag, if no tag specified).
	 */
	public function hasTerm($term = '', string $taxonomy_name = ''): bool
    {
		return has_term($term, $taxonomy_name, $this->postElseId());
    }

    public function id(): int
    {
        return $this->id;
    }

    public function title(): string
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

    /**
     * Same as `the_content()` except we don't echo.
     *
     * WP has a `get_the_content()` that doesn't apply filters or convert
     * shortcodes. Inconsistent. Doesn't accept an `id` or `WP_Post` object either. Lame.
     * Rather than duplicating core code here we just capture the output.
     */
    public function content(?string $more_link_text = null, bool $strip_teaser = false): string
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

	public function excerpt(): string
    {
		if (is_null($this->excerpt)) {
			$this->setupWpGlobals();

			ob_start();
			the_excerpt();
			$this->excerpt = ob_get_clean();

			$this->restoreWpGlobals();
		}

		return $this->excerpt;
	}

    /**
     * @return false|string
     */
    public function modifiedDate(string $date_format = '')
    {
        if (is_null($this->modifiedDate)) {
            $this->modifiedDate = get_the_modified_date($date_format, $this->postElseId());
        }

        return $this->modifiedDate;
    }

    /**
     * Gets the parent post if any
     *
     * @return bool|\WPDev\Models\Post
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
     * The parent ID if there is a parent. Else 0.
     */
    public function parentId(): int
    {
        if (is_null($this->parentId)) {
            $this->parentId = ($this->hasWpPost()) ? $this->wpPost->post_parent : 0;
        }

        return (int)$this->parentId;
    }

    /**
     * @return string
     */
    public function postType(): string
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
     * The taxonomy names associated with the post.
     *
     * @return array Taxonomy names
     */
    public function taxonomies(): array
    {
        if (is_null($this->taxonomies)) {
            $this->taxonomies = get_post_taxonomies($this->postElseId());
        }

        return $this->taxonomies;
    }

    /**
     * Terms for all or a specific associated taxonomy.
     *
     * @return array|mixed
     */
    public function terms(?string $taxonomy_name = null)
    {
        $fetch_all = is_null($taxonomy_name);

        // return cache if we have it
        if (!$fetch_all && isset($this->terms[$taxonomy_name])) {
            return $this->terms[$taxonomy_name];
        }

        // fetch and return all the terms with recursion
        if ($fetch_all) {
            foreach ($this->taxonomies() as $taxonomy) {
                $this->terms[$taxonomy] = $this->terms($taxonomy);
            }

            return $this->terms;
        }

        // no cache so get the terms
        $terms = get_the_terms($this->postElseId(), $taxonomy_name);

        // normalize the return type
        if (!$terms || ($terms instanceof WP_Error)) {
            $terms = [];
        }

        // cache it
        $this->terms[$taxonomy_name] = $terms;

        return $this->terms[$taxonomy_name];
    }


	public function wpPost(): ?\WP_Post
    {
		if ($this->hasWpPost()) {
			return $this->wpPost;
		}

		return null;
    }

    protected function hasWpPost(): bool
    {
        return ($this->wpPost instanceof \WP_Post);
    }

    protected function isAcfActive(): bool
    {
        return class_exists('acf');
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
    protected function restoreWpGlobals(): void
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
    protected function setupWpGlobals(): void
    {
        // stash the current global so we can set it back up after we're done
        if (isset($GLOBALS['post'])) {
            $this->globalPostStash = $GLOBALS['post'];
        }

        $GLOBALS['post'] = $this->postElseId();
        setup_postdata($this->postElseId());
    }
}