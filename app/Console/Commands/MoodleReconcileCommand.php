<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lms\LmsSyncLog;

class MoodleReconcileCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'skillup:moodle-reconcile';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reconcile local enrollments against Moodle state';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Moodle reconcile...');
        
        // Scaffold that logs output
        LmsSyncLog::create([
            'action' => 'reconcile',
            'status' => 'success',
            'attempts' => 1,
            'error_message' => 'Scaffold executed',
        ]);
        
        $this->info('Reconcile scaffold executed successfully.');
    }
}
