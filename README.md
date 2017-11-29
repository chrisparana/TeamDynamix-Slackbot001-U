# TeamDynamix Slackbot001-U Installation and Setup
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.txt)
[![Build Status](https://img.shields.io/travis/chrisparana/TeamDynamix-Slackbot001-U.svg?style=flat-square)](https://travis-ci.org/chrisparana/TeamDynamix-Slackbot001-U)
[![StyleCI](https://styleci.io/repos/110712386/shield?branch=master)](https://styleci.io/repos/110712386)
[![Codacy branch grade](https://img.shields.io/codacy/grade/d4ab6a4335e7435c9e116102108b9eca/master.svg?style=flat-square)](https://www.codacy.com/app/chrisparana/TeamDynamix-Slackbot001-U?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=chrisparana/TeamDynamix-Slackbot001-U&amp;utm_campaign=Badge_Grade)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/chrisparana/TeamDynamix-Slackbot001-U/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/chrisparana/TeamDynamix-Slackbot001-U/?branch=master)

*Note! This is the basic universal version. Full point release will contain the web interface to build fully customized commands and responses.*

## Setup Slack

1. Create new app in slack
	- Note your `Verification Token`
2. Create new Slash Command
	- Command: `/td`
	- In the **Request URL field**, enter `https://yourbotservername.com/api/slack`
	- In the **Short Description field**, enter `Gets a TeamDynamix Ticket`
	- In the **Usage Hint field**, enter `name or ticket number`
3. Create new Interactive Components
	- In the **Request URL field**, enter `https://yourbotservername.com/api/slackbuttons`


## Setup App Server

*Important!* Make sure [Composer](https://getcomposer.org) is installed and the server meets Laravel's [requirements](https://laravel.com/docs/5.5/installation#server-requirements).

Clone the repo into your web directory (for example, `/var/www`).

`git clone git://github.com/chrisparana/TeamDynamix-Slackbot001-U`

Most folders should be normal `755` and files, `644`.
The only folder front facing should be everything in `/public`

The app requires some folders to be writable for the web server user:

```
sudo chgrp -R www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache
```

Point your web server to the public directory (for example, `/var/www/TeamDynamix-Slackbot001-U/public`)


**Setting the application key**

Switch to the app's root directory and run `php artisan key:generate` from the command line.


**Set up a database**

Create a MySQL database. Make sure it supports `utf8mb4`. When running the migration, if you receive an `SQLSTATE[42000]: Syntax error or access violation: 1071 Specified key was too long; max key length is 767 bytes` error, uncomment the `Schema::defaultStringLength(191);` line in `/app/Providers/AppServiceProvider.php`.


**Set up .env**

Using vi or your favorite text editor, create an `.env` file from the `.env.example` file.

1. Enter in appropriate settings for database

  ```
  DB_CONNECTION=mysql
  DB_HOST=localhost
  DB_PORT=3306
  DB_DATABASE=YOUR_DB_NAME
  DB_USERNAME=YOUR_DB_USERNAME
  DB_PASSWORD=YOUR_DB_PASSWORD
  ```

2. Enter in settings for Slack using the `Verification Token` you noted in step 1.

	`SLACK_SLASH_COMMAND_VERIFICATION_TOKEN=YOUR_SLACK_TOKEN`

3. Enter in settings for TeamDynamix

  ```
  TD_BEID=YOUR_BEID
  TD_WEBSERVICESKEY=YOUR_WEBSERVICE_KEY
  TD_URLROOT=YOUR_TD_URL
  TD_SANDBOX=FALSE
  TD_APPID=YOUR_TD_APPID
  ```


**Run Migration**

Switch to the app's root directory and run `php artisan migrate`.

Check to make sure everything is working so far by visiting your web server's address.

![working installation](https://raw.githubusercontent.com/chrisparana/TeamDynamix-Slackbot001-U/master/docs/img/webview.png)

## Choose and Setup your worker queue driver

[Supervisor](http://supervisord.org) along with the app's worker queue database is recommended for a quick setup, but you may use Amazon SQS, Beanstalkd, or Redis.

Set which queue driver you will be using in the `.env`. For our Supervisor setup, we will be using `QUEUE_DRIVER=database`.

**Set up for Supervisor**

Install Supervisor with `sudo apt-get install supervisor`

Use vi or your favorite editor to create `/etc/supervisor/conf.d/slackbot-tdticketcmd-worker.conf`

Here is a working example configuration for the worker queue. You may need to change this depending on your server’s set up.

```
[program: slackbot-tdticketcmd-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/TeamDynamix-Slackbot001-U/
artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
numprocs=8
redirect_stderr=true
stdout_logfile=/var/www/TeamDynamix-Slackbot001-U/storage/logs/worker.log
```


**Start the supervisor worker processes**

```
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
```

That's it. You should be all set to use your TeamDynamix Slackbot. From Slack, enter `/td help` for more information.

# Technical Details

All of the communications to TeamDynamix is handled by `app/Classes/CP_TDinstance.php`. This provides the mechanisms to create the authorization objects and keep tokens per user, as well as automatically update the tokens once they expire. Additionally, finding information about a ticket is handled here.

`app/Traits/Encryptable.php` is a trait responsible for encrypting objects with the application's key. In this case, see the TDsession model (`app/TDsession.php`).

Slack requires an immediate response from the bot once a command is sent. This is handled by `app/SlashCommandHandlers/ShowTDTicket.php`. ShowTDTicket determines if the request can be handled, what kind of request it is, then dispatches a job to handle the request, and responds "One moment please…" back to slack. There are two job processes from which ShowTDTicket chooses from, detailed below.

**SearchTDTicketJob**

SearchTDTicketJob (`app/SlashCommandHandlers/Jobs/SearchTDTicketJob.php`) is responsible for searching for a ticket by either the Requestor or Responsible person's name. It first checks if it is to be searching the sandbox or production instance of your TeamDynamix application, determined in your `.env` file. Next it checks if the current Slack user has made a request before or not, and handles the creation or update of the user session appropriately. This information is stored encrypted inside the TDsession model stored in the bot's database. Next SearchTDTicketJob collects the matching tickets using the TDinstance object (`app/Classes/CP_TDinstance.php`), then generates and returns the Slack attachments to Slack. Note that Slack can only handle up to 100 attachments, so searching is limited to 100 open tickets associated with the searched name.

**ShowTDTicketJob**

ShowTDTicketJob (`app/SlashCommandHandlers/Jobs/ShowTDTicketJob.php`) is responsible for displaying any given ticket. It is invoked by the ShowTDTicket method and accepts a TeamDynamix ticket number. Either entering a ticket number, or pressing the "Show Ticket" buttons generated by SearchTDTicketJob. Similar to SearchTDTicketJob, it first checks if it is to be searching the sandbox or production instance of your TeamDynamix application, and then checks if the current Slack user has made a request before or not. After the appropriate checks, it generates and returns an attachment to Slack.

**Table Migrations**

The tables in which models are stored are determined in the migrations defined in `database/migrations`. Of note is the Jobs table (`database/migrations/2017_08_11_191926_create_jobs_table.php`) and the TD/Bot Session table (`database/migrations/2017_09_19_162407_create_td_bot_sessions_table.php`).

The Jobs table keeps track of jobs dispatched by `app/SlashCommandHandlers/ShowTDTicket.php`.

The TD/Bot Session table stores the data for the user session model, which includes:

- User's Slack ID
- User's current Slack token
- User's current TeamDynamix JWT
- Number of searches user performed
- Number of tickets user requested
- Number times the user pressed the show ticket button

## Other information

If you need to display markup in Slack, (for example, the /td help command uses it extensively), you must modify `vendor/spatie/laravel-slack-slash-command/src/Attachment.php`.

In the `public function toArray()`, add `'mrkdwn_in'   => array("text", "pretext")` to the array (I usually place it immediately after the `'pretext'` item).
It should look like this:
```
public function toArray()
{
		return [
				'fallback'    => $this->fallback,
				'text'        => $this->text,
				'pretext'     => $this->preText,
				'mrkdwn_in'   => array("text", "pretext"),
				'color'       => $this->color,
				'footer'      => $this->footer,
				'footer_icon' => $this->footer,
				'ts'          => $this->timestamp ? $this->timestamp->getTimestamp() : null,
				'image_url'   => $this->imageUrl,
				'thumb_url'   => $this->thumbUrl,
				'title'       => $this->title,
				'title_link'  => $this->titleLink,
				'author_name' => $this->authorName,
				'author_link' => $this->authorLink,
				'author_icon' => $this->authorIcon,
				'callback_id' => $this->callbackId,
				'fields'      => $this->fields->map(function (AttachmentField $field) {
						return $field->toArray();
				})->toArray(),
				'actions'     => $this->actions->map(function (AttachmentAction $action) {
						return $action->toArray();
				})->toArray(),
		];
}
```

I will be creating a pull request to fix this issue in the Spatie Laravel Slack Slash Command repo, so it should be resolved soon by running `composer update`. My previous pull request to allow Spatie Laravel Slack Slash Command to handle an arbitrary number of attachments has already been [merged](https://github.com/chrisparana/laravel-slack-slash-command).
