<?php

namespace Modules\Notifications\App\Services;

use App\Enums\AppName;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class FCMService
{
    private $title = '', $body = '', $data = [], $condition = '', $topic = '', $token = '', $response = '';
    private $image = '', $link = '', $channelId = '', $color = '';
    private $url;
    private $key;
    private $project_id;

    function __construct()
    {
        if (request()->app_name == AppName::HALAL_APP) {
            $this->project_id = config('integrations-credentials.fcm.project_id.halal_app');
            $this->key = config('integrations-credentials.fcm.key.halal_app');
            $this->image = config('integrations-credentials.fcm.image_url.halal_app');
            $this->color = config('integrations-credentials.fcm.color.halal_app');
        } else {
            $this->project_id = config('integrations-credentials.fcm.project_id.zabehaty_app');
            $this->key = config('integrations-credentials.fcm.key.zabehaty_app');
            $this->image = config('integrations-credentials.fcm.image_url.zabehaty_app');
            $this->color = config('integrations-credentials.fcm.color.zabehaty_app');
        }
        $this->url = "https://fcm.googleapis.com/v1/projects/" . $this->project_id . "/messages:send";
    }

    function send()
    {
        $data = [];
        $this->validateSend();
        $data['notification']['title'] = $this->title;
        $data['notification']['body'] = $this->body;
        $data['notification']['image'] = $this->image;
        $data['notification']['color'] = $this->color;
        if ($this->channelId != '')
            $data['notification']['android_channel_id'] = $this->channelId;

        if (!empty($this->data))
            $data['data'] = $this->data;
        if ($this->condition != '')
            $data['condition'] = $this->condition;
        elseif ($this->topic != '')
            $data['to'] = '/topics/' . $this->topic;
        elseif (is_array($this->token))
            $data['token'] = $this->token[0];
        else
            $data['token'] = $this->token;
        if ($this->link != '')
            $data['data']['click_action'] = $this->link;

        // if silant message add content_available => true for ios
        //$data['content_available'] = true ;
        $accessToken = $this->getAccessToken();

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ])->post(
            $this->url,
            [
                'message' => $data
            ]
        );

        Log::error($response);

        $this->response = $response->json();

        if (isset($response->success) || isset($response->message_id)) {
            return true;
        } else {
            return false;
        }
    }

    private function getAccessToken()
    {
        $client = new GoogleClient();
        if (request()->app_name == AppName::HALAL_APP) {
            $client->setAuthConfig(config('integrations-credentials.fcm.service_accounts.halal_app'));
        } else {
            $client->setAuthConfig(config('integrations-credentials.fcm.service_accounts.zabehaty_app'));
        }
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

        $token = $client->fetchAccessTokenWithAssertion();
        return $token['access_token'];
    }

    function sendToAll()
    {
        //$this->condition =  "!('none_topic' in topics)";
        $this->topic =  "all";
        return $this->send();
    }

    function subscribe()
    {
        if (is_array($this->token))
            $this->subscribeBatch();
        else
            $this->subscribeOne();
    }

    function removeSubscription()
    {
        $url = 'https://iid.googleapis.com/iid/v1:batchRemove';

        $data['to'] = '/topics/' . $this->topic;
        if (!is_array($this->token))
            $token = [$this->token];
        else
            $token = $this->token;
        $data['registration_tokens'] = $token;
        $response = Http::withHeaders([
            'Authorization' => 'key=' . $this->key,
            'Content-Type'  => 'application/json',
        ])
            ->post($url, $data);
    }

    function info()
    {
        $token = (is_array($this->token)) ? $this->token[0] : $this->token;
        $url = 'https://iid.googleapis.com/iid/v1/info/' . $token . '?details=true';

        $response = Http::withHeaders([
            'Authorization' => 'key=' . $this->key,
            'Content-Type'  => 'application/json',
        ])
            ->post($url, []);
        return $response->json();
    }

    function response()
    {
        return $this->response;
    }
    function title($value)
    {
        $this->title = $value;
    }
    function body($value)
    {
        $this->body = $value;
    }
    function data($value)
    {
        $this->data = $value;
    }
    function topic($value)
    {
        $this->topic = $value;
    }
    function condition($value)
    {
        $this->condition = $value;
    }
    function token($value)
    {
        $this->token = $value;
    }
    function image($value)
    {
        $this->image = $value;
    }
    function link($link)
    {
        $this->link = $link;
    }
    function channelId($value)
    {
        $this->channelId = $value;
    }


    private function validateSend()
    {
        $error = '';
        if ($this->title == '')
            $error = 'Title is Required';

        if ($this->token == '' && $this->topic == '' && $this->condition == '')
            $error = 'Token Or Topic Or Condition is Required';
        if ($error != '') {
            throw new \Exception($error, 1);
        }
    }

    private function subscribeOne()
    {
        $url = 'https://iid.googleapis.com/iid/v1/' . $this->token . '/rel/topics/' . $this->topic;

        $response = Http::withHeaders([
            'Authorization' => 'key=' . $this->key,
            'Content-Type'  => 'application/json',
        ])->post($url);

        // Store the response
        $this->response = $response->json();
    }

    private function subscribeBatch()
    {
        $url = 'https://iid.googleapis.com/iid/v1:batchAdd';

        $data = [
            'to' => '/topics/' . $this->topic,
            'registration_tokens' => $this->token, // Assuming $this->token is an array of tokens
        ];

        $response = Http::withHeaders([
            'Authorization' => 'key=' . $this->key,
            'Content-Type'  => 'application/json',
        ])->post($url, $data);

        // Store the response
        $this->response = $response->json();
    }
}
