<template>
	<div>
	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-4 filter-search">
				<label class="sr-only visually-hidden" for="filter_search">search</label>
				<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="keyword or phrase..." value="" />

				<button class="btn btn-secondary" type="submit">submit</button>
			</div>
			<div class="col col-md-8 filter-select text-right">
				<label class="sr-only visually-hidden" for="filter_state">state</label>
				<select name="state" class="form-control filter filter-submit">
					<option value="*">all states</option>
					<option value="published">published</option>
					<option value="unpublished">unpublished</option>
				</select>

				<label class="sr-only visually-hidden" for="filter-access">access level</label>
				<select name="access" id="filter-access" class="form-control filter filter-submit">
					<option value="*">select level</option>
					<option v-for="(level, i) in levels" :key="i" :value="level.id">{{ level.title }}</option>
				</select>

				<label class="sr-only visually-hidden" for="filter-type">Type</label>
				<select name="type" id="filter-type" class="form-control filter filter-submit">
					<option value="0">all categories</option>
				</select>
			</div>
		</div>
	</fieldset>

	<table class="table table-hover adminlist">
		<thead>
			<tr>
				<th scope="col">
					Check
				</th>
				<th scope="col">
					ID
				</th>
				<th scope="col">
					Headline
				</th>
				<th scope="col">
					State
				</th>
				<th scope="col">
					Type
				</th>
				<th scope="col" colspan="2">
					Publish window
				</th>
				<th scope="col" class="text-right">
					Updates !
				</th>
			</tr>
		</thead>
		<tbody>
			<admin-row
				v-for="row in rows"
				v-bind="row"
				:key="row.id"
			></admin-row>
		</tbody>
	</table>

	<el-table
		:data="rows"
		stripe
		ref="pageTable"
		v-loading.body="working"
		@sort-change="handleSortChange"
		@selection-change="handleSelectionChange">
		<el-table-column type="selection"></el-table-column>
		<el-table-column :label="ID"></el-table-column>
		<el-table-column :label="Name"></el-table-column>
	</el-table>

	<el-pagination
		:page-size="listQuery.limit"
		:hide-on-single-page="true"
		:pager-count="11"
		layout="total, sizes, prev, pager, next, jumper"
		:page-sizes="[20, 50, 100, 150]"
		:total="list.length"
		@current-change="handlePageChange"
		@size-change="handleSizeChange"
	/>
	</div>
</template>

<script>
	/*function Article({ id, color, name}) {
		this.id = id;
		this.color = color;
		this.name = name;
	}*/

	import AdminRow from './AdminRow.vue';

	export default {
		data() {
			return {
				levels: [],
				rows: [],
				working: false,
				total: 0,
				listQuery: {
					page: 1, // Tracks current page
					limit: 20, // Sets limit of items per page
				},
				search: ''
			}
		},
		methods: {
			read() {
				this.mute = true;

				if (!this.levels.length) {
					window.axios.get(window.ROOT_URL + '/api/users/levels', {
					}).then(({ data }) => {
						this.levels = [];
						data.data.forEach(datum => {
							this.levels.push(datum);
						});
					});
				}

				window.axios.get(window.ROOT_URL + '/api/news', {
					params: {
						search: this.keywords
					}
				}).then(({ data }) => {
					this.rows = [];
					data.data.forEach(datum => {
						this.rows.push(datum); //new Article(datum));
					});
					this.total = data.total;
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
			AdminRow
		},
		mounted() {
			this.read();
		}
	}
</script>
