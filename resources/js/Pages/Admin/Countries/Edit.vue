<template>
	<PanelLayout
		:breadcrumbs="[
			{ href: route('admin.countries.index'), label: 'Kraje' },
			{
				href: route('admin.countries.edit', { country: country }),
				label: $t(`country.${country.code}`),
			},
		]">
		<ContentContainer>
			<div class="col-span-12">
				<Card>
					<template #header>
						<h2>Edytuj kraj: {{ $t(`country.${country.code}`) }}</h2>
					</template>

					<form
						class="mt-4 grid grid-cols-4 gap-x-4 gap-y-5"
						@submit.prevent="form.put(route('admin.countries.update', { country: country }))">
						<div class="input-group col-span-4 md:col-span-2">
							<InputLabel :value="capitalize($t('main.currency'))" />
							<SimpleSelect v-model="form.currency" :options="currencyOptions" />
							<div v-if="form.errors.currency" class="error">
								{{ form.errors.currency }}
							</div>
						</div>

						<div class="input-group col-span-4 md:col-span-2">
							<InputLabel :value="capitalize($t('main.locale'))" />
							<SimpleSelect v-model="form.locale" :options="localeOptions(props.availableLocales)" />
							<div v-if="form.errors.locale" class="error">
								{{ form.errors.locale }}
							</div>
						</div>

						<div class="input-group col-span-4 md:col-span-2">
							<InputLabel :value="capitalize($t('main.timezone'))" />
							<SimpleSelect v-model="form.timezone" :options="timezoneOptions(props.availableTimezones)" />
							<div v-if="form.errors.timezone" class="error">
								{{ form.errors.timezone }}
							</div>
						</div>

						<div class="input-group col-span-2 md:col-span-1">
							<InputLabel value="Numer kierunkowy" />
							<TextInput v-model="form.dialing_code" />
							<div v-if="form.errors.dialing_code" class="error">
								{{ form.errors.dialing_code }}
							</div>
						</div>

						<div class="input-group col-span-2 items-start md:col-span-1">
							<div class="flex h-full flex-wrap items-center">
								<div class="mt-3 flex w-full">
									<InputLabel :value="capitalize($t('main.enabled'))" for="active" />
									<Checkbox id="active" v-model="form.active" :checked="form.active" class="ml-3" />
								</div>
								<div v-if="form.errors.active" class="error">
									{{ form.errors.active }}
								</div>
							</div>
						</div>

						<Button class="col-span-4 md:col-span-2 md:col-start-3" type="submit">
							{{ capitalize($t('main.action.update')) }}
						</Button>
					</form>
				</Card>
			</div>
		</ContentContainer>
	</PanelLayout>
</template>

<script lang="ts" setup>
import { Country, PaymentMethod } from '@/Types/models';
import PanelLayout from '@/Layouts/PanelLayout.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import { useForm } from '@inertiajs/vue3';
import Card from '@/Components/Dashboard/Card.vue';
import { useString } from '@/Composables/useString';
import InputLabel from '@/Components/Auth/InputLabel.vue';
import Checkbox from '@/Components/Dashboard/Checkbox.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import { useSelectOptions } from '@/Composables/useSelectOptions';
import SimpleSelect from '@/Components/Dashboard/SimpleSelect.vue';
import TextInput from '@/Components/Dashboard/TextInput.vue';

const { capitalize } = useString();
const { currencyOptions, onlinePaymentMethodOptions, localeOptions, timezoneOptions } = useSelectOptions();

const props = defineProps<{
	country: Country;
	paymentMethods: PaymentMethod[];
	availableLocales: Array<String>;
	availableTimezones: Array<String>;
}>();

const form = useForm({
	active: props.country.active,
	currency: props.country.currency,
	locale: props.country.locale,
	timezone: props.country.timezone,
	payment_method_type: props.country.paymentMethod?.type,
	dialing_code: props.country.dialing_code,
});
</script>
