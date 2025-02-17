<template>
	<WidgetAlert>
		<template #title>
			{{ $t('calendar.pay-attention') }}
		</template>
		<template #description>
			{{ $t('calendar.select-what-you-want-to-book') }}
		</template>
	</WidgetAlert>
</template>

<script lang="ts" setup>
import WidgetAlert from '@/Components/Widget/Ui/WidgetAlert.vue';
</script>
