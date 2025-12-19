<?php

namespace App\Traits;

use Illuminate\Support\Facades\App;

trait HasTranslations
{
    /**
     * Get translatable attributes.
     *
     * @return array
     */
    public function getTranslatableAttributes(): array
    {
        return $this->translatable ?? [];
    }

    /**
     * Check if an attribute is translatable.
     *
     * @param  string  $key
     * @return bool
     */
    public function isTranslatableAttribute(string $key): bool
    {
        return in_array($key, $this->getTranslatableAttributes());
    }

    /**
     * Get translation for a specific locale.
     *
     * @param  string  $key
     * @param  string|null  $locale
     * @param  bool  $fallback
     * @return mixed
     */
    public function getTranslation(string $key, ?string $locale = null, bool $fallback = true): mixed
    {
        $locale = $locale ?? App::getLocale();

        if (! $this->isTranslatableAttribute($key)) {
            return $this->getAttribute($key);
        }

        $translations = $this->getTranslations($key);

        if (isset($translations[$locale])) {
            return $translations[$locale];
        }

        if (! $fallback) {
            return null;
        }

        // Fallback to app fallback locale
        $fallbackLocale = config('app.fallback_locale', 'en');
        if (isset($translations[$fallbackLocale])) {
            return $translations[$fallbackLocale];
        }

        // Fallback to first available translation
        return !empty($translations) ? reset($translations) : null;
    }

    /**
     * Get all translations for an attribute.
     *
     * @param  string  $key
     * @return array
     */
    public function getTranslations(string $key): array
    {
        if (! $this->isTranslatableAttribute($key)) {
            return [$this->getLocale() => $this->getAttribute($key)];
        }

        $value = $this->getAttributeValue($key);

        if (is_string($value)) {
            // Try to decode JSON string
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        return is_array($value) ? $value : [];
    }

    /**
     * Set translation for a specific locale.
     *
     * @param  string  $key
     * @param  string  $locale
     * @param  mixed  $value
     * @return $this
     */
    public function setTranslation(string $key, string $locale, mixed $value): self
    {
        if (! $this->isTranslatableAttribute($key)) {
            $this->setAttribute($key, $value);

            return $this;
        }

        $translations = $this->getTranslations($key);
        $translations[$locale] = $value;

        $this->setAttribute($key, $translations);

        return $this;
    }

    /**
     * Set translations for an attribute.
     *
     * @param  string  $key
     * @param  array  $translations
     * @return $this
     */
    public function setTranslations(string $key, array $translations): self
    {
        $this->setAttribute($key, $translations);

        return $this;
    }

    /**
     * Forget a translation for a specific locale.
     *
     * @param  string  $key
     * @param  string  $locale
     * @return $this
     */
    public function forgetTranslation(string $key, string $locale): self
    {
        $translations = $this->getTranslations($key);
        unset($translations[$locale]);

        $this->setAttribute($key, $translations);

        return $this;
    }

    /**
     * Get the current locale.
     *
     * @return string
     */
    protected function getLocale(): string
    {
        return App::getLocale();
    }

    /**
     * Get attribute value for translatable attributes.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (! $this->isTranslatableAttribute($key)) {
            return parent::getAttribute($key);
        }

        return $this->getTranslation($key);
    }
}
