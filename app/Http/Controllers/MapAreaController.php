<?php

namespace Twinleaf\Http\Controllers;

use Activity;
use Twinleaf\Map;
use Twinleaf\MapArea;
use Twinleaf\Accounts\Generator;
use Twinleaf\Http\Requests\StoreMapArea;

class MapAreaController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Map $map)
    {
        return view('maps.areas.create')->with('map', $map);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreMapArea $request)
    {
        $area = MapArea::create($request->all());
        $area->speed_scan = $request->get('speed_scan', false);
        $area->beehive = $request->get('beehive', false);
        $area->save();

        $map = Map::find($area->map_id);

        return redirect()->route('maps.areas.show', [
            'map' => $map,
            'area' => $area,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Twinleaf\MapArea  $mapArea
     * @return \Illuminate\Http\Response
     */
    public function show(Map $map, MapArea $area)
    {
        $logs = Activity::whereContentType('map_area')
                        ->whereContentId($area->id)
                        ->orderBy('updated_at', 'desc')
                        ->limit(50)
                        ->get();

        $logsByDate = [];

        foreach ($logs as $log) {
            $date = $log->updated_at->toDateString();

            if (!array_key_exists($date, $logsByDate)) {
                $logsByDate[$date] = [];
            }

            $logsByDate[$date][] = $log;
        }

        return view('maps.areas.details')->with([
            'map' => $map,
            'area' => $area,
            'logsByDate' => $logsByDate,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Twinleaf\MapArea  $mapArea
     * @return \Illuminate\Http\Response
     */
    public function edit(Map $map, MapArea $area)
    {
        return view('maps.areas.edit')
                ->with('map', $map)
                ->with('area', $area);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Twinleaf\MapArea  $mapArea
     * @return \Illuminate\Http\Response
     */
    public function update(StoreMapArea $request, Map $map, MapArea $area)
    {
        $area->fill($request->all());
        $area->beehive = $request->get('beehive', false);
        $area->is_enabled = $request->get('enable_scan', false);
        $area->speed_scan = $request->get('speed_scan', false);
        $area->spin_pokestops = $request->get('spin_pokestops', false);
        $area->geofence = $request->get('geofence');
        $area->save();

        $area->writeGeofenceFile();

        if ($request->ajax()) {
            return ['success' => true, 'area' => $area];
        }

        return redirect()->route('maps.areas.show', [
            'map' => $area->map,
            'area' => $area,
        ]);
    }

    /**
     * Replace all accounts for the specified area.
     * @param \Twinleaf\Map  $map
     * @param \Twinleaf\MapArea  $area
     * @return \Illuminate\Http\Response
     */
    public function regenerate(Map $map, MapArea $area)
    {
        $oldCount = $area->accounts()->count();

        foreach ($area->accounts as $account) {
            $account->area()->dissociate();
            $account->save();
        }

        $result = (new Generator($area))->generate();

        $area->writeLog('regenerate', sprintf(
            '<a href="%s">%s</a>\'s accounts were regenerated.',
            $area->url(), $area->name
        ), sprintf(
            'Before: %s; After: %s',
            $oldCount, $area->accounts()->count()
        ));

        return $result;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Twinleaf\MapArea  $mapArea
     * @return \Illuminate\Http\Response
     */
    public function destroy(Map $map, MapArea $area)
    {
        $area->delete();

        return redirect()->route('maps.show', ['map' => $map]);
    }
}
