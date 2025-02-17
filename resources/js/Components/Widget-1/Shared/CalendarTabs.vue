<template>
  <TabGroup :selectedIndex="modelValue" @change="changeTab">
    <TabList
      class="mx-auto flex w-fit flex-wrap rounded-2xl border-2 border-ui-green p-2"
    >
      <Tab as="template" v-slot="{ selected }" v-for="tab in tabs">
        <button
          :class="[
            'flex-1 whitespace-nowrap rounded-lg px-6 py-1.5 text-lg font-medium focus:outline-none',
            selected ? 'bg-ui-green text-white' : 'text-ui-green',
          ]"
        >
          {{ tab }}
        </button>
      </Tab>
    </TabList>
    <TabPanels class="mt-7">
      <slot />
    </TabPanels>
  </TabGroup>
</template>

<script lang="ts" setup>
import { Tab, TabGroup, TabList, TabPanels } from "@headlessui/vue";

defineProps<{
  tabs: string[];
  modelValue: number;
}>();

const emit = defineEmits(["update:modelValue"]);

function changeTab(index: number) {
  emit("update:modelValue", index);
}
</script>
