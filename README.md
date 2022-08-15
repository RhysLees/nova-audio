# Nova Audio
Nova audio is a simple audio player for Nova using HTML5 audio.

## Installing

```bash
composer require rhyslees/nova-audio
```

---

## Usage:

```php
use RhysLees\NovaAudio\Audio;

public function fields(Request $request)
{
    return [
        ...
        Audio::make('Audio')
            ->disk('public'),
        ...
    ];
}
```

Nova Audio extends Nova's built-in File Field so you can use the same options the come with Nova's File Field. In addition, Nova Audio has a few extra options:

```php
    /**
     * Preloads the audio for instant playback.
     */
    ->preload($value = 'auto')

    /**
     * Instructs the field to autoplay the audio.
     */
    ->autoplay($value = true)

    /**
     * Hide the playback speed controls for the audio field.
     */
    ->noPlaybackRate()
```

