<?php

namespace Twinleaf\Http\Controllers;

use Twinleaf\Proxy;

use Carbon\Carbon;
use Illuminate\Http\Request;

class ProxyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('proxies.index')->with([
            'proxies' => Proxy::with('area')->get(),
            'providers' => config('proxy.providers'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        $provider = $request->get('provider');
        $mode = $request->get('mode');
        $for = [
            'scanning' => $request->get('for_scanning', false),
            'creation' => $request->get('for_creation', false),
            'activation' => $request->get('for_activation', false),
        ];

        if ($provider && !config('proxy.providers.'.$provider)) {
            return redirect()->back()->withErrors([
                'provider' => 'Given provider is invalid.',
            ]);
        } elseif ($mode == 'p') {
            $proxies = [];
        } else {
            $proxies = $request->get('proxies');

            if (!$proxies) {
                return redirect()->back()->withErrors([
                    'proxies' => 'Proxies are required.',
                ]);
            }

            $proxies = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $proxies);
            $proxies = explode("\n", $proxies);
        }

        if ($mode == 'r' || $mode == 'p') {
            Proxy::whereProvider($provider)->delete();
        }

        $activationSet = false;

        foreach ($proxies as $proxy) {
            $proxy = Proxy::firstOrNew(['url' => $proxy]);
            $proxy->provider = $provider;
            $proxy->for_scanning = $for['scanning'];
            $proxy->for_creation = $for['creation'];
            $proxy->for_activation = $activationSet ? false : $for['activation'];
            $proxy->save();

            if (!$activationSet && $for['activation']) {
                $activationSet = true;
            }
        }

        return redirect()->route('proxies.index');
    }

    public function check(Request $request)
    {
        $proxies = Proxy::dueBanCheck()->get();

        if (!$proxies->count()) {
            return redirect()->route('proxies.index');
        }

        return view('proxies.check')->with('proxies', Proxy::dueBanCheck()->get());
    }

    public function checkPtc(Proxy $proxy)
    {
        return $this->checkProxy('ptc', $proxy);
    }

    public function checkPogo(Proxy $proxy)
    {
        return $this->checkProxy('pogo', $proxy);
    }

    protected function checkProxy(string $service, Proxy $proxy)
    {
        sleep(rand(1, 3));

        $result = exec(base_path('bin/bancheck')." {$service} '{$proxy->url}'");
        list($type, $code) = explode(':', $result);
        $status = $type == 'CURL' ? 'C'.$code : $code;

        $banKey = $service.'_ban';
        $bannedBefore = $proxy->$banKey;
        $proxy->$banKey = !($type == 'HTTP' && (int) $code == 200);

        if ($service == 'pogo') {
            $proxy->pogo_status = $status;
            $proxy->checked_at = Carbon::now();
        } else {
            $proxy->ptc_status = $status;
        }

        $proxy->save();

        return [
            'status' => $status,
            'proxy' => $proxy
        ];
    }
}
