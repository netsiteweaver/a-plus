# Brand Logo Upload

## Overview

Brands can now have logos uploaded and managed directly through the admin panel. Logos are stored in local storage and can be easily uploaded, replaced, or deleted.

## Features

✅ **Drag & Drop Upload** - Drag images directly to the upload area  
✅ **Click to Upload** - Traditional file picker interface  
✅ **Live Preview** - See uploaded images immediately  
✅ **Replace & Delete** - Easy management of brand logos  
✅ **Validation** - File type and size validation (max 2MB)  
✅ **Multiple Formats** - Supports JPG, PNG, GIF, SVG, WEBP  
✅ **Visual Table Display** - Logo thumbnails shown in brand list  

## Implementation

### Backend

#### 1. Routes (`routes/api.php`)

```php
// Upload logo
POST /api/admin/brands/{brand}/logo

// Delete logo
DELETE /api/admin/brands/{brand}/logo
```

#### 2. Controller Methods (`BrandController.php`)

**Upload Logo:**
- Validates file (image, max 2MB)
- Deletes old logo if exists
- Stores file in `storage/app/public/brands/logos/`
- Updates brand record with new URL

**Delete Logo:**
- Removes file from storage
- Sets `logo_url` to null in database
- Returns updated brand data

#### 3. Storage Configuration

- **Disk**: `public`
- **Path**: `storage/app/public/brands/logos/`
- **URL**: `/storage/brands/logos/{filename}`
- **Symlink**: `public/storage` → `storage/app/public`

### Frontend

#### 1. ImageUpload Component (`components/admin/ImageUpload.vue`)

Reusable image upload component with:
- **Preview Area** - Shows current/uploaded image
- **Upload Button** - Click or drag & drop
- **Action Buttons** - Change and delete options
- **Progress Indicator** - Animated spinner during upload
- **Error Handling** - Display validation errors
- **Customizable** - Props for label, help text, validation

#### 2. Brand Modal Integration

**For Existing Brands:**
- Upload triggers immediately on file selection
- Logo updates without closing modal
- Can replace logo multiple times

**For New Brands:**
- Logo file is held in memory
- Uploaded after brand creation
- Seamless user experience

#### 3. Brand List Table

- **Logo Column** - Displays 48x48px thumbnail
- **Placeholder** - Shows icon when no logo
- **Border & Shadow** - Professional appearance
- **Object-fit contain** - Maintains aspect ratio

## Usage

### Upload Logo for New Brand

1. Click "New brand" button
2. Fill in brand details
3. Click or drag image to upload area
4. See live preview
5. Click "Save brand"
6. Logo uploads automatically after brand creation

### Upload Logo for Existing Brand

1. Click "Edit" on a brand
2. Click or drag image to upload area
3. Logo uploads immediately
4. See updated preview
5. Close modal or continue editing

### Replace Logo

1. Edit brand with existing logo
2. Hover over logo preview
3. Click "Change image" button
4. Select new file
5. Old logo automatically deleted
6. New logo uploads

### Delete Logo

1. Edit brand with logo
2. Hover over logo preview
3. Click "Remove image" (X) button
4. Confirm deletion
5. Logo removed from storage and database

## File Validation

### Accepted Formats
- JPEG/JPG
- PNG
- GIF
- SVG
- WEBP

### Size Limit
- Maximum: 2MB (2048KB)
- Validation on both frontend and backend

### Filename Format
- Pattern: `{timestamp}_{uniqid}.{extension}`
- Example: `1699564321_64fa2b1c4e8f9.jpg`
- Prevents naming conflicts

## Storage Management

### Directory Structure
```
storage/
  app/
    public/
      brands/
        logos/
          1699564321_64fa2b1c4e8f9.jpg
          1699564322_64fa2b1c4e900.png
          ...
```

### Public Access
```
public/
  storage/ → symlink to storage/app/public
```

### URL Pattern
```
http://localhost:8000/storage/brands/logos/{filename}
```

## Error Handling

### Frontend Validation
- File type check before upload
- File size check before upload
- User-friendly error messages
- Prevents invalid uploads

### Backend Validation
- Request validation using `UploadBrandLogoRequest`
- Detailed error messages
- Automatic cleanup on failure

### Common Errors

**"Invalid file type"**
- Solution: Use JPG, PNG, GIF, SVG, or WEBP

**"File size must not exceed 2MB"**
- Solution: Compress or resize image

**"Failed to upload logo"**
- Check storage permissions
- Verify storage symlink exists
- Check disk space

## API Response Examples

### Successful Upload

```json
{
  "message": "Logo uploaded successfully",
  "logo_url": "http://localhost:8000/storage/brands/logos/1699564321_64fa2b1c4e8f9.jpg",
  "brand": {
    "id": 1,
    "name": "Nike",
    "slug": "nike",
    "logo_url": "http://localhost:8000/storage/brands/logos/1699564321_64fa2b1c4e8f9.jpg",
    ...
  }
}
```

### Successful Delete

```json
{
  "message": "Logo deleted successfully",
  "brand": {
    "id": 1,
    "name": "Nike",
    "slug": "nike",
    "logo_url": null,
    ...
  }
}
```

### Validation Error

```json
{
  "message": "The logo must be a file of type: jpeg, jpg, png, gif, svg, webp.",
  "errors": {
    "logo": [
      "The logo must be a file of type: jpeg, jpg, png, gif, svg, webp."
    ]
  }
}
```

## Component Props (ImageUpload.vue)

```javascript
{
  modelValue: String,        // Current image URL
  label: String,            // Label text
  required: Boolean,        // Show required indicator
  accept: String,           // Accepted MIME types
  acceptText: String,       // Help text for accepted formats
  helpText: String,         // Additional help text
  allowDelete: Boolean,     // Show delete button
  maxSize: Number          // Max file size in KB (default 2048)
}
```

## Component Events

```javascript
@upload="handleUpload"    // Emitted with File object
@delete="handleDelete"    // Emitted when delete clicked
@error="handleError"      // Emitted on validation error
```

## Security Considerations

1. **File Validation** - Both frontend and backend checks
2. **Size Limits** - Prevents large file uploads
3. **Type Restrictions** - Only image files allowed
4. **Unique Filenames** - Prevents overwrites
5. **Permission Checks** - Requires `catalog.manage` permission
6. **Storage Isolation** - Public storage separate from private

## Future Enhancements

Potential improvements:
- [ ] Image cropping tool
- [ ] Automatic thumbnail generation
- [ ] CDN integration
- [ ] Image optimization/compression
- [ ] Multiple logo variants (light/dark mode)
- [ ] Bulk upload for multiple brands
- [ ] Logo URL import from external sources

## Reusability

The `ImageUpload.vue` component is designed to be reusable:

```vue
<ImageUpload
  v-model="categoryImage"
  label="Category Image"
  @upload="handleCategoryImageUpload"
  @delete="handleCategoryImageDelete"
/>
```

Can be used for:
- Category images
- Product images
- User avatars
- Banner images
- Any image upload need

