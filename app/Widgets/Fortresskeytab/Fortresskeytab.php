<?php
namespace App\Widgets\Fortresskeytab;

use App\Modules\Widgets\Entities\Widget;

/**
 * Display a form to generate a new Fortresskeytab
 */
class Fortresskeytab extends Widget
{
	/**
	 * Display
	 *
	 * @return  void
	 */
	public function run()
	{
		$offcampus = false;
		$ip = request()->ip();

		if (!$this->is_ipinsubnet($ip, '128.210.0.0', 16)
		 && !$this->is_ipinsubnet($ip, '128.211.128.0', 19)
		 && !$this->is_ipinsubnet($ip, '128.10.0.0', 16)
		 && !$this->is_ipinsubnet($ip, '128.211.0.0', 17)
		 && !$this->is_ipinsubnet($ip, '128.46.0.0', 16)
		 && !$this->is_ipinsubnet($ip, '172.16.0.0', 12)
		 && !$this->is_ipinsubnet($ip, '10.0.0.0', 8))
		{
			$offcampus = true;
		}
$offcampus = false;
		return view($this->getViewName(), [
			'offcampus' => $offcampus,
		]);
	}

	/**
	 * Test if an IP is in a given CIDR subnet range
	 *
	 * @param  string  $ip
	 * @param  string  $subnet
	 * @param  integer $cidr_value
	 * @return bool
	 */
	private function is_ipinsubnet($ip, $subnet, $cidr_value)
	{
		$ip_long = ip2long($ip);
		$subnet_long = ip2long($subnet);

		$ip_shifted = $ip_long >> (32 - $cidr_value);
		$subnet_shifted = $subnet_long >> (32 - $cidr_value);

		if ($ip_shifted == $subnet_shifted)
		{
			return true;
		}

		return false;
	}
}
