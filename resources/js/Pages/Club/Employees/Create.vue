<template>
	<PanelLayout :breadcrumbs="[{ href: route('club.employees.index'), label: $t('employee.plural') }]">
		<ContentContainer>
			<div class="col-span-12 xl:col-span-9">
				<Card>
					<template #header>
						<h2>{{ $t('employee.add-employee') }}</h2>
					</template>

					<form
						class="grid grid-cols-2 gap-x-4 gap-y-5"
						@submit.prevent="
							form.post(route('club.employees.store'), {
								preserveState: true,
								preserveScroll: true,
							})
						">
						<div class="col-span-2 space-y-2 md:col-span-1">
							<InputLabel :value="capitalize($t('main.first-name'))" required />
							<TextInput v-model="form.first_name" />
							<div v-if="form.errors.first_name" class="error">
								{{ form.errors.first_name }}
							</div>
						</div>

						<div class="col-span-2 space-y-2 md:col-span-1">
							<InputLabel :value="capitalize($t('main.last-name'))" required />
							<TextInput v-model="form.last_name" />
							<div v-if="form.errors.last_name" class="error">
								{{ form.errors.last_name }}
							</div>
						</div>

						<div class="col-span-2 space-y-2 md:col-span-1">
							<InputLabel :value="capitalize($t('main.email'))" required />
							<TextInput v-model="form.email" />
							<div v-if="form.errors.email" class="error">
								{{ form.errors.email }}
							</div>
						</div>

						<div class="col-span-2 space-y-2 md:col-span-1">
							<InputLabel :value="capitalize($t('employee.position'))" required />
							<div class="flex items-center">
								<SimpleSelect class="mr-4 grow" v-model="form.type" :options="employeeTypeOptions" />
								<PermissionIcon
									class="h-10 w-10 cursor-pointer text-brand-base hover:text-brand-dark/100 active:text-brand-dark/80"
									@click="employeePermissionsShowing = true">
									{{ $t('nav.employees') }}
								</PermissionIcon>
								<EmployeePermissionsModal
									:showing="employeePermissionsShowing"
									@close="employeePermissionsShowing = false" />
							</div>
							<div v-if="form.errors.type" class="error">
								{{ form.errors.type }}
							</div>
						</div>

						<div class="col-span-2 flex justify-end">
							<Button class="lg mt-3 max-w-full capitalize" type="submit">
								{{ $t('main.action.save') }}
							</Button>
						</div>
					</form>
				</Card>
			</div>

			<ResponsiveHelper width="3">
				<template #mascot>
					<Mascot :type="17" />
				</template>
				<template #button>
					<Button
						:href="usePage().props.generalTranslations['help-employees-link']"
						class="grey xl:!px-0"
						type="link">
						{{ $t('help.learn-more') }}
					</Button>
				</template>
				{{ usePage().props.generalTranslations['help-employees-content'] }}
			</ResponsiveHelper>
		</ContentContainer>
	</PanelLayout>
</template>

<script lang="ts" setup>
import PanelLayout from '@/Layouts/PanelLayout.vue';
import Card from '@/Components/Dashboard/Card.vue';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import TextInput from '@/Components/Dashboard/TextInput.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import SimpleSelect from '@/Components/Dashboard/SimpleSelect.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import ResponsiveHelper from '@/Components/Dashboard/Help/ResponsiveHelper.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { useString } from '@/Composables/useString';
import { useSelectOptions } from '@/Composables/useSelectOptions';
import Mascot from '@/Components/Dashboard/Mascot.vue';
import PermissionIcon from '@/Components/Dashboard/Icons/PermissionIcon.vue';
import EmployeePermissionsModal from '@/Components/Dashboard/Modals/EmployeePermissionsModal.vue';
import { ref } from 'vue';

const { capitalize } = useString();
const { employeeTypeOptions } = useSelectOptions();
const employeePermissionsShowing = ref(false);

const form = useForm({
	type: 'employee',
	first_name: null,
	last_name: null,
	email: null,
});
</script>
