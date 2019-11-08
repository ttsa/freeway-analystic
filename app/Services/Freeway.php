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

    public function import($year, $month, $day, $hour, $output)
    {
        if ($month < 10) {
            $month = "0" . $month;
        }
        if ($day < 10) {
            $day = "0" . $day;
        }
        if ($hour < 10) {
            $hour = "0" . $hour;
        }
        $date = $year . $month . $day;
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
