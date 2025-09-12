<?php

namespace App\Traits;

trait CountryDatabaseTrait
{
    /**
     * Boot the trait
     */
    protected static function bootCountryDatabaseTrait()
    {
        // Set the connection when the model is instantiated
        static::creating(function ($model) {
            if (request()->has('app_country_code')) {
                $model->setConnection(strtolower(request()->get('app_country_code')));
            }
        });

        static::updating(function ($model) {
            if (request()->has('app_country_code')) {
                $model->setConnection(strtolower(request()->get('app_country_code')));
            }
        });

        static::deleting(function ($model) {
            if (request()->has('app_country_code')) {
                $model->setConnection(strtolower(request()->get('app_country_code')));
            }
        });
    }

    /**
     * Set the database connection for this model instance
     *
     * @param string $countryCode
     * @return $this
     */
    public function setCountryConnection(string $countryCode): self
    {
        $this->setConnection($countryCode);
        return $this;
    }

    /**
     * Get a new query builder for the model's table with country connection
     *
     * @param string $countryCode
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function forCountry(string $countryCode)
    {
        return (new static)->setConnection($countryCode)->newQuery();
    }

    /**
     * Create a new model instance with country connection
     *
     * @param array $attributes
     * @param string $countryCode
     * @return static
     */
    public static function createForCountry(array $attributes, string $countryCode)
    {
        $model = new static($attributes);
        $model->setConnection($countryCode);
        $model->save();
        return $model;
    }

    /**
     * Find a model by its primary key with country connection
     *
     * @param mixed $id
     * @param string $countryCode
     * @return static|null
     */
    public static function findForCountry($id, string $countryCode)
    {
        return static::forCountry($countryCode)->find($id);
    }

    /**
     * Get all models with country connection
     *
     * @param string $countryCode
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function allForCountry(string $countryCode)
    {
        return static::forCountry($countryCode)->get();
    }

    /**
     * Get the current country code from request
     *
     * @return string|null
     */
    protected function getCurrentCountryCode(): ?string
    {
        return strtolower(request()->get('app_country_code'));
    }

    /**
     * Override the newQuery method to use country-specific connection
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function newQuery()
    {
        $countryCode = $this->getCurrentCountryCode();
        
        if ($countryCode) {
            $this->setConnection($countryCode);
        }

        return parent::newQuery();
    }
}
