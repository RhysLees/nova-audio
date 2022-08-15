<?php

namespace Rhyslees\NovaAudio;

use Illuminate\Support\Facades\Storage;
use Laravel\Nova\Fields\File;

class Audio extends File
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'audio';

    public $textAlign = 'center';

    public $showOnIndex = true;

    public $expires = null;

    public function __construct($name, $attribute = null, $disk = 'public', $storageCallback = null, $expiration = null)
    {
        parent::__construct($name, $attribute, $disk, $storageCallback);

        $this->preview(function () {
            if ($this->value) {
                $storage = Storage::disk($this->disk);
                if ($this->expires) {
                    return $storage->temporaryUrl($this->attribute, $this->expires);
                } else {
                    return $storage->url($this->value);
                }
            } else {
                return null;
            }
        });
    }

    /**
     * Sets the preload property on the Audio tag to the given value
     *
     * @param  string  $value the preload value. Can be: auto|metadata|none
     * @return $this
     */
    public function preload($value = 'auto')
    {
        return $this->withMeta(['preload' => $value]);
    }

    /**
     * Sets the autoplay property on the Audio tag to the given value
     *
     * @param  bool  $value
     * @return $this
     */
    public function autoplay($value = true)
    {
        return $this->withMeta(['autoplay' => $value]);
    }

    /**
     * Sets the noPlaybackRate property on the Audio tag to the given value
     *
     * @return $this
     */
    public function noPlaybackRate()
    {
        return $this->withMeta(['noPlaybackRate' => true]);
    }
}
