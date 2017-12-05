<?php

namespace Twinleaf\Console\Commands;

use Twinleaf\MapArea;
use Twinleaf\Setting;

use Illuminate\Console\Command;

class UpdateAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounts:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update accounts and restart applicable Map Areas';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $areas = MapArea::dueUpdate()->withActivatedAccounts()->get();

        foreach ($areas as $area) {
            $this->updateArea($area);
        }
    }

    protected function updateArea(MapArea $area)
    {
        if (!$area->accounts->count()) {
            $this->line("Skipping area {$area->name}, it has no accounts.");
            return;
        }

        $result = $this->writeAccounts($area);

        if (!$result) {
            if ($result === false) {
                $message = "Failed writing accounts for {$area->name}";
                $area->writeLog('error', '[cron] '.$message);
                $this->error($message);
            }
            return;
        }

        $this->kickstart($area);

        $this->info("Completed update for the {$area->slug} area.");
    }

    protected function writeAccounts(MapArea $area)
    {
        $csv = $area->accountsToCsv();

        $path = storage_path("maps/rocketmap/config/{$area->map->code}/{$area->slug}.csv");

        if ($csv == file_get_contents($path)) {
            $this->line("Skipping area {$area->name}, accounts are identical.");
            return null;
        }

        $area->writeLog('update', sprintf(
            '<code>[cron]</code> Writing %s accounts for <a href="%s">%s</a>.',
            count($area->accounts), $area->url(), $area->name
        ));

        return false !== file_put_contents($path, $csv);
    }

    protected function kickstart(MapArea $area, $action = 'start')
    {
        if ($area->isUp()) {
            $action = 'restart';

            $area->stop();
        }

        if ($area->start()) {
            $area->writeLog($action, sprintf(
                '[cron] %sed <a href="%s">%s</a>.',
                ucfirst($action), $area->url(), $area->name
            ));
        }
    }
}
