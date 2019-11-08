<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDetectiontimeGantryIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->index(['DerectionTime_O', 'DerectionTime_D']);
            $table->index(['GantryID_O']);
            $table->index(['GantryID_D']);
            $table->index(['TripLength']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropIndex(['DerectionTime_O', 'DerectionTime_D']);
            $table->dropIndex(['GantryID_O']);
            $table->dropIndex(['GantryID_D']);
            $table->dropIndex(['TripLength']);
        });
    }
}
