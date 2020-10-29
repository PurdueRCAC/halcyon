<?php

namespace App\Modules\Core\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Core\Entities\KnowItAll;

class InfoController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * @return Response
	 */
	public function index(Request $request)
	{
		$model = new KnowItAll();

		return view('core::admin.info.index', [
			'model' => $model,
		]);
	}

	/**
	 * Display a listing of the resource.
	 * @return Response
	 */
	public function styles(Request $request)
	{
		return view('core::admin.styles');
	}

	/**
	 * Store a newly created resource in storage.
	 * @param  Request $request
	 * @return Response
	 */
	public function store(Request $request)
	{
		$request->validate([
			'name' => 'required'
		]);

		$order = new Order([
			'name'         => $request->get('name'),
			'parentid'     => $request->get('parentid'),
			'rolename'     => $request->get('rolename'),
			'listname'     => $request->get('listname'),
			'resourcetype' => $request->get('resourcetype'),
			'producttype'  => $request->get('producttype')
		]);

		$order->save();

		event('onAfterSaveOrder', $order);

		return redirect(route('admin.resources.index'))->with('success', 'Resource saved!');
	}

	/**
	 * Show the form for editing the specified resource.
	 * @return Response
	 */
	public function edit($id)
	{
		$order = Order::find($id);

		$types = Type::orderBy('name', 'asc')->get();

		return view('orders::admin.edit', [
			'row'   => $order,
			'types' => $types
		]);
	}

	/**
	 * Update the specified resource in storage.
	 * @param  Request $request
	 * @return Response
	 */
	public function update(Request $request, $id)
	{
		$request->validate([
			'name' => 'required'
		]);

		$order = Order::find($id);
		$order->set([
			'name'         => $request->get('name'),
			'parentid'     => $request->get('parentid'),
			'rolename'     => $request->get('rolename'),
			'listname'     => $request->get('listname'),
			'resourcetype' => $request->get('resourcetype'),
			'producttype'  => $request->get('producttype')
		]);

		$order->save();

		//event(new ResourceUpdated($order));
		event('onAfterSaveOrder', $order);

		return redirect(route('admin.resources.index'))->with('success', 'Resource updated!');
	}

	/**
	 * Remove the specified resource from storage.
	 * @return Response
	 */
	public function destroy($id)
	{
		$order = Order::find($id);
		$order->delete();

		return redirect(route('admin.resources.index'))->with('success', 'Resource deleted!');
	}
}
