<?php namespace xapi;

class Requests {
    public function get_json($url, $headers, $has_basic_auth) {
        $ch = $this->intialize_request($url, $headers, $has_basic_auth);
        return $this->exec_request($ch);
    }

    public function delete($url, $headers, $has_basic_auth) {
        $ch = $this->intialize_request($url, $headers, $has_basic_auth);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        return $this->exec_request($ch);
    }

    public function post_json($url, $headers, $has_basic_auth, $body) {
        array_push($headers, 'Content-Type: application/json');
        $ch = $this->intialize_request($url, $headers, $has_basic_auth);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        return $this->exec_request($ch);
    }

    public function put_json($url, $headers, $has_basic_auth, $body) {
        array_push($headers, 'Content-Type: application/json');
        $ch = $this->intialize_request($url, $headers, $has_basic_auth);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        return $this->exec_request($ch);
    }

    protected function intialize_request($url, $headers, $has_basic_auth) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if ($has_basic_auth) curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        return $ch;
    }

    protected function exec_request($ch) {
        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        return $info['http_code'] === 200 ? json_decode($result, true) : null;
    }
}

?>