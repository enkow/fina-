<template>
	<PanelLayout
		:breadcrumbs="[
			{ href: route('admin.clubs.index'), label: 'Kluby' },
			{ href: route('admin.clubs.edit', { club: club }), label: club.name },
		]">
		<ContentContainer>
			<div class="col-span-12">
				<Card>
					<template #header>
						<h2>Edytuj klub: {{ club.name }}</h2>
					</template>

					<div class="text-center text-sm font-medium text-gray-500 dark:border-gray-700 dark:text-gray-400">
						<ul class="mb-10 flex flex-wrap">
							<li
								v-for="(item, index) in {
									1: 'Dostęp',
									2: 'Dane klubu',
									3: 'Gry',
									4: 'Linki',
									5: 'Ustawienia',
									6: 'Produkty',
									7: 'Faktury',
									8:
										club.paymentMethod?.adminTab &&
										club.paymentMethod?.adminTab !== null &&
										(club.online_payments_enabled === 'external' || !club.paymentMethod.globalSettings)
											? 'Płatności online'
											: null,
								}"
								class="mr-2">
								<p
									v-if="item !== null"
									:class="{
										'border-blue-600 text-blue-600': currentTab === parseInt(index),
									}"
									class="inline-block cursor-pointer rounded-t-lg border-b border-transparent p-4 hover:border-gray-500 hover:text-gray-600 dark:hover:text-gray-500"
									@click="currentTab = parseInt(index)">
									{{ item }}
								</p>
							</li>
						</ul>
					</div>

					<ClubBooleans v-if="currentTab === 1" :club="club" />
					<ClubData v-if="currentTab === 2" :club="club" />

					<ClubGames
						v-if="currentTab === 3"
						:club="club"
						:custom-game-names="customGameNames"
						:disabled-games="disabledGames"
						:enabled-games="enabledGames" />

					<ClubLinks v-if="currentTab === 4" :club="club" />
					<ClubSettings
						v-if="currentTab === 5"
						:club="club"
						:settings="settings"
						:bulbsAdaptersFields="bulbsAdaptersFields" />
					<ClubProducts
						v-if="currentTab === 6"
						:club="club"
						:products="products"
						:commissions="commissions" />
					<ClubInvoices v-if="currentTab === 7" :club="club" />

					<PaymentMethodSetup v-if="currentTab === 8" :club="club" :key="club.paymentMethod?.adminTab" />
				</Card>
			</div>
		</ContentContainer>
	</PanelLayout>
</template>

<script lang="ts" setup>
import { Club, Game, ClubProduct as ClubProductType, Product } from '@/Types/models';
import PanelLayout from '@/Layouts/PanelLayout.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import Card from '@/Components/Dashboard/Card.vue';
import { useString } from '@/Composables/useString';
import { Ref, defineAsyncComponent, ref } from 'vue';
import ClubData from '@/Pages/Admin/Clubs/Tabs/Data.vue';
import ClubGames from '@/Pages/Admin/Clubs/Tabs/Games.vue';
import ClubBooleans from '@/Pages/Admin/Clubs/Tabs/Booleans.vue';
import ClubLinks from '@/Pages/Admin/Clubs/Tabs/Links.vue';
import ClubInvoices from '@/Pages/Admin/Clubs/Tabs/Invoices.vue';
import ClubSettings from '@/Pages/Admin/Clubs/Tabs/ClubSettings.vue';
import ClubProducts from '@/Pages/Admin/Clubs/Tabs/Products.vue';
import { ucFirst } from '@/Utils';

const { capitalize } = useString();

const props = defineProps<{
	club: Club;
	disabledGames: Game[];
	enabledGames: Game[];
	settings: Record<string, Record<string, any>>;
	customGameNames: Record<number, Record<number, string>>;
	products: Product[];
	commissions: [];
	bulbsAdaptersFields: {
		[key: string]: string[];
	};
}>();

const PaymentMethodSetup = defineAsyncComponent(
	() => import(`./Tabs/PaymentMethods/${ucFirst(props.club.paymentMethod?.adminTab || '')}.vue`),
);

const currentTab: Ref<number> = ref(1);
</script>
