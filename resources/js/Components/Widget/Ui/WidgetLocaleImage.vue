<template>
	<object class="h-4.5 w-6">
		<img :src="source.value" :alt="locale" class="w-full rounded" />
	</object>
</template>

<script lang="ts" setup>
import { useDynamicResource } from '@/Composables/useDynamicResource';
import { computed } from 'vue';

const props = defineProps<{ locale: string }>();

const source = computed(() =>
	useDynamicResource(import(`../../../../assets/images/flags/${props.locale.toLowerCase()}.svg`)),
);
</script>
