<?php
namespace Slackbot001\SlashCommandHandlers\Jobs;

use Spatie\SlashCommand\Jobs\SlashCommandResponseJob;
use Spatie\SlashCommand\Attachment;
use Spatie\SlashCommand\AttachmentField;
use Slackbot001\Classes\CP_TDinstance;
use Slackbot001\TDsession;
use Log;

class ShowTDTicketJob extends SlashCommandResponseJob
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
          Log::info('CP_ShowTDTicketJob: Found user session.');
          if($userSession->td_token){
            $TDinstance = new CP_TDinstance( env('TD_BEID'), env('TD_WEBSERVICESKEY'), env('TD_URLROOT'), env('TD_APPID'), $env, (string)$userSession->td_token );
            Log::info('CP_ShowTDTicketJob: CP_TDinstance initialized with existing JWT.');
          }
          else {
            $TDinstance = new CP_TDinstance( env('TD_BEID'), env('TD_WEBSERVICESKEY'), env('TD_URLROOT'), env('TD_APPID'), $env );
            Log::info('CP_ShowTDTicketJob: CP_TDinstance initialized with new JWT.');
          }

          Log::info('CP_ShowTDTicketJob: Updating existing CP_TDsession s_token and td_token.');
          $userSession->s_token =  $this->request->token;
          $userSession->td_token = $TDinstance;
          $userSession->save();
        }
        else {
          Log::info('CP_ShowTDTicketJob: No user session.');
          Log::info('CP_ShowTDTicketJob: Creating new user session.');
          $TDinstance = new CP_TDinstance( env('TD_BEID'), env('TD_WEBSERVICESKEY'), env('TD_URLROOT'), env('TD_APPID'), $env );
          Log::info('CP_ShowTDTicketJob: New CP_TDinstance initialized.');
          $userSession = new TDsession;
          Log::info('CP_ShowTDTicketJob: New CP_TDsession initialized.');
          Log::info('CP_ShowTDTicketJob: Updating CP_TDsession s_token and td_token.');
          $userSession->s_user_id = $this->request->userId;
          $userSession->s_token =  $this->request->token;
          $userSession->td_token = $TDinstance;
          $userSession->save();
        }

        if($TDinstance->checkToken()){
          Log::info('CP_ShowTDTicketJob: There is a token.');
          $auth = true;
          $ticket = $TDinstance->ticket($this->request->text);
        }
        else {
          Log::info('CP_ShowTDTicketJob: No token.');
          $auth = false;
          $ticket = null;
        }


        if($ticket && $auth) {
          $date = date('F jS, Y', strtotime($ticket['CreatedDate']));
          $ticketURL = (string)$TDinstance->rootAppsUrl() . "Tickets/TicketDet?TicketID=" . $ticket['ID'];
          $assets = $TDinstance->searchAssets($ticket['ID']);
          $assetnames = "No Assets";
          $userSession->increment('td_tickets');

          if(count($assets)){
            $assetnames = '';
            foreach ($assets as $asset) {
                $assetnames = $assetnames . $asset['Name'] . ', ';
            }
            $assetnames = substr($assetnames, 0, -2);
          }

          $attachmentFields[] = AttachmentField::create('Description', $ticket['Description']);
          $attachmentFields[] = AttachmentField::create('Requestor', $ticket['RequestorName'] . "\n" . $ticket['RequestorEmail'] . "\n" . $ticket['RequestorPhone'])->displaySideBySide();
          $attachmentFields[] = AttachmentField::create('Location', $ticket['LocationName'] . "\n" . $ticket['LocationRoomName'])->displaySideBySide();
          $attachmentFields[] = AttachmentField::create('Service', $ticket['ServiceName'])->displaySideBySide();
          $attachmentFields[] = AttachmentField::create('Status', $ticket['StatusName'])->displaySideBySide();
          $attachmentFields[] = AttachmentField::create('Assets', $assetnames)->displaySideBySide();
          $attachmentFields[] = AttachmentField::create('Assigned To', $ticket['ResponsibleFullName'] . "\n" . $ticket['ResponsibleEmail'])->displaySideBySide();
          $attachmentFields[] = AttachmentField::create('Date Opened', $date)->displaySideBySide();

            $this
               ->respondToSlack("🕵️ Here's what I found about ticket `{$this->request->text}`")
               ->withAttachment(Attachment::create()
                   ->setFallback("{$ticket['Title']}")
                   ->setColor('#439FE0')
                   ->setTitle("{$ticket['Title']}")
                   ->setTitleLink("{$ticketURL}")
                   ->setFields($attachmentFields)
                   ->setFooter("CP_TD/S API bot microservice v" . $TDinstance->getVersion() . " | Build: " . $build)
               )
               ->displayResponseToEveryoneOnChannel()
               ->send();
        }
        elseif(!$ticket && $auth) {
          $this->respondToSlack("🕵️ I could not find that ticket.")->send();
        }
        else {
          $this->respondToSlack("🕵️ I wasn't authorized to access TeamDynamix.")->send();
        }
    }
}
