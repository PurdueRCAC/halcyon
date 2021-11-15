<template>
	<div v-bind:id="'report-' + id">
		<div class="card">
			<div class="card-header" v-if="can.edit || can.delete">
				<span class="newsid">
					#{{ id }}
				</span>
				<a v-bind:href="this.ROOT_URL + '/contactreports/?edit&amp;id=' + id" v-if="can.edit">
					<span class="fa fa-pencil" aria-hidden="true"></span>
					Edit
				</a>
				<a v-bind:href="this.ROOT_URL + '/contactreports/?delete&amp;id=' + id" class="edit news-delete" v-on:click="del($event)" v-if="can.delete">
					<span class="fa fa-trash" aria-hidden="true"></span>
					Delete
				</a>
			</div>
			<div class="card-header">
				<h3 class="card-title">
					{{ formatteddate }}
				</h3>
				<ul class="card-meta">
					<li v-if="resources.length > 0">
						<span class="fa fa-tags fa-1x" aria-hidden="true"></span>
						<span v-html="formattedResources">
						</span>
					</li>
					<li v-if="type">
						<span class="fa fa-folder fa-1x" aria-hidden="true"></span>
						{{ type.name }}
					</li>
				</ul>
			</div>
			<div class="card-body" v-html="formattedreport">
			</div>
			<div class="card-footer">
				<ul v-if="users.length > 0">
					<li v-for="user in users" :key="user.userid">
						{{ user.name }}
					</li>
				</ul>
			</div>
		</div>

		<ul class="comment">
			<crm-comment
				v-for="comment in comments"
				v-bind="comment"
				:key="comment.id"></crm-comment>
		</ul>
	</div>
</template>

<script>
	import CrmComment from './CrmComment.vue';

	export default {
		props: [
			'id',
			'headline',
			'datetimecreated',
			'datetimecontact',
			'formattedreport',
			'formatteddate',
			'type',
			'api',
			'uri',
			'can',
			'resources',
			'tags',
			'users',
			'comments'
		],
		methods: {
			update(val) {
				this.$emit('update', this.id, val.target.selectedOptions[0].value);
			},
			del(event) {
				event.preventDefault();
				this.$emit('delete', this.id);
			}
		},
		computed: {
			formattedResources() {
				let resourcesList = [];
				for (let idx = 0; idx < this.resources.length; idx++) {
					resourcesList.push(this.resources[idx].name);
				}
				return '<span class="badge badge-info">' + resourcesList.join('</span>, <span class="badge badge-info">') + '</span>';
			}
		},
		components: {
			'crm-comment': CrmComment
		},
		filters: {
			properCase(string) {
				return string.charAt(0).toUpperCase() + string.slice(1);
			}
		}
	}
</script>
