<?php

namespace Slackbot001;

use Illuminate\Support\Facades\Log;

class CP_TDauth
{
    protected $auth = '';
    protected $expires = '';
    protected $BEID = '';
    protected $WebServicesKey = '';
    protected $urlroot = '';
    protected $appsroot = '';
    protected $appid = '';
    protected $authstring = '';

    public function __construct()
    {
        $a = func_get_args();
        $i = func_num_args();
        if (method_exists($this, $f = '__construct'.$i)) {
            call_user_func_array([$this, $f], $a);
        }
    }

    private function __construct0()
    {
        Log::info('CP_TDauth: Constructing empty self.');
        $this->auth = 'FALSE';
    }

    private function __construct5($b, $w, $u, $i, $e)
    {
        Log::info('CP_TDauth: Constructing self with new authorization.');

        $this->setEnv($e, $u);
        $this->authorize($b, $w, $this->urlroot);
        $this->BEID = $b;
        $this->WebServicesKey = $w;
        $this->appid = $i;
    }

    private function __construct6($b, $w, $u, $i, $e, $a)
    {
        Log::info('CP_TDauth: Constructing self with existing authorization.');

        $this->setEnv($e, $u);
        $this->BEID = $b;
        $this->WebServicesKey = $w;
        $this->appid = $i;

        list($JWTheader, $JWTpayload, $JWTsig) = explode('.', $a);
        $this->auth = $a;
        $this->expires = json_decode(base64_decode($JWTpayload))->exp;
        $this->authstring = 'Authorization: Bearer '.$this->auth;
    }

    private function setEnv($e, $u)
    {
        if ($e == 'prod') {
            Log::info('CP_TDauth: Setup for production.');
            $this->urlroot = $u.'TDWebApi/api/';
            $this->appsroot = $u.'TDNext/Apps/';
        } elseif ($e == 'sandbox') {
            Log::info('CP_TDauth: Setup for sandbox.');
            $this->urlroot = $u.'SBTDWebApi/api/';
            $this->appsroot = $u.'SBTDNext/Apps/';
        }
    }

    private function authorize($b, $w, $u)
    {
        Log::info('CP_TDauth: authorize method called.');
        Log::info('CP_TDauth: authorize requesting at ['.$u.'auth/loginadmin].');
        $ch = curl_init($u.'auth/loginadmin');
        $payload = json_encode(['BEID' => $b, 'WebServicesKey' => $w]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $bearer = curl_exec($ch);
        curl_close($ch);
        if (!$bearer) {
            Log::info('CP_TDauth: Authorization failed.');
        } else {
            Log::info('CP_TDauth: Authorization successful.');
            list($JWTheader, $JWTpayload, $JWTsig) = explode('.', $bearer);
            $this->auth = $bearer;
            $this->expires = json_decode(base64_decode($JWTpayload))->exp;
            $this->authstring = 'Authorization: Bearer '.$this->auth;
        }
    }

    public function checkToken()
    {
        Log::info('CP_TDauth: checkToken method called.');
        if ($this->authstring) {
            if (($this->expires - time()) <= 10) {
                $this->authorize($this->BEID, $this->WebServicesKey, $this->urlroot);
                Log::info('CP_TDauth: Token was expired. Replaced with new token.');
            } else {
                $remain = $this->expires - time();
                Log::info('CP_TDauth: Token ok, time remaining: '.$remain);
            }

            return true;
        } else {
            Log::info('CP_TDauth: No token.');

            return false;
        }
    }

    public function __toString()
    {
        Log::info('CP_TDauth: __toString method called.');
        $this->checkToken();
        Log::info('CP_TDauth: returning JWT.');

        return $this->auth;
    }

    public function getVersion()
    {
        Log::info('CP_TDauth: getVersion method called.');

        return '0.98b';
    }
}

class CP_TDinstance extends CP_TDauth
{
    private function connect($type, $point, $data)
    {
        Log::info('CP_TDinstance: connect method called.');
        $this->checkToken();
        $ch = curl_init($this->urlroot.$point);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json', $this->authstring]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($type == 'post') {
            Log::info('CP_TDinstance: connect method POST.');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        } else {
            Log::info('CP_TDinstance: connect method GET.');
        }
        $result = curl_exec($ch);

        return json_decode($result, true);
    }

    private function flagCheck($r)
    {
        Log::info('CP_TDinstance: flagCheck method called.');
        $check = substr($r, -3);
        if (substr($check, 1, 1) == '-') {
            $flag = substr($check, 1, 2);
            Log::info('CP_TDinstance: flagCheck given flag '.$flag);

            return $flag;
        } else {
            return;
        }
    }

    public function ticket($ticketno)
    {
        Log::info('CP_TDinstance: ticket method called.');
        $ticket = $this->connect('get', $this->appid.'/tickets/'.$ticketno, '');

        return $ticket;
    }

    public function searchTicketsName($search)
    {
        Log::info('CP_TDinstance: searchTicketsName method called.');
        $flag = $this->flagCheck($search);

        if (!$flag) {
            $people = $this->searchPeople($search);
            foreach ($people as $person) {
                $uids[] = $person['UID'];
            }
            if (isset($uids)) {
                $tickets = $this->searchResponsibility($uids);

                return $tickets;
            } else {
                return;
            }
        } else {
            if ($flag = '-r') {
                $data = ['RequestorNameSearch' => substr($search, 0, -3)];
                $data_string = json_encode($data);
                $tickets = $this->connect('post', $this->appid.'/tickets/search', $data_string);

                return $tickets;
            } else {
                return;
            }
        }
    }

    public function searchPeople($search)
    {
        Log::info('CP_TDinstance: searchPeople method called.');
        $data = ['SearchText' => $search];
        $data_string = json_encode($data);
        $people = $this->connect('post', 'people/search', $data_string);

        return $people;
    }

    public function searchAssets($search)
    {
        Log::info('CP_TDinstance: searchAssets method called.');
        $ticketids = [(int) $search];
        $data = ['TicketIDs' => $ticketids];
        $data_string = json_encode($data);
        $assets = $this->connect('post', 'assets/search', $data_string);

        return $assets;
    }

    public function searchResponsibility($search)
    {
        Log::info('CP_TDinstance: searchResponsibility method called.');
        if (!is_array($search)) {
            $search = [(string) $search];
        }
        $ResponsibilityUids = $search;
        $data = ['ResponsibilityUids' => $ResponsibilityUids];
        $data_string = json_encode($data);
        $tickets = $this->connect('post', '/tickets/search', $data_string);

        return $tickets;
    }

    public function rootAppsUrl()
    {
        Log::info('CP_TDinstance: rootAppsUrl method called.');

        return $this->appsroot.$this->appid.'/';
    }
}
