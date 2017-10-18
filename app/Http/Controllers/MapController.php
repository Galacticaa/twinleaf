<?php

namespace Twinleaf\Http\Controllers;

use Twinleaf\Map;
use Twinleaf\Http\Requests\StoreMap;

class MapController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('maps.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Twinleaf\Http\Requests\StoreMap  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreMap $request)
    {
        #$map = Map::create(array_except($request->all(), ['_token']));
        $map = Map::create($request->all());

        return redirect()->route('maps.show', ['map' => $map]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Twinleaf\Map  $map
     * @return \Illuminate\Http\Response
     */
    public function show(Map $map)
    {
        return view('maps.details')->with('map', $map);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Twinleaf\Map  $map
     * @return \Illuminate\Http\Response
     */
    public function edit(Map $map)
    {
        return view('maps.edit')->with('map', $map);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Twinleaf\Map  $map
     * @return \Illuminate\Http\Response
     */
    public function update(StoreMap $request, Map $map)
    {
        $map->fill($request->all());
        $map->save();

        return redirect()->route('maps.show', ['map' => $map]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Twinleaf\Map  $map
     * @return \Illuminate\Http\Response
     */
    public function destroy(Map $map)
    {
        $map->delete();

        return redirect()->route('dashboard');
    }
}