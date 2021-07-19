<?php namespace xapi;

require_once('RequestParams.php');

class Resource {
    private $resource_name;
    private $requests;
    private $has_basic_auth;
    private $path;
    private $headers;

    public function __construct($root_url, $resource_name, $requests, $headers, $has_basic_auth) {
        $this->resource_name = $resource_name;
        $this->requests = $requests;
        $this->has_basic_auth = $has_basic_auth;
        $this->path = "{$root_url}/{$resource_name}";
        $this->headers = $headers;
    }

    public function get_resource_name() {
        return $this->resource_name;
    }

    public function get_resource() {
        return new RequestParams($this->path, function ($resource_path) {
            return $this->requests->get_json($resource_path, $this->headers, $this->has_basic_auth);
        });
    }

    public function delete_resource() {
        return new RequestParams($this->path, function ($resource_path) {
            return $this->requests->delete($resource_path, $this->headers, $this->has_basic_auth);
        });
    }

    public function create_resource($body) {
        return new RequestParams($this->path, function ($resource_path) use ($body) {
            return $this->requests->post_json($resource_path, $this->headers, $this->has_basic_auth, $body);
        });
    }

    public function update_resource($body) {
        return new RequestParams($this->path, function ($resource_path) use ($body) {
            return $this->requests->put_json($resource_path, $this->headers, $this->has_basic_auth, $body);
        });
    }
}

?>
