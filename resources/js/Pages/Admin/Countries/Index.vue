<template>
	<PanelLayout :breadcrumbs="[{ href: route('admin.countries.index'), label: 'Kraje' }]">
		<ContentContainer>
			<div class="col-span-12">
				<Card>
					<template #header>
						<h2>Kraje</h2>
					</template>

					<Table
						:header="{
							code: 'Kod',
							name: 'Kraj',
							currency: 'Waluta',
							active: 'Czy włączony',
							clubs_count: 'Zarejestrowanych klubów',
						}"
						:items="countries"
						:narrow="['actions', 'active']"
						table-name="admin_countries">
						<template #cell_active="props">
							<div class="flex justify-center">
								<div
									class="cursor-pointer"
									@click="
										router.visit(
											route('admin.countries.toggle-active', {
												country: props.item.id,
											}),
											{ method: 'post' },
										)
									">
									<SuccessSquareIcon v-if="props?.item.active" />
									<DangerSquareIcon v-else />
								</div>
							</div>
						</template>

						<template #cell_code="props">
							{{ props.item.code.toUpperCase() }}
						</template>

						<template #cell_name="props">
							{{ $t(`country.${props?.item.code.toUpperCase()}`) }}
						</template>

						<template #cell_actions="props">
							<div class="flex items-center space-x-1">
								<Button
									:href="route('admin.countries.edit', { country: props.item.id })"
									class="warning-light xs uppercase"
									type="link">
									<PencilSquareIcon class="-mx-0.5 -mt-0.5" />
								</Button>
							</div>
						</template>
					</Table>
				</Card>
			</div>
		</ContentContainer>
	</PanelLayout>
</template>

<script lang="ts" setup>
import { Country } from '@/Types/models';
import PanelLayout from '@/Layouts/PanelLayout.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import { PaginatedResource } from '@/Types/responses';
import Card from '@/Components/Dashboard/Card.vue';
import Table from '@/Components/Dashboard/Table.vue';
import PencilSquareIcon from '@/Components/Dashboard/Icons/PencilSquareIcon.vue';
import Button from '@/Components/Dashboard/Buttons/Button.vue';
import SuccessSquareIcon from '@/Components/Dashboard/Icons/SuccessSquareIcon.vue';
import DangerSquareIcon from '@/Components/Dashboard/Icons/DangerSquareIcon.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps<{
	countries: PaginatedResource<Country>;
}>();
</script>
