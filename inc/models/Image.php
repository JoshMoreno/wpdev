<?php

namespace WPDev\Models;

class Image
{
    /**
     * The attributes to output in the `img` tag. (i.e. `data-test => 'something'`)
     *
     * @var array
     */
    public $attributes = [];
    /**
     * Output the image with the caption if it exists.
     *
     * Will be in a `figure` if the caption exists
     *
     * @var bool
     */
    public $withCaption = false;
    protected $caption;
    protected $id;
    protected $metadata = [];
    protected $size;

    /**
     * Constructor. For a more fluid syntax use `Image::create()`.
     *
     * @param $id The image id
     * @param string $size The image size
     */
    public function __construct($id, string $size = 'full')
    {
        $this->id   = (int)$id;
        $this->size = $size;
        $this->getAndSetDefaultData();
    }

    /**
     * Make it easy to echo it at any time.
     *
     * @return string
     */
    public function __toString()
    {
        if ( empty($this->attributes['src'])) {
            return '';
        }

        if (!$this->withCaption || !$this->caption()) {
            return "<img {$this->buildAttributesString()}>";
        }

        ob_start(); ?>
        <figure>
            <img <?= $this->buildAttributesString(); ?>>
            <figcaption><?= $this->caption(); ?></figcaption>
        </figure>
        <?php return ob_get_clean();
    }

    /**
     * Get the alt text.
     *
     * @return null|string
     */
    public function alt()
    {
        return $this->getAttribute('alt');
    }

    /**
     * Get the caption.
     *
     * @return false|string
     */
    public function caption()
    {
        if (is_null($this->caption)) {
            $this->caption = wp_get_attachment_caption($this->id);
        }

        return $this->caption;
    }

    /**
     * For a more fluid syntax.
     *
     * @param int $id The image id
     * @param string $size The image size
     *
     * @return $this
     */
    public static function create($id, $size = 'full')
    {
        return new static($id, $size);
    }

    /**
     * Gets an attribute.
     *
     * @param string $attribute The attribute to get
     *
     * @return string|null
     */
    public function getAttribute(string $attribute)
    {
        if (isset($this->attributes[$attribute])) {
            return $this->attributes[$attribute];
        }

        return null;
    }

    /**
     * Get the height of the image in the current size.
     *
     * @return null|string
     */
    public function height()
    {
        return $this->getAttribute('height');
    }

    /**
     * Gets the metadata.
     *
     * @return array|mixed
     */
    public function metadata()
    {
        if (is_null($this->metadata)) {
            $this->metadata = wp_get_attachment_metadata($this->id);
        }

        return $this->metadata;
    }

    /**
     * Remove an attribute.
     *
     * @param string $name The attribute name
     *
     * @return $this
     */
    public function removeAttribute(string $name)
    {
        unset($this->attributes[$name]);

        return $this;
    }

    /**
     * Add an attribute.
     *
     * @param string $name The attribute name
     * @param null|string $value The value. Null to just output the name.
     *
     * @return $this
     */
    public function setAttribute(string $name, string $value = null)
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * Gets the title field. This is not the filename.
     *
     * @return null|string
     */
    public function title()
    {
        return $this->getAttribute('title');
    }

    /**
     * Gets the url of the image in the current size.
     *
     * @return null|string
     */
    public function url()
    {
        return $this->getAttribute('src');
    }

    /**
     * Gets the width of the image in the current size.
     *
     * @return null|string
     */
    public function width()
    {
        return $this->getAttribute('width');
    }

    /**
     * Sets the flag to output with the caption.
     *
     * @param bool $bool Whether to output with the caption or not.
     *
     * @return $this
     */
    public function withCaption(bool $bool = true)
    {
        $this->withCaption = $bool;

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | Protected - Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Builds the `name="value"` attribute string.
     *
     * @return string
     */
    protected function buildAttributesString()
    {
        // concatenate key and value to make joining easier
        $attribute_strings = [];

        foreach ($this->attributes as $key => $value) {
            if (is_null($value)) {
                $attribute_strings[] = $key;
            } else {
                $attribute_strings[] = "$key=\"$value\"";
            }
        }

        return join(' ', $attribute_strings);
    }

    /**
     * Gets the image data and sets up the attributes.
     */
    protected function getAndSetDefaultData()
    {
        $image_data = wp_get_attachment_image_src($this->id, $this->size);
        if ( ! $image_data) {
            return;
        }

        $this->attributes = [
            'src'    => $image_data[0],
            'width'  => $image_data[1],
            'height' => $image_data[2],
        ];

        if ($alt = get_post_meta($this->id, '_wp_attachment_image_alt', true)) {
            $this->attributes['alt'] = $alt;
        }

        if ($srcset = wp_get_attachment_image_srcset($this->id, $this->size)) {
            $this->attributes['srcset'] = $srcset;
            $this->attributes['sizes']  = "(max-width: $image_data[1]px) 100vw, $image_data[1]px";
        }

        if ($title = get_the_title($this->id)) {
            $this->attributes['title'] = $title;
        }
    }
}