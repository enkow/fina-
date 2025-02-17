<template>
	<Head title="Widget" />
	<div class="flex min-h-screen w-full items-center justify-center bg-transparent">
		<!-- Map start -->
		<Transition>
			<div
				v-if="widgetStore.displayedClubMapUrl.length"
				class="absolute top-0 z-30 flex h-full w-full items-center justify-center overflow-hidden bg-gray-2/40">
				<div
					v-click-away="widgetStore.closeMap"
					class="border-widget text-widget relative flex max-h-[625px] w-full max-w-[769px] justify-center rounded border-[3px] bg-white py-10 shadow-lg">
					<img ref="zoomable" :src="widgetStore.displayedClubMapUrl" class="max-h-[545px]" />
					<a :href="widgetStore.displayedClubMapUrl" target="_blank">
						<svg
							class="absolute left-2 top-2 h-6 w-6 cursor-pointer"
							xmlns="http://www.w3.org/2000/svg"
							height="512"
							viewBox="0 0 25 25"
							width="512">
							<g>
								<path
									d="m10.354 14.646c-.195-.195-.512-.195-.707 0l-3.147 3.147v-2.793c0-.276-.224-.5-.5-.5s-.5.224-.5.5v4c0 .065.013.13.039.191.05.122.148.22.271.271.06.024.125.038.19.038h4c.276 0 .5-.224.5-.5s-.224-.5-.5-.5h-2.793l3.146-3.146c.196-.196.196-.512.001-.708z" />
								<path
									d="m19.191 5.538c-.061-.024-.126-.038-.191-.038h-4c-.276 0-.5.224-.5.5s.224.5.5.5h2.793l-3.146 3.146c-.195.195-.195.512 0 .707.097.098.225.147.353.147s.256-.049.354-.146l3.146-3.147v2.793c0 .276.224.5.5.5s.5-.224.5-.5v-4c0-.065-.013-.13-.039-.191-.05-.122-.148-.22-.27-.271z" />
								<path
									d="m20 2.5h-15c-1.378 0-2.5 1.121-2.5 2.5v15c0 1.379 1.122 2.5 2.5 2.5h15c1.378 0 2.5-1.121 2.5-2.5v-15c0-1.379-1.122-2.5-2.5-2.5zm1.5 17.5c0 .827-.673 1.5-1.5 1.5h-15c-.827 0-1.5-.673-1.5-1.5v-15c0-.827.673-1.5 1.5-1.5h15c.827 0 1.5.673 1.5 1.5z" />
							</g>
						</svg>
					</a>

					<XIcon class="absolute right-2 top-2 h-5 w-5 cursor-pointer" @click="widgetStore.closeMap()" />
				</div>
			</div>
		</Transition>
		<!-- Map end -->

		<!-- Alert start -->
		<div
			v-if="widgetStore.alertContent.length"
			class="absolute z-30 flex h-full w-full items-center justify-center overflow-hidden bg-gray-2/40"
			@click="widgetStore.closeAlert()">
			<div class="border-widget relative max-w-full rounded border-[3px] bg-white px-7 py-5 shadow-lg">
				{{ widgetStore.alertContent }}
				<XIcon class="absolute right-2 top-2 h-3 w-3 cursor-pointer" />
			</div>
		</div>
		<!-- Alert end -->

		<div
			class="relative mx-1.5 mb-8 mt-6 flex h-[625px] w-[calc(100%-12px)] items-end md:h-[625px] md:w-[769px]">
			<div class="border-widget h-[625px] w-full rounded border border-4 bg-white md:h-[600px] md:w-[769px]">
				<div v-if="widgetStore.club.widget_enabled">
					<StepIndicatorTop v-if="!props.withoutNavigation" />
					<AccountActions :without-logout="withoutLogout" />

					<ReservationDetails v-if="withReservationDetails && !props.withoutNavigation" />
					<div class="relative">
						<FullLoader />
						<slot name="header" />
						<div
							class="relative flex h-100 flex-col justify-between space-y-5 px-2 pt-2.5 xxs:px-4.75 sm:pt-5 md:px-8">
							<slot />
						</div>
					</div>

					<BottomNavigation v-if="!props.withoutNavigation" class="absolute z-20 bottom-0 w-full" />
					<div v-if="!props.withoutNavigation" class="absolute z-20 -bottom-5 flex w-full justify-center">
						<StepIndicatorBottom />
					</div>
				</div>
				<div class="flex h-full w-full items-center justify-center px-10 text-center" v-else>
					Przykro nam, ale system rezerwacji online jest obecnie niedostępny. Spróbuj później lub umów się
					telefonicznie.
				</div>
			</div>
		</div>
	</div>
</template>

<style>
body {
	background: transparent;
}

button {
	border-color: v-bind(widgetColor);
}

h2 {
	@apply mb-2.5 mt-0 text-sm font-extrabold uppercase;
}

.text-widget {
	color: v-bind(widgetColor);
}

.hover-bg-widget:hover {
	background: v-bind(widgetColor);
}

.border-widget {
	border-color: v-bind(widgetColor);
	color: v-bind(widgetColor);
}

.widget-header {
	@apply mb-2 text-xl font-bold uppercase;
}

.widget-input-group {
	@apply !w-60 md:!w-64;

	.widget-input {
		@apply mb-1 h-6 !w-60 pl-0;
	}

	.widget-input::placeholder {
		@apply opacity-20;
	}

	.widget-error {
		@apply text-xxs text-danger-base;
	}
}

.widget-button {
	@apply flex h-9 w-48 cursor-pointer items-center rounded-md border-[3px] text-base font-bold uppercase transition-all;
	border-color: v-bind(widgetColor);
}

.widget-button:hover {
	@apply text-white;
	background-color: v-bind(widgetColor);
}

.widget-button:disabled {
	@apply cursor-not-allowed opacity-20;
}

.widget-button:active {
	@apply opacity-60;
}

.v-enter-active,
.v-leave-active {
	transition: opacity 0.2s ease;
}

.v-enter-from,
.v-leave-to {
	opacity: 0;
}
</style>

<script lang="ts" setup>
import { Head } from '@inertiajs/vue3';
import 'vue-toastification/dist/index.css';
import { useWidgetStore } from '@/Stores/widget';
import StepIndicatorTop from '@/Components/Widget-3/StepIndicatorTop.vue';
import AccountActions from '@/Components/Widget-3/AccountActions.vue';
import StepIndicatorBottom from '@/Components/Widget-3/StepIndicatorBottom.vue';
import BottomNavigation from '@/Components/Widget-3/BottomNavigation.vue';
import ReservationDetails from '@/Components/Widget-3/Icons/ReservationDetails.vue';
import XIcon from '@/Components/Dashboard/Icons/XIcon.vue';
import PinchZoom from 'pinchzoom';
import { onMounted, ref, watch } from 'vue';
import WidgetUnstyledLoader from '@/Components/Widget/Unstyled/WidgetUnstyledLoader.vue';
import FullLoader from '@/Pages/Widget-3/Partials/FullLoader.vue';

const widgetStore = useWidgetStore();
const widgetColor: string = widgetStore.widgetColor;

const zoomable: any = ref(null);

const props = withDefaults(
	defineProps<{
		withReservationDetails?: boolean;
		withoutNavigation?: boolean;
		withoutLogout?: boolean;
	}>(),
	{
		withReservationDetails: false,
		withoutNavigation: false,
		withoutLogout: false,
	},
);
watch(
	() => widgetStore.displayedClubMapUrl,
	() => {
		setTimeout(() => {
			if (zoomable.value) {
				zoomable.value.onload = () => {
					new PinchZoom(zoomable, {});
				};
			}
		}, 1000);
	},
);
</script>
