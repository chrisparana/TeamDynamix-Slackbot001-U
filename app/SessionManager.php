<?php

namespace Slackbot001;

use Illuminate\Support\Facades\Log;

use Slackbot001\CP_TDinstance;
use Slackbot001\TDsession;

class SessionManager
{
  protected $userSession;
  protected $TDinstance;

  public function __invoke(){
    return $this->userSession;
  }

  public function setupSession($slackUID, $slackToken) {
    if (env('TD_SANDBOX') == 'TRUE') {
        $env = 'sandbox';
    } else {
        $env = 'prod';
    }

    $userSession = TDsession::where('s_user_id', $slackUID)->first();

    if ($userSession != null) {
        Log::info('CP_SessionManager: Found user session.');
        if ($userSession->td_token) {
            $TDinstance = new CP_TDinstance(env('TD_BEID'), env('TD_WEBSERVICESKEY'), env('TD_URLROOT'), env('TD_APPID'), $env, (string) $userSession->td_token);
            Log::info('CP_SessionManager: CP_TDinstance initialized with existing JWT.');
        } else {
            $TDinstance = new CP_TDinstance(env('TD_BEID'), env('TD_WEBSERVICESKEY'), env('TD_URLROOT'), env('TD_APPID'), $env);
            Log::info('CP_SessionManager: CP_TDinstance initialized with new JWT.');
        }

        Log::info('CP_SessionManager: Updating existing CP_TDsession s_token and td_token.');
        $userSession->s_token = $slackToken;
        $userSession->td_token = $TDinstance;
        $userSession->save();
        $this->userSession = $userSession;
        $this->TDinstance = $TDinstance;
    } else {
        Log::info('CP_SessionManager: No user session.');
        Log::info('CP_SessionManager: Creating new user session.');
        $TDinstance = new CP_TDinstance(env('TD_BEID'), env('TD_WEBSERVICESKEY'), env('TD_URLROOT'), env('TD_APPID'), $env);
        Log::info('CP_SessionManager: New CP_TDinstance initialized.');
        $userSession = new TDsession();
        Log::info('CP_SessionManager: New CP_TDsession initialized.');
        Log::info('CP_SessionManager: Updating CP_TDsession s_token and td_token.');
        $userSession->s_user_id = $slackUID;
        $userSession->s_token = $slackToken;
        $userSession->td_token = $TDinstance;
        $userSession->save();
        $this->userSession = $userSession;
        $this->TDinstance = $TDinstance;
    }
    return $TDinstance;
  }

  public function checkSession($slackUID)
  {
      Log::info('CP_SessionManager: checkSession method called.');
      $userSession = TDsession::where('s_user_id', $slackUID)->first();
      if ($userSession != NULL) {
        Log::info('CP_SessionManager: Session exists.');
        return true;
      } else {
        Log::info('CP_SessionManager: Session does not exist.');
        return false;
      }
  }
  public function deleteSession($slackUID)
  {
      Log::info('CP_SessionManager: deleteSession method called.');
      if($this->checkSession($slackUID)){
        $userSession = TDsession::where('s_user_id', $slackUID)->first();
        $sid = $userSession->id;
        $userSession->delete();
        Log::info('CP_SessionManager: Deleted session ' . $sid . '.');
      }
  }
}
