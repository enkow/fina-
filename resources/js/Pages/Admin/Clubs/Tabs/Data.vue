<template>
	<form
		class="grid grid-cols-6 gap-x-4 gap-y-5"
		@submit.prevent="
			dataForm.put(route('admin.clubs.update', { club: club }), {
				preserveScroll: true,
				preserveState: true,
			})
		">
		<div class="input-group col-span-6 md:col-span-1">
			<InputLabel value="Nazwa" required />
			<TextInput v-model="dataForm.name" />
			<div v-if="dataForm.errors.name" class="error">
				{{ dataForm.errors.name }}
			</div>
		</div>

		<div class="input-group col-span-6 md:col-span-1">
			<InputLabel value="Numer telefonu" required />
			<TextInput v-model="dataForm.phone_number" />
			<div v-if="dataForm.errors.phone_number" class="error">
				{{ dataForm.errors.phone_number }}
			</div>
		</div>

		<div class="input-group col-span-6 md:col-span-2">
			<InputLabel value="Symbol linku" required />
			<TextInput v-model="dataForm.slug" />
			<div v-if="dataForm.errors.slug" class="error">
				{{ dataForm.errors.slug }}
			</div>
		</div>

		<div class="input-group col-span-6 md:col-span-2">
			<InputLabel value="Adres email" required />
			<TextInput v-model="dataForm.email" />
			<div v-if="dataForm.errors.email" class="error">
				{{ dataForm.errors.email }}
			</div>
		</div>

		<div class="input-group col-span-6 md:col-span-2">
			<InputLabel value="Numer VAT" required />
			<TextInput v-model="dataForm.vat_number" />
			<div v-if="dataForm.errors.vat_number" class="error">
				{{ dataForm.errors.vat_number }}
			</div>
		</div>

		<div class="input-group col-span-6 md:col-span-4">
			<InputLabel value="Kraj" required />
			<SimpleSelect v-model="dataForm.country_id" :options="countryOptions" />
			<div v-if="dataForm.errors.country_id" class="error">
				{{ dataForm.errors.country_id }}
			</div>
		</div>

		<div class="input-group col-span-6 md:col-span-1">
			<InputLabel value="Kod pocztowy" required />
			<TextInput v-model="dataForm.postal_code" />
			<div v-if="dataForm.errors.postal_code" class="error">
				{{ dataForm.errors.postal_code }}
			</div>
		</div>

		<div class="input-group col-span-6 md:col-span-1">
			<InputLabel value="Miasto" required />
			<TextInput v-model="dataForm.city" />
			<div v-if="dataForm.errors.city" class="error">
				{{ dataForm.errors.city }}
			</div>
		</div>

		<div class="input-group col-span-6 md:col-span-4">
			<InputLabel value="Adres" required />
			<TextInput v-model="dataForm.address" />
			<div v-if="dataForm.errors.address" class="error">
				{{ dataForm.errors.address }}
			</div>
		</div>

		<div class="input-group col-span-6">
			<InputLabel value="Opis" />
			<TextareaInput v-model="dataForm.description" :value="dataForm.description" rows="3" />
			<div v-if="dataForm.errors.description" class="error">
				{{ dataForm.errors.description }}
			</div>
		</div>

		<div class="input-group col-span-6">
			<InputLabel value="Adresy email do faktur (każdy w nowej linii)" />
			<TextareaInput v-model="dataForm.invoice_emails" :value="dataForm.invoice_emails" rows="3" />
			<div
				v-for="key in Object.keys(dataForm.errors).filter((key) => key.includes('invoice_emails'))"
				class="error">
				{{ dataForm.errors[key] }}
			</div>
		</div>

		<div class="input-group col-span-6">
			<InputLabel value="Informacja po rejestracji" />
			<TextareaInput v-model="dataForm.first_login_message" :value="dataForm.first_login_message" rows="5" />
			<div v-if="dataForm.errors.first_login_message" class="error">
				{{ dataForm.errors.first_login_message }}
			</div>
		</div>

		<div class="input-group col-span-2">
			<InputLabel value="Kraje na wtyczce" required />
			<TextInput v-model="dataForm.widget_countries" />
			<div v-if="dataForm.errors.widget_countries" class="error">
				{{ dataForm.errors.widget_countries }}
			</div>
		</div>

		<Button class="col-span-6 md:col-span-1 md:col-start-6" type="submit">
			{{ capitalize($t('main.action.update')) }}
		</Button>
	</form>
</template>

<script lang="ts" setup>
import { useString } from '@/Composables/useString';
import TextareaInput from '@/Components/Dashboard/TextareaInput.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import TextInput from '@/Components/Dashboard/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { Club, Country } from '@/Types/models';
import { useSelectOptions } from '@/Composables/useSelectOptions';
import SimpleSelect from '@/Components/Dashboard/SimpleSelect.vue';

const { capitalize } = useString();
const { countryOptions } = useSelectOptions();

const props = defineProps<{
	club: Club;
}>();

let widgetCountries: string[] = [];
props.club.widget_countries.forEach((country: Country): void => {
	widgetCountries.push(country.code);
});

const dataForm = useForm({
	country_id: props.club.country?.id,
	customer_registration_required: props.club.customer_registration_required,
	name: props.club.name,
	slug: props.club.slug,
	address: props.club.address,
	postal_code: props.club.postal_code,
	city: props.club.city,
	phone_number: props.club.phone_number,
	email: props.club.email,
	vat_number: props.club.vat_number,
	description: props.club.description,
	first_login_message: props.club.first_login_message,
	additional_commission_percent: props.club.additional_commission_percent,
	additional_commission_fixed: (props.club.additional_commission_fixed ?? 0) / 100,
	invoice_emails: props.club.invoice_emails === null ? '' : props.club.invoice_emails?.join('\n'),
	widget_countries: widgetCountries.join(','),
});
</script>
