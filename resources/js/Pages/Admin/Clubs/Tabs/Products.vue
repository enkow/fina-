<template>
	<div class="flex w-full flex-col">
		<Table
			:header="{
				id: 'Identyfikator',
				name_pl: 'Nazwa produktu',
				period: 'Typ rozliczeniowy',
				cost: 'Cena',
			}"
			:narrow="['id', 'actions']"
			:items="{ data: club.products.filter((product) => product.system_label === null) }"
			table-name="admin_club_product">
			<template #cell_id="props">
				{{ props.item.pivot.id }}
			</template>

			<template #cell_period="props">
				<template v-if="editedData.id !== props.item.pivot.id">
					<p>
						{{ periods.findLast((period) => period.code === props.item.pivot.period)?.label }}
					</p>
				</template>
				<div class="relative" v-else>
					<SimpleSelect v-model="editedData.period" :options="periods" append-to-body />
				</div>
			</template>

			<template #cell_cost="props">
				<template v-if="editedData.id !== props.item.pivot.id">
					<p>
						{{ (props.item.pivot.cost ?? 0) / 100 }}
						{{ club.country?.currency }}
					</p>
				</template>
				<AmountInput v-else v-model="editedData.cost" :custom-symbol="club.country?.currency" />
			</template>

			<template #cell_actions="props">
				<div class="flex h-11 items-center space-x-1">
					<Button
						as="button"
						class="warning-light xs uppercase"
						type="button"
						@click="setEditedDataForm(props.item.pivot)"
						v-if="editedData.id !== props.item.pivot.id">
						<PencilSquareIcon class="-mx-0.5 -mt-0.5" />
					</Button>
					<Button
						as="button"
						class="warning-light xs uppercase"
						type="button"
						@click="
							() => {
								editedData.put(
									route('admin.clubs.product.update', { club: club.id, product_club: props.item.pivot.id }),
								);
								editedData.id = 0;
							}
						"
						v-else>
						<SuccessSquareIcon class="-mx-0.5 -mt-0.5" />
					</Button>
					<Button
						as="button"
						class="danger-light xs uppercase"
						preserve-scroll
						type="button"
						@click="
							showDialog(capitalize($t('main.are-you-sure')), () => {
								router.delete(
									route('admin.clubs.product.remove', { club: club.id, product_club: props.item.pivot.id }),
								);
							})
						"
						:disabled="editedData.id === props.item.pivot.id">
						<TrashIcon class="-mx-0.5 -mt-0.5" />
					</Button>
				</div>
			</template>
		</Table>
		<div class="mt-5 grid">
			<h3 class="mt-5 text-lg">Nowy produkt</h3>
			<form
				class="mt-4 grid grid-cols-8 gap-x-4 gap-y-5"
				@submit.prevent="
					addProductForm.post(
						route('admin.clubs.product.add', { club: club, product: addProductForm.product }),
					)
				">
				<div class="input-group col-span-4">
					<InputLabel value="Produkt" required />
					<SimpleSelect v-model="addProductForm.product" :options="productsToSelect" />
					<div v-if="addProductForm.errors.product" class="error">
						{{ addProductForm.errors.product }}
					</div>
				</div>
				<div class="input-group col-span-2">
					<InputLabel value="Okres" required />
					<SimpleSelect v-model="addProductForm.period" :options="periods" />
					<div v-if="addProductForm.errors.period" class="error">
						{{ addProductForm.errors.period }}
					</div>
				</div>
				<div class="input-group col-span-1">
					<InputLabel value="Cena" required />
					<AmountInput v-model="addProductForm.cost" :custom-symbol="club.country?.currency" />
					<div v-if="addProductForm.errors.cost" class="error">
						{{ formatAmount(addProductForm.errors.cost) }}
					</div>
				</div>

				<div class="col-span-1 col-start-8">
					<div class="flex h-full w-full items-end">
						<Button class="w-full" type="submit">Dodaj produkt</Button>
					</div>
				</div>
			</form>
		</div>
		<div class="mt-5 grid">
			<h3 class="mt-5 text-lg">Gry</h3>
			<Table
				:header="{
					name: 'Nazwa',
					include_on_invoice_status: 'Status dodawany do faktury',
					include_on_invoice: 'Zawarte w fakturze',
					commission: 'Miesięczna prowizja',
				}"
				:narrow="['actions', 'include_on_invoice', 'commission']"
				:items="{ data: commissions }"
				table-name="admin_club_commissions"
				:columnsCustomWidths="{ include_on_invoice_status: 500 }">
				<template #cell_include_on_invoice="props">
					<BooleanInput
						:model-value="props.item.include_on_invoice"
						:disabled="true"
						v-if="props.item.id !== editedGame.id" />
					<BooleanInput v-model="editedGame.include_on_invoice" v-else />
				</template>

				<template #cell_include_on_invoice_status="props">
					<p v-if="editedGame.id !== props.item.id">
						{{
							(JSON.parse(props.item.include_on_invoice_status) || ['brak'])
								.map(
									(include_on_invoice_status: number) =>
										statuses.findLast((status) => status.code === include_on_invoice_status)?.label,
								)
								.join(', ')
						}}
					</p>
					<div class="w-full" v-else>
						<SimpleSelect
							v-model="editedGame.include_on_invoice_status"
							:options="statuses"
							append-to-body
							multiple />
					</div>
				</template>
				<template #cell_actions="props">
					<div class="flex h-11 items-center space-x-1">
						<Button
							as="button"
							class="warning-light xs uppercase"
							type="button"
							@click="
								() => {
									editedGame.id = props.item.id;
									editedGame.include_on_invoice_status = JSON.parse(props.item.include_on_invoice_status);
									editedGame.include_on_invoice = props.item.include_on_invoice;
								}
							"
							v-if="editedGame.id !== props.item.id">
							<PencilSquareIcon class="-mx-0.5 -mt-0.5" />
						</Button>
						<Button
							as="button"
							class="warning-light xs uppercase"
							type="button"
							@click="
								() => {
									editedGame.put(
										route('admin.clubs.game-commissions.update', { club: club.id, game_club: props.item.id }),
									);
									editedGame.id = 0;
								}
							"
							v-else>
							<SuccessSquareIcon class="-mx-0.5 -mt-0.5" />
						</Button>
					</div>
				</template>
			</Table>
		</div>
		<div class="mt-5 grid">
			<h3 class="mt-5 text-lg">Smsy</h3>
			<Table
				:header="{
					name: 'Nazwa',
					status: 'Status SMSów',
					price: 'Cena jednostkowa',
					commission: 'Miesięczna prowizja',
				}"
				:narrow="['actions', 'commission', 'status']"
				:items="{ data: smsType }"
				table-name="admin_club_sms"
				:columnsCustomWidths="{ price: 500 }">
				<template #cell_status="props">
					<BooleanInput :model-value="props.item.status" :disabled="true" />
				</template>

				<template #cell_commission="props">
					{{ (props.item.count * props.item.price) / 100 }} {{ club.country?.currency }}
				</template>

				<template #cell_price="props">
					<p
						v-if="editedSms.id !== props.item.id"
						v-text="`${props.item.price / 100} ${club.country?.currency}`" />
					<div class="relative" v-else>
						<AmountInput v-model="editedSms.cost" :custom-symbol="club.country?.currency" />
					</div>
				</template>

				<template #cell_actions="props">
					<div class="flex h-11 items-center space-x-1">
						<Button
							as="button"
							class="warning-light xs uppercase"
							type="button"
							@click="
								() => {
									editedSms.id = props.item.id;
									editedSms.cost = props.item.price / 100;
								}
							"
							v-if="editedSms.id !== props.item.id">
							<PencilSquareIcon class="-mx-0.5 -mt-0.5" />
						</Button>
						<Button
							as="button"
							class="warning-light xs uppercase"
							type="button"
							@click="
								() => {
									editedSms.cost = editedSms.cost;

									editedSms.put(
										route('admin.clubs.product.update', { club: club.id, product_club: editedSms.id }),
										{ preserveScroll: true },
									);
									props.item.price = editedSms.cost.replace(',', '.') * 100;
									editedSms.id = 0;
								}
							"
							v-else>
							<SuccessSquareIcon class="-mx-0.5 -mt-0.5" />
						</Button>
					</div>
				</template>
			</Table>
		</div>
	</div>
	<DecisionModal
		:showing="confirmationDialogShowing"
		@confirm="confirmDialog()"
		@decline="cancelDialog()"
		@close="confirmationDialogShowing = false">
		{{ dialogContent }}
	</DecisionModal>
</template>

<script lang="ts" setup>
import Table from '@/Components/Dashboard/Table.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import BooleanInput from '@/Components/Dashboard/BooleanInput.vue';

import TrashIcon from '@/Components/Dashboard/Icons/TrashIcon.vue';
import PencilSquareIcon from '@/Components/Dashboard/Icons/PencilSquareIcon.vue';
import { ClubProduct as ClubProductType, Product } from '@/Types/models';
import { useString } from '@/Composables/useString';
import SimpleSelect from '@/Components/Dashboard/SimpleSelect.vue';
import SuccessSquareIcon from '@/Components/Dashboard/Icons/SuccessSquareIcon.vue';
import AmountInput from '@/Components/Dashboard/AmountInput.vue';

import { Club } from '@/Types/models';

import InputLabel from '@/Components/Auth/InputLabel.vue';
import { useForm, router } from '@inertiajs/vue3';

import { useConfirmationDialog } from '@/Composables/useConfirmationDialog';
import DecisionModal from '@/Components/Dashboard/Modals/DecisionModal.vue';
import { useNumber } from '@/Composables/useNumber';
import UnstyledNumberInput from '@/Components/Unstyled/UnstyledNumberInput.vue';
const { confirmationDialogShowing, showDialog, cancelDialog, confirmDialog, dialogContent } =
	useConfirmationDialog();

const { capitalize } = useString();
const { formatAmount } = useNumber();

const props = defineProps<{
	club: Club;
	products: Product[];
	commissions: [];
	sms: [];
}>();

const periods = [
	{ code: 'month', label: 'Miesięczny' },
	{ code: 'year', label: 'Roczny' },
];

const productsToSelect = props.products
	.filter((product) => product.system_label === null)
	.map((product) => ({
		id: product.id,
		code: product.id,
		label: product.name_pl,
	}));

const statuses = [
	{ code: 0, label: 'Nieopłacone' },
	{ code: 1, label: 'Opłacone' },
	{ code: 2, label: 'Anulowane' },
];

let editedGame = useForm({
	id: 0,
	include_on_invoice_status: [],
	include_on_invoice: true,
});

const addProductForm = useForm({
	product: productsToSelect[0]?.code,
	period: periods[0].code,
	cost: 0,
});

let editedData = useForm({
	id: 0,
	period: '',
	cost: 0,
});

function setEditedDataForm(data: ClubProductType) {
	editedData.id = data.id;
	editedData.period = data.period;
	editedData.cost = data.cost / 100;
}

const smsProductOnline = props.club.products.filter((product) => product.system_label === 'sms_online')[0];
const smsProductOffline = props.club.products.filter((product) => product.system_label === 'sms_offline')[0];
const smsType = [
	{
		name: smsProductOnline.name_pl,
		status: props.club.sms_notifications_online,
		price: smsProductOnline.pivot.cost,
		count: props.club.sms_count_online_monthly,
		id: smsProductOnline.pivot.id,
	},
	{
		name: smsProductOffline.name_pl,
		status: props.club.sms_notifications_offline,
		price: smsProductOffline.pivot.cost,
		count: props.club.sms_count_offline_monthly,
		id: smsProductOffline.pivot.id,
	},
];

const editedSms = useForm({
	id: 0,
	period: 'month',
	cost: '0',
});
</script>
