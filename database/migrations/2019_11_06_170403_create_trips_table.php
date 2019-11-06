<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTripsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->bigIncrements('id');

            // 車種，31小客車、32小貨車、41大客車、42大貨車、5聯結車
            $table->integer('VehicleType');

            // 車輛通過本旅次第1個測站時間
            $table->integer('DerectionTime_O');

            // 車輛通過本旅次第1個測站編號
            $table->string('GantryID_O');

            // 車輛通過本旅次最後1個測站時間
            $table->integer('DerectionTime_D');

            // 車輛通過本旅次最後1個測站編號
            $table->string('GantryID_D');

            // 本旅次行駛距離
            $table->decimal('TripLength', 10, 2);

            //旅次標記(Y正常結束，N異常)
            $table->string('TripEnd');

            // 本旅次經過各個測站之通過時間及編號
            $table->text('TripInformation');

            $table->index(['DerectionTime_O']);
            $table->index(['DerectionTime_D']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trips');
    }
}
