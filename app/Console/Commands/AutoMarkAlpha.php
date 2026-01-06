<?php

namespace App\Console\Commands;

use App\Services\AttendanceRulesService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoMarkAlpha extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'attendance:auto-mark-alpha {--date=}';

    /**
     * The console command description.
     */
    protected $description = 'Auto-mark siswa yang tidak absen sebagai ALPHA';

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

        $this->info("🔄 Auto-marking alpha for date: {$date->format('Y-m-d')}");

        try {
            $results = $this->attendanceRules->autoMarkAlpha($date);

            $this->info("✅ Auto-mark alpha completed:");
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Total Students', $results['total_students']],
                    ['Marked as ALPHA', $results['marked_alpha']],
                    ['Already Present', $results['already_present']],
                    ['Has Permission (Izin/Sakit)', $results['has_permission']],
                ]
            );

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
