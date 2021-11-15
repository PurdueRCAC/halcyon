<template>
	<li>
		<div class="card panel panel-default">
			<div class="card-header panel-heading news-admin" v-if="canDelete">
				<!-- <span class="newsid">#{{ id }}</span> -->
				<a class="edit news-update-delete" href="/news/manage?delete&amp;update=121" id="121_deleteicon" title="Delete update.">
					<span class="fa fa-x" aria-hidden="true"></span>
					Delete
				</a>
			</div>
			<div class="card-body panel-body crmcomment crmcommenttext" v-html="formattedbody">
			</div>
			<div class="card-footer panel-footer bg-white">
				<div class="crmcommentpostedby text-right">
				</div>
			</div>
		</div>
	</li>
</template>

<script>
	export default {
		mounted() {
			console.log('Comment Component mounted.')
		},
		props: [
			'id',
			'formattedbody',
			'datetimecreated',
			'datetimeremoved',
			'can',
			'username'
		],
		components: {
		}
	}
</script>
