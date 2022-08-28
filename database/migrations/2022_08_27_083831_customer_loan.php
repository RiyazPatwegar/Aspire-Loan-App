<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CustomerLoan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /* TO store customer short information */
        /* Schema::create('customer', function (Blueprint $table) {
            $table->id();
            $table->string('name');            
            $table->timestamp('email');            
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        }); */

        /* TO store customer loan application information */
        Schema::create('loan_application', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id');
            $table->timestamp('applied_at');
            $table->integer('amount');
            $table->integer('term');
            $table->string('application_status')->default('PENDING');
            $table->string('loan_status')->default('PENDING');
            $table->integer('approved_by')->default('0');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        /* To maintain customer repalyment details */
        Schema::create('payment_schedule', function (Blueprint $table) {
            $table->id();
            //$table->integer('customer_id');
            $table->integer('application_id');
            $table->timestamp('schedule_date');
            $table->string('schedule_amount');
            $table->string('status')->default('PENDING');
            $table->string('paid_amount')->nullable();
            $table->timestamp('paid_date')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer');
        Schema::dropIfExists('payment_schedule');
    }
}
