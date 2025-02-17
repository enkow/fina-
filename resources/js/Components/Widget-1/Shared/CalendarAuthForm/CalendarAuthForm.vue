<template>
	<form class="space-y-4 text-lg" @submit.prevent="onSubmit">
		<slot />
		<WidgetButton type="submit" fill>
			{{ buttonTitle }}
			<ArrowRightIcon />
		</WidgetButton>
		<!-- <p class="text-center text-ui-black/60">{{ $t("calendar.or-sign-in-with") }}</p> -->
		<!-- <ul class="flex justify-center gap-x-7.5">
      <li>
        <CalendarAuthFormSocialButton label="Continue with facebook" social="facebook" />
      </li>
      <li>
        <CalendarAuthFormSocialButton label="Continue with google" social="google" />
      </li>
      <li>
        <CalendarAuthFormSocialButton label="Continue with apple" social="apple" />
      </li>
    </ul> -->
	</form>
</template>

<script lang="ts" setup>
import WidgetButton from '@/Components/Widget/Ui/WidgetButton.vue';
import ArrowRightIcon from '@/Components/Widget-1/Icons/ArrowRightIcon.vue';
// import CalendarAuthFormSocialButton from "./CalendarAuthFormSocialButton.vue";

defineProps<{
	onSubmit?: () => void;
	buttonTitle: string;
}>();
</script>
