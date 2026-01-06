<?php

namespace App\Console\Commands;

use App\Services\AttendanceRulesService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoMarkForgotCheckOut extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'attendance:auto-mark-forgot-checkout {--date=}';

    /**
     * The console command description.
     */
    protected $description = 'Auto-mark siswa yang lupa check-out';

    protected $attendanceRules;

    public function __construct(AttendanceRulesService $attendanceRules)
    {
        parent::__construct();
        $this->attendanceRules = $attendanceRules;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : Carbon::today();

        $this->info("🔄 Auto-marking forgot check-out for date: {$date->format('Y-m-d')}");

        try {
            $results = $this->attendanceRules->autoMarkForgotCheckOut($date);

            $this->info("✅ Auto-mark forgot check-out completed:");
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Total Checked', $results['total_checked']],
                    ['Marked as Forgot', $results['marked_forgot']],
                    ['Already Complete', $results['already_complete']],
                ]
            );

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
