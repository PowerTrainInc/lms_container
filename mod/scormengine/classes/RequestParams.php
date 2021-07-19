<?php namespace xapi;

class RequestParams {
    private $request_function;
    private $root_path;

    public function __construct($root_path, $request_function) {
        $this->request_function = $request_function;
        $this->root_path = $root_path;
    }

    public function with_path_params($path) {
        return $this->req_func()("{$this->root_path}/{$path}");
    }

    public function with_query_params($query) {;
        return $this->req_func()("{$this->root_path}?".http_build_query($query, '', '&'));
    }

    public function with_path_and_query_params($path, $query) {
        return $this->req_func()("{$this->root_path}/{$path}?"
            .http_build_query($query, '', '&'));
    }

    public function with_no_params() {
        return $this->req_func()($this->root_path);
    }

    protected function req_func() {
        return $this->request_function;
    }
}

?>
