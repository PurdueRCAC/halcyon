<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Modules\Mailer\Models\Message;

class AddNameField extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('mail_messages'))
		{
			if (!Schema::hasColumn('mail_messages', 'name'))
			{
				Schema::table('mail_messages', function (Blueprint $table)
				{
					$table->string('name', 255)->nullable();
				});

				$rows = Message::query()
					->where('template', '=', 1)
					->get();

				foreach ($rows as $row)
				{
					$row->name = $row->subject;
					$row->save();
				}
			}
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		if (Schema::hasTable('mail_messages'))
		{
			if (Schema::hasColumn('mail_messages', 'name'))
			{
				Schema::table('mail_messages', function (Blueprint $table)
				{
					$table->dropColumn('name');
				});
			}
		}
	}
}
