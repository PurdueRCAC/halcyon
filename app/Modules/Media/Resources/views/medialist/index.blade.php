<?php
$base = url('/');

$filters = array(
	'limit' => config('module.media.list_limit', 100),
	'page'  => 1,
);
$filters['page'] = request()->input('page', 1);
$filters['start'] = ($filters['limit'] * $filters['page']) - $filters['limit'];

$children = array_merge($children['folders'], $children['files']);
$total    = count($children);
$children = array_slice($children, $filters['start'], $filters['limit']);

$paginator = new \Illuminate\Pagination\LengthAwarePaginator($children, $total, $filters['limit'], $filters['page']);
$paginator->withPath(route('admin.media.index', ['folder' => $folder, 'layout' => $layout]));
?>

<?php ($active = $layout == 'list' ? true : false); ?>
@include('media::medialist.list')

<?php ($active = $layout == 'thumbs' ? true : false); ?>
@include('media::medialist.thumbs')

@if ($total > $filters['limit'])
	<div class="media-pagination">
		<?php echo $paginator->render(); ?>
	</div>
@endif
<input type="hidden" name="page" id="page" value="{{ $filters['page'] }}" />

<div class="spinner d-none">
	<div class="spinner-border" role="status">
		<span class="sr-only visually-hidden">{{ trans('global.loading') }}</span>
	</div>
</div>