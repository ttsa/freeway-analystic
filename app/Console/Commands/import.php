<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;

class import extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'freeway:import
                            {csvfolder : 解壓縮後的 M06A 資料夾絕對路徑(至 M06A 為止，如 /Users/bency/Downloads/M06A)}';

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
        $end_year = date('Y');
        $start_year = $this->ask("要從哪一年開始匯入?", 2015);
        $start_month = $this->ask("要從哪一月開始匯入?", 1);
        $start_day = $this->ask("要從哪一天開始匯入?", 1);
        $import_data = [];
        for ($year = $start_year; $year <= 2019; $year++) {
            for ($month = 1; $month <= 12; $month++) {
                if ($year == $start_year and $month < $start_month) {
                    continue;
                }
                if ($month < 10) {
                    $month = "0" . $month;
                }
                $max_day = date('t', strtotime($year . '/' . $month));
                for ($day = 1; $day <= $max_day; $day++) {
                    if ($year == $start_year and $month == $start_month and $day < $start_day) {
                        continue;
                    }
                    if ($day < 10) {
                        $day = "0" . $day;
                    }
                    $date = $year . $month . $day;
                    for ($hour = 0; $hour <= 23; $hour++) {
                        if ($hour < 10) {
                            $hour = "0" . $hour;
                        }
                        $rows = $this->getRow($date, $hour);
                        try {
                            $bar = $this->output->createProgressBar($this->getLineCount($date, $hour));
                            $this->info(sprintf("匯入 %s-%s-%s %s 時資料", $year, $month, $day, $hour));
                            $bar->setFormat('debug');
                            $bar->setRedrawFrequency(300);
                            $bar->start();
                            $i = 0;
                            foreach ($rows as $data) {
                                $bar->advance();
                                $data[1] = strtotime($data[1]);
                                $data[3] = strtotime($data[3]);
                                $import_data[$i++] = [
                                    'VehicleType' => $data[0],
                                    'DerectionTime_O' => $data[1],
                                    'GantryID_O' => $data[2],
                                    'DerectionTime_D' => $data[3],
                                    'GantryID_D' => $data[4],
                                    'TripLength' => $data[5],
                                    'TripEnd' => $data[6],
                                    'TripInformation' => $data[7],
                                ];
                                if ($i == env('MAX_BUNDLE', 4000)) {
                                    DB::disableQueryLog();
                                    DB::table('trips')->insert($import_data);
                                    $i = 0;
                                }
                            }
                            if ($i > 0) {
                                DB::disableQueryLog();
                                DB::table('trips')->insert($import_data);
                            }
                            $bar->finish();
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

    private function getLineCount($date, $hour)
    {
        $file  = sprintf($this->path . '/%s/%s/TDCS_M06A_%1$s_%2$s0000.csv', $date, $hour);
        $linecount = 0;
        $handle = fopen($file, "r");
        while(!feof($handle)){
            $line = fgets($handle);
            $linecount++;
        }

        fclose($handle);

        return $linecount;
    }

    private function getRow($date, $hour)
    {
        $file  = sprintf($this->path . '/%s/%s/TDCS_M06A_%1$s_%2$s0000.csv', $date, $hour);
        $fp = fopen($file, 'r');
        if (false === $fp) {
            return false;
        }
        while ($row = fgetcsv($fp)) {
            yield $row;
        }
        fclose($fp);
    }
}
