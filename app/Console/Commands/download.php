<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use App\Services\Freeway;

ini_set('memory_limit', '2560M');
class Download extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'freeway:download
                            {downloadfolder : 解壓縮後的 M06A 資料夾絕對路徑(至 M06A 為止，如 /Users/bency/Downloads/M06A)}';

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
        $this->path = $this->argument('downloadfolder');
        $this->freeway = new Freeway($this->path);
        $end_year = date('Y');
        $start_year = $this->ask("要從哪一年開始下載?", 2015);
        $end_year = $this->ask("要從下載到哪一年?", 2015);
        $start_month = $this->ask("要從哪一月開始下載?", 1);
        $start_day = $this->ask("要從哪一天開始下載?", 1);
        for ($year = $start_year; $year <= $end_year; $year++) {
            for ($month = 1; $month <= 12; $month++) {
                if ($year == $start_year and $month < $start_month) {
                    continue;
                }
                if ($month < 10) {
                    $month = "0" . $month;
                }
                $max_day = date('t', strtotime($year . '/'.  $month));
                for ($day = 1; $day <= $max_day; $day++) {
                    if ($year == $start_year and $month == $start_month and $day < $start_day) {
                        continue;
                    }
                    if ($day < 10) {
                        $day = "0" . $day;
                    }
                    $date = $year . $month . $day;
                    $this->freeway->downloadFile($date, $this->output);
                    try {
                      // echo $date . "\n";
                    } catch (\Exception $e) {
                        $this->error($e->getMessage());
                        $this->error(sprintf("%s-%s-%s %s檔案不存在，不執行匯入工作", $year, $month, $day, $hour));
                        if (!$this->confirm('要繼續匯入下一個檔案嗎？')) {
                            // 強制離開迴圈
                            $hour = $day = $month = $year = $end_year + 1;
                        }
                    }
                }
            }
        }
    }
}
