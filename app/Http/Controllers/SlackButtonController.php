<?php

namespace Slackbot001\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Slackbot001\SessionManager;

class SlackButtonController extends Controller
{
    public function processBtn(Request $request)
    {
        Log::info('CP_SlackButtonController: processBtn method called.');
        Log::info('CP_SlackButtonController: Request type is '.gettype($request).'.');
        $data = json_decode($request['payload']);
        if ($data) {
            Log::info('CP_SlackButtonController: Controller was given data.');
        } else {
            Log::info('CP_SlackButtonController: Controller was called, but no data given.');
        }
        if ($data->token == env('SLACK_SLASH_COMMAND_VERIFICATION_TOKEN')) {
            Log::info('CP_SlackButtonController: Token verified. Proceeding.');
            $this->buttonResponse($data);
        } else {
            Log::info('CP_SlackButtonController: Token did not pass verification.');
        }
    }

    private function buttonResponse($data)
    {
        Log::info('CP_SlackButtonController: buttonResponse method called.');
        $ch = curl_init($data->response_url);
        $payload = '{"text":"One moment please…"}';
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        $userSession = new SessionManager();
        $TDinstance = $userSession->setupSession($data->user->id, $data->token);
        $userSession()->increment('s_showtickets');

        Log::info('CP_SlackButtonController: Creating instance of SlashCommand Request and CP_ShowTDTicket.');

        $slackRequest = new \Spatie\SlashCommand\Request();
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
