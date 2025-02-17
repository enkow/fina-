<template>
	<StatusModal :showing="showing" type="danger" @close="$emit('close')">
		<template #header>
			{{ $t('invoice.unpaid-modal-title') }}
		</template>
		<div class="text-lg" v-html="$t('invoice.unpaid-modal-content')"></div>
	</StatusModal>
</template>

<script lang="ts" setup>
import XIcon from '@/Components/Dashboard/Icons/XIcon.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import StatusModal from '@/Components/Dashboard/Modals/StatusModal.vue';

const props = withDefaults(
	defineProps<{
		showing?: boolean;
		type?: string;
	}>(),
	{
		showing: true,
		type: 'success',
	},
);

const emit = defineEmits<{
	(e: 'close'): void;
}>();

const hide = () => {
	emit('close');
};
</script>
