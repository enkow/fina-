<template>
	<PanelLayout :breadcrumbs="[{ href: route('admin.invoices.index'), label: 'Faktury' }]">
		<ContentContainer>
			<div class="col-span-12">
				<Card>
					<template #header>
						<h2>Faktury</h2>
					</template>
				</Card>
			</div>
		</ContentContainer>
	</PanelLayout>
</template>

<script lang="ts" setup>
import PanelLayout from '@/Layouts/PanelLayout.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import Card from '@/Components/Dashboard/Card.vue';
</script>
