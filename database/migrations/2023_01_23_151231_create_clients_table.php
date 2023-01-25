<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            
            $table->string('name')->nullable();
            $table->string('address')->nullable();
            $table->boolean('checked')->nullable();
            $table->string('description', 8192)->nullable();
            $table->string('interest')->nullable();
            $table->dateTime('dateOfBirth')->nullable();
            $table->string('email')->nullable();
            $table->string('account')->nullable();

            $table->unsignedBigInteger('credit_card')->index()->nullable();
            $table->foreign('credit_card')->references('id')->on('cards');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clients');
    }
};
