<?php

namespace Slackbot001\SlashCommandHandlers\Jobs;

use Illuminate\Support\Facades\Log;
use Slackbot001\SessionManager;
use Spatie\SlashCommand\Attachment;
use Spatie\SlashCommand\AttachmentAction;
use Spatie\SlashCommand\AttachmentField;
use Spatie\SlashCommand\Jobs\SlashCommandResponseJob;

class SearchTDTicketJob extends SlashCommandResponseJob
{
    // notice here that Laravel will automatically inject dependencies here
    public function handle()
    {
        $build = \Tremby\LaravelGitVersion\GitVersionHelper::getVersion();

        $userSession = new SessionManager();
        $TDinstance = $userSession->setupSession($this->request->userId, $this->request->token);

        if ($TDinstance->checkToken()) {
            Log::info('CP_SearchTDTicketJob: There is a token.');
            $auth = true;
            $tickets = $TDinstance->searchTicketsName($this->request->text);
            $userSession()->increment('td_searches');
        } else {
            Log::info('CP_SearchTDTicketJob: No token.');
            $auth = false;
            $tickets = null;
        }

        $opencount = 0;
        if (!count($tickets) && $auth) {
            return $this->respondToSlack('🕵️ I could not find any tickets with that requestor name.')->send();
        } elseif (!$auth) {
            return $this->respondToSlack("🕵️ I wasn't authorized to access TeamDynamix.")->send();
        }

        foreach ($tickets as $ticket) {
            if ($ticket['StatusName'] != 'Closed' && $ticket['StatusName'] != 'Cancelled') {
                $opencount++;
                $date = date('F jS, Y', strtotime($ticket['CreatedDate']));
                $ticketURL = (string) $TDinstance->rootAppsUrl().'Tickets/TicketDet?TicketID='.$ticket['ID'];
                $ticketDescription = $TDinstance->ticket($ticket['ID']); //Need to do this because in the TD API, searched ticket's desctiption is empty.

                $attachmentFields[] = AttachmentField::create('Opened On', $date);
                $attachmentFields[] = AttachmentField::create('Requested By', $ticket['RequestorName'])->displaySideBySide();
                $attachmentFields[] = AttachmentField::create('Assigned To', $ticket['ResponsibleFullName'])->displaySideBySide();

                $attachmentAction = AttachmentAction::create('action', '📋 Show Ticket', 'button')
                ->setValue($ticket['ID'])
                ->setStyle('good');

                $action = $attachmentAction->toArray();
                $ticketattachments[] = Attachment::create()
                ->setFallback('Ticket '.$ticket['ID'])
                ->setTitle('🏷 '.$ticket['ID'])
                ->setText($ticketDescription['Description'])
                ->setFields($attachmentFields)
                ->setCallbackId($ticket['ID'])
                ->addAction($action)
                ->setColor('good')
                ->setFooter('CP_TD/S API bot microservice v'.$TDinstance->getVersion().' | Build: '.$build);
            }
            unset($attachmentFields);
        }

        if ($opencount > 0) {
            $this
             ->respondToSlack('🕵️ I found '.$opencount.' open tickets.')
             ->withAttachments($ticketattachments)
             ->displayResponseToEveryoneOnChannel()
             ->send();
        } elseif ($opencount > 100) {
            $this
            ->respondToSlack('🕵️ I found '.$opencount.' open tickets, but Slack only supports 100 attachments. Sorry!')
            ->displayResponseToEveryoneOnChannel()
            ->send();
        } else {
            return $this
             ->respondToSlack('🕵️ I found '.$opencount.' open tickets.')
             ->withAttachment(Attachment::create()
                 ->setFallback('🕵️ I found no open tickets.')
                 ->setColor('warning')
                 ->setFooter('CP_TD/S API bot microservice v'.$TDinstance->getVersion().' | Build: '.$build)
             )
             ->displayResponseToEveryoneOnChannel()
             ->send();
        }
    }
}
