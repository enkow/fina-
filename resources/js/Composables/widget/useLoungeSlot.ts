import { useWidgetStore } from '@/Stores/widget';
import { computed } from 'vue';
import type { Slot } from '@/Types/models';


export function useLoungeSlot(slot: Slot){
    const widgetStore = useWidgetStore();

    const slotLoungeData = computed(() =>
    { const loungeFeature = slot.features.find(
         ({ type }) => type === 'slot_has_lounge'
     );
 
     if (!loungeFeature?.pivot?.data){
         return null;
     }
 
     const {max, min, status} = JSON.parse(loungeFeature.pivot.data)
 
     return {
         max: Number(max), 
         min: Number(min), 
         status: Boolean(status),
         translations: loungeFeature.translations
     }
 }
 );
 
 const loungeFeatureData = computed(() => {
     return {
         translations: widgetStore.gameFeatures.slot_has_lounge[0].translations as {[key: string]: string},
         id: Number(widgetStore.gameFeatures.slot_has_lounge[0].id)
     } || null;
 })
 
 const loungeMessage = computed(() =>
 {
     if(!slotLoungeData.value?.status || !loungeFeatureData.value.translations){
         return ''
     }
 
     return `${loungeFeatureData.value.translations['slots-capacity']}: ${slotLoungeData.value.min}-${slotLoungeData.value.max}`;
 });

 return {
    loungeFeatureData,
    loungeMessage,
    slotLoungeData
 }
}