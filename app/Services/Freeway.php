<?php

namespace App\Services;

use DB;

class Freeway
{
    private $path;
    public function __construct($path)
    {
        $this->path = $path;
    }

    public function downloadFile($date = '20150101', $output = null) {
        // Initialize a file URL to the variable
        $url = 'http://tisvcloud.freeway.gov.tw/history/TDCS/M06A/M06A_' . $date .'.tar.gz';

        // Use basename() function to return the base name of file
        $file_name = $this->path . '/' . basename($url);

        // Use file_get_contents() function to get the file
        // from url and use file_put_contents() function to
        // save the file by using base name
        // if(file_put_contents($file_name, file_get_contents($url))) {
        //     echo "File {$date}.tar.gz downloaded successfully";
        // }
        // else {
        //     echo "File {$date}.tar.gz downloading failed.";
        // }
        //$this->info("\nDownloading {$date}");
        $this->bar = $output->createProgressBar(100);
        // $this->bar->setFormat('debug');
        // $this->bar->setRedrawFrequency(300);
        $this->bar->start();
        //save progress to variable instead of a file
        $temp_progress = '';
        $targetFile = fopen($file_name, 'w');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOPROGRESS, false);
        curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, [$this, 'curlProgressCallback']);
        curl_setopt($ch, CURLOPT_FILE, $targetFile);
        curl_exec($ch);
        fclose($targetFile);
        //must add $resource to the function after a newer php version. Previous comments states php 5.5
        system('tar -xzf ' . $file_name);
        $this->bar->finish();
    }

    private function curlProgressCallback( $resource, $download_size, $downloaded_size, $upload_size, $uploaded_size )
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
    }

    public function import($date, $output)
    {
        $year = substr($date, 0, 4);
        $month = substr($date, 4, 2);
        $day = substr($date, 6, 2);
        $rows = $this->getRow($date, $hour);
        $bar = $output->createProgressBar($this->getLineCount($date, $hour));
        $bar->setFormat('debug');
        $bar->setRedrawFrequency(300);
        $bar->start();
        $i = 0;
        $import_data = [];
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
            if (strlen(serialize($import_data)) > env('MAX_BUNDLE', 1048576)) {
                DB::disableQueryLog();
                DB::table('trips')->insert($import_data);
                $import_data = [];
                $i = 0;
            }
        }
        if ($i > 0) {
            DB::disableQueryLog();
            DB::table('trips')->insert($import_data);
        }
        $bar->finish();
    }

    private function getLineCount($date, $hour)
    {
        $file  = sprintf($this->path . '/M06A/%s/%s/TDCS_M06A_%1$s_%2$s0000.csv', $date, $hour);
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