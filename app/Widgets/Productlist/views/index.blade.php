<?php
/**
 * Product list
 */

$ids = array();
?>
@foreach ($categories as $category)
	<div id="{{ $category->alias }}">
		<h3>{{ $category->name }}</h3>
		@if ($category->description)
			<p>{!! $category->description !!}</p>
		@endif
		<ul class="purchases">
			@foreach ($category->resources as $resourceid => $data)
				<li>
					<?php
					$resource = $data['resource'];
					$products = $data['products'];

					$id = $resource->listname;

					if (in_array($id, $ids)):
						$id .= '-' . $category->alias;
					endif;

					$ids[] = $id;
					?>
					<div id="{{ $id }}" class="card purchase-card">
						<div class="card-content">
							<div class="card-body">
								<div class="row">
									<div class="col-md-8">
										<p class="card-title"><a class="purchase-resource" href="{{ route('site.orders.products', ['category' => $category->id]) }}">{{ $resource->name }}</a></p>
									</div>
									<div class="col-md-4 text-right text-end">
										<p><a class="btn btn-default btn-sm btn-purchase" href="{{ route('site.orders.products', ['category' => $category->id]) }}">{!! trans('widget.productlist::productlist.purchase now', ['name' => e($resource->name)]) !!}</a></p>
									</div>
								</div>

								<p class="card-text">{{ $resource->description }}</p>
							</div>
							@if (count($products))
								<div class="card-footer purchase-pricing">
									@if (auth()->user())
										@foreach ($products as $prod)
											<div class="row<?php if (!$prod->public) { echo ' orderproductitemprivate'; } ?>">
												<div class="col-md-7">
													@if (!$prod->public)
														<span class="badge badge-warning">{{ trans('widget.productlist::productlist.hidden') }}</span>
													@endif
													{{ $prod->name }}
												</div>
												<div class="col-md-2 text-right text-end">
													$ {{ $prod->price }}
												</div>
												<div class="col-md-3">
													per {{ $prod->unit }}
												</div>
											</div>
										@endforeach
									@else
										<div class="row">
											<div class="col-md-12">
												<span class="text-muted">{{ trans('widget.productlist::productlist.login to see pricing') }}</span>
											</div>
										</div>
									@endif
								</div>
							@endif
						</div>
					</div>
				</li>
			@endforeach
		</ul>
	</div>
@endforeach
