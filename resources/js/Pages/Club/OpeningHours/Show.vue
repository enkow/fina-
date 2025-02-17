<template>
	<PanelLayout
		:breadcrumbs="[
			{
				href: route('club.opening-hours.show'),
				label: $t('opening-hours.singular'),
			},
		]">
		<ContentContainer>
			<div class="col-span-12 xl:col-span-6">
				<Card>
					<template #header>
						<h2>{{ $t('opening-hours.standard-opening-hours') }}</h2>
					</template>
					<template #description>
						{{ $t('opening-hours.standard-opening-hours-description') }}
					</template>

					<div class="space-y-5">
						<AccordionTab v-for="i in 7" :key="`openingHours${i}`" class="bolder icon-grey">
							<template #title>
								{{ capitalize($t(`main.week-day.${i}`)) }}
							</template>

							<form
								@submit.prevent="
									forms[i].put(route('club.opening-hours.show'), {
										preserveScroll: true,
										onsuccess: reloadInitReservationClosedStatuses,
									})
								">
								<div class="w-full space-y-5 sm:space-y-0">
									<div class="space-y-2">
										<InputLabel :value="$t('opening-hours.club-opening')" class="text-sm font-medium" />
										<div class="flex w-full flex-wrap">
											<div class="w-full sm:w-1/2 sm:pr-3">
												<SimpleSelect
													:searchable="true"
													:filterable="true"
													v-model="forms[i].club_start"
													:disabled="
														forms[i].club_closed || !['admin', 'manager'].includes(usePage().props.user.type)
													"
													:class="{
														'disabled-readable':
															!['admin', 'manager'].includes(usePage().props.user.type) &&
															!forms[i].club_closed,
													}"
													:options="fromOptions" />
												<div v-if="forms[i].errors.club_start" class="error">
													{{ forms[i].errors.club_start }}
												</div>
											</div>

											<div class="mt-3 w-full sm:mt-0 sm:w-1/2 sm:pl-3">
												<SimpleSelect
													:searchable="true"
													:filterable="true"
													v-model="forms[i].club_end"
													:disabled="
														forms[i].club_closed || !['admin', 'manager'].includes(usePage().props.user.type)
													"
													:class="{
														'disabled-readable':
															!['admin', 'manager'].includes(usePage().props.user.type) &&
															!forms[i].club_closed,
													}"
													:options="toOptions" />
												<div v-if="forms[i].errors.club_end" class="error">
													{{ forms[i].errors.club_end }}
												</div>
											</div>
										</div>

										<div class="flex justify-end pt-2">
											<Checkbox
												:id="'clubClosed' + (i - 1)"
												v-model="forms[i].club_closed"
												:checked="forms[i].club_closed"
												:disabled="!['admin', 'manager'].includes(usePage().props.user.type)" />
											<InputLabel
												:for="'clubClosed' + (i - 1)"
												:value="$t('opening-hours.closed')"
												class="ml-3" />
											<div v-if="forms[i].errors.club_closed" class="error">
												{{ forms[i].errors.club_closed }}
											</div>
										</div>
									</div>

									<div class="space-y-2">
										<InputLabel :value="$t('reservation.online-reservations')" class="text-sm font-medium" />
										<div class="flex w-full flex-wrap">
											<div class="w-full sm:w-1/2 sm:pr-3">
												<SimpleSelect
													:searchable="true"
													:filterable="true"
													v-model="forms[i].reservation_start"
													:disabled="
														forms[i].reservation_closed ||
														forms[i].club_closed ||
														!['admin', 'manager'].includes(usePage().props.user.type)
													"
													:class="{
														'disabled-readable':
															!['admin', 'manager'].includes(usePage().props.user.type) &&
															!forms[i].club_closed &&
															!forms[i].reservation_closed,
													}"
													:options="fromOptions" />
												<div v-if="forms[i].errors.reservation_start" class="error">
													{{ forms[i].errors.reservation_start }}
												</div>
											</div>

											<div class="mt-3 w-full sm:mt-0 sm:w-1/2 sm:pl-3">
												<SimpleSelect
													:searchable="true"
													:filterable="true"
													v-model="forms[i].reservation_end"
													:disabled="
														forms[i].reservation_closed ||
														forms[i].club_closed ||
														!['admin', 'manager'].includes(usePage().props.user.type)
													"
													:class="{
														'disabled-readable':
															!['admin', 'manager'].includes(usePage().props.user.type) &&
															!forms[i].club_closed &&
															!forms[i].reservation_closed,
													}"
													:options="toOptions" />
												<div v-if="forms[i].errors.reservation_end" class="error">
													{{ forms[i].errors.reservation_end }}
												</div>
											</div>
										</div>

										<div class="flex justify-end pt-2">
											<Checkbox
												:id="'onlineReservations' + (i - 1)"
												v-model="forms[i].reservation_closed"
												:checked="forms[i].reservation_closed"
												:disabled="
													forms[i].club_closed || !['admin', 'manager'].includes(usePage().props.user.type)
												" />
											<InputLabel
												:for="'onlineReservations' + (i - 1)"
												:value="$t('opening-hours.closed')"
												class="ml-3" />
											<div v-if="forms[i].errors.reservation_closed" class="error">
												{{ forms[i].errors.reservation_closed }}
											</div>
										</div>
									</div>
								</div>

								<div class="accordion-footer">
									<div class="w-1/2 space-y-2"></div>
									<div class="w-1/2">
										<Button
											v-if="['admin', 'manager'].includes(usePage().props.user.type)"
											class="lg accordion-footer__submit"
											type="submit">
											{{ $t('main.action.update') }}
										</Button>
										<Button
											v-else
											class="lg accordion-footer__submit disabled"
											v-tippy="{ allowHTML: true }"
											:content="'<p style=\'font-size:12px\'>' + $t('settings.no-permissions') + '</p>'">
											{{ $t('main.action.update') }}
										</Button>
									</div>
								</div>
							</form>
						</AccordionTab>
					</div>
				</Card>
			</div>
			<div class="col-span-12 space-y-6 xl:col-span-6">
				<WideHelper float="left">
					<template #mascot>
						<Mascot :type="16" class="-ml-5" />
					</template>
					<template #button>
						<Button
							:href="usePage().props.generalTranslations['help-opening-hours-link']"
							class="grey w-full sm:w-fit"
							type="link">
							{{ $t('help.learn-more') }}
						</Button>
					</template>
					{{ usePage().props.generalTranslations['help-opening-hours-content'] }}
				</WideHelper>
				<Card :class="{ '!pb-0': !!openingHoursExceptions.data.length }" class="!px-0 !pb-3">
					<template #header>
						<h2 class="mb-0 mt-0 px-7 text-lg font-medium">
							{{ $t('opening-hours.calendar-for-future-days') }}
						</h2>
					</template>
					<template #description>
						<p class="px-7 font-light">
							{{ $t('opening-hours.calendar-for-future-days-description') }}
						</p>
					</template>
					<Table
						v-if="!!openingHoursExceptions.data.length"
						:header="{
							date_range: capitalize($t('main.date')),
							club_hours: capitalize($t('opening-hours.club-opening-short')),
							reservation_hours: capitalize($t('opening-hours.online-reservations-short')),
							creator: $t('opening-hours.created-by'),
						}"
						:items="openingHoursExceptions"
						table-name="opening_hours_exceptions">
						<template #cell_actions="props">
							<div class="flex items-center space-x-1">
								<Button
									v-if="['admin', 'manager'].includes(usePage().props.user.type)"
									:href="
										route('club.opening-hours-exceptions.edit', {
											opening_hours_exception: props.item.id,
										})
									"
									class="warning-light xs uppercase"
									type="link">
									<PencilSquareIcon class="-mx-0.5 -mt-0.5" />
								</Button>
								<Button
									v-else
									:href="
										route('club.opening-hours-exceptions.edit', {
											opening_hours_exception: props.item.id,
										})
									"
									class="info info-light xs"
									type="link">
									<InfoIcon class="-mx-0.5 -mt-0.5" />
								</Button>
								<Button
									v-if="['admin', 'manager'].includes(usePage().props.user.type)"
									as="button"
									class="danger-light xs uppercase"
									preserve-scroll
									type="button"
									@click="
										showDialog(capitalize($t('main.are-you-sure')), () => {
											router.visit(
												route('club.opening-hours-exceptions.destroy', {
													opening_hours_exception: props.item,
												}),
												{ method: 'delete', preserveScroll: true },
											);
										})
									">
									<TrashIcon class="-mx-0.5 -mt-0.5" />
								</Button>
							</div>
						</template>

						<template #cell_date_range="props">
							<p class="whitespace-nowrap">od {{ props?.item.start_at }}</p>
							<p class="whitespace-nowrap">do {{ props?.item.end_at }}</p>
						</template>

						<template #cell_club_hours="props">
							<p v-if="props?.item.club_closed === false" class="whitespace-nowrap">
								{{ props?.item.club_start }}-{{ props?.item.club_end }}
							</p>
							<p v-else>
								{{ $t('opening-hours.closed') }}
							</p>
						</template>

						<template #cell_reservation_hours="props">
							<p v-if="props?.item.reservation_closed === false" class="whitespace-nowrap">
								{{ props?.item.reservation_start }}-{{ props?.item.reservation_end }}
							</p>
							<p v-else>
								{{ $t('opening-hours.closed') }}
							</p>
						</template>

						<template #cell_creator="props">
							{{ props?.item.creator.email }}
						</template>
					</Table>
				</Card>
				<Button
					v-if="['admin', 'manager'].includes(usePage().props.user.type)"
					:href="route('club.opening-hours-exceptions.create')"
					class="!mt-4 w-fit !px-5"
					type="link">
					{{ $t('opening-hours.add-future-opening-hours-short') }}
				</Button>
			</div>
		</ContentContainer>

		<DecisionModal
			:showing="confirmationDialogShowing"
			@confirm="confirmDialog()"
			@decline="cancelDialog()"
			@close="confirmationDialogShowing = false">
			{{ dialogContent }}
		</DecisionModal>
	</PanelLayout>
</template>

<script lang="ts" setup>
import PanelLayout from '@/Layouts/PanelLayout.vue';
import Card from '@/Components/Dashboard/Card.vue';
import AccordionTab from '@/Components/Dashboard/AccordionTab.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import SimpleSelect from '@/Components/Dashboard/SimpleSelect.vue';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import Checkbox from '@/Components/Dashboard/Checkbox.vue';
import WideHelper from '@/Components/Dashboard/Help/WideHelper.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import { OpeningHours, OpeningHoursException, SelectOption } from '@/Types/models';
import Table from '@/Components/Dashboard/Table.vue';
import TrashIcon from '@/Components/Dashboard/Icons/TrashIcon.vue';
import PencilSquareIcon from '@/Components/Dashboard/Icons/PencilSquareIcon.vue';
import { PaginatedResource } from '@/Types/responses';
import { useString } from '@/Composables/useString';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { useConfirmationDialog } from '@/Composables/useConfirmationDialog';
import DecisionModal from '@/Components/Dashboard/Modals/DecisionModal.vue';
import { useSelectOptions } from '@/Composables/useSelectOptions';
import Mascot from '@/Components/Dashboard/Mascot.vue';
import { ref, Ref, watch } from 'vue';
import InfoIcon from '@/Components/Dashboard/Icons/InfoIcon.vue';
import { wTrans } from 'laravel-vue-i18n';

const { capitalize } = useString();

const props = defineProps<{
	openingHours: OpeningHours[];
	openingHoursExceptions: PaginatedResource<OpeningHoursException>;
}>();

const { pad } = useString();
const fromOptions: Ref<Array<SelectOption>> = ref([]);
const toOptions: Ref<Array<SelectOption>> = ref([]);
for (let i = 0; i <= 24; i++) {
	fromOptions.value.push({
		code: pad(i.toString(), 2) + ':00',
		label: wTrans('main.from-value', { value: `${i}:00` }),
	});
	toOptions.value.push({
		code: pad(i.toString(), 2) + ':00',
		label: wTrans('main.to-value', { value: `${i}:00` }),
	});
	if (i !== 24) {
		fromOptions.value.push({
			code: pad(i.toString(), 2) + ':30',
			label: wTrans('main.from-value', { value: `${i}:30` }),
		});
		toOptions.value.push({
			code: pad(i.toString(), 2) + ':30',
			label: wTrans('main.to-value', { value: `${i}:30` }),
		});
	}
}

let forms: Record<number, any> = {};
props.openingHours.forEach((item) => {
	forms[item.day] = useForm({
		day: item.day,
		club_start: item.club_start,
		club_end: item.club_end,
		club_closed: item.club_closed,
		reservation_start: item.reservation_start,
		reservation_end: item.reservation_end,
		reservation_closed: item.reservation_closed,
	});
});

let initReservationClosedStatuses: Record<number, boolean> = {};
[1, 2, 3, 4, 5, 6, 7].forEach((i) => {
	initReservationClosedStatuses[i] = forms[i].reservation_closed;
	watch(
		() => forms[i].club_closed,
		() => {
			if (forms[i].club_closed) {
				forms[i].reservation_closed = true;
			} else {
				forms[i].reservation_closed = initReservationClosedStatuses[i];
			}
		},
	);
});

function reloadInitReservationClosedStatuses() {
	[1, 2, 3, 4, 5, 6, 7].forEach((i) => {
		initReservationClosedStatuses[i] = forms[i].reservation_closed;
	});
}

const { confirmationDialogShowing, showDialog, cancelDialog, confirmDialog, dialogContent } =
	useConfirmationDialog();
</script>

<style scoped>
.accordion-footer {
	@apply my-5 flex w-full items-end space-x-6;

	.accordion-footer__submit {
		@apply float-right !w-full !px-22 !text-base capitalize;
	}
}
</style>
