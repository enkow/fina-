<template>
	<PanelLayout :breadcrumbs="[{ href: route('admin.help-sections.index'), label: 'Sekcje pomocy' }]">
		<ContentContainer>
			<div class="col-span-12">
				<Card>
					<template #header>
						<div class="mb-3 flex w-full justify-between">
							<div>
								<h2>Sekcje pomocy</h2>
							</div>
							<div class="p-2 sm:flex">
								<SimpleSelect
									:options="copyFromCountriesOptions"
									v-model="copyFromCountry"
									placeholder="Kopiuj sekcje z kraju"
									class="mr-4 w-50 md:w-80" />
								<Button class="mt-2 w-50 sm:m-0" :href="route('admin.help-sections.create')" type="link">
									Dodaj
								</Button>
							</div>
						</div>
					</template>

					<Table
						:header="{
							title: 'Tytuł',
							weight: 'Waga wyświetlania',
							active: 'Czy widoczna',
						}"
						:items="helpSections"
						:narrow="['active', 'actions']"
						table-name="admin_help_sections">
						<template #cell_active="props">
							<div class="flex justify-center">
								<div
									class="cursor-pointer"
									@click="
										router.visit(
											route('admin.help-sections.toggle-active', {
												help_section: props?.item.id,
											}),
											{ method: 'post' },
										)
									">
									<SuccessSquareIcon v-if="props?.item.active" />
									<DangerSquareIcon v-else />
								</div>
							</div>
						</template>

						<template #cell_actions="props">
							<div class="flex items-center space-x-1">
								<Button
									:href="
										route('admin.help-sections.help-items.index', {
											help_section: props.item.id,
										})
									"
									class="info-light xs uppercase"
									type="link">
									<InfoIcon class="-mx-0.5 -mt-0.5" />
								</Button>
								<Button
									:href="
										route('admin.help-sections.edit', {
											help_section: props.item.id,
										})
									"
									class="warning-light xs uppercase"
									type="link">
									<PencilSquareIcon class="-mx-0.5 -mt-0.5" />
								</Button>
								<Button
									class="danger-light xs uppercase"
									preserve-scroll
									type="button"
									@click="
										showDialog(capitalize($t('main.are-you-sure')), () => {
											router.visit(
												route('admin.help-sections.destroy', {
													help_section: props.item,
												}),
												{ method: 'delete' },
											);
										})
									">
									<TrashIcon class="-mx-0.5 -mt-0.5" />
								</Button>
							</div>
						</template>
					</Table>
				</Card>
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
import { HelpSection, SelectOption, User } from '@/Types/models';
import { PaginatedResource } from '@/Types/responses';
import { router, usePage } from '@inertiajs/vue3';
import PanelLayout from '@/Layouts/PanelLayout.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import Card from '@/Components/Dashboard/Card.vue';
import Table from '@/Components/Dashboard/Table.vue';
import PencilSquareIcon from '@/Components/Dashboard/Icons/PencilSquareIcon.vue';
import TrashIcon from '@/Components/Dashboard/Icons/TrashIcon.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import DecisionModal from '@/Components/Dashboard/Modals/DecisionModal.vue';
import { useConfirmationDialog } from '@/Composables/useConfirmationDialog';
import InfoIcon from '@/Components/Dashboard/Icons/InfoIcon.vue';
import SuccessSquareIcon from '@/Components/Dashboard/Icons/SuccessSquareIcon.vue';
import DangerSquareIcon from '@/Components/Dashboard/Icons/DangerSquareIcon.vue';
import { useString } from '@/Composables/useString';
import SimpleSelect from '@/Components/Dashboard/SimpleSelect.vue';
import { wTrans } from 'laravel-vue-i18n';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
	helpSections: PaginatedResource<HelpSection>;
}>();

const { confirmationDialogShowing, showDialog, cancelDialog, confirmDialog, dialogContent } =
	useConfirmationDialog();
const { capitalize } = useString();

const copyFromCountriesOptions = computed<SelectOption[]>(() => {
	let result: SelectOption[] = [];
	Object.keys(usePage().props.activeCountries)
		?.filter((countryId: string) => parseInt(countryId) !== usePage().props.user.country_id)
		.forEach((countryId: string) => {
			result.push({
				code: countryId ?? usePage().props.activeCountries[countryId],
				label: wTrans(`country.${usePage().props.activeCountries[countryId]}`).value,
			});
		});
	return result;
});

const copyFromCountry = ref<number | undefined>(undefined);

watch(copyFromCountry, () => {
	let activeCountries = usePage().props.activeCountries;
	let fromCountryCode = activeCountries[copyFromCountry.value];
	let toCountryCode = activeCountries[usePage().props.user.country_id];
	if (copyFromCountry.value !== undefined) {
		showDialog(
			`Czy na pewno chcesz przekopiować sekcje pomocy: ${wTrans(`country.${fromCountryCode}`).value} => ${
				wTrans(`country.${toCountryCode}`).value
			} ?`,
			() => {
				router.visit(
					route('admin.help-sections.copy', {
						from: copyFromCountry.value,
						to: (usePage().props.user as User).country_id,
					}),
					{
						method: 'post',
						preserveState: true,
						preserveScroll: true,
						onSuccess: () => {
							copyFromCountry.value = undefined;
						},
					},
				);
			},
			() => {
				copyFromCountry.value = undefined;
			},
		);
	}
});
</script>
