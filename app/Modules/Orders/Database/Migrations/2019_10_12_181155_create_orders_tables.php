<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateOrdersTables extends Migration
{
	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		if (!Schema::hasTable('ordercategories'))
		{
			Schema::create('ordercategories', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('parentordercategoryid')->unsigned();
				$table->char('name', 64);
				$table->string('description', 2000);
				$table->timestamp('datetimecreated');
				$table->softDeletes('datetimeremoved');
				$table->smallInteger('sequence');
			});

			$this->info('Created `ordercategories` table.');

			DB::table('ordercategories')->insert([
				'name' => 'ROOT',
				'parentordercategoryid' => 0,
				'description' => 'Root Category',
				'sequence' => 1
			]);

			$this->info('Created root entry in `ordercategories` table.');
		}

		if (!Schema::hasTable('orders'))
		{
			Schema::create('orders', function (Blueprint $table)
			{
				//$table->engine = 'InnoDB';
				$table->increments('id');
				$table->integer('userid')->unsigned()->default(0);
				$table->integer('submitteruserid')->unsigned()->default(0);
				$table->integer('groupid')->unsigned()->default(0);
				$table->timestamp('datetimecreated');
				$table->softDeletes('datetimeremoved');
				$table->string('usernotes', 2000);
				$table->string('staffnotes', 2000);
				$table->tinyInteger('notice')->unsigned()->default(0);
				$table->index('groupid');
			});
			$this->info('Created `orders` table.');
		}

		if (!Schema::hasTable('orderitems'))
		{
			Schema::create('orderitems', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('orderid')->unsigned();
				$table->smallInteger('orderproductid')->unsigned()->default(0);
				$table->integer('origorderitemid')->unsigned()->default(0);
				$table->integer('prevorderitemid')->unsigned()->default(0);
				$table->smallInteger('quantity')->unsigned()->default(0);
				$table->integer('price')->unsigned()->default(0);
				$table->integer('origunitprice')->unsigned()->default(0);
				$table->tinyInteger('recurringtimeperiodid')->unsigned()->default(0);
				$table->tinyInteger('timeperiodcount')->unsigned()->default(0);
				$table->timestamp('datetimecreated');
				$table->softDeletes('datetimeremoved');
				$table->timestamp('datetimefulfilled');
				$table->index('orderid');

				$table->foreign('orderid')->references('id')->on('orders');
			});
			$this->info('Created `orderitems` table.');
		}

		if (!Schema::hasTable('orderproducts'))
		{
			Schema::create('orderproducts', function (Blueprint $table)
			{
				$table->increments('id');
				$table->smallInteger('ordercategoryid')->unsigned()->default(0);
				$table->char('name', 255);
				$table->string('description', 2000);
				$table->char('mou', 255);
				$table->integer('unitprice')->unsigned()->default(0);
				$table->tinyInteger('recurringtimeperiodid')->unsigned()->default(0);
				$table->tinyInteger('public')->unsigned()->default(0);
				$table->tinyInteger('ticket')->unsigned()->default(0);
				$table->timestamp('datetimecreated');
				$table->softDeletes('datetimeremoved');
				$table->smallInteger('sequence')->unsigned()->default(0);
				$table->smallInteger('successororderproductid')->unsigned()->default(0);
				$table->string('terms', 2000);
				$table->tinyInteger('restricteddata')->unsigned()->default(0);
				$table->integer('resourceid')->unsigned()->default(0);
				$table->index('recurringtimeperiodid');
				$table->index('ordercategoryid');

				$table->foreign('ordercategoryid')->references('id')->on('ordercategories');
			});
			$this->info('Created `orderproducts` table.');
		}

		if (!Schema::hasTable('orderpurchaseaccounts'))
		{
			Schema::create('orderpurchaseaccounts', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('orderid')->unsigned()->default(0);
				$table->char('purchasefund', 8);
				$table->char('purchasecostcenter', 10);
				$table->char('purchaseorder', 10);
				$table->string('budgetjustification', 2000);
				$table->integer('amount')->unsigned()->default(0);
				$table->char('paymentdocid', 12);
				$table->timestamp('datetimecreated');
				$table->softDeletes('datetimeremoved');
				$table->timestamp('datetimeapproved');
				$table->timestamp('datetimedenied');
				$table->timestamp('datetimepaid');
				$table->timestamp('datetimepaymentdoc');
				$table->tinyInteger('notice')->unsigned()->default(0);
				$table->char('purchaseio', 10);
				$table->char('purchasewbse', 17);
				$table->index('orderid');

				$table->foreign('orderid')->references('id')->on('orders');
			});
			$this->info('Created `orderpurchaseaccounts` table.');
		}
	}

	/**
	 * Reverse the migrations.
	 * @return void
	 */
	public function down()
	{
		$tables = array(
			'orders',
			'ordercategories',
			'orderitems',
			'orderproducts',
			'orderpurchaseaccounts',
		);

		foreach ($tables as $table)
		{
			Schema::dropIfExists($table);

			$this->info('Dropped `' . $table . '` table.');
		}
	}
}
