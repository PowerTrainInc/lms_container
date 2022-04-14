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
class RequestParams {
    private $requestfunction;
    private $rootpath;

    public function __construct($rootpath, $requestfunction) {
        $this->requestfunction = $requestfunction;
        $this->rootpath = $rootpath;
    }

    public function with_path_params($path) {
        return $this->req_func()("{$this->rootpath}/{$path}");
    }

    public function with_query_params($query) {
        return $this->req_func()("{$this->rootpath}?".http_build_query($query, '', '&'));
    }

    public function with_path_and_query_params($path, $query) {
        return $this->req_func()("{$this->rootpath}/{$path}?"
            .http_build_query($query, '', '&'));
    }

    public function with_no_params() {
        return $this->req_func()($this->rootpath);
    }

    protected function req_func() {
        return $this->requestfunction;
    }
}