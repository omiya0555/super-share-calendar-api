<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserIconsTable extends Migration
{
    public function up()
    {
        Schema::create('user_icons', function (Blueprint $table) {
            $table->id();
            $table->string('icon_url');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_icons');
    }
}