<template>
	<template v-if="current && current.external_id">
		<Card v-if="current.activated">
			<template #header>
				<div class="flex items-center justify-between">
					<h2 class="text-2xl font-semibold">{{ $t('online-payments.stripe.active.title') }}</h2>
				</div>
			</template>
			<template #description>
				<h3 class="text-lg font-normal">
					{{ $t('online-payments.stripe.active.desc') }}
					<div class="flex flex-wrap sm:space-x-2">
						<Button
							type="a"
							target="_blank"
							href="https://dashboard.stripe.com/login"
							class="mt-5 inline-flex w-full !bg-[#5433FF] text-base sm:w-fit">
							{{ $t('online-payments.stripe.login') }}
							<img alt="" class="-mr-2 h-7" src="@as/images/payments/stripe.png" />
						</Button>
						<Button
							@click="
								showDialog(capitalize($t('main.are-you-sure')), () => {
									router.visit(route('club.online-payments.disconnect', { type: 'stripe' }), {
										method: 'get',
										preserveScroll: true,
										preserveState: true,
									});
								})
							"
							class="danger mt-5 inline-flex w-full text-base sm:w-fit">
							{{ $t('online-payments.stripe.disconnect') }}
							<img alt="" class="-mr-2 h-7" src="@as/images/payments/stripe.png" />
						</Button>
					</div>
				</h3>
			</template>
		</Card>
		<Card v-else>
			<template #header>
				<h2 class="text-2xl font-semibold">{{ $t('online-payments.stripe.connecting.title') }}</h2>
			</template>
			<template #description>
				<h3 class="text-lg font-normal">
					{{ $t('online-payments.stripe.connecting.desc') }}
				</h3>
			</template>

			<Link
				:href="route('club.online-payments.connect', { type: 'stripe' })"
				class="inline-flex h-12 items-center self-start rounded bg-[#635bff] px-8 py-2 font-semibold text-white transition-colors duration-150 hover:bg-[#4462c9] hover:shadow-inner active:bg-[#21376f]">
				{{ $t('online-payments.stripe.continue-connection') }}
				<img alt="" class="-mr-2 h-7" src="@as/images/payments/stripe.png" />
			</Link>
		</Card>
	</template>
	<template v-else>
		<Card>
			<template #header>
				<h2 class="text-2xl font-semibold">{{ $t('online-payments.stripe.not-connected.title') }}</h2>
			</template>
			<template #description>
				<h3 class="text-lg font-normal">
					{{ $t('online-payments.stripe.not-connected.desc') }}
				</h3>
			</template>
			<template #default>
				<Link
					:href="route('club.online-payments.connect', { type: 'stripe' })"
					class="inline-flex h-12 items-center self-start rounded bg-[#635bff] px-8 py-2 font-semibold text-white transition-colors duration-150 hover:bg-[#4462c9] hover:shadow-inner active:bg-[#21376f]">
					{{ $t('online-payments.stripe.connect') }}
					<img alt="" class="-mr-2 h-7" src="@as/images/payments/stripe.png" />
				</Link>
			</template>
		</Card>
	</template>
	<DecisionModal
		:showing="confirmationDialogShowing"
		@confirm="confirmDialog()"
		@decline="cancelDialog()"
		@close="confirmationDialogShowing = false">
		{{ dialogContent }}
	</DecisionModal>
</template>

<script lang="ts" setup>
import { Link, router } from '@inertiajs/vue3';
import { PaymentMethod } from '@/Types/models';
import Card from '@/Components/Dashboard/Card.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import DecisionModal from '@/Components/Dashboard/Modals/DecisionModal.vue';
import { useConfirmationDialog } from '@/Composables/useConfirmationDialog';
import { useString } from '@/Composables/useString';

const props = defineProps<{
	current: PaymentMethod;
}>();
const { capitalize } = useString();

const { confirmationDialogShowing, showDialog, cancelDialog, confirmDialog, dialogContent } =
	useConfirmationDialog();
</script>

<style scoped></style>
