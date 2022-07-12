<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProfilePictureIdToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->bigInteger('profile_picture_id')->unsigned()->nullable();
            $table->foreign('profile_picture_id')->references('id')->on('media');
            $table->bigInteger('secondary_profile_picture_id')->unsigned()->nullable();
            $table->foreign('secondary_profile_picture_id')->references('id')->on('media');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['profile_picture_id']);
            $table->dropColumn('profile_picture_id');
            $table->dropForeign(['secondary_profile_picture_id']);
            $table->dropColumn('secondary_profile_picture_id');
        });
    }
}
