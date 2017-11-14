<?php

namespace Slackbot001\Http\Controllers;

use Illuminate\Http\Request;
use Slackbot001\Classes\CP_TDinstance;
use Log;
use DB;

class PageController extends Controller
{
  public function home() {
    Log::info('CP_PageController: home method called.');
    $CPTD = new CP_TDinstance();

    $colors = array('#ffffff', '#000000', '1');
    $version = $CPTD->getVersion();
    $usersTotal = DB::table('TDsession')->count();
    $td_searchesTotal = DB::table('TDsession')->sum('td_searches');
    $td_ticketsTotal = DB::table('TDsession')->sum('td_tickets');
    $td_otherTotal = DB::table('TDsession')->sum('td_other');
    $s_showticketsTotal = DB::table('TDsession')->sum('s_showtickets');

    $stats = array($usersTotal, $td_searchesTotal, $td_ticketsTotal, $td_otherTotal, $s_showticketsTotal);

    return view('welcome', compact('version', 'colors', 'stats'));
  }
}
