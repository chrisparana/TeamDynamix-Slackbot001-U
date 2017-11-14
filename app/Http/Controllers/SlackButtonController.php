<?php
namespace Slackbot001\Http\Controllers;
use Illuminate\Http\Request;
use Spatie\SlashCommand\Jobs\SlashCommandResponseJob;
use Slackbot001\TDsession;
use Log;

class SlackButtonController extends Controller
{
    public function processBtn(Request $request) {
      Log::info('CP_SlackButtonController: processBtn method called.');
      Log::info('CP_SlackButtonController: Request type is ' . gettype($request) . '.');
      $data = json_decode($request['payload']);
      if($data) {
        Log::info('CP_SlackButtonController: Controller was given data.');
      }
      else {
        Log::info('CP_SlackButtonController: Controller was called, but no data given.');
      }
      if($data->token == env('SLACK_SLASH_COMMAND_VERIFICATION_TOKEN')) {
        Log::info('CP_SlackButtonController: Token verified. Proceeding.');
        $this->buttonResponse($data);
      }
      else {
        Log::info('CP_SlackButtonController: Token did not pass verification.');
      }
    }

    private function buttonResponse($data) {
      Log::info('CP_SlackButtonController: buttonResponse method called.');
      $ch = curl_init( $data->response_url );
      $payload = '{"text":"One moment please…"}';
      curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
      curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
      curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
      $result = curl_exec($ch);
      curl_close($ch);

      $userSession = TDsession::where('s_user_id', $data->user->id)->first();
      if($userSession != null ) {
        Log::info('CP_SlackButtonController: Found user session.');
        $userSession->increment('s_showtickets');
      }
      else {
        Log::info('CP_SlackButtonController: No user session.');
        Log::info('CP_SlackButtonController: Creating new user session.');
        $userSession = new TDsession;
        Log::info('CP_SlackButtonController: New CP_TDsession initialized.');
        Log::info('CP_SlackButtonController: Updating CP_TDsession s_user_id and s_token.');
        $userSession->s_user_id = $data->user->id;
        $userSession->s_token =  $data->token;
        $userSession->save();

        $userSession->increment('s_showtickets');
      }

      Log::info('CP_SlackButtonController: Creating instance of SlashCommand Request and CP_ShowTDTicket.');

      $slackRequest = new \Spatie\SlashCommand\Request;
          $slackRequest->token = $data->token;
          $slackRequest->teamId = $data->team->id;
          $slackRequest->teamDomain = $data->team->domain;
          $slackRequest->channelName = $data->channel->name;
          $slackRequest->userId = $data->user->id;
          $slackRequest->userName = $data->user->name;
          $slackRequest->command = 'td';
          $slackRequest->text = $data->callback_id;
          $slackRequest->responseUrl = stripslashes($data->response_url);

      $slackResponder = new \Slackbot001\SlashCommandHandlers\ShowTDTicket($slackRequest);
      $slackResponder->handle($slackRequest);
    }
}
