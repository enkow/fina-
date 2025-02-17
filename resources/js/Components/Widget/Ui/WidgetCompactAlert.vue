<template>
	<div class="4 flex items-center gap-x-2.5 rounded-2xl bg-ui-green-50 p-5">
		<WarningIcon class="w-5 flex-shrink-0 text-ui-green" />
		<slot />
	</div>
</template>

<script lang="ts" setup>
import WarningIcon from '../Icons/WarningIcon.vue';
</script>
