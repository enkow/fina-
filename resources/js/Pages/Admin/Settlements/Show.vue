<template>
	<PanelLayout
		:breadcrumbs="[
			{ href: route('admin.settlements.index'), label: $t('settlement.plural') },
			{ href: route('admin.settlements.show-club', { club: club.id }), label: club.name },
			{
				href: route('admin.settlements.show', { club: club.id, settlement: invoice.id }),
				label: $t('settlement.settlement-from-range', {
					from: invoice?.from,
					to: invoice?.to,
				}),
			},
		]">
		<div class="space-z-6 lg:space-z-0 flex w-full flex-col-reverse flex-wrap px-10 py-8 lg:flex-row">
			<div class="w-full space-y-6 lg:w-1/2 lg:pr-5">
				<GameInfoSettlements
					v-for="game in games"
					:invoice="invoice"
					:game="game"
					:currency-symbol="currencySymbol"
					:url-reservations="
						route('admin.settlements.club-reservations', {
							settlement: invoice.id,
							club: club.id,
							game: game.model?.id,
						})
					"
					:route-export="{
						name: 'admin.settlements.club-game-download',
						options: {
							settlement: invoice.id,
							club: club.id,
							game: game.model?.id,
						},
					}" />

				<FeeInfoSettlements :currency-symbol="currencySymbol" :invoice="invoice" />
			</div>
			<div class="mb-6 w-full space-y-6 lg:w-1/2 lg:pl-5">
				<WideHelper float="left" class="w-full">
					<template #mascot>
						<Mascot :type="19" class="-ml-2" />
					</template>
					<template #button>
						<Button
							:href="usePage().props.generalTranslations['help-discount-codes-link']"
							class="grey w-full sm:w-fit"
							type="link">
							{{ $t('help.learn-more') }}
						</Button>
					</template>
					{{ usePage().props.generalTranslations['help-discount-codes-content'] }}
				</WideHelper>
				<SummarySettlements :club="club" :invoice="invoice" :currency-symbol="currencySymbol" />
			</div>
		</div>
	</PanelLayout>
</template>

<script lang="ts" setup>
import PanelLayout from '@/Layouts/PanelLayout.vue';
import GameInfoSettlements from '@/Components/Dashboard/Settlements/GameInfoSettlements.vue';
import FeeInfoSettlements from '@/Components/Dashboard/Settlements/FeeInfoSettlements.vue';
import SummarySettlements from '@/Components/Dashboard/Settlements/SummarySettlements.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import { Club, Invoice, User } from '@/Types/models';
import WideHelper from '@/Components/Dashboard/Help/WideHelper.vue';
import Mascot from '@/Components/Dashboard/Mascot.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useReservations } from '@/Composables/useReservations';

const { currencySymbols } = useReservations();

const props = defineProps<{
	club: Club;
	invoice: Invoice;
	user: User;
}>();

const games = computed(() => props.invoice.items.filter((item) => !item.settings.period));

const currencySymbol = computed(() => currencySymbols[props.club?.country?.currency || 'PLN']);
</script>
