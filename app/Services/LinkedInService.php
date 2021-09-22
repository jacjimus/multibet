<?php

namespace App\Services;

class LinkedInService
{
    private $_client_id;

    private $_client_secret;

    private $_grant_type;

    private $_request_service;

    public function __construct()
    {
        $this->_client_id = env('LINKEDIN_CLIENT_ID');
        $this->_client_secret = env('LINKEDIN_CLIENT_SECRET');
        $this->_grant_type = 'client_credentials';
    }

    public function getRequestService()
    {
        if (!($service = $this->_request_service)) {
            $service = app()->make('RequestService');
            $this->_request_service = $service;
        }

        return $service;
    }

    public function auth()
    {
        $client_id = $this->_client_id;
        $redirect_uri = ''; //url?code=Auth Code
        $state = '';
        $scope = 'r_liteprofile r_emailaddress w_member_social';

        $url = 'https://www.linkedin.com/oauth/v2/authorization';
        $url .= '?response_type=code';
        $url .= '&client_id=' . $client_id;
        $url .= '&redirect_uri=' . urlencode($redirect_uri);
        $url .= '&state=' . $state;
        $url .= '&scope=' . urlencode($scope);
    }

    public function xauth()
    {
        $url = 'https://www.linkedin.com/oauth/v2/accessToken';
        $data = [
            'client_id' => $this->_client_id,
            'client_secret' => $this->_client_secret,
            'grant_type' => $this->_grant_type,
        ];
        $response = $this->getRequestService()->request(
            $url,
            $data,
            $headers=[],
            $is_post=true,
            $cached=true,
            $save_body=true,
            $trim_body=true,
        );
        dd($response);
    }
}
