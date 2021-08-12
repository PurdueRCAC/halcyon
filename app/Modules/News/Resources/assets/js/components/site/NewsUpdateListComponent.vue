<template>
	<li>
		<div class="card panel panel-default">
			<div class="card-header panel-heading news-admin">
				<span class="newsid">#{{ id }}</span>
				<a class="edit news-update-delete" href="/news/manage?delete&amp;update=121" id="121_deleteicon" title="Delete update." v-if="canDelete">
					<span class="fa fa-x" aria-hidden="true"></span>
					Delete
				</a>
			</div>
			<div class="card-body panel-body crmcomment crmcommenttext" v-html="formattedbody">
			</div>
			<div class="card-footer panel-footer">
				<div class="crmcommentpostedby text-right">
					<formatted-date-time :rawDateTime="datetimecreated"></formatted-date-time>
				</div>
			</div>
		</div>
	</li>
</template>

<script>
	import FormattedDateTime from './FormattedDateTimeComponent.vue';

	export default {
		mounted() {
			console.log('Component mounted.')
		},
		props: [
			'id',
			'formattedbody',
			'datetimecreated',
			'datetimeremoved',
			'canEdit',
			'canDelete'
		],
		components: {
			FormattedDateTime
		}
	}
</script>
