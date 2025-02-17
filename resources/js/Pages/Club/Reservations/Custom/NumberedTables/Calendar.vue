<template>
	<div class="grid w-full grid-cols-1 gap-x-10 gap-y-10 sm:px-6 lg:grid-cols-2 lg:px-8">
		<div v-for="parentSlotFillData in parentSlotsFillData">
			<table class="table w-full table-auto">
				<thead>
					<tr>
						<th>
							{{ parentSlotFillData.name }}
							<br />
							<p class="text-xs">
								({{ parentSlotFillData.totalCapacity }}
								{{
									game.features.find((feature) => feature.type === 'book_singular_slot_by_capacity')
										.translations['capacity-value-postfix']
								}})
							</p>
						</th>
						<th class="narrow text-center capitalize">
							{{ $t('main.occupied') }}
						</th>
						<th class="narrow text-center capitalize">{{ $t('main.free') }}</th>
						<th>
							{{
								game.features.find((feature) => feature.type === 'book_singular_slot_by_capacity')
									.translations['free-table-numbers']
							}}
						</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="(capacityData, capacity) in parentSlotFillData['capacities']">
						<td>
							{{
								game.features.find((feature) => feature.type === 'book_singular_slot_by_capacity')
									.translations['slot'] +
								' ' +
								capacity +
								game.features.find((feature) => feature.type === 'book_singular_slot_by_capacity')
									.translations['capacity-value-postfix-short']
							}}.
						</td>
						<td class="text-center !font-bold">
							{{ capacityData.all_count - (capacityData.freeSlots?.length ?? 0) }}
						</td>
						<td class="cell-number">{{ capacityData.freeSlots?.length ?? 0 }}</td>
						<td>
							<div class="flex flex-wrap">
								<span
									v-for="slot in capacityData.freeSlots"
									@click="emit('selectSlot', slot.id, parentSlotFillData.id)"
									class="my-0.5 mr-1 flex h-6 min-w-6 cursor-pointer items-center justify-center rounded bg-brand-base px-1.5 font-medium leading-5 text-white">
									{{ slot.name }}
								</span>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</template>

<script lang="ts" setup>
import { Game, Slot } from '@/Types/models';

const props = defineProps<{
	game: Game;
	parentSlotsFillData: {
		id: number;
		name: string;
		capacities: {
			[capacity: number]: {
				all_count: number;
				freeSlots: {
					id: number;
					name: string;
				}[];
			};
		};
		totalCapacity: number;
	}[];
}>();

const emit = defineEmits<{
	(e: 'selectSlot'): void;
}>();
</script>

<style scoped>
table {
	th,
	td {
		@apply ml-5 border-r border-r-gray-2 px-3 py-5;
	}

	td:last-child,
	th:last-child {
		@apply border-r-0;
	}

	thead {
		th {
			@apply text-left text-xs font-semibold;

			&:first-child {
				@apply w-40 text-lg;
			}
		}
	}

	tbody {
		tr {
			@apply border-t border-t-gray-2;
		}

		td {
			@apply font-light;

			&.cell-number {
				@apply text-center font-bold;
			}
		}
	}

	.narrow {
		width: 1%;
		white-space: nowrap;
	}
}
</style>
