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
require_once('Resource.php');

class LrsApi {
    private $endpoint;
    private $activitystateresource;
    private $statementresource;
    private $baseheaders;

    public function __construct($requests, $endpoint, $username, $password) {
        $this->endpoint = $endpoint;
        $this->base_headers = [
            'X-Experience-API-Version: 1.0.1',
            'Authorization: Basic '. base64_encode("{$username}:{$password}"),
        ];
        $this->activity_state_resource = new Resource(
            $this->endpoint, 'activities/state', $requests, $this->base_headers, true);
        $this->statement_resource = new Resource(
            $this->endpoint, 'statements', $requests, $this->base_headers, true);
    }

    public function activity_states() {
        return $this->activity_state_resource;
    }

    public function statements() {
        return $this->statement_resource;
    }
}

