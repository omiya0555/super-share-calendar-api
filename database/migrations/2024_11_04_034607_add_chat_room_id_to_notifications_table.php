<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('chat_room_id')->nullable()->after('user_id');
            $table->foreign('chat_room_id')->references('id')->on('chat_rooms')->onDelete('cascade');
        });
    }
    
    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['chat_room_id']);
            $table->dropColumn('chat_room_id');
        });
    }
};
