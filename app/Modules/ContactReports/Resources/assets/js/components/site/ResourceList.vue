<template>
	<select class="form-control searchable-select-multi" multiple="multiple" v-bind:name="name" v-bind:id="id">
		<option v-for="(option, i) in options" v-bind:key="i" v-bind:value="option.id">
			{{ option.name }}
		</option>
	</select>
</template>

<script>
	export default {
		data() {
			return {
				options: [],
				working: false,
				total: 0
			}
		},
		props: {
			name: String,
			id: String
		},
		methods: {
			read() {
				let self = this;
				//console.log('Retrieving resources...');
				this.mute = true;

				fetch(window.ROOT_URL + '/api/resources?limit=50', {
					method: 'GET',
					headers: window.fetch_headers
				})
				.then(function (response) {
					return response.json();
				})
				.then(function (data) {
					self.options = [];
					data.data.forEach(function (datum) {
						self.options.push(datum);
					});
					self.total = data.meta.total;
					self.mute = false;
				})
				.catch(function (error) {
					console.log(error);
				});
	
				/*window.axios.get(window.ROOT_URL + '/api/resources', {
					params: {
						limit: 50
					}
				}).then(({ data }) => {
					this.options = [];
					data.data.forEach(function (datum) {
						this.options.push(datum);
					});
					this.total = data.meta.total;
					this.mute = false;
				});*/
			}
		},
		mounted() {
			this.read();

			var vm = this;

			/*$(this.$el)
				// init select2
				.select2({
					multiple: true,
					closeOnSelect: false,
					templateResult: function (item) {
						if (typeof item.children != 'undefined') {
							//var s = $(item.element).find('option').length - $(item.element).find('option:selected').length;
							var el = $('<button class="btn btn-sm btn_select2_optgroup" data-group="' + item.text + '">Select All</span>');

							// Click event
							el.on('click', function (e) {
								e.preventDefault();
								// Select all optgroup child if there aren't, else deselect all
								rselects.find('optgroup[label="' + $(this).data('group') + '"] option').prop(
									'selected',
									$(item.element).find('option').length - $(item.element).find('option:selected').length
								);

								// Trigger change event + close dropdown
								rselects.trigger('change.select2');
								rselects.select2('close');
								CRMSearch();
							});

							var elp = $('<span class="my_select2_optgroup">' + item.text + '</span>');
							elp.append(el);

							return elp;
						}
						return item.text;
					}
				})
				//.val(this.value)
				.trigger("change")
				// emit event on change.
				.on("change", function() {
					vm.$emit("input", this.value);
				});*/
			let sel = new TomSelect(this.$el, {
				//maxItems: 1,
				valueField: 'id',
				labelField: 'name',
				searchField: ['name', 'rolename', 'listname'],
				plugins: ['remove_button'],
				persist: false,
				// Fetch remote data
				load: function (query, callback) {
					var url = this.$el.getAttribute('data-api') + '?api_token=' + document.querySelector('meta[name="api-token"]').getAttribute('content') + '&search=' + encodeURIComponent(query);

					fetch(url)
						.then(response => response.json())
						.then(json => {
							callback(json.data);
						}).catch(() => {
							callback();
						});
				}
			});
			sel.on('change', function () {
				
			});
		}
	}
</script>
