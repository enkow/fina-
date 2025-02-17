<template>
	<PanelLayout
		:breadcrumbs="[
			{ href: route('admin.translations.index'), label: 'Tłumaczenia' },
			{
				href: currentUrl,
				label: currentPageLabel !== '' ? currentPageLabel : 'Typy tłumaczeń',
			},
		]">
		<ContentContainer>
			<div class="col-span-12">
				<Card>
					<template #header>
						<div class="mb-4 flex w-full items-center justify-between">
							<h2>Tłumaczenia</h2>
							<div v-if="translations.length === 0">
								<SimpleSelect
									:options="copyFromCountriesOptions"
									v-model="copyFromCountry"
									placeholder="Kopiuj tłumaczenia z kraju"
									class="w-80" />
							</div>
						</div>
					</template>

					<table v-if="translations.length === 0" class="table w-full table-auto space-y-2">
						<thead>
							<tr>
								<th class="text-left">Typ</th>
								<th class="text-left">Nazwa</th>
								<th class="text-left">Pokaż</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>Ogólne</td>
								<td>Ogólne</td>
								<td>
									<Button
										:href="
											route('admin.translations.index', {
												game_id: 0,
												feature_id: 0,
											})
										"
										class="warning-light xs uppercase"
										type="link">
										<PencilSquareIcon class="-mx-0.5 -mt-0.5" />
									</Button>
								</td>
							</tr>
							<tr v-for="game in games">
								<td>Gra</td>
								<td>{{ game.name }}</td>
								<td>
									<Button
										:href="route('admin.translations.index', { game_id: game.id })"
										class="warning-light xs uppercase"
										type="link"
										preserve-scroll>
										<PencilSquareIcon class="-mx-0.5 -mt-0.5" />
									</Button>
								</td>
							</tr>
							<tr v-for="feature in features">
								<td>Cecha</td>
								<td>{{ feature.game.name + ' - ' + feature.code }}</td>
								<td>
									<Button
										:href="
											route('admin.translations.index', {
												feature_id: feature.id,
											})
										"
										class="warning-light xs uppercase"
										type="link"
										preserve-scroll>
										<PencilSquareIcon class="-mx-0.5 -mt-0.5" />
									</Button>
								</td>
							</tr>
						</tbody>
					</table>

					<div v-else>
						<div class="mt-4 grid grid-cols-12 gap-x-4 gap-y-5 font-bold">
							<div class="flex-items-center col-span-2">Klucz</div>
							<div class="col-span-4">Polska wartość</div>
							<div class="col-span-5">Wartość</div>
							<div class="col-span-1"></div>
						</div>

						<div v-for="(translation, key) in translations">
							<form
								class="mt-4 grid grid-cols-12 gap-x-4 gap-y-5"
								@submit.prevent="
									forms[key].put(
										route('admin.translations.update', {
											key: key,
											feature_id: queryValue(currentUrl, 'feature_id'),
											game_id: queryValue(currentUrl, 'game_id'),
										}),
									)
								">
								<div class="col-span-2 flex items-center">
									{{ key }}
								</div>
								<div class="col-span-4 flex items-center">
									{{ baseLocaleTranslations[key] }}
								</div>
								<div class="col-span-5 flex items-center">
									<TextInput v-model="forms[key].value" />
								</div>
								<div class="col-span-1 flex items-center">
									<Button class="w-full" type="submit">Zapisz</Button>
								</div>
							</form>
						</div>
					</div>
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
import PanelLayout from '@/Layouts/PanelLayout.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import { User, Country, Feature, Game, SelectOption } from '@/Types/models';
import Card from '@/Components/Dashboard/Card.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import PencilSquareIcon from '@/Components/Dashboard/Icons/PencilSquareIcon.vue';
import TextInput from '@/Components/Dashboard/TextInput.vue';
import { useQueryString } from '@/Composables/useQueryString';
import { computed, onMounted, Ref, ref, watch } from 'vue';
import SimpleSelect from '@/Components/Dashboard/SimpleSelect.vue';
import { wTrans } from 'laravel-vue-i18n';
import DecisionModal from '@/Components/Dashboard/Modals/DecisionModal.vue';
import { useConfirmationDialog } from '@/Composables/useConfirmationDialog';

const props = defineProps<{
	countriesToCopy: Country[];
	games: Game[];
	features: Feature[];
	baseLocaleTranslations: Object;
	translations: Record<string, string>;
	entityName: String;
}>();

const { queryArray, queryValue } = useQueryString();
const { confirmationDialogShowing, showDialog, cancelDialog, confirmDialog, dialogContent } =
	useConfirmationDialog();

const forms: Ref<Record<string, any>> = ref({});

for (const [key, value] of Object.entries(props.translations)) {
	forms.value[key] = useForm({
		value: value,
	});
}

let currentPageLabel: Ref<string> = ref('');
let arr: Record<string, string> = queryArray(window.location.search);
if (!arr['game_id'] && !arr['feature_id']) {
	currentPageLabel.value = '';
} else if (!arr['game_id']) {
	currentPageLabel.value = `Cecha: ${props.entityName}`;
} else if (!arr['feature_id']) {
	currentPageLabel.value = `Gra: ${props.entityName}`;
} else {
	currentPageLabel.value = 'Ogólne';
}

const currentUrl: Ref<string> = ref('');

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
			`Czy na pewno chcesz przekopiować tłumaczenia: ${wTrans(`country.${fromCountryCode}`).value} => ${
				wTrans(`country.${toCountryCode}`).value
			} ?`,
			() => {
				router.visit(
					route('admin.translations.copy', {
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

onMounted(() => {
	currentUrl.value = window.location.href;
});
</script>

<style scoped>
td,
th {
	@apply px-2 py-2;
}

tr:nth-child(2n) {
	@apply bg-gray-1;
}
</style>
