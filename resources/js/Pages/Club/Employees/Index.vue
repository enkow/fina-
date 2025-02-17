<template>
	<PanelLayout :breadcrumbs="[{ href: route('club.employees.index'), label: $t('employee.plural') }]">
		<ContentContainer>
			<div class="col-span-12 xl:col-span-9">
				<Card>
					<template #header>
						<div class="flex items-center">
							<h2 class="my-0 mr-8">
								{{ $t('employee.plural') }}
							</h2>
							<Button :href="route('club.employees.create')" type="link">
								{{ $t('employee.add-employee') }}
							</Button>

							<PermissionIcon
								class="ml-auto h-10 w-10 cursor-pointer text-brand-base hover:text-brand-dark/100 active:text-brand-dark/80"
								@click="employeePermissionsShowing = true">
								{{ $t('nav.employees') }}
							</PermissionIcon>
							<EmployeePermissionsModal
								:showing="employeePermissionsShowing"
								@close="employeePermissionsShowing = false" />
						</div>
					</template>

					<Table
						v-if="employees.meta.total"
						:centered="['type']"
						:disabled="['last_name']"
						:header="{
							id: capitalize($t('main.id')),
							first_name: capitalize($t('main.first-name-and-last-name')),
							last_name: capitalize($t('main.last-name')),
							email: capitalize($t('main.email')),
							last_login: $t('employee.last-login'),
							type: $t('employee.position'),
						}"
						:items="employees"
						:narrow="['last_login', 'type', 'actions']"
						class="mt-8"
						table-name="employees">
						<template #header_first_name>
							<div class="pl-3">
								{{ capitalize($t('main.first-name-and-last-name')) }}
							</div>
						</template>

						<template #cell_first_name="props">
							<div class="pl-3">{{ props?.item.first_name }} {{ props?.item.last_name }}</div>
						</template>

						<template #cell_actions="props">
							<div class="flex items-center space-x-1">
								<Button
									:href="route('club.employees.edit', { employee: props.item.id })"
									class="warning-light xs uppercase"
									type="link">
									<PencilSquareIcon class="-mx-0.5 -mt-0.5" />
								</Button>
								<Button
									as="button"
									class="danger-light xs uppercase"
									preserve-scroll
									type="button"
									@click="
										showDialog(capitalize($t('main.are-you-sure')), () => {
											router.visit(
												route('club.employees.destroy', {
													employee: props.item,
												}),
												{
													method: 'delete',
													preserveScroll: true,
													preserveState: true,
												},
											);
										})
									">
									<TrashIcon class="-mx-0.5 -mt-0.5" />
								</Button>
							</div>
						</template>

						<template #cell_type="props">
							<Badge
								:class="{
									'brand-accent': props?.item.type === 'manager',
									'info-accent': props?.item.type === 'employee',
								}"
								class="px-0 text-center !text-xs">
								{{ $t(`employee.type.${props?.item.type}`) }}
							</Badge>
						</template>
					</Table>
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
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import EmployeePermissionsModal from '@/Components/Dashboard/Modals/EmployeePermissionsModal.vue';
import { useModal } from '@/Composables/useModal';
import PermissionIcon from '@/Components/Dashboard/Icons/PermissionIcon.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import ResponsiveHelper from '@/Components/Dashboard/Help/ResponsiveHelper.vue';
import { User } from '@/Types/models';
import Table from '@/Components/Dashboard/Table.vue';
import { PaginatedResource } from '@/Types/responses';
import TrashIcon from '@/Components/Dashboard/Icons/TrashIcon.vue';
import PencilSquareIcon from '@/Components/Dashboard/Icons/PencilSquareIcon.vue';
import Badge from '@/Components/Dashboard/Badge.vue';
import { useString } from '@/Composables/useString';
import { router, usePage } from '@inertiajs/vue3';
import Mascot from '@/Components/Dashboard/Mascot.vue';
import DecisionModal from '@/Components/Dashboard/Modals/DecisionModal.vue';
import { useConfirmationDialog } from '@/Composables/useConfirmationDialog';
import { ref } from 'vue';

const { capitalize } = useString();

const employeePermissionsShowing = ref<boolean>(false);
useModal(employeePermissionsShowing);

const { confirmationDialogShowing, showDialog, cancelDialog, confirmDialog, dialogContent } =
	useConfirmationDialog();

const props = defineProps<{
	employees: PaginatedResource<User>;
}>();
</script>
