<template>
	<li>
		<div class="panel panel-default">
			<div class="panel-heading news-admin">
				<span class="newsid">#{{ id }}</span>
				<a class="edit news-update-delete" href="/news/manage?delete&amp;update=121" id="121_deleteicon" title="Delete update." v-if="canDelete">
					<i class="fa fa-x" aria-hidden="true"></i>
					Delete
				</a>
			</div>
			<div class="panel-body crmcomment crmcommenttext" v-html="formattedbody">
			</div>
			<div class="panel-footer">
				<div class="crmcommentpostedby">Posted by PErson on {{ datetimecreated }}</div>
			</div>
		</div>
	</li>
</template>

<script>
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
	}
</script>
