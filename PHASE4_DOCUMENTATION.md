# Phase 4: Multi-language, RBAC, Workflow & Audit Log

This document explains the Phase 4 features for the Flownest Hybrid CMS - multi-language support, advanced role-based access control, approval workflow, and audit logging.

## Overview

Phase 4 introduces:
- Multi-language content support with translation management
- Advanced RBAC with Editor, Publisher, and Admin roles
- Approval workflow with pending review status
- Complete audit log with revision history and restoration

## Features Implemented

### 1. Multi-language Content Architecture

Support for multiple languages across the CMS.

#### HasTranslations Trait

Location: `app/Traits/HasTranslations.php`

**Purpose:** Enable translatable attributes on any model

**Features:**
- Automatic translation retrieval based on current locale
- Fallback to default locale if translation not available
- Individual translation management per attribute
- Support for JSON-stored translations

**Usage in Models:**

```php
use App\Traits\HasTranslations;

class Post extends Model
{
    use HasTranslations;

    protected $translatable = [
        'title',
        'excerpt',
    ];

    protected $casts = [
        'title' => 'array',
        'excerpt' => 'array',
    ];
}
```

**Working with Translations:**

```php
// Get translation for current locale
$title = $post->title; // Automatically returns translation

// Get translation for specific locale
$titleEn = $post->getTranslation('title', 'en');
$titleVi = $post->getTranslation('title', 'vi');

// Get all translations
$allTitles = $post->getTranslations('title');
// Returns: ['en' => 'Title', 'vi' => 'Tiêu đề', 'jp' => 'タイトル']

// Set translation for specific locale
$post->setTranslation('title', 'vi', 'Tiêu đề mới');
$post->save();

// Set multiple translations at once
$post->setTranslations('title', [
    'en' => 'New Title',
    'vi' => 'Tiêu đề mới',
    'jp' => '新しいタイトル',
]);
$post->save();
```

#### LanguageSwitcher Component

Location: `resources/js/components/cms/LanguageSwitcher.vue`

**Purpose:** UI component for switching between languages

**Props:**
- `modelValue` (string): Current language code
- `availableLanguages` (array): List of available languages

**Default Languages:**
- `en` - English
- `vi` - Tiếng Việt  
- `jp` - 日本語

**Usage:**

```vue
<script setup>
import LanguageSwitcher from '@/components/cms/LanguageSwitcher.vue';
import { ref } from 'vue';

const currentLang = ref('en');
</script>

<template>
  <LanguageSwitcher v-model="currentLang" />
</template>
```

### 2. Advanced RBAC (Role-Based Access Control)

Complete permission system using Spatie Permission package.

#### Roles

**Editor:**
- Create and edit posts
- Manage media
- Submit posts for review
- Cannot publish or delete posts

**Publisher:**
- All Editor permissions
- Approve/reject posts
- Publish posts
- Cannot manage users or roles

**Admin:**
- All permissions
- Manage users and roles
- Full system access

#### Permissions

| Permission | Editor | Publisher | Admin |
|-----------|--------|-----------|-------|
| view-posts | ✅ | ✅ | ✅ |
| create-posts | ✅ | ✅ | ✅ |
| edit-posts | ✅ | ✅ | ✅ |
| delete-posts | ❌ | ❌ | ✅ |
| publish-posts | ❌ | ✅ | ✅ |
| manage-users | ❌ | ❌ | ✅ |
| manage-roles | ❌ | ❌ | ✅ |
| manage-media | ✅ | ✅ | ✅ |
| view-analytics | ❌ | ❌ | ✅ |

#### Setup

**1. Run Migrations:**

```bash
php artisan migrate
```

**2. Seed Roles and Permissions:**

```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```

**3. Assign Role to User:**

```php
$user = User::find(1);
$user->assignRole('Editor');

// Or assign during creation
$editor = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => bcrypt('password'),
]);
$editor->assignRole('Editor');
```

#### Checking Permissions

**In Controllers:**

```php
// Check if user has permission
if ($request->user()->can('publish-posts')) {
    // User can publish
}

// Check if user has role
if ($request->user()->hasRole('Admin')) {
    // User is admin
}

// Abort if user doesn't have permission
abort_unless($request->user()->can('edit-posts'), 403);
```

**In Blade/Vue:**

```php
@can('publish-posts')
    <button>Publish</button>
@endcan
```

**In Routes:**

```php
Route::middleware('can:publish-posts')->group(function () {
    Route::post('posts/{post}/publish', [PostController::class, 'publish']);
});
```

#### Permission Management UI

Location: `resources/js/pages/admin/users/Permissions.vue`

**Access:** `/admin/users/permissions`

**Features:**
- View all users and their roles
- Assign roles to users
- Remove roles from users
- View role permissions
- Search users by name or email

**Requirements:** User must have `manage-roles` permission

### 3. Approval Workflow

Post approval system with notifications.

#### Post Statuses

- `draft` - Initial state, being edited
- `pending_review` - Submitted for review by editor
- `published` - Approved and published by publisher
- `archived` - Archived/removed from public view

#### Workflow Actions

**Submit for Review:**

```php
// Route: POST /admin/posts/{post}/submit-for-review
// Permission: edit-posts

$post = Post::find(1);
// Changes status to 'pending_review'
// Notifies all Publishers
```

**Approve Post:**

```php
// Route: POST /admin/posts/{post}/approve
// Permission: publish-posts

// Body:
[
    'review_notes' => 'Looks great!' // Optional
]

// Changes status to 'published'
// Records reviewer and review time
```

**Reject Post:**

```php
// Route: POST /admin/posts/{post}/reject
// Permission: publish-posts

// Body:
[
    'review_notes' => 'Needs more work' // Required
]

// Changes status back to 'draft'
// Records reviewer and review notes
```

#### Workflow Fields

Added to `posts` table:

- `reviewed_by` - ID of user who reviewed (nullable)
- `reviewed_at` - Timestamp of review (nullable)
- `review_notes` - Notes from reviewer (nullable)

#### PostPendingReview Notification

Location: `app/Notifications/PostPendingReview.php`

**Channels:** Mail, Database

**Triggered when:** Editor submits post for review

**Recipients:** All users with Publisher role

**Email Content:**
- Post title
- Author name
- Link to review post

**Database Content:**
- Post ID
- Post title
- Author name
- Action URL

### 4. Activity Logs & Revision History

Complete audit trail for all post changes.

#### PostRevision Model

Location: `app/Models/PostRevision.php`

**Fields:**
- `post_id` - Related post
- `user_id` - User who created revision
- `title` - Post title at this revision
- `slug` - Post slug at this revision
- `content` - Post content at this revision
- `excerpt` - Post excerpt at this revision
- `status` - Post status at this revision
- `is_featured` - Featured flag at this revision
- `seo_data` - SEO data at this revision
- `reason` - Reason for creating this revision
- `created_at` - When revision was created

#### Creating Revisions

**Automatic Creation:**

Revisions are automatically created:
- When a post is created (reason: "Initial version")
- When a post is updated (reason: "Post updated")
- When workflow actions occur (reason: specific to action)

**Manual Creation:**

```php
$post = Post::find(1);

// Create revision with reason
$revision = $post->createRevision('Before major changes');

// Revision stores complete snapshot of post
```

#### Restoring from Revision

**Via API:**

```php
// Route: POST /admin/posts/{post}/revisions/{revision}/restore
// Permission: edit-posts

// Restores post to the state saved in revision
// Creates two new revisions:
// 1. Before restore (saves current state)
// 2. After restore (documents the restore action)
```

**In Code:**

```php
$post = Post::find(1);
$revision = $post->revisions()->first();

// Create backup before restoring
$post->createRevision('Before restoring to revision #' . $revision->id);

// Restore post data
$post->update([
    'title' => $revision->title,
    'slug' => $revision->slug,
    'content' => $revision->content,
    'excerpt' => $revision->excerpt,
    // ... other fields
]);

// Create revision after restoring
$post->createRevision('Restored from revision #' . $revision->id);
```

#### RevisionHistory Component

Location: `resources/js/components/cms/RevisionHistory.vue`

**Purpose:** Display and manage post revisions

**Props:**
- `revisions` (array): Array of revision objects
- `postId` (number): ID of the post

**Features:**
- Timeline view of all revisions
- Display revision metadata (date, user, reason)
- Status badges for each revision
- View detailed revision information
- Restore to any previous revision
- Confirmation before restoration

**Usage:**

```vue
<script setup>
import RevisionHistory from '@/components/cms/RevisionHistory.vue';
import { usePage } from '@inertiajs/vue3';

const { post } = usePage().props;
</script>

<template>
  <RevisionHistory 
    :revisions="post.revisions" 
    :post-id="post.id" 
  />
</template>
```

## API Routes

### Workflow Routes

```
POST   /admin/posts/{post}/submit-for-review    - Submit post for review
POST   /admin/posts/{post}/approve               - Approve post (Publishers)
POST   /admin/posts/{post}/reject                - Reject post (Publishers)
POST   /admin/posts/{post}/revisions/{id}/restore - Restore from revision
```

### Permission Management Routes

```
GET    /admin/users/permissions                  - View permissions page
POST   /admin/users/{user}/assign-role           - Assign role to user
POST   /admin/users/{user}/remove-role           - Remove role from user
```

## Configuration

### Available Locales

Edit `config/app.php`:

```php
'locale' => 'en',
'fallback_locale' => 'en',
'locales' => ['en', 'vi', 'jp'],
```

### Permission Guard

Edit `config/permission.php`:

```php
'defaults' => [
    'guard' => 'web',
    'cache' => [
        'expiration_time' => \DateInterval::createFromDateString('24 hours'),
    ],
],
```

## Database Schema

### Posts Table Updates

```sql
ALTER TABLE posts 
ADD COLUMN reviewed_by BIGINT UNSIGNED NULL,
ADD COLUMN reviewed_at TIMESTAMP NULL,
ADD COLUMN review_notes TEXT NULL,
MODIFY COLUMN status ENUM('draft', 'pending_review', 'published', 'archived');

ALTER TABLE posts
ADD CONSTRAINT posts_reviewed_by_foreign 
FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL;
```

### Post Revisions Table

```sql
CREATE TABLE post_revisions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    post_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    title JSON NOT NULL,
    slug VARCHAR(255) NOT NULL,
    content JSON NULL,
    excerpt JSON NULL,
    status ENUM('draft', 'pending_review', 'published', 'archived'),
    is_featured BOOLEAN DEFAULT FALSE,
    seo_data JSON NULL,
    reason TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_post_created (post_id, created_at),
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

## Testing

### Run All Phase 4 Tests

```bash
php artisan test --filter="WorkflowTest|RolePermissionTest|PostRevisionTest"
```

### Test Coverage

**RolePermissionTest** (9 tests):
- Roles creation verification
- Permissions creation verification
- Role permission assignments
- User role assignment
- Admin role management
- Access control enforcement

**WorkflowTest** (5 tests):
- Submit for review functionality
- Post approval process
- Post rejection process
- Permission restrictions
- Revision tracking on workflow actions

**PostRevisionTest** (6 tests):
- Revision creation on post creation
- Revision creation on post update
- Revision data integrity
- Post restoration from revision
- Revision ordering (latest first)
- Multiple revision support

## Best Practices

### Translations

1. **Always provide fallback translations:**
   ```php
   'title' => [
       'en' => 'English Title',
       'vi' => 'Tiêu đề tiếng Việt',
       'jp' => '日本語のタイトル',
   ]
   ```

2. **Use getTranslation() for specific locales:**
   ```php
   $titleEn = $post->getTranslation('title', 'en', false); // No fallback
   ```

3. **Keep translation keys consistent across the app**

### Permissions

1. **Check permissions in controllers, not views:**
   ```php
   // Good
   public function publish(Post $post) {
       abort_unless(auth()->user()->can('publish-posts'), 403);
   }

   // Avoid relying only on view checks
   ```

2. **Use middleware for route-level protection:**
   ```php
   Route::middleware('can:publish-posts')->group(function () {
       // Protected routes
   });
   ```

3. **Assign roles, not individual permissions when possible**

### Workflow

1. **Always create revisions before major changes:**
   ```php
   $post->createRevision('Before status change');
   $post->update(['status' => 'published']);
   ```

2. **Provide clear review notes:**
   ```php
   $post->update([
       'status' => 'published',
       'review_notes' => 'Great article! Fixed minor typo in paragraph 3.',
   ]);
   ```

3. **Use notifications to keep team informed:**
   - Editors get notified when posts are approved/rejected
   - Publishers get notified when posts need review

### Revisions

1. **Provide meaningful revision reasons:**
   ```php
   $post->createRevision('Fixed grammar issues');
   $post->createRevision('Added new section about security');
   ```

2. **Review revision history before major changes**

3. **Use restore feature carefully - it creates 2 new revisions**

## Troubleshooting

### Permissions Not Working

**Problem:** User with role still can't access features

**Solutions:**
- Clear permission cache: `php artisan permission:cache-reset`
- Verify role assignment: `$user->roles`
- Check middleware is registered in `bootstrap/app.php`

### Translations Not Showing

**Problem:** Always getting fallback locale

**Solutions:**
- Check `APP_LOCALE` in `.env`
- Verify translatable attributes in model: `$translatable = []`
- Ensure data is stored as JSON array in database
- Check current locale: `App::getLocale()`

### Revisions Not Created

**Problem:** Revisions table empty after updates

**Solutions:**
- Verify `createRevision()` is called in controller
- Check `post_revisions` table exists
- Ensure `user_id` is set on post
- Check for database errors in logs

### Workflow Actions Failing

**Problem:** 403 errors when submitting/approving posts

**Solutions:**
- Verify user has required role/permission
- Check route middleware configuration
- Ensure Spatie Permission is properly installed
- Run `php artisan config:clear` and `php artisan cache:clear`

## Future Enhancements

Potential additions for future phases:

- **Multi-language UI:**
  - Translate admin interface
  - Language-specific form validation
  - RTL language support

- **Advanced Workflow:**
  - Multi-step approval process
  - Role-based review assignments
  - Scheduled publishing
  - Draft scheduling

- **Enhanced Audit Log:**
  - Visual diff between revisions
  - Bulk restore operations
  - Revision comments
  - Change notifications

- **Permission Improvements:**
  - Custom permission creation via UI
  - Permission templates
  - Temporary permissions
  - Permission inheritance

## Migration Guide

If upgrading from Phase 3:

1. **Install Spatie Permission:**
   ```bash
   composer require spatie/laravel-permission
   ```

2. **Run Migrations:**
   ```bash
   php artisan migrate
   ```

3. **Seed Roles and Permissions:**
   ```bash
   php artisan db:seed --class=RolesAndPermissionsSeeder
   ```

4. **Assign Roles to Existing Users:**
   ```php
   $admins = User::whereIn('email', ['admin@example.com'])->get();
   foreach ($admins as $admin) {
       $admin->assignRole('Admin');
   }
   ```

5. **Update Post Data to Use Translations:**
   ```php
   Post::all()->each(function ($post) {
       if (is_string($post->getRawOriginal('title'))) {
           $post->update([
               'title' => ['en' => $post->getRawOriginal('title')],
               'excerpt' => ['en' => $post->getRawOriginal('excerpt')],
           ]);
       }
   });
   ```

6. **Clear Caches:**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan permission:cache-reset
   ```

## Support

For issues or questions:
1. Check this documentation
2. Review test files for usage examples
3. Check Laravel and Spatie Permission documentation
4. Review application logs

## Conclusion

Phase 4 implementation is complete with:

✅ Multi-language content support with flexible translation system
✅ Advanced RBAC with 3 roles and 9 permissions
✅ Complete approval workflow with notifications
✅ Full audit trail with revision history
✅ 20 comprehensive tests (all passing)
✅ Production-ready with database migrations and seeders

The system follows Laravel and Vue.js best practices and is ready for production deployment.
