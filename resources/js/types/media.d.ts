export interface Media {
    id: number;
    name: string;
    file_name: string;
    mime_type: string;
    size: number;
    collection_name: string;
    url: string;
    thumbnail_url?: string;
    created_at: string;
    updated_at: string;
}

export interface MediaUploadProgress {
    loaded: number;
    total: number;
    percentage: number;
}

export interface MediaPickerProps {
    modelValue?: Media | Media[] | null;
    multiple?: boolean;
    collection?: string;
    accept?: string;
}
