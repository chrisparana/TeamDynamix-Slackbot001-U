# TeamDynamix Slackbot001-U Installation and Setup


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
