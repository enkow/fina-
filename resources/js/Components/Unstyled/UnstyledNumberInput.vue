<template>
  <button
    :disabled="internalValue <= min"
    v-bind="prevButtonClass && { class: prevButtonClass }"
    @click="internalValue -= step"
  >
    <slot name="prevButton" />
  </button>
  <div v-if="$slots.default" v-bind="$attrs">
    <slot />
  </div>
  <input
    type="number"
    :id="id"
    :min="min"
    :max="max"
    :value="internalValue"
    v-bind="$attrs"
    @input="
      isInputElement($event.target) &&
        (internalValue = Number($event.target.value))
    "
    v-else
  />
  <button
    :disabled="internalValue >= max"
    v-bind="nextButtonClass && { class: nextButtonClass }"
    @click="internalValue += step"
  >
    <slot name="nextButton" />
  </button>
</template>

<script lang="ts" setup>
import { isInputElement } from "@/Utils";
import { onMounted, ref, watch } from "vue";

const props = withDefaults(
  defineProps<{
    id?: string;
    prevButtonClass?: string;
    nextButtonClass?: string;
    step?: number;
    modelValue: number;
    min: number;
    max: number;
  }>(),
  { step: 1 }
);

const internalValue = ref(props.modelValue);

const emit = defineEmits(["update:modelValue"]);

const updateInputValue = (value: number) => {
  const newValue =
    value <= props.min ? props.min : value >= props.max ? props.max : value;

  internalValue.value = newValue;
  emit("update:modelValue", newValue);
};

watch(internalValue, updateInputValue);

watch(
  () => props.modelValue,
  (value) => {
    internalValue.value = value;
  }
);

onMounted(() => {
  updateInputValue(props.modelValue);
});
</script>
