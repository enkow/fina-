<template>
	<PanelLayout
		:breadcrumbs="[
			{
				href: route('club.special-offers.index'),
				label: $t('special-offer.plural'),
			},
			{
				href: route('club.special-offers.create'),
				label: $t('special-offer.add-special-offer'),
			},
		]">
		<ContentContainer>
			<div class="col-span-12 xl:col-span-9">
				<Card>
					<template #header>
						<h2>{{ $t('special-offer.add-special-offer') }}</h2>
					</template>

					<form
						class="mt-9 grid grid-cols-4 gap-x-4 gap-y-5"
						@submit.prevent="
							form.post(route('club.special-offers.store'), {
								preserveState: true,
								preserveScroll: true,
							})
						">
						<div class="col-span-4 space-y-2 md:col-span-2">
							<InputLabel :value="capitalize($t('special-offer.special-offer-name'))" required />
							<TextInput v-model="form.name" />
							<div v-if="form.errors.name" class="error">
								{{ form.errors.name }}
							</div>

							<div class="flex items-center">
								<Checkbox
									id="setAsActive"
									v-model="form.active_by_default"
									:checked="form.active_by_default" />
								<InputLabel
									:value="$t('special-offer.customer-must-choose-this-promotion')"
									class="ml-3"
									for="setAsActive" />
							</div>
							<div v-if="form.errors.active_by_default" class="error">
								{{ form.errors.active_by_default }}
							</div>
						</div>

						<div class="col-span-4 space-y-2 md:col-span-1">
							<InputLabel :value="capitalize($t('main.game-type'))" required />
							<SimpleSelect v-model="form.game_id" :options="userGameOptions" />
							<div v-if="form.errors.game_id" class="error">
								{{ form.errors.game_id }}
							</div>
						</div>

						<div class="col-span-4 space-y-2 md:col-span-1">
							<InputLabel :value="capitalize($t('special-offer.percent-discount'))" required />
							<TextInput v-model="form.value" />
							<div v-if="form.errors.value" class="error">
								{{ form.errors.value }}
							</div>
						</div>

						<div class="col-span-4 space-y-2 md:col-span-4">
							<InputLabel :value="capitalize($t('special-offer.description'))" />
							<TextareaInput v-model="form.description" />
							<div v-if="form.errors.description" class="error">
								{{ form.errors.description }}
							</div>
						</div>

						<div class="col-span-4 space-y-2 md:col-span-2">
							<InputLabel :value="capitalize($t('special-offer.week-day-range'))" required />
							<Tagify
								:onChange="updateActiveDays"
								:settings="{
									whitelist: Object.values(usePage().props.generalTranslations['week-day-short']),
									maxTags: 15,
									dropdown: {
										maxItems: 20,
										classname: 'customer-tags',
										enabled: 0,
										closeOnSelect: true,
									},
								}"
								mode="text" />
							<input v-model="form.active_week_days" type="hidden" />
							<div v-if="form.errors.active_week_days" class="error">
								{{ form.errors.active_week_days }}
							</div>
						</div>

						<div class="col-span-4 space-y-2 md:col-span-1">
							<InputLabel :value="capitalize($t('main.duration'))" />
							<SimpleSelect v-model="form.duration" :options="timeOptions(30)" />
							<div v-if="form.errors.duration" class="error">
								{{ form.errors.duration }}
							</div>
						</div>

						<div class="col-span-4 space-y-2 md:col-span-1">
							<InputLabel :value="props.gameTranslations[form.game_id ?? 1]['slots-quantity']" />
							<TextInput v-model="form.slots" />
							<div v-if="form.errors.slots" class="error">
								{{ form.errors.slots }}
							</div>
						</div>

						<div class="col-span-4 space-y-2 md:col-span-2">
							<div class="mb-3 flex items-center space-x-2">
								<input
									id="time-range-type-start"
									v-model="form.time_range_type"
									class="cursor-pointer"
									type="radio"
									value="start" />
								<InputLabel
									:required="form.time_range_type === 'start'"
									:value="capitalize($t('special-offer.reservation-start-hour-range'))"
									for="time-range-type-start" />
							</div>
							<div
								:class="{
									'disabled opacity-30': form.time_range_type === 'end',
								}"
								class="space-y-3"
								@click="form.time_range_type = 'start'">
								<div v-for="(item, index) in form.time_range['start']" class="flex w-full space-x-2.5">
									<div class="w-5/12">
										<SimpleSelect
											v-model="form.time_range['start'][index].from"
											:disabled="form.time_range_type === 'end'"
											:options="props.clubSettings['full_hour_start_reservations_status'].value ? fromOptions : fromOptionsHalfHourly"
											:placeholder="capitalize($t('main.action.choose'))" />
									</div>
									<div class="w-5/12">
										<SimpleSelect
											v-model="form.time_range['start'][index].to"
											:disabled="form.time_range_type === 'end'"
											:options="props.clubSettings['full_hour_start_reservations_status'].value ? toOptions : toOptionsHalfHourly"
											:placeholder="capitalize($t('main.action.choose'))" />
									</div>
									<div class="flex w-2/12 items-center">
										<PlusButton
											v-if="form.time_range['start'].length === 1"
											:class="{
												'!cursor-not-allowed': form.time_range_type === 'end',
											}"
											@click="form.time_range['start'].push({ from: null, to: null })" />
										<TrashButton v-else @click="form.time_range['start'].splice(index, 1)" />
									</div>
								</div>
								<div class="flex">
									<div class="w-5/12"></div>
									<div class="w-5/12"></div>
									<div class="ml-5 w-2/12">
										<PlusButton
											v-if="form.time_range['start'].length > 1"
											@click="form.time_range['start'].push({ from: null, to: null })" />
									</div>
								</div>
								<div
									v-if="form.time_range_type === 'start' && form.errors['time_range.start']"
									class="error !mt-0">
									{{ form.errors['time_range.start'] }}
								</div>
							</div>
						</div>

						<div class="col-span-4 space-y-2 md:col-span-2">
							<div class="mb-3 flex items-center space-x-2">
								<input
									id="time-range-type-end"
									v-model="form.time_range_type"
									class="cursor-pointer"
									type="radio"
									value="end" />
								<InputLabel
									:required="form.time_range_type === 'end'"
									:value="capitalize($t('special-offer.reservation-end-hour-range'))"
									for="time-range-type-end" />
							</div>
							<div
								:class="{
									'disabled opacity-30': form.time_range_type === 'start',
								}"
								class="space-y-3"
								@click="form.time_range_type = 'end'">
								<div v-for="(item, index) in form.time_range['end']" class="flex w-full space-x-2.5">
									<div class="w-5/12">
										<SimpleSelect
											v-model="form.time_range['end'][index].from"
											:disabled="form.time_range_type === 'start'"
											:options="props.clubSettings['full_hour_start_reservations_status'].value ? fromOptions : fromOptionsHalfHourly"
											:placeholder="capitalize($t('main.action.choose'))" />
									</div>
									<div class="w-5/12">
										<SimpleSelect
											v-model="form.time_range['end'][index].to"
											:disabled="form.time_range_type === 'start'"
											:options="props.clubSettings['full_hour_start_reservations_status'].value ? toOptions: toOptionsHalfHourly"
											:placeholder="capitalize($t('main.action.choose'))" />
									</div>
									<div class="flex w-2/12 items-center">
										<PlusButton
											v-if="form.time_range['end'].length === 1"
											:class="{
												'!cursor-not-allowed': form.time_range_type === 'start',
											}"
											@click="form.time_range['end'].push({ from: null, to: null })" />
										<TrashButton v-else @click="form.time_range['end'].splice(index, 1)" />
									</div>
								</div>
								<div class="flex">
									<div class="w-5/12"></div>
									<div class="w-5/12"></div>
									<div class="ml-5 w-2/12">
										<PlusButton
											v-if="form.time_range['end'].length > 1"
											@click="form.time_range['end'].push({ from: null, to: null })" />
									</div>
								</div>
							</div>
							<div v-if="form.time_range_type === 'end' && form.errors['time_range.end']" class="error !mt-0">
								{{ form.errors['time_range.end'] }}
							</div>
						</div>
						<div class="col-span-4 space-y-2 md:col-span-2">
							<div class="w-full">
								<h4 class="mb-1 text-xl font-medium">
									{{ $t('special-offer.when-applies') }}
								</h4>
								<lorem add="30w" />
							</div>

							<div class="w-full">
								<div class="w-full">
									<div v-if="form.applies_default === false" class="w-full">
										<InputLabel :value="capitalize($t('main.date'))" />
										<div v-for="(item, index) in form.when_applies" class="mt-2 flex w-full space-x-2.5">
											<div class="w-5/12">
												<SimpleDatepicker
													v-model="form.when_applies[index].from"
													:placeholder="capitalize($t('main.action.choose'))"
													input-with-icon />
											</div>
											<div class="w-5/12">
												<SimpleDatepicker
													v-model="form.when_applies[index].to"
													:placeholder="capitalize($t('main.action.choose'))"
													input-with-icon />
											</div>
											<div class="mt-1.5 flex w-2/12">
												<PlusButton
													v-if="form.when_applies.length === 1"
													@click="form.when_applies.push({ from: null, to: null })" />
												<TrashButton v-else @click="form.when_applies.splice(index, 1)" />
											</div>
										</div>
										<div v-if="form.when_applies.length > 1" class="flex w-full space-x-2.5">
											<div class="w-5/12"></div>
											<div class="w-5/12"></div>
											<div class="flex w-2/12 items-center">
												<PlusButton @click="form.when_applies.push({ from: null, to: null })" />
											</div>
										</div>
										<div v-if="form.errors['when_applies']" class="error">
											{{ form.errors['when_applies'] }}
										</div>
									</div>

									<div class="!mt-3 flex items-center">
										<Checkbox
											id="appliesDefault"
											v-model="form.applies_default"
											:checked="form.applies_default" />
										<InputLabel :value="$t('special-offer.all-the-time')" class="ml-3" for="appliesDefault" />
										<div v-if="form.errors.applies_default" class="error !mt-0">
											{{ form.errors.applies_default }}
										</div>
									</div>
								</div>
							</div>
							<div v-if="form.applies_default" class="w-full">
								<h4 class="mb-1 mt-8 text-xl font-medium">
									{{ $t('special-offer.when-not-applies') }}
								</h4>
								<lorem add="30w" />

								<div class="w-full">
									<div class="w-full">
										<InputLabel :value="capitalize($t('main.date'))" />
										<div v-for="(item, index) in form.when_not_applies" class="mt-2 flex w-full space-x-2.5">
											<div class="w-5/12">
												<SimpleDatepicker
													v-model="form.when_not_applies[index].from"
													:placeholder="capitalize($t('main.action.choose'))"
													input-with-icon />
											</div>
											<div class="w-5/12">
												<SimpleDatepicker
													v-model="form.when_not_applies[index].to"
													:placeholder="capitalize($t('main.action.choose'))"
													input-with-icon />
											</div>
											<div class="mt-1.5 flex w-2/12">
												<PlusButton
													v-if="form.when_not_applies.length === 1"
													@click="form.when_not_applies.push({ from: null, to: null })" />
												<TrashButton v-else @click="form.when_not_applies.splice(index, 1)" />
											</div>
										</div>
										<div v-if="form.when_not_applies.length > 1" class="flex w-full space-x-2.5">
											<div class="w-5/12"></div>
											<div class="w-5/12"></div>
											<div class="flex w-2/12 items-center">
												<PlusButton @click="form.when_not_applies.push({ from: null, to: null })" />
											</div>
										</div>
										<div v-if="form.errors['when_not_applies']" class="error">
											{{ form.errors['when_not_applies'] }}
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="col-span-4 space-y-2 md:col-span-2">
							<InputLabel :value="$t('special-offer.photo')" />
							<div class="relative">
								<label
									class="flex h-12 w-full cursor-pointer items-center space-x-3 rounded-md border border-gray-3 pl-4"
									for="photo-upload">
									<svg
										fill="none"
										height="15"
										viewBox="0 0 13 15"
										width="13"
										xmlns="http://www.w3.org/2000/svg">
										<path
											clip-rule="evenodd"
											d="M0.546875 13.4529C0.546875 13.2275 0.636428 13.0113 0.795834 12.8519C0.95524 12.6925 1.17144 12.6029 1.39688 12.6029H11.5969C11.8223 12.6029 12.0385 12.6925 12.1979 12.8519C12.3573 13.0113 12.4469 13.2275 12.4469 13.4529C12.4469 13.6784 12.3573 13.8946 12.1979 14.054C12.0385 14.2134 11.8223 14.3029 11.5969 14.3029H1.39688C1.17144 14.3029 0.95524 14.2134 0.795834 14.054C0.636428 13.8946 0.546875 13.6784 0.546875 13.4529ZM3.34593 4.70389C3.18657 4.54449 3.09706 4.32833 3.09706 4.10294C3.09706 3.87755 3.18657 3.66139 3.34593 3.50199L5.89593 0.951993C6.05532 0.792643 6.27149 0.703125 6.49688 0.703125C6.72226 0.703125 6.93843 0.792643 7.09783 0.951993L9.64783 3.50199C9.80266 3.66231 9.88833 3.87702 9.8864 4.09988C9.88446 4.32275 9.79507 4.53594 9.63747 4.69354C9.47987 4.85114 9.26668 4.94053 9.04382 4.94247C8.82095 4.9444 8.60624 4.85873 8.44593 4.70389L7.34688 3.60484V10.0529C7.34688 10.2784 7.25732 10.4946 7.09792 10.654C6.93851 10.8134 6.72231 10.9029 6.49688 10.9029C6.27144 10.9029 6.05524 10.8134 5.89583 10.654C5.73643 10.4946 5.64688 10.2784 5.64688 10.0529V3.60484L4.54782 4.70389C4.38843 4.86324 4.17226 4.95276 3.94688 4.95276C3.72149 4.95276 3.50532 4.86324 3.34593 4.70389Z"
											fill="#9AA1B3"
											fill-rule="evenodd" />
									</svg>

									<p
										:class="{
											'text-gray-6': form.photo,
											'text-gray-3': !form.photo,
										}">
										{{ form.photo?.name ?? $t('set.add-photo-placeholder') }}
									</p>
								</label>
								<input
									id="photo-upload"
									class="absolute top-0 h-12 w-full opacity-0"
									type="file"
									@input="form.photo = $event.target.files[0]" />
							</div>
							<div v-if="form.errors.photo" class="error">
								{{ form.errors.photo }}
							</div>
						</div>

						<div class="col-span-4 flex items-center justify-end">
							<Checkbox id="setAsActive" v-model="form.active" :checked="form.active" />
							<InputLabel :value="$t('discount-code.set-as-active')" class="ml-3" for="setAsActive" />
						</div>

						<div class="col-span-4">
							<div class="flex w-full justify-end">
								<Button class="lg !w-full !px-0 md:!w-64" type="submit">{{ $t('main.action.save') }}</Button>
							</div>
						</div>
					</form>
				</Card>
			</div>

			<ResponsiveHelper width="3">
				<template #mascot>
					<Mascot :type="19" class="w-full" />
				</template>
				<template #button>
					<Button
						:href="usePage().props.generalTranslations['help-special-offers-link']"
						class="grey xl:!px-0"
						type="link">
						{{ $t('help.learn-more') }}
					</Button>
				</template>
				{{ usePage().props.generalTranslations['help-special-offers-content'] }}
			</ResponsiveHelper>
		</ContentContainer>
	</PanelLayout>
</template>

<script lang="ts" setup>
import PanelLayout from '@/Layouts/PanelLayout.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import TextInput from '@/Components/Dashboard/TextInput.vue';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import Card from '@/Components/Dashboard/Card.vue';
import SimpleSelect from '@/Components/Dashboard/SimpleSelect.vue';
import Checkbox from '@/Components/Auth/Checkbox.vue';
import PlusButton from '@/Components/Dashboard/Buttons/PlusButton.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import ResponsiveHelper from '@/Components/Dashboard/Help/ResponsiveHelper.vue';
import TextareaInput from '@/Components/Dashboard/TextareaInput.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { useString } from '@/Composables/useString';
import { useSelectOptions } from '@/Composables/useSelectOptions';
import Tagify from '@/Components/Dashboard/Tagify.vue';
import { SelectOption } from '@/Types/models';
import Mascot from '@/Components/Dashboard/Mascot.vue';
import TrashButton from '@/Components/Dashboard/Buttons/TrashButton.vue';
import SimpleDatepicker from '@/Components/Dashboard/SimpleDatepicker.vue';
import { watch } from 'vue';

const { capitalize } = useString();

let rangePlaceholder = {
	from: null,
	to: null,
};

const form = useForm({
	name: null,
	active: false,
	active_by_default: false,
	value: 10,
	game_id: null,
	description: null,
	active_week_days: [],
	duration: null,
	slots: null,
	time_range_type: 'start',
	time_range: {
		start: [structuredClone(rangePlaceholder)],
		end: [structuredClone(rangePlaceholder)],
	},
	when_applies: [structuredClone(rangePlaceholder)],
	applies_default: true,
	when_not_applies: [structuredClone(rangePlaceholder)],
	photo: null,
});

watch(
	() => form.applies_default,
	() => {
		form.when_not_applies = [
			{
				from: null,
				to: null,
			},
		];
		form.when_applies = [
			{
				from: null,
				to: null,
			},
		];
	},
);

const props = usePage().props;

function updateActiveDays(e: any) {
	form.active_week_days = [];
	for (const [index, value] of Object.entries(usePage().props.generalTranslations['week-day-short'])) {
		if (
			JSON.parse(e.target.value)
				.map((x: { value: string }) => x.value)
				.includes(value)
		) {
			form.active_week_days.push(parseInt(index) + 1);
		}
	}
}

const { fromOptions, toOptions, fromOptionsHalfHourly, toOptionsHalfHourly, timeOptions, gameOptions } = useSelectOptions();

const userGameOptions: SelectOption[] = gameOptions();
</script>
