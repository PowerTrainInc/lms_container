<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace xapi;
require_once('RequestParams.php');

class Resource {
    private $resourcename;
    private $requests;
    private $hasbasicauth;
    private $path;
    private $headers;

    public function __construct($rooturl, $resourcename, $requests, $headers, $hasbasicauth) {
        $this->resource_name = $resourcename;
        $this->requests = $requests;
        $this->has_basic_auth = $hasbasicauth;
        $this->path = "{$rooturl}/{$resourcename}";
        $this->headers = $headers;
    }

    public function get_resource_name() {
        return $this->resource_name;
    }

    public function get_resource() {
        return new RequestParams($this->path, function ($resourcepath) {
            return $this->requests->get_json($resourcepath, $this->headers, $this->has_basic_auth);
        });
    }

    public function delete_resource() {
        return new RequestParams($this->path, function ($resourcepath) {
            return $this->requests->delete($resourcepath, $this->headers, $this->has_basic_auth);
        });
    }

    public function create_resource($body) {
        return new RequestParams($this->path, function ($resourcepath) use ($body) {
            return $this->requests->post_json($resourcepath, $this->headers, $this->has_basic_auth, $body);
        });
    }

    public function update_resource($body) {
        return new RequestParams($this->path, function ($resourcepath) use ($body) {
            return $this->requests->put_json($resourcepath, $this->headers, $this->has_basic_auth, $body);
        });
    }
}

