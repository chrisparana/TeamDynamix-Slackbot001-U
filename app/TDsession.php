<?php

namespace Slackbot001;

use Illuminate\Database\Eloquent\Model;


class TDsession extends Model
{
    use Traits\Encryptable;

    protected $table = 'TDsession';

    protected $encryptable = [
        's_token',
        'td_token',
    ];
}
