# Phase 2: Centralized Media Manager & Dynamic Content System

This document explains the new features added in Phase 2 of the Flownest CMS.

## Features Implemented

### 1. Centralized Media Manager

A centralized media library for managing all uploaded files.

#### Access
Navigate to `/admin/media` to access the media library.

#### Features
- **Grid/List View**: Toggle between grid and list views for better file management
- **Drag & Drop Upload**: Drag files directly into the upload area
- **File Search**: Search media by name or filename
- **Image Thumbnails**: Automatically generated thumbnails for images (300x300px)
- **Actions**: Copy URL, Download, and Delete media files
- **Supported Formats**: JPG, PNG, GIF, WEBP, PDF, DOC, DOCX
- **File Size Limit**: 10MB per file

#### API Endpoints
- `GET /admin/media` - Media library index page
- `POST /admin/media` - Upload new files
- `DELETE /admin/media/{id}` - Delete a media file
- `GET /admin/media/list` - Get media list for picker (JSON API)

### 2. MediaPicker Component

A reusable Vue component for selecting media from the library.

#### Usage in Vue Components

```vue
<script setup lang="ts">
import MediaPicker from '@/components/cms/MediaPicker.vue';
import type { Media } from '@/types/media';
import { ref } from 'vue';

const selectedMedia = ref<Media | null>(null);
</script>

<template>
  <MediaPicker
    v-model="selectedMedia"
    label="Select Featured Image"
    accept="image/*"
  />
</template>
```

#### Props
- `modelValue`: Selected media (single or array)
- `multiple`: Allow multiple selection (default: false)
- `collection`: Filter by collection name (default: 'default')
- `accept`: File types to accept (default: 'image/*')
- `label`: Button label (default: 'Select Media')

### 3. Custom Post Types (Content Types)

Define custom content types with dynamic schemas.

#### Database Structure
The `content_types` table stores content type definitions:
- `name`: Display name
- `slug`: Unique identifier
- `icon`: Icon name
- `description`: Content type description
- `schema`: JSON array of field definitions

#### Sample Content Types
Run the seeder to create sample content types:

```bash
php artisan db:seed --class=ContentTypeSeeder
```

Sample types include:
- **Projects**: Portfolio projects with client info and completion dates
- **Services**: Service offerings with pricing
- **Testimonials**: Client reviews with ratings
- **Team Members**: Staff profiles with social links

### 4. DynamicFields Component

Renders form fields dynamically based on a content type schema.

#### Usage

```vue
<script setup lang="ts">
import DynamicFields from '@/components/cms/DynamicFields.vue';
import { ref } from 'vue';
import type { FieldSchema } from '@/types/content-type';

const schema: FieldSchema[] = [
  {
    name: 'title',
    label: 'Project Title',
    type: 'text',
    required: true,
  },
  {
    name: 'price',
    label: 'Price',
    type: 'number',
    required: false,
  },
];

const formData = ref({});
</script>

<template>
  <DynamicFields v-model="formData" :schema="schema" />
</template>
```

#### Supported Field Types
- `text`: Single-line text input
- `textarea`: Multi-line text input
- `number`: Numeric input
- `boolean`: Toggle switch
- `select`: Dropdown select
- `date`: Date picker
- `datetime`: Date and time picker
- `image`: Image picker (uses MediaPicker)
- `file`: File picker (uses MediaPicker)
- `rich_text`: Rich text editor (uses TipTap)

### 5. SeoManager Component

Advanced SEO management with live previews.

#### Features
- **Meta Title & Description**: With character count indicators
- **Meta Keywords**: Comma-separated keywords
- **Google Search Preview**: See how your page appears in Google
- **Facebook Preview**: Open Graph preview
- **Twitter Card Preview**: Twitter card preview
- **Character Counters**: 
  - Title: 30-60 characters recommended
  - Description: 120-160 characters recommended
- **Custom Social Tags**: Override OG and Twitter tags independently

#### Usage

```vue
<script setup lang="ts">
import SeoManager from '@/components/cms/SeoManager.vue';
import { ref } from 'vue';

const seoData = ref({
  meta_title: '',
  meta_description: '',
  meta_keywords: '',
  og_title: '',
  og_description: '',
  og_image: '',
  twitter_title: '',
  twitter_description: '',
  twitter_image: '',
});
</script>

<template>
  <SeoManager
    v-model="seoData"
    fallback-title="My Page Title"
    fallback-description="My page description"
    site-url="https://example.com"
  />
</template>
```

#### Integration
The SeoManager has been integrated into:
- Post Create page (`/admin/posts/create`)
- Post Edit page (`/admin/posts/{id}/edit`)

## TypeScript Types

### Media Type
```typescript
interface Media {
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
```

### ContentType Type
```typescript
interface ContentType {
  id: number;
  name: string;
  slug: string;
  icon?: string;
  description?: string;
  schema: FieldSchema[];
  created_at: string;
  updated_at: string;
}

interface FieldSchema {
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
```

## Configuration

### Media Library Configuration
Edit `config/media-library.php` to customize:
- Disk storage location
- Maximum file size
- Queue settings
- Image optimization settings

### Image Conversions
The system automatically generates:
- `thumb`: 300x300px thumbnail (non-queued)
- `medium`: 800x600px preview (queued)
- `preview`: 800x600px for posts (queued)

## Database Migrations

Run migrations to create required tables:

```bash
php artisan migrate
```

New tables:
- `content_types`: Stores content type definitions
- `media_items`: Stores media item metadata (for standalone media)
- `media`: Spatie MediaLibrary table (created automatically)

## Development

### Adding New Field Types
To add a new field type to DynamicFields:

1. Add the type to `FieldType` in `resources/js/types/content-type.d.ts`
2. Add a new template section in `DynamicFields.vue`
3. Update the `ContentType::validateSchema()` method

### Customizing Media Conversions
Edit the `registerMediaConversions()` method in:
- `app/Models/MediaItem.php` (for standalone media)
- `app/Models/Post.php` (for post-related media)

## Best Practices

1. **Media File Naming**: Use descriptive names for uploaded files
2. **SEO Optimization**: 
   - Keep meta titles between 30-60 characters
   - Keep meta descriptions between 120-160 characters
   - Always add alt text to images
3. **Content Types**: 
   - Create focused, single-purpose content types
   - Use clear, descriptive field labels
   - Add help text for complex fields
4. **Performance**:
   - Use thumbnails in list views
   - Enable queue for image conversions in production
   - Consider CDN for media storage

## Troubleshooting

### Media Upload Fails
- Check file size limits in `config/media-library.php`
- Ensure `storage/app/public` is writable
- Verify `php.ini` upload limits

### Images Not Displaying
- Run `php artisan storage:link` to create symbolic link
- Check disk configuration in `config/filesystems.php`

### Queue Not Processing
- Run `php artisan queue:work` to process queued jobs
- Check queue configuration in `.env`

## Future Enhancements

Potential additions for Phase 3:
- Media folders/categories
- Bulk media operations
- Image editing (crop, resize)
- Custom content type CRUD pages
- Frontend rendering for custom content types
- Media usage tracking
- Advanced search and filtering
