<template>
	<div class="w-full rounded-md bg-gray-7 px-7 pb-8 pt-7 text-center text-white">
		<h3 class="mb-4 text-xl font-semibold capitalize">
			<slot name="title">
				{{ $t('help.need-additional-help') }}
			</slot>
		</h3>
		<p class="mb-7 text-xs font-light">
			<slot>
				<lorem add="15w" />
			</slot>
		</p>
		<Button class="brand w-full py-3">
			<slot name="button">
				{{ $t('help.learn-more') }}
			</slot>
		</Button>
	</div>
</template>

<script lang="ts" setup>
import Button from '@/Components/Dashboard/Buttons/Button.vue';
</script>
