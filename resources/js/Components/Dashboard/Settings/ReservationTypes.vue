<template>
	<AccordionTab
		key="reservation_types"
		:class="`icon-${settingIconColor}`"
		:setting-icon-color="settingIconColor">
		<template #title>
			{{ $t('reservation-type.plural') }}
		</template>

		<template #icon>
			<svg fill="none" height="16" viewBox="0 0 16 16" width="16" xmlns="http://www.w3.org/2000/svg">
				<path
					d="M16 9.22353H9.31765V16H6.65098V9.22353H0V6.80784H6.65098V0H9.31765V6.80784H16V9.22353Z"
					fill="#3699FF" />
			</svg>
		</template>

		<p class="mb-4 font-semibold">
			{{ $t('settings.reservation-types-bolder') }}
		</p>
		{{ $t('settings.reservation-types-description') }}

		<form class="mt-10 flex w-full text-sm" @submit.prevent="submitForm">
			<div
				class="flex-1 space-y-1 pr-1"
				:class="{
					'w-8/12': hasPermission,
					'w-10/12': !hasPermission,
				}">
				<InputLabel :value="$t('reservation-type.reservation-type-name')" class="text-xs" />
				<TextInput
					v-model="form.name"
					class="w-full"
					:disabled="!hasPermission"
					:class="{ 'disabled-readable': !hasPermission }" />
			</div>
			<div class="w-[60px] flex-none space-y-1 border-gray-2 text-center">
				<InputLabel class="text-xs" value="&nbsp;" />
				<SquareColorPicker
					:color="String(form.color)"
					:preview-classes="{
						'!w-12 !h-10.5 2xl:!h-12': true,
					}"
					:disabled="!hasPermission"
					@updated="(hex) => (form.color = hex)" />
			</div>
			<div class="w-[120px] flex-none space-y-1" v-if="hasPermission">
				<InputLabel class="text-xs" value="&nbsp;" />
				<Button
					v-if="!hasPermission"
					class="disabled !h-10.5 w-full 2xl:!h-12"
					v-tippy="{ allowHTML: true }"
					:content="'<p style=\'font-size:12px\'>' + $t('settings.no-permissions') + '</p>'">
					{{ capitalize($t('main.action.add')) }}
				</Button>
				<Button v-else class="!h-10.5 w-full 2xl:!h-12" type="submit">
					{{ capitalize($t('main.action.add')) }}
				</Button>
			</div>
		</form>
		<div v-if="form.errors.name" class="error">{{ form.errors.name }}</div>
		<div v-if="form.errors.color" class="error">
			{{ form.errors.color }}
		</div>
		<div class="mt-4 flex w-full text-sm font-semibold" v-if="reservationTypes.data.length">
			<div
				class="flex-1 pb-2 pr-2"
				:class="{
					'w-8/12': hasPermission,
					'w-10/12': !hasPermission,
				}">
				{{ capitalize($t('main.number')) }}
			</div>
			<div class="w-[60px] flex-none border-l border-r border-gray-2 pb-2 text-center">
				{{ capitalize($t('main.color')) }}
			</div>
			<div class="w-[120px] flex-none pb-2 pl-2" v-if="hasPermission">
				{{ capitalize($t('main.action.plural')) }}
			</div>
		</div>
		<div
			v-for="reservationType in reservationTypes.data"
			class="border-grey-2 flex w-full items-center border-t">
			<div
				class="flex-1 p-2"
				:class="{
					'w-8/12': hasPermission,
					'w-10/12': !hasPermission,
				}">
				<div v-if="editFormOpenedStatus?.[reservationType.id]?.value === true">
					<TextInput v-model="newNames[reservationType.id].value" />
					<div v-if="editFormErrors[reservationType.id].value['name']" class="error !text-xs">
						{{ editFormErrors[reservationType.id].value['name'] }}
					</div>
				</div>
				<div v-else>{{ reservationType.name }}</div>
			</div>
			<div class="border-grey-2 flex w-[60px] flex-none items-center justify-center border-l border-r p-2">
				<SquareColorPicker
					:color="String(reservationType.color)"
					:preview-classes="{
						'!w-12 !h-12 -mb-1.5': true,
					}"
					:disabled="!hasPermission"
					@updated="(hex) => reservationTypeColorUpdated(reservationType.id, hex)" />
			</div>
			<div class="w-[120px] flex-none p-2" v-if="hasPermission">
				<div class="flex w-full space-x-1">
					<Button
						v-if="editFormOpenedStatus?.[reservationType.id]?.value === true"
						class="brand-light xs uppercase"
						@click="updateName(reservationType.id)">
						<SuccessCircleIcon class="w-4.5" />
					</Button>
					<Button
						v-if="editFormOpenedStatus?.[reservationType.id]?.value === false"
						class="warning-light xs flex-initial uppercase"
						@click="editFormOpenedStatus[reservationType.id].value = true">
						<PencilSquareIcon class="-mx-0.5 -mt-0.5" />
					</Button>
					<Button
						as="button"
						class="danger-light xs flex-initial uppercase"
						preserve-scroll
						type="button"
						@click="
							showDialog(capitalize($t('main.are-you-sure')), () => {
								router.visit(
									route('club.reservation-types.destroy', {
										reservation_type: reservationType,
									}),
									{
										method: 'delete',
										preserveState: true,
										preserveScroll: true,
									},
								);
							})
						">
						<TrashIcon class="-mx-0.5 -mt-0.5" />
					</Button>
				</div>
			</div>
		</div>
	</AccordionTab>

	<DecisionModal
		:showing="confirmationDialogShowing"
		@confirm="confirmDialog()"
		@decline="cancelDialog()"
		@close="confirmationDialogShowing = false">
		{{ dialogContent }}
	</DecisionModal>
</template>

<script lang="ts" setup>
import AccordionTab from '@/Components/Dashboard/AccordionTab.vue';
import TrashIcon from '@/Components/Dashboard/Icons/TrashIcon.vue';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import TextInput from '@/Components/Dashboard/TextInput.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import { useString } from '@/Composables/useString';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { ReservationType } from '@/Types/models';
import { Ref, ref } from 'vue';
import Table from '@/Components/Dashboard/Table.vue';
import DecisionModal from '@/Components/Dashboard/Modals/DecisionModal.vue';
import { useConfirmationDialog } from '@/Composables/useConfirmationDialog';
import { PaginatedResource } from '@/Types/responses';
import SquareColorPicker from '@/Components/SquareColorPicker.vue';
import PencilSquareIcon from '@/Components/Dashboard/Icons/PencilSquareIcon.vue';
import SuccessCircleIcon from '@/Components/Dashboard/Icons/SuccessCircleIcon.vue';
import { usePanelStore } from '@/Stores/panel';

const { capitalize } = useString();

const props = withDefaults(
	defineProps<{
		reservationTypes: PaginatedResource<ReservationType[]>;
		settingIconColor?: string;
	}>(),
	{
		settingIconColor: 'info',
	},
);

const { page } = usePanelStore();
const hasPermission = ['admin', 'manager'].includes(page.props.user.type as string);

let newNames: { [reservation_type_id: number]: Ref<string> } = {};
let editFormOpenedStatus: { [reservation_type_id: number]: Ref<boolean> } = {};
let editFormErrors: { [reservation_type_id: number]: Ref<Object> } = {};

function reloadReservationTypeEditUtilities() {
	newNames = {};
	editFormOpenedStatus = {};
	(props.reservationTypes.data as ReservationType[]).forEach((reservationType: ReservationType) => {
		newNames[reservationType.id as number] = ref<string>(reservationType.name as string);
		editFormOpenedStatus[reservationType.id as number] = ref<boolean>(false);
		editFormErrors[reservationType.id as number] = ref<Object>({});
	});
}
reloadReservationTypeEditUtilities();

const form = useForm({
	name: '',
	color: ref<string>('#1bc5bd'),
});

function reservationTypeColorUpdated(reservationTypeId: Number, hex: string): void {
	router.patch(
		route('club.reservation-types.update', {
			reservation_type: reservationTypeId,
		}),
		{ color: hex },
		{ preserveState: true, preserveScroll: true },
	);
}

function updateName(reservation_type_id: number) {
	router.patch(
		route('club.reservation-types.update', {
			reservation_type: reservation_type_id,
		}),
		{ name: newNames[reservation_type_id].value },
		{
			preserveState: true,
			preserveScroll: true,
			onSuccess: () => {
				editFormOpenedStatus[reservation_type_id].value = false;
			},
			onError: (response) => {
				editFormErrors[reservation_type_id].value = response;
			},
		},
	);
}

function submitForm() {
	form.post(route('club.reservation-types.store'), {
		preserveState: true,
		preserveScroll: true,
		onSuccess: () => {
			reloadReservationTypeEditUtilities();
			form.name = '';
		},
	});
}

const { confirmationDialogShowing, showDialog, cancelDialog, confirmDialog, dialogContent } =
	useConfirmationDialog();
</script>
