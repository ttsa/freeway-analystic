<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
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

                    $this->downloadFile($date);
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

    private function downloadFile($date = '20150101') {
        // Initialize a file URL to the variable
        $url = 'http://tisvcloud.freeway.gov.tw/history/TDCS/M06A/M06A_' . $date .'.tar.gz';

        // Use basename() function to return the base name of file
        $file_name = basename($url);

        // Use file_get_contents() function to get the file
        // from url and use file_put_contents() function to
        // save the file by using base name
        // if(file_put_contents($file_name, file_get_contents($url))) {
        //     echo "File {$date}.tar.gz downloaded successfully";
        // }
        // else {
        //     echo "File {$date}.tar.gz downloading failed.";
        // }
        $this->info("\nDownloading {$date}");
        $this->bar = $this->output->createProgressBar(100);
        // $this->bar->setFormat('debug');
        // $this->bar->setRedrawFrequency(300);
        $this->bar->start();
        //save progress to variable instead of a file
        $temp_progress = '';
        $targetFile = fopen($file_name, 'w');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOPROGRESS, false);
        // curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, 'progressCallback');
        curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, [$this, 'progressCallback']);
        curl_setopt($ch, CURLOPT_FILE, $targetFile);
        curl_exec($ch);
        fclose($targetFile);
        //must add $resource to the function after a newer php version. Previous comments states php 5.5
        $this->info("\nExtracting {$date}");
        system('tar -xzf ' . $file_name);
        $this->bar->finish();
    }

    private function progressCallback( $resource, $download_size, $downloaded_size, $upload_size, $uploaded_size )
    {

        static $previousProgress = 0;

        if ( $download_size == 0 ) {
            $progress = 0;
        } else {
            $progress = round( $downloaded_size * 100 / $download_size );
        }

        if ( $progress > $previousProgress)
        {
            $previousProgress = $progress;
            $temp_progress = $progress;
        }

        $this->bar->setProgress($progress);

        //update javacsript progress bar to show download progress
        // echo $progress;
    }

}
