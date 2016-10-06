<?php

namespace Component\Phacility\Conduit;

use ConduitClient;

class Helper
{
    protected $url;
    protected $apiToken;

    public function __construct($url, $apiToken)
    {
        $this->url = $url;
        $this->apiToken = $apiToken;
    }

    public function callMethod($method, array $parameters = [])
    {
        $client = new ConduitClient($this->url);
        $client->setConduitToken($this->apiToken);

        return $client->callMethodSynchronous($method, $parameters);
    }

    /**
     * @deprecated
     */
    public function queryUser(array $parameters = [])
    {
        return $this->callMethod('user.query', $parameters);
    }

    /**
     * @deprecated
     */
    public function queryManiphest(array $parameters = [])
    {
        return $this->callMethod('maniphest.query', $parameters);
    }

    public function searchUser(array $parameters = [])
    {
        return $this->callMethod('user.search', $parameters);
    }

    public function searchManiphest(array $parameters = [])
    {
        return $this->callMethod('maniphest.search', $parameters);
    }

    public function searchProjects(array $parameters = [])
    {
        return $this->callMethod('project.search', $parameters);
    }

    public function updateManiphest(array $parameters)
    {
        return $this->callMethod('maniphest.update', $parameters);
    }
}
