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
    protected $authsig = '';
    protected $header = '';

    public function __construct()
    {
        $args = func_get_args();
        $argcount = func_num_args();
        if (method_exists($this, $func = '__construct'.$argcount)) {
            call_user_func_array([$this, $func], $args);
        }
    }

    public function __construct0()
    {
        Log::info('CP_TDauth: Constructing empty self.');
    }

    public function __construct5($beid, $wskey, $urlroot, $appid, $env)
    {
        Log::info('CP_TDauth: Constructing self with new authorization.');
        $this->setEnv($env, $urlroot);
        $this->authorize($beid, $wskey, $this->urlroot);
        $this->BEID = $beid;
        $this->WebServicesKey = $wskey;
        $this->appid = $appid;
    }

    public function __construct6($beid, $wskey, $urlroot, $appid, $env, $auth)
    {
        Log::info('CP_TDauth: Constructing self with existing authorization.');
        $this->setEnv($env, $urlroot);
        $this->BEID = $beid;
        $this->WebServicesKey = $wskey;
        $this->appid = $appid;

<<<<<<< HEAD
        $parts = explode('.', $auth);
        if(count($parts) == 3) {
=======
        $parts = explode('.', $a);
        if (count($parts) == 3) {
>>>>>>> e693102ad88ad9336e40ad505fb7aa39f5c980e5
            list($JWTheader, $JWTpayload, $JWTsig) = $parts;
            $this->auth = $auth;
            $this->expires = json_decode(base64_decode($JWTpayload))->exp;
            $this->authstring = 'Authorization: Bearer '.$this->auth;
            $this->header = $JWTheader;
            $this->authsig = $JWTsig;

            return;
        }
        Log::info('CP_TDauth: Invalid token.');
    }

    private function setEnv($env, $urlroot)
    {
        if ($env == 'prod') {
            Log::info('CP_TDauth: Setup for production.');
            $this->urlroot = $urlroot.'TDWebApi/api/';
            $this->appsroot = $urlroot.'TDNext/Apps/';
        } elseif ($env == 'sandbox') {
            Log::info('CP_TDauth: Setup for sandbox.');
            $this->urlroot = $urlroot.'SBTDWebApi/api/';
            $this->appsroot = $urlroot.'SBTDNext/Apps/';
        }
    }

    private function authorize($beid, $wskey, $urlroot)
    {
        Log::info('CP_TDauth: authorize method called.');
        Log::info('CP_TDauth: authorize requesting at ['.$urlroot.'auth/loginadmin].');
        $ch = curl_init($urlroot.'auth/loginadmin');
        $payload = json_encode(['BEID' => $beid, 'WebServicesKey' => $wskey]);
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
            $this->header = $JWTheader;
            $this->authsig = $JWTsig;
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

    private function flagCheck($search)
    {
        Log::info('CP_TDinstance: flagCheck method called.');
        $check = substr($search, -3);
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
            if ($flag == '-r') {
                $data = ['RequestorNameSearch' => substr($search, 0, -3)];
                $data_string = json_encode($data);
                $tickets = $this->connect('post', $this->appid.'/tickets/search', $data_string);

                return $tickets;
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
