<template>
	<PanelLayout
		:breadcrumbs="[
			{ href: route('admin.clubs.index'), label: 'Kluby' },
			{ href: route('admin.clubs.create'), label: 'Utwórz nowy klub' },
		]">
		<ContentContainer>
			<div class="col-span-12">
				<Card>
					<template #header>
						<h2>Utwórz nowy klub</h2>
					</template>

					<form
						class="grid grid-cols-4 gap-x-4 gap-y-5"
						@submit.prevent="form.post(route('admin.clubs.store'))">
						<div class="input-group col-span-4 md:col-span-2">
							<InputLabel value="Nazwa" />
							<TextInput v-model="form.name" />
							<div v-if="form.errors.name" class="error">
								{{ form.errors.name }}
							</div>
						</div>

						<div class="input-group col-span-4 md:col-span-2">
							<InputLabel value="Kraj" />
							<SimpleSelect v-model="form.country_id" :options="countryOptions" />
							<div v-if="form.errors.country_id" class="error">
								{{ form.errors.country_id }}
							</div>
						</div>

						<Button class="col-span-2 md:col-span-1 md:col-start-4" type="submit">
							{{ capitalize($t('main.action.update')) }}
						</Button>
					</form>
				</Card>
			</div>
		</ContentContainer>
	</PanelLayout>
</template>

<script lang="ts" setup>
import PanelLayout from '@/Layouts/PanelLayout.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import Card from '@/Components/Dashboard/Card.vue';
import { useString } from '@/Composables/useString';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import TextInput from '@/Components/Dashboard/TextInput.vue';
import SimpleSelect from '@/Components/Dashboard/SimpleSelect.vue';
import { useSelectOptions } from '@/Composables/useSelectOptions';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import { User } from '@/Types/models';

const { capitalize } = useString();
const { countryOptions } = useSelectOptions();

//@ts-ignore
const props: {
	user: User;
} = usePage().props;

const form = useForm({
	country_id: props.user.country_id,
	name: null,
});
</script>
