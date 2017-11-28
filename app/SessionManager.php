<?php

namespace Slackbot001;

use Log;

use Slackbot001\Classes\CP_TDinstance;


class SessionManager extends TDsession
{
  public function setupSession($slackToken, $slackUID)
  {
    if (env('TD_SANDBOX') == 'TRUE') {
        $env = 'sandbox';
    } else {
        $env = 'prod';
    }

    if ($this != null) {
        Log::info('CP_SessionManager: Found user session.');
        if ($this->td_token) {
            $TDinstance = new CP_TDinstance(env('TD_BEID'), env('TD_WEBSERVICESKEY'), env('TD_URLROOT'), env('TD_APPID'), $env, (string) $this->td_token);
            Log::info('CP_SessionManager: CP_TDinstance initialized with existing JWT.');
        } else {
            $TDinstance = new CP_TDinstance(env('TD_BEID'), env('TD_WEBSERVICESKEY'), env('TD_URLROOT'), env('TD_APPID'), $env);
            Log::info('CP_SessionManager: CP_TDinstance initialized with new JWT.');
        }

        Log::info('CP_SessionManager: Updating existing CP_TDsession s_token and td_token.');
        $this->s_token = $slackToken;
        $this->td_token = $TDinstance;
        $this->save();
    } else {
        Log::info('CP_SessionManager: No user session.');
        Log::info('CP_SessionManager: Creating new user session.');
        $TDinstance = new CP_TDinstance(env('TD_BEID'), env('TD_WEBSERVICESKEY'), env('TD_URLROOT'), env('TD_APPID'), $env);
        Log::info('CP_SessionManager: New CP_TDinstance initialized.');
        $this = new TDsession();
        Log::info('CP_SessionManager: New CP_TDsession initialized.');
        Log::info('CP_SessionManager: Updating CP_TDsession s_token and td_token.');
        $this->s_user_id = $slackUID;
        $this->s_token = $slackToken;
        $this->td_token = $TDinstance;
        $this->save();
    }
    return $TDinstance;
  }
}
