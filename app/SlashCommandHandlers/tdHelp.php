<?php

namespace Slackbot001\SlashCommandHandlers;

use Spatie\SlashCommand\Attachment;
use Spatie\SlashCommand\Handlers\SignatureHandler;
use Spatie\SlashCommand\Request;
use Spatie\SlashCommand\Response;

class tdHelp extends SignatureHandler
{
    protected $signature = '* help {command? : The command you want information about}';

    public function handle(Request $request): Response
    {
        //$command = $this->getArgument('command');

        return $this->respondToSlack('*TeamDynamix Slackbot Help*')
            ->withAttachment(
                Attachment::create()
                    ->setColor('#567185')
                    ->setTitle('Welcome')
                    ->setText("TeamDynamix Slackbot is built as a companion to TeamDynamix. It can search for a ticket given a ticket number, or search for open tickets given a user's name. This can be useful for when you're out in the field, chatting with coworkers, or just want a quick list of tickets you need to work on. \n\n*Usage*\nTo begin, type `/td` followed by a name. For example, `/td stergion` will show a list of open tickets assigned to that user.\n\n_Note: Ticket information will be shown to everyone in the current channel or chat. Momentary messages and errors will only be shown to you._\n\nAfter the list of tickets is displayed, you can request details about a specific ticket. To achieve this, press the *Show Ticket* button. From here you can open the ticket in TeamDynamix by clicking or pressing on the ticket's title.\n\n*Other Options*\nThere are other search options available to use with the command. If you know the *ticket number*, you can request it directly by typing `/td` followed by the ticket number. For example, `/td 3791888` will display ticket `3791888`.\n\nTo instead search for tickets by the *requestor name* rather than the *responsible name*, you can use the `-r` flag. For example `/td parana -r` will show all tickets requested by that person.")
            );
    }
}
