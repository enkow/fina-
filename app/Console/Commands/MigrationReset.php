<?php

namespace App\Console\Commands;

use App\Models\Club;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrationReset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migration:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migration reset';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        DB::table('imported_models')->truncate();
        $clubsToDelete = Club::where('created_at','<','2023-01-01 00:00:00')->get();
        foreach($clubsToDelete as $club) {
            $club->delete();
        }
    }
}
