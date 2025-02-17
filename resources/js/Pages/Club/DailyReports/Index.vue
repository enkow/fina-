<template>
	<PanelLayout
		:breadcrumbs="[
			{
				href: route('club.statistics.main'),
				label: $t('settlement.daily-reports'),
			},
		]"></PanelLayout>
</template>

<script lang="ts" setup>
import PanelLayout from '@/Layouts/PanelLayout.vue';
</script>
