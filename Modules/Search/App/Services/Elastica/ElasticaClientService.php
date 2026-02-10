<?php

namespace Modules\Search\App\Services\Elastica;

use Elastica\Client;

/**
 * Provides the Elastica client instance from config.
 *
 * @see https://elastica.io/getting-started/installation.html
 */
class ElasticaClientService
{
    protected ?Client $client = null;

    public function getClient(): Client
    {
        if ($this->client === null) {
            $this->client = new Client(config('elastica', []));
        }

        return $this->client;
    }
}
