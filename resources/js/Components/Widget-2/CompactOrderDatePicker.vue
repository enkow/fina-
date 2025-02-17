<template>
	<div class="relative grid w-full grid-cols-2 items-center sm:grid-cols-3 md:hidden">
		<SectionLabel>Choose date</SectionLabel>
		<OrderDatePicker :inline="false" />
	</div>
</template>

<script lang="ts" setup>
import SectionLabel from './SectionLabel.vue';
import OrderDatePicker from './OrderDatePicker.vue';
</script>
