<?php
/**
 * Product list
 */
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
					$products = $data['products'];//$resource->listname
					?>
					<div id="{{ $resource->listname }}" class="card purchase-card">
						<div class="card-content">
							<div class="card-body">
								<div class="row">
									<div class="col-md-8">
										<p class="card-title"><a class="purchase-resource" href="{{ route('site.orders.products', ['category' => $category->id]) }}">{{ $resource->name }}</a></p>
									</div>
									<div class="col-md-4 text-right">
										<p><a class="btn btn-default btn-sm btn-purchase" href="{{ route('site.orders.products', ['category' => $category->id]) }}">Purchase <span class="sr-only">{{ $resource->name }} </span>Now</a></p>
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
														<span class="badge badge-warning">HIDDEN</span>
													@endif
													{{ $prod->name }}
												</div>
												<div class="col-md-2 text-right">
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
												<span class="text-muted">Login to see pricing options.</span>
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
