<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
				$table->integer('parentordercategoryid')->unsigned()->default(0)->comment('The parent ordercategories.id');
				$table->char('name', 64);
				$table->string('description', 2000);
				$table->dateTime('datetimecreated');
				$table->dateTime('datetimeremoved');
				$table->smallInteger('sequence');
			});

			DB::table('ordercategories')->insert([
				'name' => 'ROOT',
				'parentordercategoryid' => 0,
				'description' => 'Root Category',
				'sequence' => 1,
				'datetimecreated' => Carbon::now()->toDateTimeString()
			]);
		}

		if (!Schema::hasTable('orders'))
		{
			Schema::create('orders', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('userid')->unsigned()->default(0)->comment('Who the order for. FK to users.id');
				$table->integer('submitteruserid')->unsigned()->default(0)->comment('Who submitted the order. FK to users.id');
				$table->integer('groupid')->unsigned()->default(0)->comment('The group the order is for. FK to groups.id');
				$table->dateTime('datetimecreated');
				$table->dateTime('datetimeremoved');
				$table->string('usernotes', 2000);
				$table->string('staffnotes', 2000);
				$table->tinyInteger('notice')->unsigned()->default(0);
				$table->index('groupid');
				$table->index('userid');
				$table->index('submitteruserid');
			});
		}

		if (!Schema::hasTable('orderitems'))
		{
			Schema::create('orderitems', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('orderid')->unsigned()->comment('FK to orders.id');
				$table->smallInteger('orderproductid')->unsigned()->default(0)->comment('FK to orderproducts.id');
				$table->integer('origorderitemid')->unsigned()->default(0)->comment('References orderitems.id for first recurring item in sequence');
				$table->integer('prevorderitemid')->unsigned()->default(0)->comment('References orderitems.id for previous item in recurring sequence');
				$table->smallInteger('quantity')->unsigned()->default(0);
				$table->integer('price')->unsigned()->default(0);
				$table->integer('origunitprice')->unsigned()->default(0);
				$table->tinyInteger('recurringtimeperiodid')->unsigned()->default(0)->comment('FK to timeperiods.id');
				$table->tinyInteger('timeperiodcount')->unsigned()->default(0);
				$table->dateTime('datetimecreated');
				$table->dateTime('datetimeremoved');
				$table->dateTime('datetimefulfilled');
				$table->index('orderid');

				$table->foreign('orderid')->references('id')->on('orders');
			});
		}

		if (!Schema::hasTable('orderproducts'))
		{
			Schema::create('orderproducts', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('ordercategoryid')->unsigned()->default(0)->comment('FK to ordercategories.id');
				$table->char('name', 255);
				$table->string('description', 2000);
				$table->char('mou', 255);
				$table->integer('unitprice')->unsigned()->default(0);
				$table->tinyInteger('recurringtimeperiodid')->unsigned()->default(0)->comment('FK to timeperiods.id');
				$table->tinyInteger('public')->unsigned()->default(0);
				$table->tinyInteger('ticket')->unsigned()->default(0);
				$table->dateTime('datetimecreated');
				$table->dateTime('datetimeremoved');
				$table->smallInteger('sequence')->unsigned()->default(0);
				$table->smallInteger('successororderproductid')->unsigned()->default(0);
				$table->string('terms', 2000);
				$table->tinyInteger('restricteddata')->unsigned()->default(0);
				$table->integer('resourceid')->unsigned()->default(0)->comment('FK to resources.id');
				$table->index('recurringtimeperiodid');
				$table->index('ordercategoryid');

				//$table->foreign('ordercategoryid')->references('id')->on('ordercategories');
			});
		}

		if (!Schema::hasTable('orderpurchaseaccounts'))
		{
			Schema::create('orderpurchaseaccounts', function (Blueprint $table)
			{
				$table->increments('id');
				$table->integer('orderid')->unsigned()->default(0)->comment('FK to orders.id');
				$table->char('purchasefund', 8);
				$table->char('purchasecostcenter', 10);
				$table->char('purchaseorder', 10);
				$table->string('budgetjustification', 2000);
				$table->integer('amount')->unsigned()->default(0);
				$table->integer('approveruserid')->unsigned()->default(0)->comment('The assigned approver for this account. FK to users.id');
				$table->char('paymentdocid', 12);
				$table->dateTime('datetimecreated');
				$table->dateTime('datetimeremoved');
				$table->dateTime('datetimeapproved');
				$table->dateTime('datetimedenied');
				$table->dateTime('datetimepaid');
				$table->dateTime('datetimepaymentdoc');
				$table->tinyInteger('notice')->unsigned()->default(0);
				$table->char('purchaseio', 10)->default(0);
				$table->char('purchasewbse', 17)->default(0);
				$table->index('orderid');

				//$table->foreign('orderid')->references('id')->on('orders');
			});
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
		}
	}
}
