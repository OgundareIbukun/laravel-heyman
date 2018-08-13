<?php

namespace Imanghafoori\HeyMan\Hooks;

use Imanghafoori\HeyMan\WatchingStrategies\RouterEventManager;
use Imanghafoori\HeyMan\YouShouldHave;

trait RouteHooks
{
    /**
     * @param mixed ...$url
     *
     * @return YouShouldHave
     */
    public function whenYouVisitUrl(...$url): YouShouldHave
    {
        return $this->authorizeURL($url, 'GET');
    }

    /**
     * @param mixed ...$url
     *
     * @return YouShouldHave
     */
    public function whenYouSendPost(...$url): YouShouldHave
    {
        return $this->authorizeURL($url, 'POST');
    }

    /**
     * @param mixed ...$url
     *
     * @return YouShouldHave
     */
    public function whenYouSendPatch(...$url): YouShouldHave
    {
        return $this->authorizeURL($url, 'PATCH');
    }

    /**
     * @param mixed ...$url
     *
     * @return YouShouldHave
     */
    public function whenYouSendPut(...$url): YouShouldHave
    {
        return $this->authorizeURL($url, 'PUT');
    }

    /**
     * @param mixed ...$url
     *
     * @return YouShouldHave
     */
    public function whenYouSendDelete(...$url): YouShouldHave
    {
        return $this->authorizeURL($url, 'DELETE');
    }

    /**
     * @param mixed ...$routeName
     *
     * @return YouShouldHave
     */
    public function whenYouReachRoute(...$routeName): YouShouldHave
    {
        return $this->authorizeRoute($this->normalizeInput($routeName));
    }

    /**
     * @param mixed ...$action
     *
     * @return YouShouldHave
     */
    public function whenYouCallAction(...$action): YouShouldHave
    {
        $action = $this->normalizeAction($action);

        return $this->authorizeRoute($action);
    }

    /**
     * @param $value
     *
     * @return YouShouldHave
     */
    private function authorizeRoute($value): YouShouldHave
    {
        $this->chain->eventManager = app(RouterEventManager::class)->init($value);

        return app(YouShouldHave::class);
    }

    /**
     * @param $url
     * @param $verb
     *
     * @return YouShouldHave
     */
    private function authorizeURL($url, $verb): YouShouldHave
    {
        return $this->authorizeRoute($this->normalizeUrl($url, $verb));
    }

    /**
     * @param $url
     * @param $verb
     *
     * @return array
     */
    private function normalizeUrl($url, $verb): array
    {
        $removeSlash = function ($url) use ($verb) {
            return $verb.ltrim($url, '/');
        };

        return array_map($removeSlash, $this->normalizeInput($url));
    }

    /**
     * @param $action
     *
     * @return array
     */
    private function normalizeAction($action): array
    {
        $addNamespace = function ($action) {
            if ($action = ltrim($action, '\\')) {
                return $action;
            }

            return app()->getNamespace().'\\Http\\Controllers\\'.$action;
        };

        return array_map($addNamespace, $this->normalizeInput($action));
    }
}
