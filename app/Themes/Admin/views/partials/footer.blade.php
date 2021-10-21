				<footer id="footer">
					<section class="basement">
						<p class="copyright">
							{!! trans('theme::admin.copyright', ['name' => config('app.name'), 'url' => url()->to('/'), 'date' => gmdate("Y")]) !!}
						</p>
						<p class="promotion">
							{!! trans('theme::admin.powered by', ['v' => 1]) !!}
						</p>
					</section><!-- / .basement -->
				</footer><!-- / #footer -->
