<?php

namespace Twinleaf\Listeners;

use Twinleaf\Map;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;

class InjectMapMenu
{
    /**
     * Inject maps into the main sidebar menu
     *
     * @param BuildingMenu $event
     * @return void
     */
    public function handle(BuildingMenu $event)
    {
        $nav = ['MAP MANAGER'];

        foreach (Map::select('name', 'code')->get() as $map) {
            $nav[] = [
                'text' => $map->name,
                'url' => route('maps.show', ['map' => $map->code]),
            ];
        }

        $nav[] = [
            'text' => 'Add new Map',
            'route' => 'maps.create',
            'icon' => 'plus',
        ];

        $event->menu->add(...$nav);
    }
}
