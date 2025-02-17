<template>
	<ul class="flex justify-center gap-x-5">
		<li>
			<CalendarSocialButton social="facebook" label="Sign in with facebook" />
		</li>
		<li>
			<CalendarSocialButton social="google" label="Sign in with google" />
		</li>
		<li>
			<CalendarSocialButton social="apple" label="Sign in with apple" />
		</li>
	</ul>
</template>

<script lang="ts" setup>
import CalendarSocialButton from '@/Components/Widget-1/Shared/CalendarSocialButton.vue';
</script>
