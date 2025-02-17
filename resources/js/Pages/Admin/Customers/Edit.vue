<template>
	<PanelLayout
		:breadcrumbs="[
			{ href: route('admin.customers.index'), label: 'Klienci' },
			{
				href: route('admin.customers.edit', { customer: customer }),
				label: customer.first_name + ' ' + customer.last_name,
			},
		]">
		<ContentContainer>
			<div class="col-span-12">
				<Card>
					<template #header>
						<h2>
							Edytuj klienta:
							{{ customer.first_name + ' ' + customer.last_name }}
						</h2>
					</template>

					<form
						class="mt-4 grid grid-cols-4 gap-x-4 gap-y-5"
						@submit.prevent="
							form.put(
								route(
									'admin.customers.update',
									{ customer: customer },
									{ preserveState: true, preserveScroll: true },
								),
							)
						">
						<div class="input-group col-span-4 md:col-span-2">
							<InputLabel :value="capitalize($t('main.first-name'))" />
							<TextInput v-model="form.first_name" />
							<div v-if="form.errors.first_name" class="error">
								{{ form.errors.first_name }}
							</div>
						</div>

						<div class="input-group col-span-4 md:col-span-2">
							<InputLabel :value="capitalize($t('main.last-name'))" />
							<TextInput v-model="form.last_name" />
							<div v-if="form.errors.last_name" class="error">
								{{ form.errors.last_name }}
							</div>
						</div>

						<div class="input-group col-span-4 md:col-span-2">
							<InputLabel :value="capitalize($t('main.email'))" />
							<TextInput v-model="form.email" />
							<div v-if="form.errors.email" class="error">
								{{ form.errors.email }}
							</div>
						</div>

						<div class="input-group col-span-4 md:col-span-2">
							<InputLabel :value="capitalize($t('main.phone'))" />
							<TextInput v-model="form.phone" />
							<div v-if="form.errors.phone" class="error">
								{{ form.errors.phone }}
							</div>
						</div>

						<Button class="col-span-4 md:col-span-2 md:col-start-3" type="submit">
							{{ capitalize($t('main.action.update')) }}
						</Button>
					</form>
				</Card>
				<Card class="mt-3">
					<template #header>
						<div class="mb-4 flex justify-between">
							<div>
								<h2>Lista rezerwacji</h2>
							</div>
							<div>
								<GameFilter :games="games" table-name="reservations" />
							</div>
						</div>
					</template>

					<ReservationTable
						:reservations="reservations"
						:table-headings="reservationsTableHeadings"
						with-reservation-modals />
				</Card>
			</div>
		</ContentContainer>
	</PanelLayout>
</template>

<script lang="ts" setup>
import PanelLayout from '@/Layouts/PanelLayout.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import { Customer, Game, Reservation } from '@/Types/models';
import { useForm } from '@inertiajs/vue3';
import Card from '@/Components/Dashboard/Card.vue';
import { useString } from '@/Composables/useString';
import TextInput from '@/Components/Dashboard/TextInput.vue';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import { PaginatedResource } from '@/Types/responses';
import ReservationTable from '@/Components/Dashboard/ReservationTable.vue';
import GameFilter from '@/Components/Dashboard/GameFilter.vue';

const { capitalize } = useString();

const props = defineProps<{
	games: Game[];
	customer: Customer;
	reservations: PaginatedResource<Reservation>;
	reservationsTableHeadings: Record<string, string>;
}>();

const form = useForm({
	first_name: props.customer.first_name,
	last_name: props.customer.last_name,
	email: props.customer.email,
	phone: props.customer.phone,
});
</script>
