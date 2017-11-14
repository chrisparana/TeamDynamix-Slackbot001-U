<?php
namespace Slackbot001\SlashCommandHandlers;

use Spatie\SlashCommand\Request;
use Spatie\SlashCommand\Response;
use Spatie\SlashCommand\Handlers\BaseHandler;
use Slackbot001\SlashCommandHandlers\Jobs\ShowTDTicketJob;
use Slackbot001\SlashCommandHandlers\Jobs\SearchTDTicketJob;
use Log;

class ShowTDTicket extends BaseHandler
{
    /**
     * If this function returns true, the handle method will get called.
     *
     * @param \Spatie\SlashCommand\Request $request
     *
     * @return bool
     */
    public function canHandle(Request $request): bool
    {
        Log::info('CP_ShowTDTicket: canHandle method called.');
        Log::info('CP_ShowTDTicket: Request type is ' . gettype($request) . '.');
        if($request->command) {
          Log::info('CP_ShowTDTicket: Given command ' . $request->command . '.');
          if($request->text == 'help' || $request->text == 'Help') {
            Log::info('CP_ShowTDTicket: User is requesting help. Stepping aside.');
            return false;
          }
          return true;
        }
        else {
          Log::info('CP_ShowTDTicket: Not given a command, canHandle returning false.');
          return false;
        }
    }

    /**
     * Handle the given request. Remember that Slack expects a response
     * within three seconds after the slash command was issued. If
     * there is more time needed, dispatch a job.
     *
     * @param \Spatie\SlashCommand\Request $request
     *
     * @return \Spatie\SlashCommand\Response
     */
    public function handle(Request $request): Response
    {
        if(is_numeric($request->text)) {
          $this->dispatch(new ShowTDTicketJob());
          return $this->respondToSlack("One moment please…");
        }
        else {
          $this->dispatch(new SearchTDTicketJob());
          return $this->respondToSlack("One moment please…");
        }

    }
}
