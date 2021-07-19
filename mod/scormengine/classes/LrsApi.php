<?php namespace xapi;

require_once('Resource.php');

class LrsApi {
    private $endpoint;
    private $activity_state_resource;
    private $statement_resource;
    private $base_headers;

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

?>
