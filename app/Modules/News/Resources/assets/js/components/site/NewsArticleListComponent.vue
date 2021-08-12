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
			<div class="card-body panel-body">
				<div class="newsposttext" v-html="formattedbody">
				</div>
			</div>
			<div class="card-footer panel-footer">
				<div class="newspostedby">
					<!-- <div class="newspostuser">Posted by Person on {{ formattedDateTime }}</div> -->
					<formatted-date-time :rawDateTime="datetimenews"></formatted-date-time>
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
		// computed: {
		// 	formattedDateTime() {
		// 		const rawDate = this.datetimenews.substring(0, this.datetimenews.indexOf("T"));
		// 		const rawTime = this.datetimenews.substring(this.datetimenews.indexOf("T") + 1, this.datetimenews.indexOf("Z"));

		// 		const date_list = rawDate.split('-');
		// 		let month_word = null;
		// 		switch (parseInt(date_list[1])) {
		// 			case 1:
		// 				month_word = "January";
		// 				break;
		// 			case 2:
		// 				month_word = "February";
		// 				break;
		// 			case 3:
		// 				month_word = "March";
		// 				break;
		// 			case 4:
		// 				month_word = "April";
		// 				break;
		// 			case 5:
		// 				month_word = "May";
		// 				break;
		// 			case 6:
		// 				month_word = "June";
		// 				break;
		// 			case 7:
		// 				month_word = "July";
		// 				break;
		// 			case 8:
		// 				month_word = "August";
		// 				break;
		// 			case 9:
		// 				month_word = "September";
		// 				break;
		// 			case 10:
		// 				month_word = "October";
		// 				break;
		// 			case 11:
		// 				month_word = "November";
		// 				break;
		// 			case 12:
		// 				month_word = "December";
		// 				break;
		// 		}
		// 		const formattedDate = month_word + " " + parseInt(date_list[2]).toString() + ", " + date_list[0].toString();

		// 		const time_list = rawTime.split(':');
		// 		const amOrPm = (parseInt(time_list[0]) / 12) < 1 ? "am" : "pm"
		// 		const hour = (parseInt(time_list[0]) % 12) > 0 ? (parseInt(time_list[0]) % 12) : 12;
		// 		const minute_str = time_list[1];
		// 		const formattedTime = hour.toString() + ":" + minute_str + amOrPm + " EDT";

		// 		return formattedDate + " " + formattedTime;
		// 	}
		// },
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
