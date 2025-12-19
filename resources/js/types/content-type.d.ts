export type FieldType =
    | 'text'
    | 'textarea'
    | 'number'
    | 'boolean'
    | 'select'
    | 'date'
    | 'datetime'
    | 'image'
    | 'file'
    | 'rich_text';

export interface FieldOption {
    label: string;
    value: string | number;
}

export interface FieldSchema {
    name: string;
    label: string;
    type: FieldType;
    required?: boolean;
    placeholder?: string;
    help_text?: string;
    default_value?: any;
    options?: FieldOption[];
    validation?: Record<string, any>;
}

export interface ContentType {
    id: number;
    name: string;
    slug: string;
    icon?: string;
    description?: string;
    schema: FieldSchema[];
    created_at: string;
    updated_at: string;
}

export interface DynamicFieldsProps {
    schema: FieldSchema[];
    modelValue: Record<string, any>;
}
