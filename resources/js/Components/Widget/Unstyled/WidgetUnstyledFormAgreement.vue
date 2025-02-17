<template>
	<component
		:is="hasFile ? 'a' : 'span'"
		v-bind="
			hasFile
				? { href: `/club-assets/agreements/${agreement.file}`, target: '_blank' }
				: { content: agreement.text }
		"
		v-tippy>
		<slot />
	</component>
</template>

<script lang="ts" setup>
import { Agreement } from '@/Types/models';

const { agreement } = defineProps<{
	agreement: Agreement;
}>();

const hasFile = agreement.content_type === 0;
</script>
