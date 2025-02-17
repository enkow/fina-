<template>
	<PanelLayout
		:breadcrumbs="[
			{
				href: route('club.opening-hours.show'),
				label: $t('opening-hours.singular'),
			},
			{
				href: route('club.opening-hours-exceptions.edit', {
					opening_hours_exception: openingHoursException,
				}),
				label: $t('opening-hours.edit-exception'),
			},
		]">
		<ContentContainer>
			<div class="col-span-12 xl:col-span-9">
				<Card>
					<template #header>
						<h2>{{ $t('opening-hours.singular') }}</h2>
					</template>
					<form
						class="grid grid-cols-2 gap-x-4 gap-y-5"
						@submit.prevent="
							form.put(
								route('club.opening-hours-exceptions.update', {
									opening_hours_exception: openingHoursException,
								}),
								{ preserveScroll: true },
							)
						">
						<div class="input-group col-span-2 md:col-span-1">
							<InputLabel :value="capitalize($t('main.date'))" />
							<SimpleDatepicker
								v-model="form.start_at"
								:disabled="!['admin', 'manager'].includes(usePage().props.user.type)"
								:input-with-icon="true" />
							<div v-if="form.errors.start_at" class="error">
								{{ form.errors.start_at }}
							</div>
						</div>

						<div class="input-group col-span-2 md:col-span-1">
							<InputLabel :value="capitalize($t('main.end-date'))" />
							<SimpleDatepicker
								v-model="form.end_at"
								:disabled="!['admin', 'manager'].includes(usePage().props.user.type)"
								:input-with-icon="true" />
							<div v-if="form.errors.end_at" class="error">
								{{ form.errors.end_at }}
							</div>
						</div>

						<div class="input-group col-span-2 md:col-span-1">
							<InputLabel :value="capitalize($t('opening-hours.club-opening'))" />
							<SimpleSelect
								v-model="form.club_start"
								:disabled="form.club_closed || !['admin', 'manager'].includes(usePage().props.user.type)"
								:class="{
									'disabled-readable':
										!form.club_closed && !['admin', 'manager'].includes(usePage().props.user.type),
								}"
								:options="options" />
							<div v-if="form.errors.club_start" class="error">
								{{ form.errors.club_start }}
							</div>
						</div>

						<div class="input-group col-span-2 md:col-span-1">
							<InputLabel :value="capitalize($t('opening-hours.club-closing'))" />
							<SimpleSelect
								v-model="form.club_end"
								:disabled="form.club_closed || !['admin', 'manager'].includes(usePage().props.user.type)"
								:class="{
									'disabled-readable':
										!form.club_closed && !['admin', 'manager'].includes(usePage().props.user.type),
								}"
								:options="options" />
							<div v-if="form.errors.club_end" class="error">
								{{ form.errors.club_end }}
							</div>
						</div>

						<div class="col-span-2 flex items-center space-x-3 md:col-span-2">
							<Checkbox
								id="club_closed"
								v-model="form.club_closed"
								:disabled="!['admin', 'manager'].includes(usePage().props.user.type)"
								:checked="form.club_closed" />
							<InputLabel :value="capitalize($t('opening-hours.closed'))" for="club_closed" />
							<div v-if="form.errors.club_closed" class="error">
								{{ form.errors.club_closed }}
							</div>
						</div>

						<div class="input-group col-span-2 md:col-span-1">
							<InputLabel :value="capitalize($t('opening-hours.online-reservations-opening'))" />
							<SimpleSelect
								v-model="form.reservation_start"
								:disabled="
									form.reservation_closed || !['admin', 'manager'].includes(usePage().props.user.type)
								"
								:class="{
									'disabled-readable':
										!form.reservation_closed && !['admin', 'manager'].includes(usePage().props.user.type),
								}"
								:options="options" />
							<div v-if="form.errors.reservation_start" class="error">
								{{ form.errors.reservation_start }}
							</div>
						</div>

						<div class="input-group col-span-2 md:col-span-1">
							<InputLabel :value="capitalize($t('opening-hours.online-reservations-closing'))" />
							<SimpleSelect
								v-model="form.reservation_end"
								:disabled="
									form.reservation_closed || !['admin', 'manager'].includes(usePage().props.user.type)
								"
								:class="{
									'disabled-readable':
										!form.reservation_closed && !['admin', 'manager'].includes(usePage().props.user.type),
								}"
								:options="options" />
							<div v-if="form.errors.reservation_end" class="error">
								{{ form.errors.reservation_end }}
							</div>
						</div>

						<div class="col-span-2 flex items-center space-x-3 md:col-span-2">
							<Checkbox
								id="reservation_closed"
								v-model="form.reservation_closed"
								:checked="form.reservation_closed"
								:disabled="form.club_closed || !['admin', 'manager'].includes(usePage().props.user.type)" />
							<InputLabel
								:value="capitalize($t('opening-hours.no-online-reservations'))"
								for="reservation_closed" />
							<div v-if="form.errors.reservation_closed" class="error">
								{{ form.errors.reservation_closed }}
							</div>
						</div>

						<div></div>
						<Button type="submit" v-if="['admin', 'manager'].includes(usePage().props.user.type)">
							{{ capitalize($t('main.action.update')) }}
						</Button>
						<Button
							class="disabled"
							v-tippy="{ allowHTML: true }"
							:content="'<p style=\'font-size:12px\'>' + $t('settings.no-permissions') + '</p>'"
							v-else>
							{{ capitalize($t('main.action.update')) }}
						</Button>
					</form>
				</Card>
			</div>
			<ResponsiveHelper width="3">
				<template #mascot>
					<Mascot :type="16" />
				</template>
				<template #button>
					<Button
						:href="usePage().props.generalTranslations['help-opening-hours-exception-link']"
						class="grey xl:!px-0"
						type="link">
						{{ $t('help.learn-more') }}
					</Button>
				</template>
				{{ usePage().props.generalTranslations['help-opening-hours-exception-content'] }}
			</ResponsiveHelper>
		</ContentContainer>
	</PanelLayout>
</template>

<script lang="ts" setup>
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import ResponsiveHelper from '@/Components/Dashboard/Help/ResponsiveHelper.vue';
import PanelLayout from '@/Layouts/PanelLayout.vue';
import Card from '@/Components/Dashboard/Card.vue';
import SimpleDatepicker from '@/Components/Dashboard/SimpleDatepicker.vue';
import InputLabel from '@/Components/Dashboard/InputLabel.vue';
import Checkbox from '@/Components/Dashboard/Checkbox.vue';
import { OpeningHoursException, SelectOption } from '@/Types/models';
import { useForm, usePage } from '@inertiajs/vue3';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import SimpleSelect from '@/Components/Dashboard/SimpleSelect.vue';
import { useString } from '@/Composables/useString';
import { useSelectOptions } from '@/Composables/useSelectOptions';
import Mascot from '@/Components/Dashboard/Mascot.vue';
import { watch, ref, Ref } from 'vue';
import { usePanelStore } from '@/Stores/panel';

const { capitalize } = useString();
const { pad } = useString();
const { noPermissionTooltipHtml } = usePanelStore();

const props = defineProps<{
	openingHoursException: OpeningHoursException;
}>();

const options: Ref<Array<SelectOption>> = ref([]);
for (let i = 0; i <= 23; i++) {
	let control: number = 0;
	while (control < 60) {
		options.value.push({
			code: pad(i.toString(), 2) + ':' + pad(control.toString(), 2),
			label: pad(i.toString(), 2) + ':' + pad(control.toString(), 2),
		});
		control += 30;
	}
}

const form = useForm({
	start_at: props.openingHoursException.start_at,
	end_at: props.openingHoursException.end_at,
	club_start: props.openingHoursException.club_start,
	club_end: props.openingHoursException.club_end,
	club_closed: props.openingHoursException.club_closed,
	reservation_start: props.openingHoursException.reservation_start,
	reservation_end: props.openingHoursException.reservation_end,
	reservation_closed: props.openingHoursException.reservation_closed,
});

const initReservationClosedStatus = props.openingHoursException.reservation_closed;

watch(
	() => form.club_closed,
	() => {
		if (form.club_closed) {
			form.reservation_closed = true;
		} else {
			form.reservation_closed = initReservationClosedStatus;
		}
	},
);
</script>
