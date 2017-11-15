<?php
namespace Slackbot001\SlashCommandHandlers\Jobs;

use Spatie\SlashCommand\Jobs\SlashCommandResponseJob;
use Spatie\SlashCommand\Attachment;
use Spatie\SlashCommand\AttachmentField;
use Spatie\SlashCommand\AttachmentAction;
use Slackbot001\Classes\CP_TDinstance;
use Slackbot001\TDsession;
use Log;

class SearchTDTicketJob extends SlashCommandResponseJob
{
    // notice here that Laravel will automatically inject dependencies here
    public function handle()
    {
        $build = \Tremby\LaravelGitVersion\GitVersionHelper::getVersion();
        if( env('TD_SANDBOX') == 'TRUE') {
          $env = 'sandbox';
        }
        else {
          $env = 'prod';
        }

        $userSession = TDsession::where('s_user_id', $this->request->userId)->first();
        if($userSession != null ) {
          Log::info('CP_SearchTDTicketJob: Found user session.');
          if($userSession->td_token){
            $TDinstance = new CP_TDinstance( env('TD_BEID'), env('TD_WEBSERVICESKEY'), env('TD_URLROOT'), env('TD_APPID'), $env, (string)$userSession->td_token );
            Log::info('CP_SearchTDTicketJob: CP_TDinstance initialized with existing JWT.');
          }
          else {
            $TDinstance = new CP_TDinstance( env('TD_BEID'), env('TD_WEBSERVICESKEY'), env('TD_URLROOT'), env('TD_APPID'), $env );
            Log::info('CP_SearchTDTicketJob: CP_TDinstance initialized with new JWT.');
          }

          Log::info('CP_SearchTDTicketJob: Updating existing CP_TDsession s_token and td_token.');
          $userSession->s_token =  $this->request->token;
          $userSession->td_token = $TDinstance;
          $userSession->save();
        }
        else {
          Log::info('CP_SearchTDTicketJob: No user session.');
          Log::info('CP_SearchTDTicketJob: Creating new user session.');
          $TDinstance = new CP_TDinstance( env('TD_BEID'), env('TD_WEBSERVICESKEY'), env('TD_URLROOT'), env('TD_APPID'), $env );
          Log::info('CP_SearchTDTicketJob: New CP_TDinstance initialized.');
          $userSession = new TDsession;
          Log::info('CP_SearchTDTicketJob: New CP_TDsession initialized.');
          Log::info('CP_SearchTDTicketJob: Updating CP_TDsession s_token and td_token.');
          $userSession->s_user_id = $this->request->userId;
          $userSession->s_token =  $this->request->token;
          $userSession->td_token = $TDinstance;
          $userSession->save();
        }

        if($TDinstance->checkToken()){
          Log::info('CP_SearchTDTicketJob: There is a token.');
          $auth = true;
          $tickets = $TDinstance->searchTicketsName($this->request->text);
          $userSession->increment('td_searches');
        }
        else {
          Log::info('CP_SearchTDTicketJob: No token.');
          $auth = false;
          $tickets = null;
        }

        $opencount = 0;
        if (!count($tickets) && $auth) {
            return $this->respondToSlack("🕵️ I could not find any tickets with that requestor name.")->send();
        }
        elseif(!$auth) {
            return $this->respondToSlack("🕵️ I wasn't authorized to access TeamDynamix.")->send();
        }

        foreach ($tickets as $ticket) {
          if($ticket['StatusName'] != 'Closed' && $ticket['StatusName'] != 'Cancelled') {
            $opencount++;
            $date = date('F jS, Y', strtotime($ticket['CreatedDate']));
            $ticketURL = (string)$TDinstance->rootAppsUrl() . "Tickets/TicketDet?TicketID=" . $ticket['ID'];
            $ticketDescription = $TDinstance->ticket($ticket['ID']); //Need to do this because in the TD API, searched ticket's desctiption is empty.

            $attachmentFields[] = AttachmentField::create('Opened On', $date);
            $attachmentFields[] = AttachmentField::create('Requested By', $ticket['RequestorName'])->displaySideBySide();
            $attachmentFields[] = AttachmentField::create('Assigned To', $ticket['ResponsibleFullName'])->displaySideBySide();

            $attachmentAction = AttachmentAction::create('action', '📋 Show Ticket', 'button')
                ->setValue($ticket['ID'])
                ->setStyle('good');

            $action = $attachmentAction->toArray();
            $ticketattachments[] = Attachment::create()
                ->setFallback('Ticket ' . $ticket['ID'])
                ->setTitle('🏷 ' . $ticket['ID'])
                ->setText($ticketDescription['Description'])
                ->setFields($attachmentFields)
                ->setCallbackId($ticket['ID'])
                ->addAction($action)
                ->setColor('good')
                ->setFooter("CP_TD/S API bot microservice v" . $TDinstance->getVersion() . " | Build: " . $build);
          }
          unset($attachmentFields);
        }

        if ($opencount > 0) {
          $this
             ->respondToSlack("🕵️ I found " . $opencount . " open tickets.")
             ->withAttachments($ticketattachments)
             ->displayResponseToEveryoneOnChannel()
             ->send();
        }
        elseif ($opencount > 100) {
          $this
            ->respondToSlack("🕵️ I found " . $opencount . " open tickets, but Slack only supports 100 attachments. Sorry!")
            ->displayResponseToEveryoneOnChannel()
            ->send();
        }
        else {
          return $this
             ->respondToSlack("🕵️ I found " . $opencount . " open tickets.")
             ->withAttachment(Attachment::create()
                 ->setFallback("🕵️ I found no open tickets.")
                 ->setColor('warning')
                 ->setFooter("CP_TD/S API bot microservice v" . $TDinstance->getVersion() . " | Build: " . $build)
             )
             ->displayResponseToEveryoneOnChannel()
             ->send();
        }
    }
}
