<template>
	<article v-bind:id="'news-' + id">
		<div class="card panel panel-default">
			<div class="card-header panel-heading news-admin" v-if="canEdit || canDelete">
				<span class="newsid">
					<a v-bind:href="url">#{{ id }}</a>
				</span>
				<span class="newspublication">
					<a v-if="published == 1" v-bind:href="this.ROOT_URL + '/news/manage?edit&amp;id=' + id" class="badge badge-published" title="Recall news item.">Published</a>
					<a v-else v-bind:href="this.ROOT_URL + '/news/manage?edit&amp;id=' + id" class="badge badge-unpublished" title="Publish news item.">Unpublished</a>
				</span>
				<a v-bind:href="this.ROOT_URL + '/news/manage?delete&amp;id=' + id" class="edit news-delete" v-on:click="del($event)" v-if="canDelete">
					<span class="fa fa-trash" aria-hidden="true"></span>
					Delete
				</a>
				<a v-bind:href="this.ROOT_URL + '/news/manage?mail&amp;id=' + id" class="edit news-mail" id="A_mail_1234" title="Preview mail to mailing lists.">
					<span class="newspostedby">Last sent November 19, 2018&nbsp; 4:28pm by Kevin D Colby</span>
					<span class="fa fa-envelope" aria-hidden="true"></span>
					Preview mail
				</a>
			</div>
			<div class="card-header panel-heading">
				<h3 class="panel-title newsheadline">
					{{ headline }}
				</h3>
				<ul class="panel-meta news-meta">
					<li class="news-date">
						<span class="newsdate">
							<time>{{ formatteddate }}</time>
						</span>
						<a v-bind:href="this.ROOT_URL + '/news/manage?edit&amp;id=' + id" v-if="canEdit">
							<span class="fa fa-pencil" aria-hidden="true"></span>
							Edit
						</a>
					</li>
					<li v-if="resources.length > 0">
						<span>
							<i class="fa fa-tags fa-1x"></i>
						</span>
						<span>
							{{ formattedResources }}
						</span>
					</li>
					<li class="news-type">
						<span class="newstype">
							{{ type.name }}
						</span>
						<a v-bind:href="this.ROOT_URL + '/news/manage?edit&amp;id=' + id" v-if="canEdit">
							<span class="fa fa-pencil" aria-hidden="true"></span>
							Edit
						</a>
					</li>
				</ul>
			</div>
			<div class="card-body panel-body">
				<div class="newsposttext" v-html="formattedbody">
				</div>
			</div>
			<div class="card-footer panel-footer">
				<div class="newspostedby">
					<div class="newspostuser">
						<formatted-date-time :rawDateTime="datetimecreated" :isNewsOriginalPost="true" :username="''"></formatted-date-time>
					</div>
				</div>
			</div>
		</div>

		<ul class="news-updates">
			<news-update
				v-for="update in updates"
				v-bind="update"
				:key="update.id"></news-update>
		</ul>
	</article>
</template>

<script>
	import NewsUpdate from './NewsUpdateListComponent.vue';
	import FormattedDateTime from './FormattedDateTimeComponent.vue'

	export default {
		props: [
			'id',
			'headline',
			'published',
			'formattedbody',
			'datetimecreated',
			'datetimenews',
			'datetimenewsend',
			'formatteddate',
			'type',
			'url',
			'canEdit',
			'canDelete',
			'resources',
			'updates'
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
				for (let idx = 0; idx < this.resources.length; idx++)
					resourcesList.push(this.resources[idx].name);
				return resourcesList.join(", ");
			}
		},
		components: {
			NewsUpdate,
			FormattedDateTime
		},
		filters: {
			properCase(string) {
				return string.charAt(0).toUpperCase() + string.slice(1);
			}
		}
	}
</script>
