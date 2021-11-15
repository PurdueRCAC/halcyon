<template>
	<select class="form-control searchable-select-multi" multiple="multiple" v-bind:name="name" v-bind:id="id">
		<option v-for="option in options" v-bind:value="option.id">
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
				//console.log('Retrieving resources...');
				this.mute = true;
				window.axios.get(this.ROOT_URL + '/api/resources', {
					params: {
						limit: 50
					}
				}).then(({ data }) => {
					this.options = [];
					data.data.forEach(datum => {
						this.options.push(datum);
					});
					this.total = data.meta.total;
					this.mute = false;
				});
			}
		},
		mounted() {
			this.read();

			var vm = this;

			$(this.$el)
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
				});
		}
	}
</script>
