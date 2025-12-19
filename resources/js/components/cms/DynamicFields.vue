<script setup lang="ts">
import { computed } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { Card } from '@/components/ui/card';
import Editor from '@/components/cms/Editor.vue';
import MediaPicker from '@/components/cms/MediaPicker.vue';
import type { FieldSchema } from '@/types/content-type';
import type { Media } from '@/types/media';

interface Props {
    schema: FieldSchema[];
    modelValue: Record<string, any>;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: Record<string, any>): void;
}>();

const formData = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const updateField = (fieldName: string, value: any) => {
    formData.value = {
        ...formData.value,
        [fieldName]: value,
    };
};

const getFieldValue = (fieldName: string) => {
    return formData.value[fieldName];
};
</script>

<template>
    <div class="space-y-4">
        <div v-for="field in schema" :key="field.name" class="space-y-2">
            <!-- Text Field -->
            <template v-if="field.type === 'text'">
                <Label :for="field.name">
                    {{ field.label }}
                    <span v-if="field.required" class="text-destructive">*</span>
                </Label>
                <Input
                    :id="field.name"
                    :value="getFieldValue(field.name)"
                    :placeholder="field.placeholder"
                    :required="field.required"
                    @input="updateField(field.name, ($event.target as HTMLInputElement).value)"
                />
                <p
                    v-if="field.help_text"
                    class="text-xs text-muted-foreground"
                >
                    {{ field.help_text }}
                </p>
            </template>

            <!-- Textarea Field -->
            <template v-else-if="field.type === 'textarea'">
                <Label :for="field.name">
                    {{ field.label }}
                    <span v-if="field.required" class="text-destructive">*</span>
                </Label>
                <Textarea
                    :id="field.name"
                    :value="getFieldValue(field.name)"
                    :placeholder="field.placeholder"
                    :required="field.required"
                    rows="4"
                    @input="updateField(field.name, ($event.target as HTMLTextAreaElement).value)"
                />
                <p
                    v-if="field.help_text"
                    class="text-xs text-muted-foreground"
                >
                    {{ field.help_text }}
                </p>
            </template>

            <!-- Number Field -->
            <template v-else-if="field.type === 'number'">
                <Label :for="field.name">
                    {{ field.label }}
                    <span v-if="field.required" class="text-destructive">*</span>
                </Label>
                <Input
                    :id="field.name"
                    type="number"
                    :value="getFieldValue(field.name)"
                    :placeholder="field.placeholder"
                    :required="field.required"
                    @input="updateField(field.name, parseFloat(($event.target as HTMLInputElement).value))"
                />
                <p
                    v-if="field.help_text"
                    class="text-xs text-muted-foreground"
                >
                    {{ field.help_text }}
                </p>
            </template>

            <!-- Boolean Field -->
            <template v-else-if="field.type === 'boolean'">
                <div class="flex items-center justify-between rounded-lg border p-3">
                    <div>
                        <Label :for="field.name">{{ field.label }}</Label>
                        <p
                            v-if="field.help_text"
                            class="text-xs text-muted-foreground"
                        >
                            {{ field.help_text }}
                        </p>
                    </div>
                    <Switch
                        :id="field.name"
                        :checked="getFieldValue(field.name) || false"
                        @update:checked="updateField(field.name, $event)"
                    />
                </div>
            </template>

            <!-- Select Field -->
            <template v-else-if="field.type === 'select'">
                <Label :for="field.name">
                    {{ field.label }}
                    <span v-if="field.required" class="text-destructive">*</span>
                </Label>
                <select
                    :id="field.name"
                    :value="getFieldValue(field.name)"
                    :required="field.required"
                    class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                    @change="updateField(field.name, ($event.target as HTMLSelectElement).value)"
                >
                    <option value="">
                        {{ field.placeholder || 'Select an option' }}
                    </option>
                    <option
                        v-for="option in field.options"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </select>
                <p
                    v-if="field.help_text"
                    class="text-xs text-muted-foreground"
                >
                    {{ field.help_text }}
                </p>
            </template>

            <!-- Date Field -->
            <template v-else-if="field.type === 'date'">
                <Label :for="field.name">
                    {{ field.label }}
                    <span v-if="field.required" class="text-destructive">*</span>
                </Label>
                <Input
                    :id="field.name"
                    type="date"
                    :value="getFieldValue(field.name)"
                    :required="field.required"
                    @input="updateField(field.name, ($event.target as HTMLInputElement).value)"
                />
                <p
                    v-if="field.help_text"
                    class="text-xs text-muted-foreground"
                >
                    {{ field.help_text }}
                </p>
            </template>

            <!-- DateTime Field -->
            <template v-else-if="field.type === 'datetime'">
                <Label :for="field.name">
                    {{ field.label }}
                    <span v-if="field.required" class="text-destructive">*</span>
                </Label>
                <Input
                    :id="field.name"
                    type="datetime-local"
                    :value="getFieldValue(field.name)"
                    :required="field.required"
                    @input="updateField(field.name, ($event.target as HTMLInputElement).value)"
                />
                <p
                    v-if="field.help_text"
                    class="text-xs text-muted-foreground"
                >
                    {{ field.help_text }}
                </p>
            </template>

            <!-- Image Field -->
            <template v-else-if="field.type === 'image'">
                <Label>
                    {{ field.label }}
                    <span v-if="field.required" class="text-destructive">*</span>
                </Label>
                <MediaPicker
                    :model-value="getFieldValue(field.name) as Media"
                    :label="`Select ${field.label}`"
                    accept="image/*"
                    @update:model-value="updateField(field.name, $event)"
                />
                <p
                    v-if="field.help_text"
                    class="text-xs text-muted-foreground"
                >
                    {{ field.help_text }}
                </p>
            </template>

            <!-- File Field -->
            <template v-else-if="field.type === 'file'">
                <Label>
                    {{ field.label }}
                    <span v-if="field.required" class="text-destructive">*</span>
                </Label>
                <MediaPicker
                    :model-value="getFieldValue(field.name) as Media"
                    :label="`Select ${field.label}`"
                    @update:model-value="updateField(field.name, $event)"
                />
                <p
                    v-if="field.help_text"
                    class="text-xs text-muted-foreground"
                >
                    {{ field.help_text }}
                </p>
            </template>

            <!-- Rich Text Field -->
            <template v-else-if="field.type === 'rich_text'">
                <Label>
                    {{ field.label }}
                    <span v-if="field.required" class="text-destructive">*</span>
                </Label>
                <Editor
                    :model-value="getFieldValue(field.name)"
                    :placeholder="field.placeholder"
                    @update:model-value="updateField(field.name, $event)"
                />
                <p
                    v-if="field.help_text"
                    class="text-xs text-muted-foreground"
                >
                    {{ field.help_text }}
                </p>
            </template>
        </div>
    </div>
</template>
