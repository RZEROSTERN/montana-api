<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('profile', function (Blueprint $table) {
            $table->integer('gender')->nullable()->after('phone');
            $table->integer('country')->nullable()->after('gender');
            $table->integer('state')->nullable()->after('country');
            $table->string('about_me')->nullable()->after('state');
            $table->string('url_profile_picture')->nullable()->after('about_me');
            $table->string('whatsapp_number')->nullable()->after('url_profile_picture');
            $table->string('instagram_url')->nullable()->after('whatsapp_number');
            $table->string('tiktok_url')->nullable()->after('instagram_url');
        });
    }
};
