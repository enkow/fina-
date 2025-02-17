<template>
	<div class="grid w-full grid-cols-12 gap-x-4 gap-y-5">
		<div class="col-span-12 space-y-4 md:col-span-6">
			<TimersStatus
				:club="club"
				:customRoute="
					route('admin.clubs.settings.update', {
						club: club,
						key: 'timers_status',
					})
				"
				:setting="settings.timers_status" />
			<CalendarSyncStatus
				:club="club"
				:customRoute="
					route('admin.clubs.settings.update', {
						club: club,
						key: 'calendar_sync_status',
					})
				"
				:setting="settings.calendar_sync_status" />
		</div>
		<div class="col-span-12 space-y-4 md:col-span-6">
			<PricePerPersonType
				v-for="setting in getSettingsByFeatureType('price_per_person_type')"
				:club="club"
				:setting="setting"
				setting-icon-color="info" />
			<BulbInfo
				v-for="setting in getSettingsByFeatureType('bulb_status')"
				:club="club"
				:setting="setting"
				setting-icon-color="info"
				:bulbs-adapters-fields="bulbsAdaptersFields" />
		</div>
	</div>
</template>

<script lang="ts" setup>
import { Club } from '@/Types/models';
import TimersStatus from '@/Components/Dashboard/Settings/TimersStatus.vue';
import CalendarSyncStatus from '@/Components/Dashboard/Settings/CalendarSyncStatus.vue';
import { SettingEntity } from '@/Types/responses';
import { useSettings } from '@/Composables/useSettings';
import PricePerPersonType from '@/Components/Dashboard/Settings/PricePerPersonType.vue';
import BulbInfo from '@/Components/Dashboard/Settings/BulbInfo.vue';

const props = defineProps<{
	club: Club;
	settings: {
		[key: string]: SettingEntity;
	};
	bulbsAdaptersFields: {
		[key: string]: string[];
	};
}>();

const { getSettingsByFeatureType } = useSettings(props.settings);
</script>
