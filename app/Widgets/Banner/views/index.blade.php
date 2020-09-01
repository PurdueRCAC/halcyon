<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */
?>
<?php if (count($outages) == 0) { ?>
	<div class="bannerContainer col-lg-8 col-md-8 hidden-s-down hidden-xs-down">
		<div id="wrapper-promo-home">
			<div id="promo-home">
				<div id="myGallery">
					<ul class="bxslider">
						<li>
							<div class="multiple">
								<a href="/services/communityclusters/"><img src="{{ asset('files/promo/2017/brown.png') }}" alt="Brown HPC Cluster"/></a>
								<a href="/compute/snyder/"><img src="{{ asset('files/promo/2017/snyder.png') }}" alt="Snyder Big Memory Cluster"/></a>
								<a href="/services/communityclusters/"><img src="{{ asset('files/promo/2017/workbench.png') }}" alt="Data Workbench"/></a>
							</div>
						</li>
						<li>
							<a href="{{ url('/storage/depot') }}">
								<img src="{{ asset('files/promo/depot/large.png') }}" alt="Research Data Depot" />
							</a>
						</li>
						<li> 
							<a href="{{ url('/coffee') }}">
								<img src="{{ asset('files/promo/coffee/large.jpg') }}" alt="Research Computing Coffee Consultations" />
							</a>
						</li>
						<li> 
							<a href="{{ url('/news/117') }}">
								<img src="{{ asset('files/promo/catlin/large.jpg') }}" alt="Science Highlights" />
							</a>
						</li>
						<li> 
							<a href="https://www.xsede.org/">
								<img src="{{ asset('files/promo/xsede/large.jpg') }}" alt="Extreme Science and Engineering Discovery Environment" />
							</a>
						</li>
						<li> 
							<a href="{{ url('/news/114') }}">
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
							<a href="{{ url('/services/communityclusters') }}">
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
<?php } else { ?>
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

				<?php if (count($outages)) { ?>
					<ul class="newslist">
						<?php foreach ($outages as $item) { ?>
							<li class="first">
								<a href="{{ route('site.news.show', ['id' => $item->id]) }}">{{ $item->headline }}</a>
								<p class="date">
									<span style="white-space:nowrap">{{ $item->formatDate($item->datetimenews, $item->datetimenewsend) }}</span> 
									<?php if ($item->isToday()) { ?>
										<span class="badge badge-info">Today</span>
									<?php } ?>
								</p>
								<?php
								$update = $item->updates()->orderBy('datetimecreated', 'desc')->first();
								if ($update) { ?>
									<p class="newsupdated">Updated: {{ $update->datetimecreated->format('') }}</p>
								<?php } ?>
							</li>
						<?php } ?>
					</ul>
				<?php } else { ?>
					<p>There are no outages at this time.</p>
				<?php } ?>

				<div class="more">
					<a href="{{ route('site.news.type', ['name' => $type->id]) }}">previous…</a>
				</div>
			</div>
		</div><!-- /.tileRow -->
	</div><!-- /.audienceTiles -->

	<div class="audienceTiles col-lg-4 col-md-6 col-sm-12 col-xs-12 tiles-1">
<?php } ?>

		<div class="tileRow">
			<div class="tileContainer col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<h2 class="tile">Upcoming Maintenance</h2>

				<?php if (count($maintenance)) { ?>
					<ul class="newslist">
						<?php foreach ($maintenance as $item) { ?>
							<li class="first">
								<a href="{{ route('site.news.show', ['id' => $item->id]) }}">{{ $item->headline }}</a>
								<p class="date">
									<span style="white-space:nowrap">{{ $item->formatDate($item->datetimenews, $item->datetimenewsend) }}</span> 
									<?php if ($item->isToday()) { ?>
										<span class="badge badge-info">Today</span>
									<?php } ?>
								</p>
								<?php
								$update = $item->updates()->orderBy('datetimecreated', 'desc')->first();
								if ($update) { ?>
									<p class="newsupdated">Updated: {{ $update->datetimecreated->format('') }}</p>
								<?php } ?>
							</li>
						<?php } ?>
					</ul>
				<?php } else { ?>
					<p>There is no upcoming maintenance scheduled at this time.</p>
				<?php } ?>

				<div class="more">
					<a href="{{ route('site.news.type', ['name' => $type2->id]) }}">previous…</a>
				</div>
			</div>
		</div><!-- /.tileRow -->
	</div><!-- /.audienceTiles -->
