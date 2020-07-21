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

    public function __construct(int $id, string $size = 'full')
    {
        $this->id   = (int)$id;
        $this->size = $size;
        $this->getAndSetDefaultData();
    }

    public function __toString(): string
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

    public function alt(): ?string
    {
        return $this->getAttribute('alt');
    }

    /**
     * @return false|string
     */
    public function caption()
    {
        if (is_null($this->caption)) {
            $this->caption = wp_get_attachment_caption($this->id);
        }

        return $this->caption;
    }

    public static function create(int $id, string $size = 'full'): self
    {
        return new static($id, $size);
    }

    public function getAttribute(string $attribute): ?string
    {
        if (isset($this->attributes[$attribute])) {
            return $this->attributes[$attribute];
        }

        return null;
    }

    public function height(): ?string
    {
        return $this->getAttribute('height');
    }

    /**
     * @return array|mixed
     */
    public function metadata()
    {
        if (is_null($this->metadata)) {
            $this->metadata = wp_get_attachment_metadata($this->id);
        }

        return $this->metadata;
    }

    public function removeAttribute(string $name): self
    {
        unset($this->attributes[$name]);

        return $this;
    }

    /**
     * Null will just output the attribute
     * e.g. data-example instead of data-example="something"
     */
    public function setAttribute(string $name, ?string $value = null): self
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    public function title(): ?string
    {
        return $this->getAttribute('title');
    }

    public function url(): ?string
    {
        return $this->getAttribute('src');
    }

    public function width(): ?string
    {
        return $this->getAttribute('width');
    }

    public function withCaption(bool $bool = true): self
    {
        $this->withCaption = $bool;

        return $this;
    }

    /**
     * Builds the `name="value"` attribute string.
     */
    protected function buildAttributesString(): string
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

        return implode(' ', $attribute_strings);
    }

    protected function getAndSetDefaultData(): void
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