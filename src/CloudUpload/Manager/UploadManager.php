<?php

namespace CloudUpload\Manager;


use OpenCloud\ObjectStore\Resource\Container;
use OpenCloud\ObjectStore\Service;

class UploadManager{

    /**
     * @var Container
     */
    private $container;

    // params start
    /**
     * @var \Guzzle\Http\Url
     */
    private $path;
    private $redirect;
    private $max_file_size;
    private $max_file_count;
    private $expires;
    // params end

    public function __construct(Container $container, array $params){
        $this->container = $container;
        /** @var \Guzzle\Http\Url $url */
        $url = $this->container->getUrl();
        $this->path = $url->getPath();

        // params
        $this->redirect = $this->param($params, "redirect", "");
        $this->max_file_size = $this->param($params, "max_file_size");
        $this->max_file_count = $this->param($params, "max_file_count");
        $this->expires = $this->param($params, "expires");
        //
    }

    private function param($array, $pos, $default = "0"){
        return (isset($array[$pos]) ? $array[$pos] : $default);
    }

    public function generateSignature(){
        /** @var Service $service */
        $service = $this->container->getService();
        $secret = $service->getAccount()->getTempUrlSecret();
        if($secret == null){
            throw new \Exception("Secret is null");
        }

        $urlPath = urldecode($this->path);
        $body = sprintf("%s\n%s\n%s\n%s\n%s", $urlPath, $this->redirect, $this->max_file_size, $this->max_file_count, $this->expires);
        $hash = hash_hmac('sha1', $body, $secret);

        return $hash;
    }

    public function generateForm($signature){
        $ar = array(
            "redirect" => $this->redirect,
            "max_file_size" => $this->max_file_size,
            "max_file_count" => $this->max_file_count,
            "expires" => $this->expires,
            "signature" => $signature
        );
        return $ar;
    }

    public function getActionUrl(){
        return $this->container->getUrl();
    }
}