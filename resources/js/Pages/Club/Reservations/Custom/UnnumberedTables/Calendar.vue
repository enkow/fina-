<template>
	<div class="mx-auto px-6 lg:px-8">
		<div class="overflow-hidden bg-white py-4 sm:rounded-lg">
			<div class="grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 md:gap-x-28">
				<div v-for="parentSlotFillData in parentSlotsFillData">
					<table class="first table w-full table-auto">
						<thead>
							<tr class="border-b border-b-gray-2">
								<th class="border-r border-r-gray-2 py-3.5 text-left !text-lg font-semibold">
									{{
										game.features.find((feature) => feature.type === 'slot_has_parent').translations[
											'parent-slot'
										]
									}}
								</th>
								<th class="min w-24 border-r border-r-gray-2 px-3 font-medium">
									{{ capitalize($t('main.occupied')) }}
								</th>
								<th class="min w-24 px-3 font-medium">
									{{ capitalize($t('main.free')) }}
								</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td class="border-r border-r-gray-2 py-3.5">
									{{ parentSlotFillData.name }}
								</td>
								<td class="border-r border-r-gray-2 text-center font-semibold">
									{{ parentSlotFillData.occupied }}
								</td>
								<td class="text-center font-semibold">
									{{ parentSlotFillData.capacity - parentSlotFillData.occupied }}
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</template>

<script lang="ts" setup>
import { Game } from '@/Types/models';
import { capitalize } from 'vue';

const props = defineProps<{
	game: Game;
	parentSlotsFillData: {
		id: number;
		name: string;
		capacity: number;
		occupied: number;
	}[];
}>();
</script>

<style scoped>
.min {
	width: 1%;
	white-space: nowrap;
}
</style>
