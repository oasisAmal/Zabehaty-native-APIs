<?php

namespace App\Traits;

trait TraitLanguage
{
    public function getAttribute($key)
    {
        $locale = app()->getLocale();
        if (in_array($key, $this->translatable)) {
            $column = $key . '_' . $locale;
            if (isset($this->$column)) {
                return $this->$column;
            } else {
                return parent::getAttribute($key);
            }
        } else {
            return parent::getAttribute($key);
        }
    }
}
