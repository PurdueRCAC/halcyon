<template>
	<div id="news">
		<form method="get" action="/contactreports" class="editform">
			<fieldset>
				<legend>Search Contact Reports</legend>

				<div class="form-group row" id="TR_date">
					<label for="datestartshort" class="col-sm-2 col-form-label">Date from</label>
					<div class="col-sm-4">
						<div class="input-group">
							<span class="input-group-addon"><span class="input-group-text fa fa-calendar" aria-hidden="true"></span></span>
							<input id="datestartshort" type="text" class="date-pick form-control" name="start" placeholder="YYYY-MM-DD" data-start="" value="" />
							<input id="timestartshort" type="text" class="time-pick form-control hide" name="starttime" value="" />
						</div>
					</div>

					<label for="datestopshort" class="col-sm-2 col-form-label align-right">Date to</label>
					<div class="col-sm-4">
						<div class="input-group" id="enddate">
							<span class="input-group-addon"><span class="input-group-text fa fa-calendar" aria-hidden="true"></span></span>
							<input id="datestopshort" type="text" class="date-pick form-control" name="stop" placeholder="YYYY-MM-DD" data-stop="" value="">
							<input id="timestopshort" type="text" class="time-pick form-control hide" name="stoptime" value="" />
						</div>
					</div>
				</div>
				<div class="form-group row" id="TR_newstype">
					<label for="newstype" class="col-sm-2 col-form-label">Type</label>
					<div class="col-sm-10">
						<select id="newstype" name="newstype" class="form-control">
							<option id="OPTION_all" name="all" value="-1">All</option>
						</select>
					</div>
				</div>
				<div class="form-group row" id="TR_keywords">
					<label for="keywords" class="col-sm-2 col-form-label">Keywords</label>
					<div class="col-sm-10">
						<input type="text" v-model="keywords" v-on:keyup="read" name="keyword" id="keywords" size="45" class="form-control" value="" />
					</div>
				</div>
				<div class="form-group row" id="TR_resource">
					<label for="newsresource" class="col-sm-2 col-form-label">Resource</label>
					<div class="col-sm-10">
						<resource-list name="resource" id="newsresource">
						</resource-list>
					</div>
				</div>
				<div class="form-group row" id="TR_id">
					<label for="id" class="col-sm-2 col-form-label">NEWS#</label>
					<div class="col-sm-10">
						<input name="id" type="text" id="id" size="45" class="form-control" value="" />
					</div>
				</div>
				<div class="form-group row" id="TR_search">
					<div class="col-sm-2">
					</div>
					<div class="col-sm-10 offset-sm-10">
						<input type="submit" class="btn btn-primary" value="Search" id="INPUT_search" />
						<input type="reset" class="btn btn-default" value="Clear" id="INPUT_clear" />
					</div>
				</div>

				<span id="TAB_search_action"></span>
				<span id="TAB_add_action"></span>
			</fieldset>
		</form>

		<p id="matchingnews">Found {{ total }} matching Reports</p>

		<crm-report
			v-for="report in reports"
			v-bind="report"
			:key="report.id"
			@update="update"
			@delete="del"
		></crm-report>
	</div>
</template>

<script>
	import CrmReport from './CrmReport.vue';
	import ResourceList from './ResourceList.vue';

    export default {
		data() {
			return {
				reports: [],
				working: false,
				total: 0,
				keywords: ''
			}
		},
		methods: {
			create() {
				console.log('Creating report');
				this.mute = true;
				window.axios.post(this.ROOT_URL + '/api/contactreports/create').then(({ data }) => {
					this.reports.push(datum); //new Article(data));
					this.mute = false;
				});
			},
			read() {
				console.log('Retrieving reports...');
				this.mute = true;
				window.axios.get(this.ROOT_URL + '/api/contactreports', {
					params: {
						search: this.keywords
					}
				}).then(({ data }) => {
					this.reports = [];
					data.data.forEach(datum => {
						this.reports.push(datum); //new Article(datum));
					});
					//console.log(this.reports);
					this.total = data.meta.total;
					this.mute = false;
				});
			},
			update(id, color) {
				console.log('Updating report #' + id);
				this.mute = true;
				window.axios.put(`${this.ROOT_URL}/api/contactreports/${id}`, { color }).then(() => {
					this.reports.find(datum => datum.id === id).color = color;
					this.mute = false;
				});
			},
			del(id) {
				console.log('Deleting report #' + id);
				this.mute = true;
				window.axios.delete(`${this.ROOT_URL}/api/contactreports/${id}`).then(() => {
					let index = this.reports.findIndex(datum => datum.id === id);
					this.reports.splice(index, 1);
					this.mute = false;
				});
			}
		},
		watch: {
			mute(val) {
				document.getElementById('mute').className = val ? "on" : "";
			}
		},
		components: {
			CrmReport,
			ResourceList
		},
		created() {
			//this.read();
		},
		mounted() {
			this.read();
		}
	}
</script>
