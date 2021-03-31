<?php

namespace App\Modules\Messages\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Modules\Messages\Models\Type;

class TypesDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = array(
            array(
                'name' => 'get gpfs quota',
                'resourceid' => 64,
                'classname' => 'storagedir'
            ),
            array(
                'name' => 'get lustre quota',
                'resourceid' => 45,
                'classname' => 'storagedir'
            ),
            array(
                'name' => 'get lustreC quota',
                'resourceid' => 46,
                'classname' => 'storagedir'
            ),
            array(
                'name' => 'get lustreD quota',
                'resourceid' => 60,
                'classname' => 'storagedir'
            ),
            array(
                'name' => 'get lustreR quota',
                'resourceid' => 2,
                'classname' => 'storagedir'
            ),
            array(
                'name' => 'dev test',
                'resourceid' => 0,
                'classname' => 'storagedir'
            ),
            array(
                'name' => 'lustre mkdir',
                'resourceid' => 0,
                'classname' => 'storagedir'
            ),
            array(
                'name' => 'get lustreE quota',
                'resourceid' => 75,
                'classname' => 'storagedir'
            ),
            array(
                'name' => 'get halstead quota',
                'resourceid' => 47,
                'classname' => 'storagedir'
            ),
            array(
                'name' => 'set warpfs00 quota',
                'resourceid' => 47,
                'classname' => 'storagedir'
            ),
            array(
                'name' => 'home mkdir',
                'resourceid' => 81,
                'classname' => 'storagedir'
            ),
            array(
                'name' => 'get home quota',
                'resourceid' => 81,
                'classname' => 'storagedir'
            ),
            array(
                'name' => 'mkdir depot',
                'resourceid' => 64,
                'classname' => 'storagedir'
            ),
            array(
                'name' => 'rmdir depot',
                'resourceid' => 64,
                'classname' => 'storagedir'
            ),
            array(
                'name' => 'fileset depot',
                'resourceid' => 64,
                'classname' => 'storagedir'
            ),
            array(
                'name' => 'fix depot',
                'resourceid' => 64,
                'classname' => 'storagedir'
            ),
            array(
                'name' => 'api test',
                'resourceid' => 0,
                'classname' => 'storagedir'
            ),
            array(
                'name' => 'Brown Scratch',
                'resourceid' => 88,
                'classname' => 'storagedir'
            ),
            array(
                'name' => 'get snyder quota',
                'resourceid' => 76,
                'classname' => 'storagedir'
            ),
            array(
                'name' => 'get brown quota',
                'resourceid' => 88,
                'classname' => 'storagedir'
            ),
            array(
                'name' => 'get scholar scratch',
                'resourceid' => 70,
                'classname' => 'storagedir'
            ),
            array(
                'name' => 'make snyder scratch',
                'resourceid' => 76,
                'classname' => 'storagedir'
            ),
            array(
                'name' => 'make rice scratch',
                'resourceid' => 75,
                'classname' => 'storagedir'
            ),
            array(
                'name' => 'make scholar scratch',
                'resourceid' => 70,
                'classname' => 'storagedir'
            ),
            array(
                'name' => 'make halstead scratch',
                'resourceid' => 47,
                'classname' => 'storagedir'
            ),
            array(
                'name' => 'make gilbreth scratch',
                'resourceid' => 92,
                'classname' => 'storagedir'
            ),
            array(
                'name' => 'get gilbreth quota',
                'resourceid' => 92,
                'classname' => 'storagedir'
            ),
        );

        // Populate with default values
        foreach ($types as $type)
        {
            Type::create($type);
        }
    }
}
