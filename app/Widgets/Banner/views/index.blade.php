<?php
/**
 * @package  Banner widget
 */
?>
@if (count($outages) == 0)
	<div class="bannerContainer col-lg-8 col-md-8 hidden-s-down hidden-xs-down">
		<div id="wrapper-promo-home">
			<div id="promo-home">
				<div id="myGallery">
					<ul class="bxslider">
						<li>
							<div class="multiple">
								<a href="{{ route('page', ['uri' => 'compute/brown']) }}"><img src="{{ asset('files/promo/2017/brown.png') }}" alt="Brown HPC Cluster"/></a>
								<a href="{{ route('page', ['uri' => 'compute/bell']) }}"><img src="{{ asset('files/promo/2021/bell.png') }}" alt="Bell Cluster"/></a>
								<a href="{{ route('page', ['uri' => 'compute/workbench']) }}"><img src="{{ asset('files/promo/2017/workbench.png') }}" alt="Data Workbench"/></a>
							</div>
						</li>
						<li>
							<a href="{{ route('page', ['uri' => 'storage/depot']) }}">
								<img src="{{ asset('files/promo/depot/large.png') }}" alt="Research Data Depot" />
							</a>
						</li>
						<li> 
							<a href="{{ route('page', ['uri' => 'coffee']) }}">
								<img src="{{ asset('files/promo/coffee/large.jpg') }}" alt="Research Computing Coffee Consultations" />
							</a>
						</li>
						<li> 
							<a href="{{ route('page', ['uri' => 'news/117']) }}">
								<img src="{{ asset('files/promo/catlin/large.jpg') }}" alt="Science Highlights" />
							</a>
						</li>
						<li> 
							<a href="https://www.xsede.org/">
								<img src="{{ asset('files/promo/xsede/large.jpg') }}" alt="Extreme Science and Engineering Discovery Environment" />
							</a>
						</li>
						<li> 
							<a href="{{ route('page', ['uri' => 'news/114']) }}">
								<img src="{{ asset('files/promo/delgado/large.jpg') }}" alt="Extreme Science and Engineering Discovery Environment" />
							</a>
						</li>
						<li> 
							<a href="http://www.diagrid.org">
								<img src="{{ asset('files/promo/diagrid/large.jpg') }}" alt="DiaGrid" />
							</a>
						</li>
						<li> 
							<a href="http://www.envision.purdue.edu/">
								<img src="{{ asset('files/promo/envision/large.jpg') }}" alt="Envision Center" />
							</a>
						</li>
						<li> 
							<a href="{{ route('page', ['uri' => 'services/communityclusters']) }}">
								<img src="{{ asset('files/promo/clusterprogram/large.jpg') }}" alt="Community Cluster Program" />
							</a>
						</li>
						<li> 
							<a href="http://www.itap.purdue.edu/pto">
								<img src="{{ asset('files/promo/pto/large.jpg') }}" alt="Purdue Terrestrial Observatory" />
							</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<div class="audienceTiles col-lg-4 col-md-4 col-sm-12 col-xs-12 tiles-1">
@else
	<div class="audienceTiles col-lg-4 col-md-6 col-sm-12 col-xs-12 tiles-1">
		<div class="tileRow">
			<div class="tileContainer col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<img src="{{ asset('files/logo_white.png') }}" alt="ITaP Logo" id="itap-logo" height="150" />
			</div>
		</div><!-- /.tileRow -->
	</div><!-- /.audienceTiles -->

	<div class="audienceTiles col-lg-4 col-md-6 col-sm-12 col-xs-12 tiles-1">
		<div class="tileRow">
			<div class="tileContainer col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<h2 class="tile">Current Outages</h2>

				@if (count($outages))
					<ul class="newslist">
						@foreach ($outages as $item)
							<li class="first">
								<a href="{{ route('site.news.show', ['id' => $item->id]) }}">{{ $item->headline }}</a>
								<p class="date">
									<span class="text-nowrap">{{ $item->formatDate($item->datetimenews, $item->datetimenewsend) }}</span>
									@if ($item->isToday())
										<span class="badge badge-info">Today</span>
									@endif
								</p>
								@if ($update = $item->updates()->orderBy('datetimecreated', 'desc')->first())
									<p class="newsupdated">Updated: {{ $update->datetimecreated->format('M d, Y h:ia') }}</p>
								@endif
							</li>
						@endforeach
					</ul>
				@else
					<p>There are no outages at this time.</p>
				@endif

				<div class="more">
					<a href="{{ route('site.news.type', ['name' => $type->id]) }}">previous…</a>
				</div>
			</div>
		</div><!-- /.tileRow -->
	</div><!-- /.audienceTiles -->

	<div class="audienceTiles col-lg-4 col-md-6 col-sm-12 col-xs-12 tiles-1">
@endif

		<div class="tileRow">
			<div class="tileContainer col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<h2 class="tile">Upcoming Maintenance</h2>

				@if (count($maintenance))
					<ul class="newslist">
						@foreach ($maintenance as $item)
							<li class="first">
								<a href="{{ route('site.news.show', ['id' => $item->id]) }}">{{ $item->headline }}</a>
								<p class="date">
									<span class="text-nowrap">{{ $item->formatDate($item->datetimenews, $item->datetimenewsend) }}</span> 
									@if ($item->isToday())
										<span class="badge badge-info">Today</span>
									@endif
								</p>
								@if ($update = $item->updates()->orderBy('datetimecreated', 'desc')->first())
									<p class="newsupdated">Updated: {{ $update->datetimecreated->format('M d, Y h:ia') }}</p>
								@endif
							</li>
						@endforeach
					</ul>
				@else
					<p>There is no upcoming maintenance scheduled at this time.</p>
				@endif

				<div class="more">
					<a href="{{ route('site.news.type', ['name' => $type2->id]) }}">previous…</a>
				</div>
			</div>
		</div><!-- /.tileRow -->
	</div><!-- /.audienceTiles -->
