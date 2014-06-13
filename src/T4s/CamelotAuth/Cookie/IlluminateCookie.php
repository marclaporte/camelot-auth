<?php namespace T4s\CamelotAuth\Cookie;

use Illuminate\Container\Container;
use Illuminate\Cookie\CookieJar;
use Symfony\Component\HttpFoundation\Cookie;
use Illuminate\Http\Request;

class IlluminateCookie implements CookieInterface
{
    protected $key = "camelot-auth";

    protected $cookieJar;

    protected $cookie;

    protected $request;

    protected $version;

    public function __construct(Request $request,CookieJar $cookieJar,$key = "camelot-auth")
    {
        $this->cookieJar = $cookieJar;
        $this->key = $key;
        $this->request = $request;
        $app = app();
        $this->version = $app::VERSION;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function put($value,$minutes,$key= null)
    {
        if(is_null($key))
        {
            $key = $this->getKey();
        }
        $this->cookie = $this->cookieJar->make($key,$value,$minutes);
        $this->cookieJar->queue($this->cookie);
    }

    public function forever($value)
    {
        $this->cookie = $this->cookieJar->forever($this->getKey(),$value);
        $this->cookieJar->queue($this->cookie);
    }

    public function get($key= null)
    {
        if(is_null($key))
        {
            $key = $this->getKey();
        }

        $queuedCookies = $this->cookieJar->getQueuedCookies();
        if(isset($queuedCookies[$key]))
        {
            return $queuedCookies[$key];
        }

        if($this->version < 4.1)
        {
            return $this->cookieJar->get($key);
        }

        return $this->request->cookie($key);
    }

    public function forget($key= null)
    {
        if(is_null($key))
        {
            $key = $this->getKey();
        }
        $this->cookie = $this->cookieJar->forget($key);
        $this->cookieJar->queue($this->cookie);
    }

    public function getCookie()
    {
        return $this->cookie;
    }
}
