# Global Loading Indicator

## Overview

A beautiful, centralized loading indicator that automatically tracks API requests and provides visual feedback to users during data fetching operations.

## Features

✅ **Automatic tracking** - All API requests show the loader by default  
✅ **Smart exclusions** - CSRF and public config endpoints skip the loader  
✅ **Request counting** - Multiple simultaneous requests handled gracefully  
✅ **Beautiful UI** - Modern, animated 3-ring spinner with backdrop blur  
✅ **Manual control** - Optional manual loading control for custom operations  

## Architecture

### 1. Loading Store (`stores/loading.js`)
Centralized Pinia store that manages loading state:
- Tracks active request count
- Provides start/finish loading actions
- Single source of truth for loading state

### 2. Global Loader Component (`components/shared/GlobalLoader.vue`)
Visual component that displays when loading:
- Fixed position overlay with backdrop blur
- Animated 3-ring spinner (blue gradient)
- Smooth fade in/out transitions
- Z-index 9999 to appear above all content

### 3. HTTP Interceptors (`services/http.js`)
Automatic request/response tracking:
- **Request interceptor**: Increments loading counter
- **Response interceptor**: Decrements loading counter
- **Error interceptor**: Always decrements (cleanup)
- **Smart skipping**: Certain endpoints bypassed automatically

### 4. Composable Helper (`composables/useLoader.js`)
Manual control utilities when needed

## Usage

### Automatic (Default)

All API requests automatically show the loader:

```javascript
// In any component or store
import { api } from '@/services/http';

// Loader shows automatically
await api.get('/admin/products');
await api.post('/admin/brands', data);
```

### Skip Loader for Specific Requests

```javascript
// Background polling or non-critical requests
await api.get('/admin/stats', { skipLoader: true });
```

### Manual Control

```javascript
import { useLoader } from '@/composables/useLoader';

const { showLoader, hideLoader, withLoader } = useLoader();

// Manual show/hide
showLoader();
await someOperation();
hideLoader();

// Or use wrapper
await withLoader(async () => {
    await someOperation();
});
```

## Automatically Excluded Endpoints

The following endpoints skip the loader by default:
- `/sanctum/csrf-cookie` - CSRF initialization
- `/config/*` - Public configuration endpoints

## Customization

### Modify Excluded Patterns

Edit `services/http.js`:

```javascript
const skipLoader = config.skipLoader || 
    config.url?.includes('/sanctum/csrf-cookie') ||
    config.url?.includes('/config/') ||
    config.url?.includes('/your-pattern/'); // Add your pattern
```

### Customize Loader Appearance

Edit `components/shared/GlobalLoader.vue`:
- Colors: Change `border-top-color` in `.spinner-ring`
- Speed: Adjust `animation` duration
- Size: Modify `.loader-spinner` dimensions
- Background: Update `.loader-overlay` styles

### Example: Change to Purple Theme

```css
.spinner-ring {
    border-top-color: #a855f7; /* purple-500 */
}

.spinner-ring:nth-child(2) {
    border-top-color: #c084fc; /* purple-400 */
}

.spinner-ring:nth-child(3) {
    border-top-color: #e9d5ff; /* purple-200 */
}
```

## Implementation Details

### Request Counting

The store uses a counter to handle multiple simultaneous requests:

```
Request 1 starts → counter: 1 → loading: true
Request 2 starts → counter: 2 → loading: true
Request 1 ends   → counter: 1 → loading: true
Request 2 ends   → counter: 0 → loading: false
```

### Error Handling

The loader always hides, even on errors:
```javascript
api.interceptors.response.use(
    // Success - hide loader
    // Error - also hide loader (in catch block)
);
```

## Testing

### Manual Test

1. Open admin panel
2. Navigate to any data table (brands, products, etc.)
3. Loader should appear briefly while fetching
4. Loader should disappear when data loads

### Network Throttling Test

1. Open Chrome DevTools → Network tab
2. Enable "Slow 3G" throttling
3. Navigate between admin pages
4. Observe loader behavior with slow requests

## Performance Considerations

- **Zero runtime overhead** when not loading
- **Minimal DOM impact** - Single component in DOM
- **CSS-only animations** - No JavaScript animation loops
- **Proper cleanup** - Always decrements counter, prevents leaks

## Future Enhancements

Potential improvements:
- [ ] Progress bar instead of spinner
- [ ] Customizable delay before showing (skip for fast requests)
- [ ] Per-page loading indicators
- [ ] Loading text customization
- [ ] Skeleton screens for specific components

