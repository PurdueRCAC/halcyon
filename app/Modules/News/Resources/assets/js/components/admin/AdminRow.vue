<template>
	<tr v-bind:id="'row-' + id">
		<td>
			<span class="form-check"><input type="checkbox" name="id[]" v-bind:id="'cb' + id" :value="id" class="form-check-input checkbox-toggle" /><label :for="'cb' + id"></label></span>
		</td>
		<td class="priority-5">
			{{ id }}
		</td>
		<td>
			<a v-if="can.edit" href="#edit">
				{{ headline }}
			</a>
			<span v-else>
				{{ headline }}
			</span>
		</td>
		<td>
			<span v-if="can.edit_state">
				<a v-if="published" class="btn btn-sm state published" href="#unpublish">
					published
				</a>
				<a v-else class="btn btn-sm state unpublished" href="#publish">
					unpublished
				</a>
			</span>
			<span v-else>
				<span v-if="published" class="badge state published">
					published
				</span>
				<span v-else class="badge state unpublished">
					unpublished
				</span>
			</span>
		</td>
		<td class="priority-4">
			{{ type.name }}
		</td>
		<td class="priority-4 text-nowrap">
			<time v-if="datetimenews" :datetime="datetimenews">
				{{ datetimenews }}
			</time>
			<span v-else class="none">(none)</span>
		</td>
		<td class="priority-4 text-nowrap">
			<span v-if="datetimenews">
				<time v-if="datetimenewsend" :datetime="datetimenewsend">
					{{ datetimenewsend }}
				</time>
				<span v-else class="never">(never)</span>
			</span>
			<span v-else>
				<span class="none">(none)</span>
			</span>
		</td>
		<td class="priority-4 text-right text-end">
			<a href="#updates">
				{{ updates.length }}
			</a>
		</td>
	</tr>
</template>

<script>
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
			'can',
			'resources',
			'updates'
		],
		filters: {
			properCase(string) {
				return string.charAt(0).toUpperCase() + string.slice(1);
			}
		}
	}
</script>
