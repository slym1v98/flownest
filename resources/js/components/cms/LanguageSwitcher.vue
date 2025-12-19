<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { computed, ref } from 'vue';

interface Props {
  modelValue: string;
  availableLanguages?: Array<{ code: string; name: string }>;
}

const props = withDefaults(defineProps<Props>(), {
  availableLanguages: () => [
    { code: 'en', name: 'English' },
    { code: 'vi', name: 'Tiếng Việt' },
    { code: 'jp', name: '日本語' },
  ],
});

const emit = defineEmits<{
  'update:modelValue': [value: string];
}>();

const currentLanguage = computed({
  get: () => props.modelValue,
  set: (value: string) => emit('update:modelValue', value),
});

const languageName = computed(() => {
  const lang = props.availableLanguages.find((l) => l.code === currentLanguage.value);
  return lang?.name || currentLanguage.value.toUpperCase();
});
</script>

<template>
  <div class="flex items-center gap-2">
    <span class="text-sm font-medium">Language:</span>
    <Select v-model="currentLanguage">
      <SelectTrigger class="w-[180px]">
        <SelectValue :placeholder="languageName" />
      </SelectTrigger>
      <SelectContent>
        <SelectItem
          v-for="lang in availableLanguages"
          :key="lang.code"
          :value="lang.code"
        >
          {{ lang.name }}
        </SelectItem>
      </SelectContent>
    </Select>
  </div>
</template>
