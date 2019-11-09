<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Freeway;

class import extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'freeway:import
                            {csvfolder : 解壓縮後的 M06A 資料夾絕對路徑(不含 M06A，如 /Users/bency/Downloads)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'import data from http://tisvcloud.freeway.gov.tw/history/TDCS/M06A/';

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
        $this->path = $this->argument('csvfolder');
        $this->freeway = new Freeway($this->path);
        $end_year = date('Y');
        $start_year = $this->ask("要從哪一年開始匯入?", 2015);
        $start_month = $this->ask("要從哪一月開始匯入?", 1);
        $start_day = $this->ask("要從哪一天開始匯入?", 1);
        for ($year = $start_year; $year <= 2019; $year++) {
            for ($month = 1; $month <= 12; $month++) {
                if ($year == $start_year and $month < $start_month) {
                    continue;
                }
                $max_day = date('t', strtotime($year . '/' . $month));
                for ($day = 1; $day <= $max_day; $day++) {
                    if ($year == $start_year and $month == $start_month and $day < $start_day) {
                        continue;
                    }
                    $date = $year . str_pad($month, 2, 0, STR_PAD_LEFT) . str_pad($day, 2, 0, STR_PAD_LEFT);
                    $this->freeway->downloadFile($date, $this->output);
                    for ($hour = 0; $hour <= 23; $hour++) {
                        try {
                            $this->info(sprintf("\n匯入 %s-%s-%s %s 時資料", $year, $month, $day, $hour));
                            $this->freeway->import($date, str_pad($hour, 2, 0, STR_PAD_LEFT), $this->output);
                        } catch (\Exception $e) {
                            $this->error(substr($e->getMessage(), 0, 100));
                            $this->error(sprintf("%s-%s-%s %s檔案不存在，不執行匯入工作", $year, $month, $day, $hour));
                            if (!$this->confirm('要繼續匯入下一個檔案嗎？')) {
                                // 強制離開迴圈
                                $hour = $day = $month = $year = $end_year + 1;
                            }
                        }
                    }
                    $this->freeway->unlink($date);
                }
            }
        }
    }
}
