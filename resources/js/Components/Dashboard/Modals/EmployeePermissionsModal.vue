<template>
	<Transition>
		<div v-if="showing" class="fixed left-0 top-0 z-10 h-screen max-h-screen w-full bg-black/50">
			<div
				v-click-away="hide"
				class="relative top-1/2 m-auto w-full -translate-y-1/2 transform rounded-md bg-white px-2 !pb-5.5 pt-8 text-sm sm:w-156 sm:px-5 sm:text-base">
				<div class="px-2 text-base font-semibold text-gray-7">
					{{ $t('employee.employee-permissions') }}
					<div
						class="absolute right-11 top-8.5 cursor-pointer text-gray-3 transition hover:text-gray-7"
						@click="hide">
						<XIcon />
					</div>
				</div>
				<div class="mt-5 font-light">
					<table>
						<thead>
							<tr>
								<th></th>
								<th class="w-24 sm:w-40">
									{{ $t('main.account-type.employee') }}
								</th>
								<th class="w-24 sm:w-40">
									{{ $t('main.account-type.manager') }}
								</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>{{ $t('reservation.plural') }}</td>
								<td>
									<SuccessSquareIcon class="mx-auto" />
								</td>
								<td>
									<SuccessSquareIcon class="mx-auto" />
								</td>
							</tr>
							<tr>
								<td>{{ $t('refund.plural') }}</td>
								<td>
									<SuccessSquareIcon class="mx-auto" />
									<p class="text-center text-xxs leading-4">({{ $t('main.no-edit-permission') }})</p>
								</td>
								<td>
									<SuccessSquareIcon class="mx-auto" />
								</td>
							</tr>
							<tr>
								<td>{{ $t('statistics.plural') }}</td>
								<td>
									<DangerSquareIcon class="mx-auto" />
								</td>
								<td>
									<SuccessSquareIcon class="mx-auto" />
								</td>
							</tr>
							<tr>
								<td>
									<div class="items-center sm:flex">
										<div>{{ $t('main.marketing') }}</div>
										<ChevronRightIcon class="mx-1.5 hidden sm:block" />
										<div>{{ $t('discount-code.plural') }}</div>
									</div>
								</td>
								<td>
									<SuccessSquareIcon class="mx-auto" />
									<p class="text-center text-xxs leading-4">({{ $t('main.no-edit-permission') }})</p>
								</td>
								<td>
									<SuccessSquareIcon class="mx-auto" />
								</td>
							</tr>
							<tr class="align-middle">
								<td>
									<div class="items-center sm:flex">
										<div>{{ $t('main.marketing') }}</div>
										<ChevronRightIcon class="mx-1.5 hidden sm:block" />
										<div>{{ $t('special-offer.plural') }}</div>
									</div>
								</td>
								<td>
									<SuccessSquareIcon class="mx-auto" />
									<p class="text-center text-xxs leading-4">({{ $t('main.no-edit-permission') }})</p>
								</td>
								<td>
									<SuccessSquareIcon class="mx-auto" />
								</td>
							</tr>
							<tr>
								<td>
									<div class="items-center sm:flex">
										<div>{{ $t('main.marketing') }}</div>
										<ChevronRightIcon class="mx-1.5 hidden sm:block" />
										<div>{{ $t('customer.plural') }}</div>
									</div>
								</td>
								<td>
									<DangerSquareIcon class="mx-auto" />
								</td>
								<td>
									<SuccessSquareIcon class="mx-auto" />
								</td>
							</tr>
							<tr>
								<td>{{ $t('settlement.plural') }}</td>
								<td>
									<DangerSquareIcon class="mx-auto" />
								</td>
								<td>
									<SuccessSquareIcon class="mx-auto" />
								</td>
							</tr>
							<tr>
								<td>{{ $t('settings.plural') }}</td>
								<td>
									<SuccessSquareIcon class="mx-auto" />
								</td>
								<td>
									<SuccessSquareIcon class="mx-auto" />
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</Transition>
</template>

<script lang="ts" setup>
import XIcon from '@/Components/Dashboard/Icons/XIcon.vue';
import SuccessSquareIcon from '@/Components/Dashboard/Icons/SuccessSquareIcon.vue';
import ChevronRightIcon from '@/Components/Dashboard/Icons/ChevronRightIcon.vue';
import DangerSquareIcon from '@/Components/Dashboard/Icons/DangerSquareIcon.vue';

const props = withDefaults(
	defineProps<{
		showing?: boolean;
		type?: string;
	}>(),
	{
		showing: false,
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

<style scoped>
table {
	@apply w-full;

	tr {
		@apply border-b border-gray-3/50;
	}

	tr:nth-child(even) {
		@apply bg-gray-3/20;
	}

	td,
	th {
		@apply border-r border-gray-3/50 py-2 align-middle sm:py-0;
	}

	td:nth-child(1) {
		@apply pl-4;
	}

	thead {
		@apply capitalize;

		th {
			@apply py-3;
		}
	}

	tbody {
		tr {
			@apply !h-14 border-l border-gray-3/50;
		}
	}
}
</style>
