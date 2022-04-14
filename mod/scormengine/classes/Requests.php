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
class Requests {
    public function get_json($url, $headers, $hasbasicauth) {
        $ch = $this->intialize_request($url, $headers, $hasbasicauth);
        return $this->exec_request($ch);
    }

    public function delete($url, $headers, $hasbasicauth) {
        $ch = $this->intialize_request($url, $headers, $hasbasicauth);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        return $this->exec_request($ch);
    }

    public function post_json($url, $headers, $hasbasicauth, $body) {
        array_push($headers, 'Content-Type: application/json');
        $ch = $this->intialize_request($url, $headers, $hasbasicauth);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        return $this->exec_request($ch);
    }

    public function put_json($url, $headers, $hasbasicauth, $body) {
        array_push($headers, 'Content-Type: application/json');
        $ch = $this->intialize_request($url, $headers, $hasbasicauth);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        return $this->exec_request($ch);
    }

    protected function intialize_request($url, $headers, $hasbasicauth) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if ($hasbasicauth) {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        }
        return $ch;
    }

    protected function exec_request($ch) {
        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        return $info['http_code'] === 200 ? json_decode($result, true) : null;
    }
}

