<template>
	<PanelLayout :breadcrumbs="[{ href: '#', label: $t('settlement.online-payments') }]">
		<ContentContainer>
			<div class="col-span-12 xl:col-span-9">
				<SetupComponent :current="current" />
			</div>

			<ResponsiveHelper width="3">
				<template #mascot>
					<Mascot :type="17" />
				</template>
				<p>{{ usePage().props.generalTranslations['help-online-payments-content'] }}</p>
			</ResponsiveHelper>
		</ContentContainer>
	</PanelLayout>
</template>

<script lang="ts" setup>
import PanelLayout from '@/Layouts/PanelLayout.vue';
import ContentContainer from '@/Components/Dashboard/ContentContainer.vue';
import { PaymentMethod } from '@/Types/models';
import ResponsiveHelper from '@/Components/Dashboard/Help/ResponsiveHelper.vue';
import Mascot from '@/Components/Dashboard/Mascot.vue';
import { defineAsyncComponent } from 'vue';
import { ucFirst } from '@/Utils';
import { usePage } from '@inertiajs/vue3';

const props = defineProps<{
	requiredType: 'stripe';
	current: PaymentMethod;
}>();

const SetupComponent = defineAsyncComponent(
	() => import(`./PaymentMethods/${ucFirst(props.requiredType)}.vue`),
);
</script>
