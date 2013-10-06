<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLinksTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('links', function(Blueprint $table)
		{
            $table->engine = 'InnoDB';
			$table->increments('id');

            $table->string('name');
            $table->string('parameters')->nullable();
            $table->string('controller');
            $table->string('actions');

            $table->integer('url_id')->unsigned();
            $table->foreign('url_id')->references('id')->on('urls')->onDelete('CASCADE');

            $table->integer('linkable_id')->unsigned();
            $table->string('linkable_type');

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
		Schema::drop('links');
	}

}
