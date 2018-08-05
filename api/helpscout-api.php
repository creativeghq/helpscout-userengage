<?php
class CUSTOM_HELPSCOUT_API
{
    public function __construct($api_key = '')
    {
        $this->api_key = $api_key;
    }
    public function getMailBoxes()
    {
        return $this->request('https://api.helpscout.net/v2/mailboxes');
    }
    public function getAllConversations($mailboxid, $page = 1)
    {
        return $this->request('https://api.helpscout.net/v2/conversations?mailbox=' . $mailboxid . '&status=active,open,closed,pending', "GET", $page);
    }
    public function getAllThreads($conversationid)
    {
        return $this->request('https://api.helpscout.net/v2/conversations/' . $conversationid . '/threads');
    }
    public function createCustomer($fields)
    {
        return $this->request('https://api.helpscout.net/v2/customers', 'POST',  $fields);
    }
    public function getCustomer($customerEmail)
    {
        return $this->request('https://api.helpscout.net/v2/customers?query(email:"'.$customerEmail.'")');
    }

    private function sendRequest($url, $method = 'POST', $fields = null)
    {
        $args = array(
            'method'  => $method,
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-type'  => 'application/json; charset=UTF-8',
            ),
        );
        $args['body']    = json_encode($fields);
        $args['timeout'] = 40;
        $response        = wp_remote_request($url, $args);
        if ($response['response']['code'] == '401') {
            return false; // Bail early
        }

        $data = json_decode($response['body'], true);
        return $data;
    }

    private function request($url, $method = 'GET', $page = 1, $fields = null)
    {
        $args = array(
            'method'  => $method,
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-type'  => 'application/json; charset=UTF-8',
            ),
            'body'    => array(
                'page' => $page,
            ),
        );
        if ($fields) {
            foreach ($fields as $key => $field) {
                $args['body'][$key] = $field;
            }
        }

        $args['timeout'] = 40;
        $response        = wp_remote_request($url, $args);
        if ($response['response']['code'] == '401') {
            return false; // Bail early
        }

        $data = json_decode($response['body'], true);
        return $data;
    }

    public function getAccessToken($url, $clientId, $clientSecret)
    {
        $args = array(
            'method' => 'POST',
            'body'   => array(
                'grant_type'    => 'client_credentials',
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
            ),
        );
        $response = wp_remote_post($url, $args);
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            return false;

        } else {
            return json_decode($response['body'], true);

        }
    }
}
