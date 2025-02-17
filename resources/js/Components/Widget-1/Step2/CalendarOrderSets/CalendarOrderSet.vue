<template>
    <li>
        <article
            class="flex h-full flex-col overflow-hidden break-words rounded-10 bg-white"
        >
            <img
                :src="`/images/set-images/${set.photo}`"
                :alt="set.name"
                class="w-full h-full object-scale-down hidden md:block"
            />
            <img
                :src="`/images/set-images/${set.mobile_photo}`"
                :alt="set.name"
                class="w-full h-full object-scale-down md:hidden"
            />

            <div class="flex flex-1 flex-col p-5">
                <div class="flex">
                    <h3
                        class="w-full overflow-auto text-xl font-bold text-ui-black"
                    >
                        {{ set.name }}
                    </h3>
                    <p
                        class="whitespace-nowrap text-2xl font-medium text-ui-green"
                    >
                        {{ formatPrice(set.price) }} {{ currencySymbol }}
                    </p>
                </div>
                <p class="mb-4 mt-1 text-lg text-ui-black/50">
                    {{ set.description }}
                </p>
                <WidgetNumberInput
                    v-if="typeof set.selected === 'number'"
                    :min="0"
                    :max="set.available || 0"
                    v-model="set.selected"
                />
            </div>
        </article>
    </li>
</template>

<script lang="ts" setup>
import WidgetNumberInput from '@/Components/Widget/Ui/WidgetNumberInput.vue';
import { Set } from '@/Types/models';
import { formatPrice } from '@/Utils';

const { set } = defineProps<{
    currencySymbol: string;
    set: Set;
}>();
</script>
