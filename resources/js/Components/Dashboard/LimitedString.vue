<template>
	<div v-if="limitString(string ?? '', limit) === (string ?? '') || status === true">
		<p>{{ string }}</p>
		<p
			v-if="limitString(string ?? '', limit) !== (string ?? '')"
			class="text-brand cursor-pointer text-xs font-semibold text-brand-base"
			@click="$emit('toggle')">
			{{ $t('rate.hide') }}
		</p>
	</div>
	<div v-else>
		<p>{{ limitString(string ?? '', limit) }}...</p>
		<p
			v-if="limitString(string ?? '', limit) !== (string ?? '')"
			class="text-brand cursor-pointer text-xs font-semibold text-brand-base"
			@click="$emit('toggle')">
			{{ $t('rate.see-more') }}
		</p>
	</div>
</template>

<script lang="ts" setup>
const props = defineProps<{
	string: String;
	limit: number;
	status: boolean;
}>();

const emit = defineEmits<{
	(e: 'toggle'): void;
}>();

function limitString(string: String, limit: Number): string {
	if (string.length <= limit) {
		return string;
	}
	let resultWords: Array<String> = [];
	let resultLength: number = -1;
	let words: string[] = string.split(' ');
	words.every((word): boolean => {
		if (resultLength + word.length > limit) {
			return false;
		}
		resultWords.push(word);
		resultLength += word.length + 1;
		return true;
	});

	return resultWords.join(' ');
}
</script>
