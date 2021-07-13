<template>
	<article v-bind:id="'news-' + id">
		<div class="panel panel-default">
			<div class="panel-heading news-admin" v-if="canEdit || canDelete">
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
			<div class="panel-heading">
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
					<li class="news-type">
						<span class="newstype">
							{{ type.name }}
							<a v-bind:href="this.ROOT_URL + '/news/manage?edit&amp;id=' + id" v-if="canEdit">
								<span class="fa fa-pencil" aria-hidden="true"></span>
								Edit
							</a>
						</span>
					</li>
				</ul>
			</div>
			<div class="panel-body">
				<div class="newsposttext" v-html="formattedbody">
				</div>
			</div>
			<div class="panel-footer">
				<div class="newspostedby">
					<div class="newspostuser">Posted by Person on {{ datetimenews }}</div>
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
	import NewsUpdate from './NewsUpdate.vue';

	export default {
		methods: {
			update(val) {
				this.$emit('update', this.id, val.target.selectedOptions[0].value);
			},
			del(event) {
				event.preventDefault();
				this.$emit('delete', this.id);
			}
		},
		props: [
			'id',
			'headline',
			'published',
			'formattedbody',
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
		components: {
			NewsUpdate
		},
		filters: {
			properCase(string) {
				return string.charAt(0).toUpperCase() + string.slice(1);
			}
		}
	}
</script>
