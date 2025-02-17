<template v-once>
	<textarea v-if="mode === 'textarea'" />
	<TextInput v-else :value="value" class="customLook !h-fit" :readonly="readonly" v-on:change="onChange" />
</template>

<style scoped></style>

<script>
import Tagify from '@yaireo/tagify';
import '@yaireo/tagify/dist/tagify.css';
import TextInput from '@/Components/Dashboard/TextInput.vue';

export default {
	name: 'Tags',
	components: { TextInput },
	props: {
		mode: String,
		settings: Object,
		value: [String, Array],
		onChange: Function,
		readonly: Boolean,
		disabled: {
			type: Boolean,
			default: false,
		},
	},
	watch: {
		value(newVal, oldVal) {
			this.tagify.loadOriginalValues(newVal);
		},
		disabled(newValue) {
			if (this.tagify && typeof this.tagify.setDisabled === 'function') {
				this.tagify.setDisabled(newValue);
			}
		},
	},
	mounted() {
		this.tagify = new Tagify(this.$el, this.settings);
		this.tagify.setDisabled(this.disabled);
	},
};
</script>
